<?php
/**
 * class IndexController
 */
 
class IndexController extends Front_Model_Controller
{
    const PER_PAGE = 50;
    
    /**
     * preDispatch
     *
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        // レイアウト
		$this->view->bodyLayoutName   = 'one_column.phtml';
		$this->view->menuCategory     = 'index';
		$this->view->menu             = 'index';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /index                                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 管理メニュー                                               |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
	    $this->_helper->layout->setLayout('default_flex');
	    $this->view->bodyLayoutName = 'one_column.phtml';
	    
		$request = $this->getRequest();
		
		$loginTable = new Shared_Model_Data_UserLogin();
		
		// var_dump($this->_adminProperty);
		// exit();
		
		// ログインログ
		$this->view->logList = $loginTable->getLatestWithUserId($this->_adminProperty['id']);
		
		// 全ログインログ
    	if (!empty($this->_adminProperty['is_master'])) {
			$selectObj = $loginTable->select();
			$selectObj->order('id DESC');
	        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
	        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
			$paginator->setCurrentPageNumber(1);
			
			$items = array();
	        
			foreach ($paginator->getCurrentItems() as $eachItem) {
				$items[] = $eachItem; 
			}
	
	        $this->view->allLogItems = $items;
	        $this->view->pager($paginator);
		}
		

		$userTable = new Shared_Model_Data_User();
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_management_group', 'frs_user.management_group_id = frs_management_group.id', array(
			$userTable->aesdecrypt('organization_name', false) . 'AS organization_name',
			'group_header_color',
		));
		
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array(
			$userTable->aesdecrypt('department_name', false) . 'AS department_name',
			$userTable->aesdecrypt('department_name_en', false) . 'AS department_name_en',
		));
		$selectObj->where('frs_user.parent_user_id != 0');
		$selectObj->where('frs_user.id = ' . $this->_adminProperty['parent_user_id'] . ' OR frs_user.parent_user_id = ' . $this->_adminProperty['parent_user_id']);
		$selectObj->order('frs_user.id ASC');
		
		$accountItems = $selectObj->query()->fetchAll();
		
		
		// 承認待ち件数
		$approvalTable = new Shared_Model_Data_Approval();
		$approvalItems = array();
        
		foreach ($accountItems as $eachItem) {
			$eachData = $eachItem;
			$eachData['count'] = $approvalTable->getPendingCount($eachItem['id']);
			
            $approvalItems[] = $eachData;
		}
		$this->view->approvalItems = $approvalItems;
		

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		// 倉庫リスト
		$warehouseTable = new Shared_Model_Data_Warehouse();
		$this->view->warehouseList = $warehouseTable->getActiveList($this->_adminProperty['management_group_id'], false);

		// goosa連携
		$clientDataGoosa  = array();
		$clientDataGOOSCA = array();
		
		if (APPLICATION_DOMAIN === 'localhost') {
			$clientDataGoosa = array(
				'management_web_use_basic_auth' => true,
				'management_web_basic_user' => 'goosa',
				'management_web_basic_pass' => 'goosa',
			);
			
			$clientDataGOOSCA = array(
				'management_web_use_basic_auth' => true,
				'management_web_basic_user' => 'goosca',
				'management_web_basic_pass' => 'goosca',
			);
		}

		$apiResult = Shared_Model_Gs_SupplierSetting::getApprovalCount($clientDataGoosa);
		$this->view->goosaEstimateApprovalCoount    = $apiResult['data']['estimate_approval_count'];
		$this->view->goosaItemPublishApprovalCoount = $apiResult['data']['item_publish_approval_count'];
		$this->view->goosaServiceApprovalCoount     = $apiResult['data']['service_approval_count'];
		$this->view->goosaSalesApprovalCoount       = $apiResult['data']['sales_approval_count'];
		$this->view->goosaFinalApprovalCoount       = $apiResult['data']['final_approval_count'];
		$this->view->goosaBuyerApplyCoount          = $apiResult['data']['buyer_apply_count'];
		$this->view->goosaSupplierApplyCoount       = $apiResult['data']['supplier_apply_count'];

		// GOOSCA連携
		$apiResult = Shared_Model_Gcs_SupplierSetting::getApprovalCount($clientDataGOOSCA);
		$this->view->gooscaItemPublishApprovalCoount = $apiResult['data']['item_publish_approval_count'];
		$this->view->gooscaServiceApprovalCoount     = $apiResult['data']['service_approval_count'];
		$this->view->gooscaSalesApprovalCoount       = $apiResult['data']['sales_approval_count'];
		$this->view->gooscaFinalApprovalCoount       = $apiResult['data']['final_approval_count'];

        // GMO契約アカウントリスト
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$this->view->gmoAccountList = $gmoTable->getList();
		
        
        $bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $receivableTable      = new Shared_Model_Data_AccountReceivable();
        $payableTable         = new Shared_Model_Data_AccountPayable();
        
        // GMO承認期限切れ
        $this->view->expiredList = $payableTable->getListExpired();

        // goosa入金未割当
        //$selectObj = $bankHistoryItemTable->select();
        //$selectObj->where('bank_id IN (?)', array());
        //$this->view->goosaNoAttachedItems = $selectObj->query()->fetchAll();
		
		// goosa未入金
        $selectObj = $receivableTable->select(array('COUNT(id) AS item_count','sum(total_amount) AS total'));
        $selectObj->where('frs_account_receivable.management_group_id = 2');
        $selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_SITE_DATA);
		$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);      
    	$selectObj->where('frs_account_receivable.payment_status = ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED);
        //var_dump($selectObj->__toString());
        $this->view->goosaReceivable= $selectObj->query()->fetch();

        // goosa支払予定
        $selectObj = $payableTable->select(array('paying_plan_date','COUNT(id) AS item_count','sum(total_amount) AS total'));
        $selectObj->where('frs_account_payable.management_group_id = 2');
        $selectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_SITE_DATA);
		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
		            . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
    	$selectObj->where('frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
        $selectObj->group('paying_plan_date');
        $this->view->goosaPayableItems = $selectObj->query()->fetchAll();
        
        // GOOSCA入金予定
        $this->view->gooscaPayableCount   = 0;
        $selectObj = $payableTable->select(array('paying_plan_date','COUNT(id) AS item_count','sum(total_amount) AS total'));
        $selectObj->where('frs_account_payable.management_group_id = 3');
        $selectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_SITE_DATA);
		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
		            . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
    	$selectObj->where('frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
        $selectObj->group('paying_plan_date');
        $this->view->gooscaPayableItems = $selectObj->query()->fetchAll();       
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /test                                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 管理メニュー                                               |
    +----------------------------------------------------------------------------*/
    public function testAction()
    {
    	$request = $this->getRequest();
    	$bankId = $request->getParam('bank_id');
    	
		$bankHistoryTable     = new Shared_Model_Data_AccountBankHistory();
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		
	    $latestHistory = $bankHistoryTable->latestHistoryOfBank($bankId);
	    var_dump($latestHistory);
	    
	    if (!empty($latestHistory)) {
	        $latestHistoryItem = $bankHistoryItemTable->lastRowOfHistory($latestHistory['id']);
	        var_dump($latestHistoryItem);
	    }
	    
	    
	    exit;
    }
    
}

