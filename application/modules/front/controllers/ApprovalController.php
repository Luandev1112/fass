<?php
/**
 * class ApprovalController
 */
 
class ApprovalController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '承認・確認';
		$this->view->menuCategory     = 'approval';
		
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /approval/list-debug                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認・確認待ちリスト(debug)                                |
    +----------------------------------------------------------------------------*/
    public function listDebugAction()
    {
	    $this->view->menu = 'pending';
	    
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$approvalTable = new Shared_Model_Data_Approval();
		
        $selectObj = $approvalTable->select();
        $selectObj->joinLeft('frs_user', 'frs_approval.applicant_user_id = frs_user.id', array('display_id', $approvalTable->aesdecrypt('user_name', false) . 'AS applicant_user_name'));   
		$selectObj->where('frs_approval.authorizer_user_id = ?', 3);
		$selectObj->where('frs_approval.status = ?', Shared_Model_Code::APPROVAL_STATUS_PENDDING);
		$selectObj->order('frs_approval.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /approval/list-multi                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認・確認待ちリスト(マルチ版)                             |
    +----------------------------------------------------------------------------*/
    public function listMultiAction()
    {
		$userTable        = new Shared_Model_Data_User();
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_management_group', 'frs_user.management_group_id = frs_management_group.id', array(
			$userTable->aesdecrypt('organization_name', false) . 'AS organization_name',
		));
		
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array(
			$userTable->aesdecrypt('department_name', false) . 'AS department_name',
			$userTable->aesdecrypt('department_name_en', false) . 'AS department_name_en',
		));
		$selectObj->where('frs_user.parent_user_id != 0');
		$selectObj->where('frs_user.id = ' . $this->_adminProperty['parent_user_id'] . ' OR frs_user.parent_user_id = ' . $this->_adminProperty['parent_user_id']);
		$selectObj->order('frs_user.display_id ASC');
		
        $this->view->items = $selectObj->query()->fetchAll();

    }
      
            
    /*----------------------------------------------------------------------------+
    |  action_URL    * /approval/list                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認・確認待ちリスト                                       |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
	    $this->_helper->layout->setLayout('default_flex');
	    $this->view->bodyLayoutName = 'one_column.phtml';
	    
	    $this->view->menu = 'pending';
	    
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$approvalTable = new Shared_Model_Data_Approval();
		
        $selectObj = $approvalTable->select();
        $selectObj->joinLeft('frs_user', 'frs_approval.applicant_user_id = frs_user.id', array('display_id', $approvalTable->aesdecrypt('user_name', false) . 'AS applicant_user_name'));   
		$selectObj->where('frs_approval.authorizer_user_id = ?', $this->_adminProperty['id']);
		$selectObj->where('frs_approval.status = ?', Shared_Model_Code::APPROVAL_STATUS_PENDDING);
		$selectObj->order('frs_approval.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /approval/history-list                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 全履歴リスト                                               |
    +----------------------------------------------------------------------------*/
    public function historyListAction()
    {
	    $this->view->menu = 'history';
	    
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		// 検索条件
		$conditions = array();
		$conditions['status']              = $request->getParam('status', '');
		$conditions['type']                = $request->getParam('type', '');
		$conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
		$conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
		$this->view->conditions            = $conditions;
		
		$approvalTable = new Shared_Model_Data_Approval();
		
		$dbAdapter = $approvalTable->getAdapter();

        $selectObj = $approvalTable->select();
        $selectObj->joinLeft('frs_user', 'frs_approval.applicant_user_id = frs_user.id', array('display_id', $approvalTable->aesdecrypt('user_name', false) . 'AS applicant_user_name'));   
		$selectObj->where('frs_approval.authorizer_user_id = ?', $this->_adminProperty['id']);

		if (!empty($conditions['status'])) {
			$selectObj->where('frs_approval.status = ?', $conditions['status']);
		}

		if (!empty($conditions['type'])) {
			$selectObj->where('frs_approval.type = ?', $conditions['type']);
		}

		if (!empty($conditions['applicant_user_id'])) {
			$selectObj->where('frs_approval.applicant_user_id = ?', $conditions['applicant_user_id']);
		}
		

		$selectObj->order('frs_approval.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
    }
    
}

