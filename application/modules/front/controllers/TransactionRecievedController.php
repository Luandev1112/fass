<?php
/**
 * class TransactionRecievedController
 */
 
class TransactionRecievedController extends Front_Model_Controller
{
    const PER_PAGE = 100;
    
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
		$this->view->mainCategoryName = '取引処理';
		$this->view->menuCategory     = 'transaction';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
		$this->view->menu = 'received';
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/mod                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * データ修正(develop)                                        |
    +----------------------------------------------------------------------------*/
    public function modAction()
    {
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		
		$receivableTable->updateById('207', array(
			'total_amount' => '335340',
		));
		
		echo 'OK';
		exit;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/update-date                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求日コピー(develop)                                      |
    +----------------------------------------------------------------------------*/
    /*
    public function updateDateAction()
    {
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$invoiceTable  = new Shared_Model_Data_Invoice();
		
		$selectObj = $receivableTable->select();
		$items = $selectObj->query()->fetchAll();
		foreach ($items as $each) {
			if (!empty($each['invoice_id'])) {
		    	// 請求書情報
		    	
		    	$invoiceData = $invoiceTable->getById($this->_adminProperty['management_group_id'], $each['invoice_id']);

				$receivableTable->updateById($each['id'], array(
					'accrual_date' => $invoiceData['invoice_date'],
				));
			}
			
		}
		exit;
	}
	*/

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/delete                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(管理権限あり)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$receivableTable  = new Shared_Model_Data_AccountReceivable();

			try {
				$receivableTable->getAdapter()->beginTransaction();
				
				$receivableTable->updateById($id, array(
					'status' => Shared_Model_Code::RECEIVABLE_STATUS_DELETED,
				));
			
                // commit
                $receivableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/debug                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認                                               |
    +----------------------------------------------------------------------------*/
    public function debugAction()
    {
		$receivableTable  = new Shared_Model_Data_AccountReceivable();

		$selectObj = $receivableTable->select();
	    $this->view->items = $selectObj;
	}
		
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/list                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認                                               |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_recieved_list_4');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$viewType = $request->getParam('view_type');
		if (!empty($viewType)) {
			$session->conditions['view_type'] = $viewType;
			$session->conditions['year']      = $request->getParam('year', date('Y'));
			$session->conditions['month']     = $request->getParam('month', date('m'));
			
		} else if (empty($session->conditions) || !array_key_exists('view_type', $session->conditions)) {
			$session->conditions['view_type'] = 'history';
			$session->conditions['year']      = date('Y');
			$session->conditions['month']     = date('m');
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['payment_status']      = $request->getParam('payment_status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['invoice_id']          = $request->getParam('invoice_id', '');
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['payment_status']      = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['invoice_id']       = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		$this->view->viewType = $conditions['view_type'];

		
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$dbAdapter = $receivableTable->getAdapter();
		
		$selectObj = $receivableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->joinLeft('frs_user', 'frs_account_receivable.created_user_id = frs_user.id',array($receivableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
		//$selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_INVOICE);
		
		$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
		
		
        // グループID
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		if ($session->conditions['payment_status'] !== '') {
			$selectObj->where('frs_account_receivable.payment_status = ?', $session->conditions['payment_status']);
		}
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_receivable.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_receivable.account_title_id = ?', $session->conditions['account_title_id']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_receivable.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_receivable.target_connection_id = ?', $session->conditions['connection_id']);
		}
		
		if ($session->conditions['invoice_id'] !== '') {
			$selectObj->where('frs_account_receivable.relational_display_id = ? OR frs_invoice.display_id = ?', $session->conditions['invoice_id']);
			var_dump($selectObj->__toString());
		}
		
		
		$unrecievedSelectObj = $receivableTable->select();
		$unrecievedSelectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$unrecievedSelectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
		//$unrecievedSelectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_INVOICE);
		
		$selectObj->where('frs_account_receivable.type != ?', Shared_Model_Code::RECEIVABLE_TYPE_SITE_DATA);
		
        // グループID
        $unrecievedSelectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $unrecievedSelectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
		$unrecievedSelectObj->where('frs_account_receivable.payment_status = ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED);


		$items = array(); 
		
		if ($conditions['view_type'] === 'monthly') {
			// 月別
			$nDate = new Nutex_Date();
        	$from = $conditions['year'] . '-' . $conditions['month'] . '-01';
       		$to   = $conditions['year'] . '-' . $conditions['month'] . '-' . $nDate->getMonthEndDay($conditions['year'], $conditions['month']);
			$selectObj->where('frs_account_receivable.receive_plan_date >= ?', $from);
			$selectObj->where('frs_account_receivable.receive_plan_date <= ?', $to);
			$selectObj->order('frs_account_receivable.receive_plan_date ASC');
			$selectObj->order('frs_account_receivable.id ASC');
			
			$unrecievedSelectObj->where('frs_account_receivable.receive_plan_date >= ?', $from);
			$unrecievedSelectObj->where('frs_account_receivable.receive_plan_date <= ?', $to);
			
			$zDate = new Zend_Date($conditions['year'] . '-' . $conditions['month'] . '-01', NULL, 'ja_JP');
			
			$zDate->sub('1', Zend_Date::MONTH);
			$conditionsPrev          = $conditions;
			$conditionsPrev['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsPrev['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->prevUrl = '/transaction-recieved/list?' . http_build_query($conditionsPrev);
			
			$zDate->add('2', Zend_Date::MONTH);
			$conditionsNext          = $conditions;
			$conditionsNext['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsNext['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->nextUrl = '/transaction-recieved/list?' . http_build_query($conditionsNext);
		
			$this->view->items = $items = $selectObj->query()->fetchAll();
			
		} else {
			// 全一覧
			$selectObj->order('frs_account_receivable.receive_plan_date DESC');
			$selectObj->order('frs_account_receivable.id DESC');
			
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
		
		
		$total = array();
		$total['total_count'] = 0;
				
		$unrecievedTotal = array();
		$unrecievedTotal['total_count'] = 0;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        	
        	$total[$each['id']] = $each;
        	$total[$each['id']]['item_count'] = 0;
        	$total[$each['id']]['total'] = 0;
        	
        	$unrecievedTotal[$each['id']] = $each;
        	$unrecievedTotal[$each['id']]['item_count'] = 0;
        	$unrecievedTotal[$each['id']]['total'] = 0;
        }
		$this->view->currencyList = $currencyList;

		// 未払合計(通貨毎)
		$unrecievedList = $unrecievedSelectObj->query()->fetchAll();
		foreach ($unrecievedList as $eachItemUnrecieved) {
			$unrecievedTotal[$eachItemUnrecieved['currency_id']]['item_count'] += 1;
			$unrecievedTotal[$eachItemUnrecieved['currency_id']]['total'] += (int)$eachItemUnrecieved['total_amount'];
			$unrecievedTotal['total_count'] += 1;
		}
		$this->view->unrecievedTotal = $unrecievedTotal;
		
		// 月合計
		if ($conditions['view_type'] === 'monthly') {
			foreach ($items as $eachItem) {
				$total[$eachItem['currency_id']]['item_count'] += 1;
				$total[$eachItem['currency_id']]['total'] += (int)$eachItem['total_amount'];
				$total['total_count'] += 1;
			}
		}
		$this->view->total = $total;


		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList   = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;
        
		// 採算コード
		$groupTable = new Shared_Model_Data_AccountTotalingGroup();
		$groupList = array();
		$groupItems = $groupTable->getAllList($this->_adminProperty['management_group_id']);
		
        foreach ($groupItems as $each) {
        	$groupList[$each['id']] = $each['title'] . '(' . $each['category_name'] . ')';
        }
		$this->view->groupList = $groupList;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/site-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サイト連動入金完了確認                                     |
    +----------------------------------------------------------------------------*/
    public function siteListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_recieved_site_list_1');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$viewType = $request->getParam('view_type');
		if (!empty($viewType)) {
			$session->conditions['view_type'] = $viewType;
			$session->conditions['year']      = $request->getParam('year', date('Y'));
			$session->conditions['month']     = $request->getParam('month', date('m'));
			
		} else if (empty($session->conditions) || !array_key_exists('view_type', $session->conditions)) {
			$session->conditions['view_type'] = 'history';
			$session->conditions['year']      = date('Y');
			$session->conditions['month']     = date('m');
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['payment_status']      = $request->getParam('payment_status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['invoice_id']          = $request->getParam('invoice_id', '');
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['payment_status']      = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['invoice_id']       = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		$this->view->viewType = $conditions['view_type'];

		
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$dbAdapter = $receivableTable->getAdapter();
		
		$selectObj = $receivableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->joinLeft('frs_user', 'frs_account_receivable.created_user_id = frs_user.id',array($receivableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
		//$selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_INVOICE);
		
		$selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_SITE_DATA);
		
		$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
		
		
        // グループID
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		if ($session->conditions['payment_status'] !== '') {
			$selectObj->where('frs_account_receivable.payment_status = ?', $session->conditions['payment_status']);
		}
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_receivable.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_receivable.account_title_id = ?', $session->conditions['account_title_id']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_receivable.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_receivable.target_connection_id = ?', $session->conditions['connection_id']);
		}
		
		if ($session->conditions['invoice_id'] !== '') {
			$selectObj->where('frs_account_receivable.relational_display_id = ? OR frs_invoice.display_id = ?', $session->conditions['invoice_id']);
			var_dump($selectObj->__toString());
		}
		
		
		$unrecievedSelectObj = $receivableTable->select();
		$unrecievedSelectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$unrecievedSelectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
		//$unrecievedSelectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_INVOICE);

        // グループID
        $unrecievedSelectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $unrecievedSelectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
		$unrecievedSelectObj->where('frs_account_receivable.payment_status = ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED);


		$items = array(); 
		
		if ($conditions['view_type'] === 'monthly') {
			// 月別
			$nDate = new Nutex_Date();
        	$from = $conditions['year'] . '-' . $conditions['month'] . '-01';
       		$to   = $conditions['year'] . '-' . $conditions['month'] . '-' . $nDate->getMonthEndDay($conditions['year'], $conditions['month']);
			$selectObj->where('frs_account_receivable.receive_plan_date >= ?', $from);
			$selectObj->where('frs_account_receivable.receive_plan_date <= ?', $to);
			$selectObj->order('frs_account_receivable.receive_plan_date ASC');
			$selectObj->order('frs_account_receivable.id ASC');
			
			$unrecievedSelectObj->where('frs_account_receivable.receive_plan_date >= ?', $from);
			$unrecievedSelectObj->where('frs_account_receivable.receive_plan_date <= ?', $to);
			
			$zDate = new Zend_Date($conditions['year'] . '-' . $conditions['month'] . '-01', NULL, 'ja_JP');
			
			$zDate->sub('1', Zend_Date::MONTH);
			$conditionsPrev          = $conditions;
			$conditionsPrev['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsPrev['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->prevUrl = '/transaction-recieved/site-list?' . http_build_query($conditionsPrev);
			
			$zDate->add('2', Zend_Date::MONTH);
			$conditionsNext          = $conditions;
			$conditionsNext['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsNext['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->nextUrl = '/transaction-recieved/site-list?' . http_build_query($conditionsNext);
		
			$this->view->items = $items = $selectObj->query()->fetchAll();
			
		} else {
			// 全一覧
			$selectObj->order('frs_account_receivable.receive_plan_date DESC');
			$selectObj->order('frs_account_receivable.id DESC');
			
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
		
		
		$total = array();
		$total['total_count'] = 0;
				
		$unrecievedTotal = array();
		$unrecievedTotal['total_count'] = 0;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        	
        	$total[$each['id']] = $each;
        	$total[$each['id']]['item_count'] = 0;
        	$total[$each['id']]['total'] = 0;
        	
        	$unrecievedTotal[$each['id']] = $each;
        	$unrecievedTotal[$each['id']]['item_count'] = 0;
        	$unrecievedTotal[$each['id']]['total'] = 0;
        }
		$this->view->currencyList = $currencyList;

		// 未払合計(通貨毎)
		$unrecievedList = $unrecievedSelectObj->query()->fetchAll();
		foreach ($unrecievedList as $eachItemUnrecieved) {
			$unrecievedTotal[$eachItemUnrecieved['currency_id']]['item_count'] += 1;
			$unrecievedTotal[$eachItemUnrecieved['currency_id']]['total'] += (int)$eachItemUnrecieved['total_amount'];
			$unrecievedTotal['total_count'] += 1;
		}
		$this->view->unrecievedTotal = $unrecievedTotal;
		
		// 月合計
		if ($conditions['view_type'] === 'monthly') {
			foreach ($items as $eachItem) {
				$total[$eachItem['currency_id']]['item_count'] += 1;
				$total[$eachItem['currency_id']]['total'] += (int)$eachItem['total_amount'];
				$total['total_count'] += 1;
			}
		}
		$this->view->total = $total;


		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList   = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;
        
		// 採算コード
		$groupTable = new Shared_Model_Data_AccountTotalingGroup();
		$groupList = array();
		$groupItems = $groupTable->getAllList($this->_adminProperty['management_group_id']);
		
        foreach ($groupItems as $each) {
        	$groupList[$each['id']] = $each['title'] . '(' . $each['category_name'] . ')';
        }
		$this->view->groupList = $groupList;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/detail                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$this->view->data = $data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/transaction-recieved/list';
		}
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		$userTable                = new Shared_Model_Data_User();
    	$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		
		/*
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		*/

		// 銀行口座
		if (!empty($data['bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['bank_id']);
			
			// 割当情報
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			$this->view->historyItems = $historyItemData = $bankHistoryItemTable->getListByReceivableId($id);
			//var_dump($historyItemData);exit;
		}
		
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/site-detail                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function siteDetailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$this->view->data = $data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/transaction-recieved/site-list';
		}
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		$userTable                = new Shared_Model_Data_User();
    	$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		
		/*
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		*/

		// 銀行口座
		if (!empty($data['bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['bank_id']);
			
			// 割当情報
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			$this->view->historyItems = $historyItemData = $bankHistoryItemTable->getListByReceivableId($id);
			//var_dump($historyItemData);exit;
		}
		
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/update-basic                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認 - 基本情報更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function updateBasicAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                if (!empty($errorMessage['account_title_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
                } else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				if (empty($success['account_totaling_group_id'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                }
                
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $receivableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'account_title_id'           => $success['account_title_id'],             // 会計科目ID
						'memo'                       => $success['memo'],                         // 摘要
						'account_totaling_group_id'  => $success['account_totaling_group_id'],    // 採算コードID
					);
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/update-summary                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認 - 概要更新(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function updateSummaryAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                if (!empty($errorMessage['accrual_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「発生日」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {                
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $receivableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'accrual_date'           => $success['accrual_date'],             // 発生日
					);
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/update-summary transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/update-payment                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金完了確認 - 入金状況更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function updatePaymentAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['bank_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金予定口座」を選択してください'));
                    return;
				} else if (!empty($errorMessage['payment_status']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金ステータス」を選択してください'));
                    return;
				} else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を選択してください'));
                    return;
				} else if (!empty($errorMessage['total_amount']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金額」を入力してください'));
                    return;
				} else if (!empty($errorMessage['currency_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「通貨」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $receivableTable->getAdapter()->beginTransaction();
            	
	            try {
		            // 入金済みの場合は入金完了日必須
		            if ($success['payment_status'] === (string) Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED) {
			            if (!empty($success['received_date'])) {
				            $this->sendJson(array('result' => 'NG', 'message' => '「入金予定」の場合は、「入金完了日」を空欄にしてください'));
							return;
				        }

		            } else if ($success['payment_status'] === (string) Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_RECEIVED) {
			            if (empty($success['received_date'])) {
				            $this->sendJson(array('result' => 'NG', 'message' => '「入金済み」の場合は、「入金完了日」を入力してください'));
							return;
				        }
				        
		            } else if ($success['payment_status'] === (string) Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_CANCELED) {
			            if (!empty($success['received_date'])) {
				            $this->sendJson(array('result' => 'NG', 'message' => '「キャンセル」の場合は、「入金完了日」を空欄にしてください'));
							return;
				        }
			            
		            }
		            
					if (!empty($oldData['relational_id'])) {
						// Goosa連携
						$clientData = array(
							'supplier_id'               => '8',
						);
						
						if (GS_DOMAIN !== 'goosa.net') {
							$clientData['management_web_use_basic_auth'] = true;
							$clientData['management_web_basic_user'] = 'goosa';
							$clientData['management_web_basic_pass'] = 'goosa';
						}
						
						// goosaに入金状況同期
						$data = array(
							'relational_id' => $oldData['relational_id'],
							'received_date' => $success['received_date'],
						);
						
						if ($success['payment_status'] === (string) Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED) {
							// 入金予定
							$result = Shared_Model_Gs_Account::updateToUnreceivedStatus($clientData, $data);
							
						} else if ($success['payment_status'] === (string) Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_RECEIVED) {
							// 入金済み
							$result = Shared_Model_Gs_Account::updateToReceivedStatus($clientData, $data);
						}
						
						if (empty($result['result'])) {
							$this->sendJson(array('result' => 'NG', 'message' => 'goosaへの接続に失敗しました'));
							return;
						}
					}
		            
					$data = array(
						'bank_id'              => $success['bank_id'],            // 入金予定口座
						'payment_status'       => $success['payment_status'],     // 入金ステータス
						'receive_plan_date'    => $success['receive_plan_date'],  // 入金予定日
						'received_date'        => NULL,                           // 入金完了日
						//'total_amount'         => $success['total_amount'],       // 入金予定額
						//'currency_id'          => $success['currency_id'],        // 通貨ID
					);
					
					if (!empty($success['received_date'])) {
						$data['received_date'] = $success['received_date'];      // 入金完了日
					}
					
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/update-payment transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
	

	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-list                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理                                               |
    +----------------------------------------------------------------------------*/
    public function templateListAction()
    {
	    $this->view->menu = 'receivable';
	    
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_recieved_template_list_2');
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['status']              = $request->getParam('status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['template_type']       = $request->getParam('template_type', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']              = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['template_type']       = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
		
		$dbAdapter = $receivableTemplateTable->getAdapter();
		
		// アクティブ
        $selectObj = $receivableTemplateTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_receivable_template.target_connection_id = frs_connection.id', array($receivableTemplateTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_receivable_template.created_user_id = frs_user.id',array($receivableTemplateTable->aesdecrypt('user_name', false) . 'AS user_name'));

        // グループID
        $selectObj->where('frs_account_receivable_template.management_group_id = ?', $this->_adminProperty['management_group_id']);

        if (!empty($session->conditions['status'])) {
        	if ($session->conditions['status'] === (string)Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_NOT_APPROVED) {
	        	$selectObj->where('frs_account_receivable_template.status != ' . Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_APPROVED
	        	           . ' AND frs_account_receivable_template.status != ' . Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_FINISHED
	        	           . ' AND frs_account_receivable_template.status != ' . Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_DELETED);
        	} else {
        		$selectObj->where('frs_account_receivable_template.status = ?', $session->conditions['status']);
        	}
        } else {
        	$selectObj->where('frs_account_receivable_template.status != ?', Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_DELETED);
        }
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_receivable_template.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_receivable_template.account_title_id = ?', $session->conditions['account_title_id']);
		}

		if ($session->conditions['template_type'] !== '') {
			$selectObj->where('frs_account_receivable_template.template_type = ?', $session->conditions['template_type']);
		}

		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_receivable_template.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_receivable_template.target_connection_id = ?', $session->conditions['connection_id']);
		}	
		
		$selectObj->where('frs_account_receivable_template.status NOT IN (?)', array(Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_DELETED, Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_FINISHED));
		$selectObj->order('frs_account_receivable_template.account_title_id ASC');
		$selectObj->order('frs_account_receivable_template.id ASC');

        $this->view->items = $selectObj->query()->fetchAll();
        
        // 毎月入金終了
		$selectObjF = $receivableTemplateTable->select();
        $selectObjF->where('frs_account_receivable_template.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		$selectObjF->where('frs_account_receivable_template.status = ?', Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_FINISHED);
		$this->view->itemsFinished = $selectObjF->query()->fetchAll();
		
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        }
		$this->view->currencyList = $currencyList;	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-update-to-draft             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 下書きに戻す(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function templateUpdateToDraftAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();

			try {
				$receivableTemplateTable->getAdapter()->beginTransaction();
				
				$receivableTemplateTable->updateById($id, array(
					'status' => Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_DRAFT,
				));
			
                // commit
                $receivableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/update-to-draft transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-finished                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払終了(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function templateFinishedAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();

			try {
				$receivableTemplateTable->getAdapter()->beginTransaction();
				
				$receivableTemplateTable->updateById($id, array(
					'status' => Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_FINISHED,
				));
			
                // commit
                $receivableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/template-finished transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-add                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 登録                                        |
    +----------------------------------------------------------------------------*/
    public function templateAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-add-post                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 登録(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function templateAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				
				if (!empty($errorMessage['template_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金種別」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を入力してください'));
                    return;
                } else if (!empty($errorMessage['bank_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金口座」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
	            
				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }

				$data = array(
			        'management_group_id'      => $this->_adminProperty['management_group_id'],
			        'status'                   => Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_DRAFT,
			        
			        'template_type'            => $success['template_type'],              // テンプレート種別
			        
					'account_title_id'         => $success['account_title_id'],           // 会計科目ID
					'account_totaling_group_id'=> $success['account_totaling_group_id'],  // 採算コード
					
					'target_connection_id'     => $success['target_connection_id'],       // 支払先
					
					'recieve_plan_monthly_day' => $success['recieve_plan_monthly_day'],   // 毎月入金時期
					'total_amount'             => NULL,               // 入金額
					'currency_id'              => 0,                // 通貨単位

					'description'              => $success['description'],                // 内容
					'other_memo'               => $success['other_memo'],                 // 備考

					'file_list'                => json_encode($fileList),                 // 請求書ファイルアップロード
					
					'recieve_plan_monthly_day' => $success['recieve_plan_monthly_day'],   // 毎月入金時期
					'bank_id'                  => $success['bank_id'],                    // 入金口座
					
					'created_user_id'          => $this->_adminProperty['id'],            // 申請者
					'approval_user_id'         => 0,                                      // 承認者
					
	                'created'                  => new Zend_Db_Expr('now()'),
	                'updated'                  => new Zend_Db_Expr('now()'),
				);
				
				// 新規登録	            
	            $receivableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$receivableTemplateTable->create($data);
					$id = $receivableTemplateTable->getLastInsertedId('id');
					
		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_ReceivableTemplate::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }

	                // commit
	                $receivableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-receivable/template-add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-detail                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function templateDetailAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
		$this->view->data = $data = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/transaction-recieved/template-list';
			$this->_helper->layout->setLayout('back_menu_competition');
	        
	        if ($data['status'] === (string)Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_DRAFT
	        || $data['status'] === (string)Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_MOD_REQUEST) {
	        	$this->view->saveUrl = 'javascript:void(0);';
	        }
		}  
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払元取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

		// 銀行口座
		if (!empty($data['bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['bank_id']);
		}
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-update-basic                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 基本情報更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function templateUpdateBasicAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['template_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金種別」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
                } else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				if (empty($success['account_totaling_group_id'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
				}
				
				$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
				$oldData = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

	            $receivableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'template_type'             => $success['template_type'],             // 入金種別
						'description'               => $success['description'],               // 内容
						'other_memo'                => $success['other_memo'],                // 備考
						'target_connection_id'      => $success['target_connection_id'],      // 支払元
						'account_title_id'          => $success['account_title_id'],          // 会計科目ID
						'account_totaling_group_id' => $success['account_totaling_group_id'], // 採算コード
					);
					
					$receivableTemplateTable->updateById($id, $data);

	                // commit
	                $receivableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/template-update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-update-payment              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 入金予定情報更新(Ajax)                      |
    +----------------------------------------------------------------------------*/
    public function templateUpdatePaymentAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['recieve_plan_monthly_day']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「毎月入金時期」を入力してください'));
                    return;
                } else if (!empty($errorMessage['bank_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金口座」を入力してください'));
                    return;  
                } if (!empty($errorMessage['total_amount']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金予定額(税込) 」を入力してください'));
                    return;
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を選択してください'));
                    return;  
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
	            
				$oldData = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $receivableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'recieve_plan_monthly_day' => $success['recieve_plan_monthly_day'],  // 毎月入金時期
						'bank_id'                  => $success['bank_id'],                   // 受取銀行口座
						
						'total_amount'             => $success['total_amount'],              // 支払額
						'currency_id'              => $success['currency_id'],               // 通貨単位
					);
					
					$receivableTemplateTable->updateById($id, $data);

	                // commit
	                $receivableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/template-update-payment transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-update-file-list            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 参考資料ファイルアップロード更新(Ajax)      |
    +----------------------------------------------------------------------------*/
    public function templateUpdateFileListAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
				
				$oldData = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
	            $receivableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'file_list' => json_encode($fileList), // 請求書ファイルアップロード
					);

					$receivableTemplateTable->updateById($id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_ReceivableTemplate::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $receivableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/template-update-file-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-apply-apploval              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 承認申請                                      |
    +----------------------------------------------------------------------------*/
    public function templateApplyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			// 申請者情報
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        	
			$oldData = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
			
			if (empty($oldData['template_type'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金種別」を選択してください'));
                return; 
			} else if (empty($oldData['account_title_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return;
			} else if (empty($oldData['account_totaling_group_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                return;
			} else if (empty($oldData['description'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                return;
			} else if (empty($oldData['target_connection_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を選択してください'));
                return;
			} else if (empty($oldData['total_amount'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金予定額(税込) 」を入力してください'));
                return;
			} else if (empty($oldData['currency_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を選択してください'));
                return;
			} else if (empty($oldData['recieve_plan_monthly_day'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「毎月入金時期」を入力してください'));
                return; 
			} else if (empty($oldData['bank_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金口座」を選択してください'));
                return; 
			}
            
    		// 会計科目
			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $oldData['account_title_id']);

			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $oldData['currency_id']);
			
			try {
				$receivableTemplateTable->getAdapter()->beginTransaction();
				
				$receivableTemplateTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_PENDING,
					'approval_user_id' => $userData['approver_c1_user_id'],
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_RECEIVABLE_TEMPLATE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $oldData['description'] . "\n" . "支払総額：" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				$approvalTable->create($approvalData);

				// メール送信 -------------------------------------------------------
				// 承認者
				$selectObj = $userTable->select();
		    	$selectObj->where('id = ?', $userData['approver_c1_user_id']);
		        $authorizerUserData = $selectObj->query()->fetch();
				
				$content = "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "内容：\n" . $oldData['description'];
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);

				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'managment_group_name' => $groupData['organization_name'],
					'to'       => $authorizerUserData['mail'],
					'cc'       => array(),
					'type'     => $approvalTypeList[$approvalData['type']],
					'content'  => $content,
					'name'     => $userData['user_name'],
					'user_id'  => $userData['display_id'],
				);		

				$mailer = new Shared_Model_Mail_Approval();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $receivableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/template-apply-apploval transaction faied: ' . $e);      
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-mod-request                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 修正依頼(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function templateModRequestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');
		
		
		// POST送信時
		if ($request->isPost()) {
			$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
			
    		// 会計科目
			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);

			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			try {
				$receivableTemplateTable->getAdapter()->beginTransaction();
				
				$receivableTemplateTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
				$content = "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "内容：\n" . $data['description'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-recieved/template-detail?id=' . $id;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $receivableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/template-mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-approve                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 承認(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function templateApproveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');

		
		// POST送信時
		if ($request->isPost()) {
			$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
			
    		// 会計科目
			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);

			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
			try {
				$receivableTemplateTable->getAdapter()->beginTransaction();
				
				$receivableTemplateTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_TEMPLATE_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
				$content = "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "内容：\n" . $data['description'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-recieved/template-detail?id=' . $id;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $receivableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $$receivableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/template-approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/template-history                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 入金履歴                                    |
    +----------------------------------------------------------------------------*/
    public function templateHistoryAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id = $request->getParam('id');
		$this->view->posTop      = $request->getParam('pos');
		$page = $request->getParam('page');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
		$this->view->data = $data = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->backUrl = '/transaction-recieved/template-list';
		$this->_helper->layout->setLayout('back_menu');

		$receivableTable = new Shared_Model_Data_AccountReceivable();
		
		$dbAdapter = $receivableTable->getAdapter();

        $selectObj = $receivableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_receivable.created_user_id = frs_user.id',array($receivableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->where('frs_account_receivable.template_id = ?', $id);
		$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
		$selectObj->order('frs_account_receivable.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        }
		$this->view->currencyList = $currencyList;	
    
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-add                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 新規入金予定登録                            |
    +----------------------------------------------------------------------------*/
    public function historyAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
		
		$request = $this->getRequest();
		$this->view->templateId = $templateId = $request->getParam('template_id');
		$this->view->posTop     = $request->getParam('pos');
		
		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
		$this->view->data = $data = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $templateId);

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);

		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);

		// 採算コード
    	$totalingGroupTable = new Shared_Model_Data_AccountTotalingGroup();
    	$this->view->groupData = $totalingGroupTable->getById($this->_adminProperty['management_group_id'], $data['account_totaling_group_id']);
           
		// 支払元取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		// 銀行口座
		if (!empty($data['bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['bank_id']);
		}
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-add-post                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 新規入金予定登録(Ajax)                      |
    +----------------------------------------------------------------------------*/
    public function historyAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$templateId = $request->getParam('template_id');

		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
		$templateData = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $templateId);
		
		// POST送信時
		if ($request->isPost()) {
		
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable = new Shared_Model_Data_AccountReceivable();

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
				$data = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        
			        'template_id'             => $templateId,                                     // テンプレートID
			        
			        'status'                  => Shared_Model_Code::RECEIVABLE_STATUS_DRAFT,                  // ステータス
			        'payment_status'          => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED,     // 入金ステータス
			        
					'type'                    => Shared_Model_Code::RECEIVABLE_TYPE_MONTHLY,      // 売掛管理種別
					'invoice_id'              => 0,                                               // 請求書ID
					'account_title_id'        => $templateData['account_title_id'],               // 会計科目ID
					'account_totaling_group_id'        => $templateData['account_totaling_group_id'], // 採算コード
					'target_connection_id'    => $templateData['target_connection_id'],           // 支払元取引先

					
					'bank_id'                 => $templateData['bank_id'],                        // 入金予定口座
					'receive_plan_date'       => $success['receive_plan_date'],                   // 入金予定日
					
					'file_list'               => json_encode($fileList),                          // 請求書ファイルアップロード
					
					'created_user_id'         => $this->_adminProperty['id'],                     // 登録者ユーザーID
					
					'memo'                    => $templateData['description'],                    // メモ
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);
				
				if ($templateData['template_type'] == (string)Shared_Model_Code::RECEIVABLE_TEMPLATE_TYPE_FIXED) {
					// 固定金額
					$data['total_amount']  = $templateData['total_amount'];         // 支払額
					$data['currency_id']   = $templateData['currency_id'];          // 通貨単位
				} else {
					// 毎月変動
					$data['total_amount']  = $success['total_amount'];              // 支払額
					$data['currency_id']   = $success['currency_id'];               // 通貨単位
				}
				
				// 新規登録	            
	            $receivableTable->getAdapter()->beginTransaction();
            	
	            try {
					$receivableTable->create($data);
					$id = $receivableTable->getLastInsertedId('id');

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Receivable::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/history-add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-detail                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 入金予定詳細                                |
    +----------------------------------------------------------------------------*/
    public function historyDetailAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$receivableTable = new Shared_Model_Data_AccountReceivable();
		$this->view->data = $data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
		$this->view->templateData = $templateData = $receivableTemplateTable->getById($this->_adminProperty['management_group_id'], $data['template_id']);
	
		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/transaction-recieved/template-history?id=' . $data['template_id'];
			$this->_helper->layout->setLayout('back_menu_competition');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        }
	        }
		}  
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払元取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		// 銀行口座
		if (!empty($data['bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['bank_id']);
		}
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-update-basic                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 入金予定詳細 - 基本情報更新(Ajax)           |
    +----------------------------------------------------------------------------*/
    public function historyUpdateBasicAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['account_title_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「会計科目ID」を選択してください'));
                    return;
				} else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;

			} else {
				$receivableTable = new Shared_Model_Data_AccountReceivable();
				$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $receivableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'account_title_id'        => $success['account_title_id'],      // 入金種別
						'target_connection_id'    => $success['target_connection_id'],  // 内容
						'memo'                    => $success['memo'],                  // 備考
					);
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/history-update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-update-payment               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 入金予定詳細 - 入金予定更新(Ajax)           |
    +----------------------------------------------------------------------------*/
    public function historyUpdatePaymentAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['bank_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金予定口座」を選択してください'));
                    return;
				} else if (!empty($errorMessage['payment_status']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金ステータス」を選択してください'));
                    return;
				} else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を選択してください'));
                    return;
				} else if (!empty($errorMessage['total_amount']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金額」を入力してください'));
                    return;
				} else if (!empty($errorMessage['currency_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「通貨」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;

			} else {
				$receivableTable = new Shared_Model_Data_AccountReceivable();
				$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $receivableTable->getAdapter()->beginTransaction();

	            try {
					$data = array(
						'bank_id'              => $success['bank_id'],            // 入金予定口座
						'payment_status'       => $success['payment_status'],     // 入金ステータス
						'receive_plan_date'    => $success['receive_plan_date'],  // 入金予定日
						'received_date'        => NULL,                           // 入金完了日
						'total_amount'         => $success['total_amount'],       // 入金予定額
						'currency_id'          => $success['currency_id'],        // 通貨ID
					);
					
					if (!empty($success['received_date'])) {
						$data['received_date'] = $success['received_date'];
					}
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/history-update-payment transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-update-file-list             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 - 入金予定詳細 - 参考資料ファイルアップロード更新(Ajax) 
    +----------------------------------------------------------------------------*/
    public function historyUpdateFileListAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable = new Shared_Model_Data_AccountReceivable();
				
				$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
	            $receivableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'file_list' => json_encode($fileList), // 参考ファイルアップロード
					);

					$receivableTable->updateById($id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Receivable::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-recieved/history-update-file-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }




    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-apply-apploval               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 入金予定 承認申請                             |
    +----------------------------------------------------------------------------*/
    public function historyApplyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$receivableTable = new Shared_Model_Data_AccountReceivable();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			// 申請者情報
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        	
			$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);
			
			/*
			if (empty($oldData['template_type'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金種別」を選択してください'));
                return; 
			} else if (empty($oldData['account_title_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return;
			} else if (empty($oldData['description'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                return;
			} else if (empty($oldData['target_connection_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を選択してください'));
                return;
			} else if (empty($oldData['total_amount'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金予定額(税込) 」を入力してください'));
                return;
			} else if (empty($oldData['currency_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を選択してください'));
                return;
			} else if (empty($oldData['recieve_plan_monthly_day'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「毎月入金時期」を入力してください'));
                return; 
			} else if (empty($oldData['bank_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金口座」を選択してください'));
                return; 
			}
			*/

			// 支払元取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $oldData['target_connection_id']);
			
    		// 会計科目
			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $oldData['account_title_id']);

			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $oldData['currency_id']);
			
			try {
				$receivableTable->getAdapter()->beginTransaction();
				
				$receivableTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_STATUS_PENDING,
					'approval_user_id' => $userData['approver_c1_user_id'],
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_RECEIVABLE_MONTHLY,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $oldData['memo'] . "\n" . "入金予定額：" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				$approvalTable->create($approvalData);

				// メール送信 -------------------------------------------------------
				// 承認者
				$selectObj = $userTable->select();
		    	$selectObj->where('id = ?', $userData['approver_c1_user_id']);
		        $authorizerUserData = $selectObj->query()->fetch();
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);

				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'] . "\n\n";
				
				
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'managment_group_name' => $groupData['organization_name'],
					'to'       => $authorizerUserData['mail'],
					'cc'       => array(),
					'type'     => $approvalTypeList[$approvalData['type']],
					'content'  => $content,
					'name'     => $userData['user_name'],
					'user_id'  => $userData['display_id'],
				);		

				$mailer = new Shared_Model_Mail_Approval();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $receivableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/history-apply-apploval transaction faied: ' . $e);      
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-mod-request                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 入金予定 修正依頼(Ajax)                       |
    +----------------------------------------------------------------------------*/
    public function historyModRequestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');
		
		
		// POST送信時
		if ($request->isPost()) {
			$receivableTable = new Shared_Model_Data_AccountReceivable();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

			// 支払元取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
			
    		// 会計科目
			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);

			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			try {
				$receivableTable->getAdapter()->beginTransaction();
				
				$receivableTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-recieved/history-detail?id=' . $id;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $receivableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/history-mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/history-approve                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月入金管理 入金予定 承認(Ajax)                           |
    +----------------------------------------------------------------------------*/
    public function historyApproveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');

		
		// POST送信時
		if ($request->isPost()) {
			$receivableTable = new Shared_Model_Data_AccountReceivable();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);

			// 支払元取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
			
    		// 会計科目
			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);

			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
			try {
				$receivableTable->getAdapter()->beginTransaction();
				
				$receivableTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-recieved/history-detail?id=' . $id;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $receivableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/history-approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/upload                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 参考資料ファイルアップロード(Ajax)                         |
    +----------------------------------------------------------------------------*/
    public function uploadAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
        
		if (empty($_FILES['file']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		$fileName = $_FILES['file']['name'];
		$tempFileName = uniqid();
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($tempFileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName, 'temp_file_name' => $tempFileName));
        return;
	}
    

}

