<?php
/**
 * class TransactionPaidController
 */
class TransactionPaidController extends Front_Model_Controller
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
		$this->view->menu             = 'paid';  

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/check                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * check                                                        |
    +----------------------------------------------------------------------------*/
    public function checkAction()
    {
    	$payableTable         = new Shared_Model_Data_AccountPayable();
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
		
		$selectObj = $payableTable->select();
		$selectObj->where('paying_method = ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$selectObj->where('id >= 4010');
		$selectObj->where('is_attached = 1');
        $items = $selectObj->query()->fetchAll();
        
        foreach ($items as $each) {
	        // 割当情報
	        $historyItems = $historyItemData = $cardHistoryItemTable->getListByPayableId($each['id']);
	        
	        if (empty($historyItems)) {
	        	echo $each['id'] . '<br>';
	        }
        }

		exit;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/fix                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * fix                                                        |
    +----------------------------------------------------------------------------*/
    public function fixAction()
    {
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
		$payableTable         = new Shared_Model_Data_AccountPayable();
		
		$selectObj = $cardHistoryItemTable->select();
		$selectObj->where('status = ?', Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_ATTACHED);
        $items = $selectObj->query()->fetchAll();
        
        foreach ($items as $rowData) {
	        $payableIds = $rowData['payable_ids'];
	        echo print_r($payableIds, true) . '<br>';
	        
			if (!empty($payableIds)) {
				foreach ($payableIds as $eachId) {
					$payableTable->updateById($eachId, array(
						'is_attached'    => '0',
						//'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,
					));
				}
			}
        }

		exit;
    }
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/edit                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード割り当て情報修正                                     |
    +----------------------------------------------------------------------------*/
    public function editAction()
    {
    	$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
    	$cardHistoryItemTable->updateById('1076', array(
    		//'row_count' => '29',
    		'amount'    => '-15223',
    		//'purchased_date' => '2018-11-30',
    		//'payable_ids' => serialize(array('1149')),
    	));
    	
    	echo 'OK１';
    	exit;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/add-history-item                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード割り当て情報修正                                     |
    +----------------------------------------------------------------------------*/
    public function addHistoryItemAction()
    {
	    $cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
	    
		$data = array(
	        'management_group_id' => $this->_adminProperty['management_group_id'], // 管理グループID
			'status'              => Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE,
	        'card_history_id'     => '48',                   // クレジットカード取込CSVID
	        
	        'row_count'           => '48',                        // 行番号
	        'purchased_date'      => $purchasedDate,              // 利用日(購入日)
	        'name'                => 'ラクテントラベル　コクナイシュクハク',                  // 項目名

	        'start_month'         => '8',                  // 支払開始月
	        'times'               => '1',                  // 支払回数
	        'time_count'          => '1',                  // 支払今回回数
	        'charge'              => '0',                  // 手数料
	        'balance'             => '0',                  // 残り残高
        
	        'currency_id'         => '1',         // 通貨ID
	        'amount'              => '-18975',                     // 今月支払額
	        
	        'payable_id'          => 0,                           // 買掛ID    
	
            'created'             => new Zend_Db_Expr('now()'),
            'updated'             => new Zend_Db_Expr('now()'),
		);
		
		$cardHistoryItemTable->create($data);
		echo 'OK';
		exit;
	}	
			
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/update-payable                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払データ修正(仮)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function updatePayableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		//$payableId    = $request->getParam('payable_id');
		$payableId = '210';
		
        $payableTable = new Shared_Model_Data_AccountPayable();        
        $payableTable->updateById($payableId, array(
	        //'total_amount' => '515',
        ));
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/add-payable                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプルデータ登録(仮)(Ajax)                               |
    +----------------------------------------------------------------------------*/
    public function addPayableAction()
    {
    	
    	for ($count = 1; $count <= 5; $count++) {
	    	$payableTable = new Shared_Model_Data_AccountPayable();
			$data = array(
		        'management_group_id'     => $this->_adminProperty['management_group_id'],
		        'status'                  => '50', // 承認済
		        
		        'order_form_ids'          => '',              // 発注IDリスト
		        
				'account_title_id'        => '112',          // 会計科目ID
				'target_connection_id'    => '1819',      // 支払先
				
				'paying_type'             => '15',               // 支払種別(請求支払/カード支払/自動振替)
	
				'file_list'               => '',                // 請求書ファイルアップロード
				
				'paid_user_id'            => 0,                                     // 支払処理担当者
				'paid_date'               => NULL,                                  // 支払完了日
				
				'paying_method'           => '10',             // 支払方法
				'paying_bank_id'          => '7',                         // 支払元銀行口座
				'paying_card_id'          => '0',                         // 支払元クレジットカード
				'paying_method_memo'      => '',        // 支払方法メモ
				
				
				'purchased_date'          => '2021-06-05',            // 発生日
				'paying_plan_date'        => '2021-07-05',            // 支払予定日
				
				'transfer_to_connection_bank_id' => 8,
				'bank_registered_type'    => Shared_Model_Code::BANK_REGISTERED_TYPE_GOOSA_SP,
				
				'total_amount'            => 10000 * $count,
				'currency_id'             => '1',
				'tax_division'            => '10',
				'tax'                     => 1000 * $count,
				
				
				'created_user_id'         => '22',           // 支払申請者
				'approval_user_id'        => 0,                                     // 承認者
				
	            'created'                 => new Zend_Db_Expr('now()'),
	            'updated'                 => new Zend_Db_Expr('now()'),
			);
			
			$payableTable->create($data);
    	}
    	
	    
    	echo 'ok';
    	exit;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/payable-data                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプルデータ登録(仮)(Ajax)                               |
    +----------------------------------------------------------------------------*/
    public function payableDataAction()
    {
    	$payableTable = new Shared_Model_Data_AccountPayable();
		$data = $payableTable->getById(2, 84);
    	var_dump($data);
    	exit;
    }
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/delete                                   |
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
			$payableTable = new Shared_Model_Data_AccountPayable();

			try {
				$payableTable->getAdapter()->beginTransaction();
				
				$payableTable->updateById($id, array(
					'status' => Shared_Model_Code::PAYABLE_STATUS_DELETED,
				));
			
                // commit
                $payableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-paid/delete transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
       
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/invoice-list                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払完了確認                                           |
    +----------------------------------------------------------------------------*/
    public function invoiceListAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('transaction_paid_invoice_list_2');

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
			$session->conditions['paying_method']       = $request->getParam('paying_method', '');
			
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			
			$session->conditions['relational_id']       = $request->getParam('relational_id', '');
		} else if (empty($session->conditions) || !array_key_exists('payment_status', $session->conditions)) {
			$session->conditions['payment_status']      = '';
			$session->conditions['paying_method']       = ''
			;
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
			$session->conditions['relational_id']       = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		$this->view->viewType = $conditions['view_type'];
			
		$payableTable = new Shared_Model_Data_AccountPayable();
		$dbAdapter = $payableTable->getAdapter();
		
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->joinLeft('frs_account_bank', 'frs_account_payable.paying_bank_id = frs_account_bank.id', array($payableTable->aesdecrypt('bank_name', false) . 'AS bank_name', $payableTable->aesdecrypt('branch_name', false) . 'AS branch_name', 'account_type', $payableTable->aesdecrypt('account_no', false) . 'AS account_no', 'short_name'));
		$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id', array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_account_payable.paying_type != ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_SITE_DATA);
		
		
		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
		            . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);

        if (!empty($session->conditions['payment_status'])) {
        	if ($session->conditions['payment_status'] === (string)Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID_PENDDING) {
	        	$selectObj->where('frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID
	        	            . ' OR frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PENDDING);
        	} else {
        		$selectObj->where('frs_account_payable.payment_status = ?', $session->conditions['payment_status']);
        	}
        }

		if ($session->conditions['paying_method'] !== '') {
			$selectObj->where('frs_account_payable.paying_method = ?', $session->conditions['paying_method']);
		} else {
			$selectObj->where('frs_account_payable.paying_method != ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		}
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_payable.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_payable.account_title_id = ?', $session->conditions['account_title_id']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_payable.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_payable.target_connection_id = ?', $session->conditions['connection_id']);
		}

		if ($session->conditions['relational_id'] !== '') {
			$selectObj->where('frs_account_payable.relational_display_id = ?', $session->conditions['relational_id']);
		}	


		$unpaidSelectObj = $payableTable->select();
		
		// グループID
        $unpaidSelectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $unpaidSelectObj->where('frs_account_payable.paying_type != ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_SITE_DATA);
        
		$unpaidSelectObj->where('frs_account_payable.paying_method != ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$unpaidSelectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
		                  . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
		$unpaidSelectObj->where('payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
		
		$items = array(); 
		
		if ($conditions['view_type'] === 'monthly') {
			// 月別
			$nDate = new Nutex_Date();
        	$from = $conditions['year'] . '-' . $conditions['month'] . '-01';
       		$to   = $conditions['year'] . '-' . $conditions['month'] . '-' . $nDate->getMonthEndDay($conditions['year'], $conditions['month']);
			$selectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
			$selectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
			$selectObj->order('frs_account_payable.paying_plan_date ASC');
			$selectObj->order('frs_account_payable.id ASC');
			
			$unpaidSelectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
			$unpaidSelectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
			
			$zDate = new Zend_Date($conditions['year'] . '-' . $conditions['month'] . '-01', NULL, 'ja_JP');
			
			$zDate->sub('1', Zend_Date::MONTH);
			$conditionsPrev          = $conditions;
			$conditionsPrev['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsPrev['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->prevUrl = '/transaction-paid/invoice-list?' . http_build_query($conditionsPrev);
			
			$zDate->add('2', Zend_Date::MONTH);
			$conditionsNext          = $conditions;
			$conditionsNext['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsNext['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->nextUrl = '/transaction-paid/invoice-list?' . http_build_query($conditionsNext);
		
			$this->view->items = $items = $selectObj->query()->fetchAll();
        
		} else {
			// 全一覧
			$selectObj->order('frs_account_payable.paying_plan_date DESC');
			$selectObj->order('frs_account_payable.id DESC');
			
			$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
	        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
			$paginator->setCurrentPageNumber($conditions['page']);
			
			       
			foreach ($paginator->getCurrentItems() as $eachItem) {
				$items[] = $eachItem;
			}
			
	        $this->view->items = $items;
	        $this->view->pager($paginator);
		}
		
		
		
		$total = array();
		$total['total_count'] = 0;
				
		$unpaidTotal = array();
		$unpaidTotal['total_count'] = 0;
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList  = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        	
        	$total[$each['id']] = $each;
        	$total[$each['id']]['item_count'] = 0;
        	$total[$each['id']]['total'] = 0;
        	
        	$unpaidTotal[$each['id']] = $each;
        	$unpaidTotal[$each['id']]['item_count'] = 0;
        	$unpaidTotal[$each['id']]['total'] = 0;
        }
		$this->view->currencyList = $currencyList;
		
		
		// 未払合計(通貨毎)
		$unpaidList = $unpaidSelectObj->query()->fetchAll();
		foreach ($unpaidList as $eachItemUnpaid) {
			$unpaidTotal[$eachItemUnpaid['currency_id']]['item_count'] += 1;
			$unpaidTotal[$eachItemUnpaid['currency_id']]['total'] += (int)$eachItemUnpaid['total_amount'];
			$unpaidTotal['total_count'] += 1;
		}
		$this->view->unpaidTotal = $unpaidTotal;
		
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
    |  action_URL    * /transaction-paid/site-list                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サイト連動請求支払完了確認                                 |
    +----------------------------------------------------------------------------*/
    public function siteListAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('transaction_paid_invoice_list_1');

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
			
			$session->conditions['relational_id']       = $request->getParam('relational_id', '');
		} else if (empty($session->conditions) || !array_key_exists('payment_status', $session->conditions)) {
			$session->conditions['payment_status']      = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
			$session->conditions['relational_id']       = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		$this->view->viewType = $conditions['view_type'];
			
		$payableTable = new Shared_Model_Data_AccountPayable();
		$dbAdapter = $payableTable->getAdapter();
		
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name', 'gs_bank_confirmed'));
		$selectObj->joinLeft('frs_account_bank', 'frs_account_payable.paying_bank_id = frs_account_bank.id', array($payableTable->aesdecrypt('bank_name', false) . 'AS bank_name', $payableTable->aesdecrypt('branch_name', false) . 'AS branch_name', 'account_type', $payableTable->aesdecrypt('account_no', false) . 'AS account_no', 'short_name'));
		$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id', array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_SITE_DATA);
		$selectObj->where('frs_account_payable.paying_method != ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		
		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
		            . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);

        if (!empty($session->conditions['payment_status'])) {
        	if ($session->conditions['payment_status'] === (string)Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID_PENDDING) {
	        	$selectObj->where('frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID
	        	            . ' OR frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PENDDING);
        	} else {
        		$selectObj->where('frs_account_payable.payment_status = ?', $session->conditions['payment_status']);
        	}
        }
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_payable.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_payable.account_title_id = ?', $session->conditions['account_title_id']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_payable.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_payable.target_connection_id = ?', $session->conditions['connection_id']);
		}

		if ($session->conditions['relational_id'] !== '') {
			$selectObj->where('frs_account_payable.relational_display_id = ?', $session->conditions['relational_id']);
		}	


		$unpaidSelectObj = $payableTable->select();
		
		// グループID
        $unpaidSelectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $unpaidSelectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_SITE_DATA);
        
		$unpaidSelectObj->where('frs_account_payable.paying_method != ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$unpaidSelectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
		                  . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
		$unpaidSelectObj->where('payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
		
		$items = array(); 
		
		if ($conditions['view_type'] === 'monthly') {
			// 月別
        	$from = $conditions['year'] . '-' . $conditions['month'] . '-01';
       		$to   = $conditions['year'] . '-' . $conditions['month'] . '-' . Nutex_Date::getMonthEndDay($conditions['year'], $conditions['month']);
			$selectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
			$selectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
			$selectObj->order('frs_account_payable.paying_plan_date ASC');
			$selectObj->order('frs_account_payable.id ASC');
			
			$unpaidSelectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
			$unpaidSelectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
			
			$zDate = new Zend_Date($conditions['year'] . '-' . $conditions['month'] . '-01', NULL, 'ja_JP');
			
			$zDate->sub('1', Zend_Date::MONTH);
			$conditionsPrev          = $conditions;
			$conditionsPrev['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsPrev['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->prevUrl = '/transaction-paid/invoice-list?' . http_build_query($conditionsPrev);
			
			$zDate->add('2', Zend_Date::MONTH);
			$conditionsNext          = $conditions;
			$conditionsNext['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsNext['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->nextUrl = '/transaction-paid/invoice-list?' . http_build_query($conditionsNext);
		
			$this->view->items = $items = $selectObj->query()->fetchAll();
        
		} else {
			// 全一覧
			$selectObj->order('frs_account_payable.paying_plan_date DESC');
			$selectObj->order('frs_account_payable.id DESC');
			
			$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
	        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
			$paginator->setCurrentPageNumber($conditions['page']);
			
			       
			foreach ($paginator->getCurrentItems() as $eachItem) {
				$items[] = $eachItem;
			}
			
	        $this->view->items = $items;
	        $this->view->pager($paginator);
		}
		
		
		
		$total = array();
		$total['total_count'] = 0;
				
		$unpaidTotal = array();
		$unpaidTotal['total_count'] = 0;
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList  = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        	
        	$total[$each['id']] = $each;
        	$total[$each['id']]['item_count'] = 0;
        	$total[$each['id']]['total'] = 0;
        	
        	$unpaidTotal[$each['id']] = $each;
        	$unpaidTotal[$each['id']]['item_count'] = 0;
        	$unpaidTotal[$each['id']]['total'] = 0;
        }
		$this->view->currencyList = $currencyList;
		
		
		// 未払合計(通貨毎)
		$unpaidList = $unpaidSelectObj->query()->fetchAll();
		foreach ($unpaidList as $eachItemUnpaid) {
			$unpaidTotal[$eachItemUnpaid['currency_id']]['item_count'] += 1;
			$unpaidTotal[$eachItemUnpaid['currency_id']]['total'] += (int)$eachItemUnpaid['total_amount'];
			$unpaidTotal['total_count'] += 1;
		}
		$this->view->unpaidTotal = $unpaidTotal;
		
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
    |  action_URL    * /transaction-paid/gmo-transfer-list                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO振込予約履歴                                            |
    +----------------------------------------------------------------------------*/
    public function gmoTransferListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('gmo_transfer_lsit');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$this->view->conditions = $conditions = $session->conditions;
		
		
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		$dbAdapter = $transferTable->getAdapter();
		
		$selectObj = $transferTable->select();
		$selectObj->joinLeft('frs_account_payable', 'frs_account_gmo_transfer.payable_id = frs_account_payable.id', array('target_connection_id', 'transfer_to_bank_code', 'bank_registered_type', 'paying_plan_date', 'total_amount'));
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($transferTable->aesdecrypt('company_name', false) . 'AS company_name'));
		//$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		/*
		if ($session->conditions['payment_status'] !== '') {
			$selectObj->where('frs_account_payable.payment_status = ?', $session->conditions['payment_status']);
		}
		*/
		
		// 全一覧
		$selectObj->order('id DESC');

		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber('1');

		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem;
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator);
		
		// GMO口座リスト
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankItems = $bankTable->getGMOBankList();
        $accountList = array();
        foreach ($bankItems as $eachBank) {
        	$accountList[$eachBank['gmo_bank_account_id']] = $eachBank;
        }
    	$this->view->accountList = $accountList;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/gmo-transfer-detail                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO振込予約詳細                                            |
    +----------------------------------------------------------------------------*/
    public function gmoTransferDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->applyNo = $applyNo = $request->getParam('apply_no');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		$this->view->items = $transferTable->getListByApplyNo($applyNo);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/gmo-transfer-develop-list                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO振込予約履歴(開発用)                                    |
    +----------------------------------------------------------------------------*/
    public function gmoTransferDevelopListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('gmo_transfer_lsit');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$this->view->conditions = $conditions = $session->conditions;
		
		
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		$dbAdapter = $transferTable->getAdapter();
		
		$selectObj = $transferTable->select();
		//$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		//$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		/*
		if ($session->conditions['payment_status'] !== '') {
			$selectObj->where('frs_account_payable.payment_status = ?', $session->conditions['payment_status']);
		}
		*/
		
		// 全一覧
		$selectObj->order('id DESC');

		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber('1');

		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem;
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/account-check                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 振込先確認 - 一覧                                          |
    +----------------------------------------------------------------------------*/
    public function accountCheckAction()
    {
		$this->view->menuCategory     = 'transaction';
		$this->view->menu = 'history'; 

		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');

		$connectionTable = new Shared_Model_Data_Connection();
		$dbAdapter = $connectionTable->getAdapter();
        $selectObj = $connectionTable->select(array('id', 'status', 'updated', 'display_id', 'company_name', 'relation_types', 'gs_supplier_id', 'gs_buyer_id', 'type', 'gs_bank_confirmed', 'gs_bank_confirmed_date_time'));
		$selectObj->joinLeft('frs_user', 'frs_connection.gs_bank_confirmed_user_id = frs_user.id', array($connectionTable->aesdecrypt('user_name', false) . 'AS confirmed_user_name'));
        $selectObj->where('frs_connection.gs_bank_confirmed != ?', Shared_Model_Code::BANK_CONFIRM_STATUS_NONE);
        $selectObj->order('frs_connection.gs_bank_renewaled_datetime DESC');

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
    |  action_URL    * /transaction-paid/account-check-confirm                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 振込先確認 - 詳細                                          |
    +----------------------------------------------------------------------------*/
    public function accountCheckConfirmAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		
		$connectionTable = new Shared_Model_Data_Connection();
		$userTable       = new Shared_Model_Data_User();
		
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/transaction-paid/account-check';
		}
		
		
		$userTable       = new Shared_Model_Data_User();
		
    	// 見積作成者
    	if (!empty($data['gs_bank_confirmed_user_id'])) {
    		$this->view->confirmedUser = $userTable->getById($data['gs_bank_confirmed_user_id']);
    	}
        
    } 

    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/update-target                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * CSV対象更新(Ajax)                                          |
    +----------------------------------------------------------------------------*/
    public function updateTargetAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id      = $request->getParam('id');
		$target  = $request->getParam('is_target');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable = new Shared_Model_Data_AccountPayable();
			$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
			
			if (empty($oldData)) {
                $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
                return;
            }
            
			$data = array(
				'is_csv_target' => $target,
			);
			
			$payableTable->updateById($id, $data);
				
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/all-on                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * CSV対象更新(Ajax)                                          |
    +----------------------------------------------------------------------------*/
    public function allOnAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$idListString  = $request->getParam('id_list');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable = new Shared_Model_Data_AccountPayable();
			
			$idList = explode(',', $idListString);
			
			foreach ($idList as $eachId) {
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $eachId);
				
				if (empty($oldData)) {
	                $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	                return;
	            }
	            
				$data = array(
					'is_csv_target' => '1',
				);
				
				$payableTable->updateById($eachId, $data);	
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/all-off                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * CSV対象更新(Ajax)                                          |
    +----------------------------------------------------------------------------*/
    public function allOffAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$idListString  = $request->getParam('id_list');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable = new Shared_Model_Data_AccountPayable();
			
			$idList = explode(',', $idListString);
			
			foreach ($idList as $eachId) {
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $eachId);
				
				if (empty($oldData)) {
	                $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	                return;
	            }
	            
				$data = array(
					'is_csv_target' => '0',
				);
				
				$payableTable->updateById($eachId, $data);	
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/export-csv-check                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 振込用csv出力 確認                                         |
    +----------------------------------------------------------------------------*/
    public function exportCsvCheckAction()
    {
    	$request       = $this->getRequest();
		$idListString  = $request->getParam('id_list');
		$payingType    = $request->getParam('paying_type');
		$type          = $request->getParam('type', 'common');
		
		if (empty($payingType)) {
			throw new Zend_Exception('/transaction-paid/export-csv-check - paying_type is empty');
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		
		$dbAdapter = $payableTable->getAdapter();

        $selectObj = $payableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array(
        	$payableTable->aesdecrypt('company_name', false) . 'AS company_name',
			/*
			'gs_bank_confirmed',
			'gs_basic_bank_select',
			$payableTable->aesdecrypt('gs_other_bank_name', false) . 'AS gs_other_bank_name',
			$payableTable->aesdecrypt('gs_bank_code', false) . 'AS gs_bank_code',
			$payableTable->aesdecrypt('gs_bank_branch_id', false) . 'AS gs_bank_branch_id',
			$payableTable->aesdecrypt('gs_bank_branch_name', false) . 'AS gs_bank_branch_name',
			'gs_bank_account_type',
			$payableTable->aesdecrypt('gs_bank_account_no', false) . 'AS gs_bank_account_no',
			$payableTable->aesdecrypt('gs_bank_account_name', false) . 'AS gs_bank_account_name',
			$payableTable->aesdecrypt('gs_bank_account_name_kana', false) . 'AS gs_bank_account_name_kana',
			*/
        ));
        
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $selectObj->where('frs_account_payable.paying_type = ?', $payingType);
        $selectObj->where('frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
		$selectObj->where('is_csv_target = 1');
		$selectObj->order('frs_account_payable.paying_plan_date DESC');
		
		$items = $selectObj->query()->fetchAll();

		if (empty($items)) {
			$this->errorData(0);
			return;
		}
		
		foreach ($items as $each) {
			if (!empty($each['transfer_to_bank_code'])) {
				// 個別振込先
				if ($each['transfer_to_confirmed'] !== (string)Shared_Model_Code::BANK_CONFIRM_STATUS_CONFIRMED) {
					$this->errorData($each['id']);
					return;
				}
				
			} else {
				if (empty($each['transfer_to_connection_bank_id'])) {
					// 取引先口座登録
					//if ($each['gs_bank_confirmed'] !== (string)Shared_Model_Code::BANK_CONFIRM_STATUS_CONFIRMED) {
						$this->errorData($each['id']);
						return;
					//}
				}
			}
		}
		
		$this->_redirect('/transaction-paid/export-csv/' . $type . '/' . $payingType . '/' . date('Y-m-d__H_i') . '(全' . count($items) . '件).csv');
	}

    public function errorData($id)
    {
		$this->_helper->layout->setLayout('back_menu');
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		// 銀行口座
		if (!empty($data['paying_bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
		}
		
		// クレジット
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/export-csv                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 振込用csv出力                                              |
    +----------------------------------------------------------------------------*/
    public function exportCsvAction()
    {
    	$request = $this->getRequest();
		$payingType    = $request->getParam('paying_type');
		$type          = $request->getParam('type');
		
		if (empty($payingType)) {
			throw new Zend_Exception('/transaction-paid/export-csv-check - paying_type is empty');
		}


		$payableTable = new Shared_Model_Data_AccountPayable();
		
		$dbAdapter = $payableTable->getAdapter();

        $selectObj = $payableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array(
        	$payableTable->aesdecrypt('company_name', false) . 'AS company_name',
			/*
			'gs_bank_confirmed',
			'gs_basic_bank_select',
			$payableTable->aesdecrypt('gs_other_bank_name', false) . 'AS gs_other_bank_name',
			$payableTable->aesdecrypt('gs_bank_code', false) . 'AS gs_bank_code',
			$payableTable->aesdecrypt('gs_bank_branch_id', false) . 'AS gs_bank_branch_id',
			$payableTable->aesdecrypt('gs_bank_branch_name', false) . 'AS gs_bank_branch_name',
			'gs_bank_account_type',
			$payableTable->aesdecrypt('gs_bank_account_no', false) . 'AS gs_bank_account_no',
			$payableTable->aesdecrypt('gs_bank_account_name', false) . 'AS gs_bank_account_name',
			$payableTable->aesdecrypt('gs_bank_account_name_kana', false) . 'AS gs_bank_account_name_kana',
			*/
        ));
        
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $selectObj->where('frs_account_payable.paying_type = ?', $payingType);
        $selectObj->where('frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
		$selectObj->where('is_csv_target = 1');
		$selectObj->order('frs_account_payable.paying_plan_date DESC');
		
		$items = $selectObj->query()->fetchAll();
	
		$connectionBankTable = new Shared_Model_Data_ConnectionBank();
				
				
		if ($type === 'jnb') {
			// JNB形式
	    	$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '_jnb.csv');

	    	$fp = fopen($path, 'w');	
			$total = 0;
			
			foreach ($items as $each) {
				if (empty($each['transfer_to_account_name'])) {
					// 取引先口座登録の場合
					$connectionBankData = $connectionBankTable->getById($each['transfer_to_connection_bank_id']);

					$each['transfer_to_bank_code']    = $connectionBankData['bank_code'];        // 振込先口座の銀行コード
					$each['transfer_to_branch_code']  = $connectionBankData['branch_code'];   // 振込先口座の支店コード
					
					$each['transfer_to_account_type'] = $connectionBankData['account_type'];
				
					$each['transfer_to_account_no']   = $connectionBankData['account_no'];    // 振込先口座の口座番号
					$each['transfer_to_account_name'] = $connectionBankData['account_name_kana'];  
				}
				
				$accountName = mb_convert_kana($each['transfer_to_account_name'], "a", "UTF-8"); // 全角英数字を半角英数字に変換
				$accountName = mb_convert_kana($each['transfer_to_account_name'], "C", "UTF-8"); // 全角ひらがなを全角カタカナに変換する
				$accountName = mb_convert_kana($accountName, "k", "UTF-8"); // 全角カタカナを半角カタカナに変換する
				$accountName = str_replace('ー', '-', $accountName);
				$accountName = str_replace('・', '.', $accountName);
				$accountName = str_replace('／', '/', $accountName);
				
	    		$accountType = 0;
	
	    		if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
		    		$accountType = '1';
	    		} else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
		    		$accountType = '2';
	    		} else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
					$accountType = '4';
				}
				
				$this->putData($fp, array(
					'1', // レコード区分
					$each['transfer_to_bank_code'],     // 振込先口座の銀行コード
					$each['transfer_to_branch_code'],   // 振込先口座の支店コード
					$accountType,                       // 振込先口座の預金科目（1：普通、2：当座、4：貯蓄）
					$each['transfer_to_account_no'],    // 振込先口座の口座番号
					$this->getZenginFormat($each['transfer_to_account_name'], "SJIS-win", "UTF-8"),  // 振込先口座の受取人名（口座名義）
					$each['total_amount'],  // 振込金額
					mb_convert_kana('フレスコ（カ）グーサイチ', "k", "UTF-8")
				));
				
				$total += (int)$each['total_amount'];
	
			}
	
			$this->putData($fp, array(
				'2', // レコード区分
				'',  // 予備１
				'',  // 予備２
				'',  // 予備３
				'',  // 予備４
				count($items),  // 合計件数
				$total, // 合計金額
				'', // 予備５
			));
				
			fclose($fp);
			
	        $this->_helper->binaryOutput(file_get_contents($path), array(
	            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	        ));
	        
	    } else if ($type === 'gmo') {
			// GMO形式
	    	$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '_jnb.csv');
	    	$fp = fopen($path, 'w');
	
			$total = 0;
			
			foreach ($items as $each) {
				if (empty($each['transfer_to_account_name'])) {
					// 取引先口座登録の場合
					$connectionBankData = $connectionBankTable->getById($each['transfer_to_connection_bank_id']);

					$each['transfer_to_bank_code']    = $connectionBankData['bank_code'];        // 振込先口座の銀行コード
					$each['transfer_to_branch_code']  = $connectionBankData['branch_code'];   // 振込先口座の支店コード
					
					$each['transfer_to_account_type'] = $connectionBankData['account_type'];
				
					$each['transfer_to_account_no']   = $connectionBankData['account_no'];    // 振込先口座の口座番号
					$each['transfer_to_account_name'] = $connectionBankData['account_name_kana'];  
				}
				
				$accountName = mb_convert_kana($each['transfer_to_account_name'], "a", "UTF-8"); // 全角英数字を半角英数字に変換
				$accountName = mb_convert_kana($each['transfer_to_account_name'], "C", "UTF-8"); // 全角ひらがなを全角カタカナに変換する
				$accountName = mb_convert_kana($accountName, "k", "UTF-8"); // 全角カタカナを半角カタカナに変換する
				$accountName = str_replace('ー', '-', $accountName);
				$accountName = str_replace('・', '.', $accountName);
				$accountName = str_replace('／', '/', $accountName);
				
	    		$accountType = 0;
	
	    		if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
		    		$accountType = '1';
	    		} else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
		    		$accountType = '2';
	    		} else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
					$accountType = '4';
				}
				
				$this->putData($fp, array(
					sprintf('%04d', $each['transfer_to_bank_code']),     // 振込先口座の銀行コード
					$each['transfer_to_branch_code'],   // 振込先口座の支店コード
					$accountType,                       // 振込先口座の預金科目（1：普通、2：当座、4：貯蓄）
					sprintf('%07d', $each['transfer_to_account_no']),    // 振込先口座の口座番号
					$this->getZenginFormat($accountName, "SJIS-win", "UTF-8"),  // 振込先口座の受取人名（口座名義）
					$each['total_amount'],  // 振込金額
					'',
					$this->getZenginFormat(' ')
				));
				
				$total += (int)$each['total_amount'];
	
			}

			fclose($fp);
			
			$data = file_get_contents($path, "w");
			$data = str_replace('" "', ' ', $data);
			$data = str_replace("\n", "\r\n" ,$data);

	        $this->_helper->binaryOutput($data, array(
	            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	        ));
		    
        } else {
	        // 全銀形式
	    	$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '_common.csv');

	    	$fp = fopen($path, 'w');
	
			$total = 0;
					
			// ヘッダーレコード
			$this->putData($fp, array(
				'1',                                // 1 レコード区分 ヘッダーレコード1【固定値】
				'21',                               // 2 21【固定値】...総合振込
				'0',                                // 3 コード区分 0...JIS、 1...EBCDIC
				'9999999999',                       // 3 振込依頼人コード    9999999999【固定値】(webアップロードの場合未使用)
				'ﾌﾚｽｺｶﾌ゙ｼｷｶ゙ｲｼｬ) ｸ゙ｰｻ1' . str_repeat(' ', 40 - 18),            // 4 振込元の依頼人名 40
				'0330',                             // 5 振込実施日、月日(MMDD) (例:0701)
				'0310',                             // 6 仕向銀行番号
				'ｼ゙ｰｴﾑｵｰｱｵｿ゙ﾗﾈﾂﾄ' . str_repeat(' ', 40 - 13),               // 7 仕向銀行名
				'101',                              // 8 仕向支店番号
				'ﾎｳｼ゙ﾝｴｲｷ゙ｮｳﾌ゙' . str_repeat(' ', 15 - 10),                    // 9 仕向支店名
				'1',                                // 10 預金種目(依頼人) 1...普通預金
				'1235764',                          // 11 口座番号(依頼人) 振込依頼人の口座番号
				str_repeat(' ', 17),                                 // 12 ダミー スペース
			));
		
			
			foreach ($items as $each) {
				if (empty($each['transfer_to_confirmed'])) {
					// 取引先口座登録
					$each['transfer_to_bank_code']    = $each['gs_bank_code'];          // 振込先口座の銀行コード
					$each['transfer_to_bank_name']    = $each['gs_bank_name'];
					$each['transfer_to_branch_code']  = $each['gs_bank_branch_id'];     // 振込先口座の支店コード
					$each['transfer_to_branch_name']  = $each['gs_bank_branch_name'];
					
					if ($each['gs_basic_bank_select'] !== (string)Shared_Model_Code::BASIC_BANK_OTHER) {
						$basicBankList = Shared_Model_Code::codes('basic_bank');
						
						$each['transfer_to_bank_name'] = $basicBankList[$each['gs_basic_bank_select']];
						
					}
					
					$each['transfer_to_account_type'] = $each['gs_bank_account_type'];
				
					$each['transfer_to_account_no']   = $each['gs_bank_account_no'];    // 振込先口座の口座番号
					$each['transfer_to_account_name'] = $each['gs_bank_account_name_kana'];  
				}
				
				$accountName = mb_convert_kana($each['transfer_to_account_name'], "a", "UTF-8"); // 全角英数字を半角英数字に変換
				$accountName = mb_convert_kana($each['transfer_to_account_name'], "C", "UTF-8"); // 全角ひらがなを全角カタカナに変換する
				$accountName = mb_convert_kana($accountName, "k", "UTF-8");                      // 全角カタカナを半角カタカナに変換する
				$accountName = str_replace('ー', '-', $accountName);
				$accountName = str_replace('・', '.', $accountName);
				$accountName = str_replace('／', '/', $accountName);
				
	    		$accountType = 0;
	
	    		if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
		    		$accountType = '1';
	    		} else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
		    		$accountType = '2';
	    		} else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
					$accountType = '4';
				}
				
				$this->putData($fp, array(
					'2',                                // 1 レコード区分 データレコード：2【固定値】
					$each['transfer_to_bank_code'] ,     // 2 振込先口座の銀行コード
					str_repeat(' ', 15),     // 3 被仕向銀行名
					$each['transfer_to_branch_code'],   // 4 振込先口座の支店コード
					str_repeat(' ', 15),   // 5 被仕向支店名
					str_repeat(' ', 5),                                // 6 統一手形交換所番号
					'1'/*$each['transfer_to_account_type']*/,  // 7 1...普通預金 2...当座預金 4...貯蓄預金 9...その他
					sprintf('%07d', $each['transfer_to_account_no']),    // 8 口座番号
					$accountName,  // 9 受取人名
					sprintf('%010d', $each['total_amount']),              // 10 振込金額
					'1',     // 11 新規コード
					'',                                 // 12 顧客コード1(任意)
				));
				
				$total += (int)$each['total_amount'];
			}
			
			// トレーラレコード
			$this->putData($fp, array(
				'8',            // 1 レコード区分 トレーラレコード：8【固定値】
				sprintf('%06d', count($items)),  // 2 データ・レコードの合計件数
				sprintf('%012d', $total),         // 3 合計金額
				str_repeat(' ', 101),            // 4 ダミー スペース
			));

			// エンドレコード
			$this->putData($fp, array(
				'9',            // 1 レコード区分 エンドレコード：9【固定値】
				str_repeat(' ', 119),            // 2 ダミー スペース

			));
				
			fclose($fp);
			
	        $this->_helper->binaryOutput(file_get_contents($path), array(
	            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	        ));
        }
    
    } 

    public function putData($fp, $csvRow)
    {	    
	    mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);
	}

    public function putDataMultiple($fp, $csvRow, $rowCount)
    {
	    mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);
		
		for ($count = 1; $count <= $rowCount - 1; $count++) {
			fputcsv($fp, array());
		}
	}
	
	/**
	 * 与えられた文字列を全銀フォーマットに変換
	 * @param string $kana 変換前文字列（カタカナ）
	 * @return string 全銀フォーマット対応文字列
	 */
	public function getZenginFormat($kana)
	{
	    // 全銀フォーマットの利用可能文字以外を除外するための正規表現(preg_replace用)
	    $_zengin_pattern = '/[^0-9A-Zｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝﾞﾟ\(\)｢｣\/\-\.\\ ]/u';
	
	    // 全銀フォーマット 特定文字列の置換リスト
	    $_zengin_replace_from = array('ｰ');
	    $_zengin_replace_to = array('-');
	
	    // アルファベットを大文字化、全文字を半角化、特定文字を置換、全銀フォーマット外の文字を除去
	    $kana = strtoupper($kana);
	    $kana = mb_convert_kana($kana, 'khsa');
	    $kana = str_replace($_zengin_replace_from, $_zengin_replace_to, $kana);
	    $kana = preg_replace($_zengin_pattern, '', $kana);
	
	    return $kana;
	}
	


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/update-planned                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払予定登録済みに変更                                     |
    +----------------------------------------------------------------------------*/
    public function updatePlannedAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        
    	$request = $this->getRequest();

		$payableTable = new Shared_Model_Data_AccountPayable();
		
		$dbAdapter = $payableTable->getAdapter();

		// POST送信時
		if ($request->isPost()) {
			$payableTable = new Shared_Model_Data_AccountPayable();

	        $selectObj = $payableTable->select();
	        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
			$selectObj->where('is_csv_target = 1');
			$selectObj->order('frs_account_payable.paying_plan_date DESC');
			
			$items = $selectObj->query()->fetchAll();

			foreach ($items as $each) {
				$payableTable->updateById($each['id'], array(
					'is_csv_target'  => '0',
					'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED,
				));
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    
	}
		

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/invoice-detail                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払完了確認 - 詳細                                    |
    +----------------------------------------------------------------------------*/
    public function invoiceDetailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/transaction-paid/invoice-list';
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

		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
		// 登録者
		$userTable                = new Shared_Model_Data_User();
    	$this->view->createdUser  = $userTable->getById($data['created_user_id']);

		
		if (!empty($data['paying_card_id'])) {
			// クレジット
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
			
			// 割当情報
			$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
			$this->view->historyItems = $historyItemData = $cardHistoryItemTable->getListByPayableId($id);
		} else {
			// 銀行口座・その他
			if (!empty($data['paying_bank_id'])) {
				$bankTable = new Shared_Model_Data_AccountBank();
				$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
			}
			
			// 割当情報
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			$this->view->historyItems = $historyItemData = $bankHistoryItemTable->getListByPayableId($id);    
		}


        // ネット購入委託
        if (!empty($data['online_purchase_id'])) {
	        $onlinePurchaseTable = new Shared_Model_Data_OnlinePurchase();
	        $this->view->onlinePurchaseData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $data['online_purchase_id']);
	        
        }
        
        if (!empty($data['transfer_to_connection_bank_id'])) {
        	$connectionBankTable = new Shared_Model_Data_ConnectionBank();
        	$this->view->connectionBankData = $connectionBankTable->getById($data['transfer_to_connection_bank_id']);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/site-detail                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サイト連動請求支払完了確認 - 詳細                          |
    +----------------------------------------------------------------------------*/
    public function siteDetailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/transaction-paid/site-list';
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

		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
		// 登録者
		$userTable                = new Shared_Model_Data_User();
    	$this->view->createdUser  = $userTable->getById($data['created_user_id']);
		
		
		if (!empty($data['paying_card_id'])) {
			// クレジット
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
			
			// 割当情報
			$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
			$this->view->historyItems = $historyItemData = $cardHistoryItemTable->getListByPayableId($id);
		} else {
			// 銀行口座・その他
			if (!empty($data['paying_bank_id'])) {
				$bankTable = new Shared_Model_Data_AccountBank();
				$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
			}
			
			
			if (!empty($data['transfer_to_connection_bank_id'])) {
				// 振込先 取引先金融機関ID
				//var_dump($data['transfer_to_connection_bank_id']);
				
				$connectionBankTable = new Shared_Model_Data_ConnectionBank();
				$this->view->connectionBankData = $connectionBankTable->getById($data['transfer_to_connection_bank_id']);
			}
			
			
			// 割当情報
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			$this->view->historyItems = $historyItemData = $bankHistoryItemTable->getListByPayableId($id);    
		}
		
    }
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-list                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード支払完了確認                                         |
    +----------------------------------------------------------------------------*/
    public function cardListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_paid_card_list');

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
			$conditions = array();
			$session->conditions['payment_status']      = $request->getParam('payment_status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['card_name']           = $request->getParam('card_name', '');
			$session->conditions['card_id']             = $request->getParam('card_id', '');

		} else if (empty($session->conditions) || !array_key_exists('payment_status', $session->conditions)) {
			$session->conditions['payment_status']      = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['card_name']           = '';
			$session->conditions['card_id']             = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		$this->view->viewType = $conditions['view_type'];
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$dbAdapter = $payableTable->getAdapter();
		
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->where('frs_account_payable.paying_method = ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);

		if ($session->conditions['payment_status'] !== '') {
			$selectObj->where('frs_account_payable.payment_status = ?', $session->conditions['payment_status']);
		}
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_payable.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_payable.account_title_id = ?', $session->conditions['account_title_id']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_payable.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_payable.target_connection_id = ?', $session->conditions['connection_id']);
		}

		if ($session->conditions['card_id'] !== '') {
			$selectObj->where('frs_account_payable.paying_card_id = ?', $session->conditions['card_id']);
		}

        
		$unpaidSelectObj = $payableTable->select();
		// グループID
        $unpaidSelectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$unpaidSelectObj->where('frs_account_payable.paying_method = ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$unpaidSelectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
		$unpaidSelectObj->where('payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
		
		if ($conditions['view_type'] === 'monthly') {
			$nDate = new Nutex_Date();
        	$from = $conditions['year'] . '-' . $conditions['month'] . '-01';
       		$to   = $conditions['year'] . '-' . $conditions['month'] . '-' . $nDate->getMonthEndDay($conditions['year'], $conditions['month']);
			$selectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
			$selectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
			$selectObj->order('frs_account_payable.paying_plan_date ASC');
			
			$unpaidSelectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
			$unpaidSelectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
			
			$zDate = new Zend_Date($conditions['year'] . '-' . $conditions['month'] . '-01', NULL, 'ja_JP');
			
			$zDate->sub('1', Zend_Date::MONTH);
			$conditionsPrev          = $conditions;
			$conditionsPrev['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsPrev['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->prevUrl = '/transaction-paid/card-list?' . http_build_query($conditionsPrev);
			
			$zDate->add('2', Zend_Date::MONTH);
			$conditionsNext          = $conditions;
			$conditionsNext['year']  = $zDate->get(Zend_Date::YEAR);
			$conditionsNext['month'] = $zDate->get(Zend_Date::MONTH);
			$this->view->nextUrl = '/transaction-paid/card-list?' . http_build_query($conditionsNext);
		
			$this->view->items = $items = $selectObj->query()->fetchAll();
			
		} else {
			// 全一覧
			$selectObj->order('frs_account_payable.paying_plan_date DESC');

			$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
	        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
			$paginator->setCurrentPageNumber($conditions['page']);
    
			foreach ($paginator->getCurrentItems() as $eachItem) {
				$items[] = $eachItem;
			}
			
	        $this->view->items = $items;
	        $this->view->pager($paginator);
		}
		
		$total = array();
		$total['total_count'] = 0;

		$unpaidTotal = array();
		$unpaidTotal['total_count'] = 0;
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList  = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        	
        	$total[$each['id']] = $each;
        	$total[$each['id']]['item_count'] = 0;
        	$total[$each['id']]['total'] = 0;
        	
        	$unpaidTotal[$each['id']] = $each;
        	$unpaidTotal[$each['id']]['item_count'] = 0;
        	$unpaidTotal[$each['id']]['total'] = 0;
        }
		$this->view->currencyList = $currencyList;
		
		
		// 未払合計(通貨毎)
		$unpaidList = $unpaidSelectObj->query()->fetchAll();
		foreach ($unpaidList as $eachItemUnpaid) {
			$unpaidTotal[$eachItemUnpaid['currency_id']]['item_count'] += 1;
			$unpaidTotal[$eachItemUnpaid['currency_id']]['total'] += (int)$eachItemUnpaid['total_amount'];
			$unpaidTotal['total_count'] += 1;
		}
		$this->view->unpaidTotal = $unpaidTotal;
		
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
    |  action_URL    * /transaction-paid/card-detail                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード支払完了確認 - 詳細                                  |
    +----------------------------------------------------------------------------*/
    public function cardDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id          = $id  = $request->getParam('id');
		$this->view->posTop      = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/transaction-paid/card-list';
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
        
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
		//$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);

		// クレジット
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
		// 割当情報
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $this->view->historyItems = $historyItemData = $cardHistoryItemTable->getListByPayableId($id);
        
        // ネット購入委託
        if (!empty($data['online_purchase_id'])) {
	        $onlinePurchaseTable = new Shared_Model_Data_OnlinePurchase();
	        $this->view->onlinePurchaseData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $data['online_purchase_id']);
	        
        }
        
        
    }



    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/update-basic                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報更新(Ajax)                                         |
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
				
				$payableTable = new Shared_Model_Data_AccountPayable();
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'account_title_id'           => $success['account_title_id'],             // 会計科目ID
						'memo'                       => $success['memo'],                         // 摘要
						'account_totaling_group_id'  => $success['account_totaling_group_id'],    // 採算コードID
					);
					
					$payableTable->updateById($id, $data);

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-paid/update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/update-summary                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 概要更新(Ajax)                                             |
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
				
				if (!empty($errorMessage['purchased_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable = new Shared_Model_Data_AccountPayable();
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'purchased_date' => $success['purchased_date'],  // 発生日(カード利用日)
					);
					
					$payableTable->updateById($id, $data);

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-paid/update-summary transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/update-payment                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払状況更新(Ajax)                                         |
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
				
				if (!empty($errorMessage['payment_status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払ステータス」を入力してください'));
                    return;
                } else if (!empty($errorMessage['paying_plan_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable = new Shared_Model_Data_AccountPayable();
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
				
				$payingBankId = 0;
				$payingCardId = 0;
	            if ($oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK) {	            
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	                $payingCardId = $success['paying_card_id'];
	                
	            } else if ($oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            }

	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'payment_status'          => $success['payment_status'],            // 支払ステータス
						'paying_plan_date'        => $success['paying_plan_date'],          // 支払予定日
						'paying_bank_id'          => $payingBankId,                         // 支払元銀行口座
						'paying_card_id'          => $payingCardId,                         // 支払元クレジットカード
						'paying_method_memo'      => $success['paying_method_memo'],        // 支払方法メモ
					);

					$payableTable->updateById($id, $data);

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-paid/update-payment-status transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-import-list                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード明細取込履歴                               |
    +----------------------------------------------------------------------------*/
    public function cardImportListAction()
    {
	    $this->view->menu = 'history';  

		$request  = $this->getRequest();
		
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$cardTable = new Shared_Model_Data_AccountCreditCard();
		
		$selectObj = $cardTable->select();
		$selectObj->order('frs_account_credit_card.content_order ASC');
		
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
    |  action_URL    * /transaction-paid/card-import-history                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード明細取込履歴                               |
    +----------------------------------------------------------------------------*/
    public function cardImportHistoryAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = '/transaction-paid/card-import-list';
	    
	    $this->view->menu = 'history';  
	    
  		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$request  = $this->getRequest();
		$this->view->id = $id     = $request->getParam('id');
		$page           = $request->getParam('page', '1');
		$this->view->viewType = $viewType = $request->getParam('view_type', 'history');
		
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$cardTable = new Shared_Model_Data_AccountCreditCard();
		$data = $cardTable->getById($id);

        if (empty($data)) {
			throw new Zend_Exception('/transaction-paid/card-import-history filed to fetch account title data');
		}

    	$this->view->data = $data;
		
		
		$cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
		$dbAdapter = $cardHistoryTable->getAdapter();
		
		$selectObj = $cardHistoryTable->select();
		
		$selectObj->joinLeft('frs_user', 'frs_account_credit_card_history.created_user_id = frs_user.id',array($cardHistoryTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->joinLeft('frs_account_credit_card', 'frs_account_credit_card_history.card_id = frs_account_credit_card.id', array($cardHistoryTable->aesdecrypt('card_name', false) . 'AS card_name'));
		
		$selectObj->where('frs_account_credit_card_history.card_id = ?', $id);
		$selectObj->where('frs_account_credit_card_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
		$selectObj->order('frs_account_credit_card_history.id DESC');
		
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
    |  action_URL    * /transaction-paid/card-import                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード CSV取込                                   |
    +----------------------------------------------------------------------------*/
    public function cardImportAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/transaction-paid/card-import-history';
        
		$request    = $this->getRequest();
		
		$cardTable = new Shared_Model_Data_AccountCreditCard();		
        $selectObj = $cardTable->select();
		$selectObj->order('content_order ASC');
		$this->view->cardList = $selectObj->query()->fetchAll();
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/import-csv                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード CSV取込(アップロード)                     |
    +----------------------------------------------------------------------------*/
    public function importCsvAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();

		$cardId = $request->getParam('card_id', '');
		if (empty($cardId)) {
	        $this->sendJson(array('result' => 'NG', 'message' => '「対象カード」を選択してください'));
	        return;
		}
		
		$payingPlanDate = $request->getParam('paying_plan_date', '');
		if (empty($payingPlanDate)) {
	        $this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を入力してください'));
	        return;
		}

		
		if (empty($_FILES['csv']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
        
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        $csvData = file_get_contents($_FILES['csv']['tmp_name']);
        $csvData = preg_replace("/\r\n|\r|\n/", "\n", $csvData);
        $dataEncoded = mb_convert_encoding($csvData, 'UTF-8', 'SJIS-win');

		$key = uniqid();
        $savePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($key . '.csv');
        
        $handle = fopen($savePath, "w+");
        
		// 一旦文字コードを変換したCSVを保存
        fwrite($handle, $dataEncoded);
        rewind($handle);
		
		$cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();

        $csvFilePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($key . '.csv');;
		
        if (file_exists($csvFilePath)) {  
            $handle = fopen($csvFilePath, "r");
            
            $skipHeaderCount = 1;
            
            // 説明行
            for ($count = 0; $count < $skipHeaderCount; $count++) {
            	$csvRow = fgetcsv($handle, 0, ","); // 0
            }
            
			$rowCount = 1;
			$itemList = array();
			while (($csvRow = fgetcsv($handle, 0, ",")) !== FALSE) {
				$csvRow['row_count'] = $rowCount;
				$itemList[] = $csvRow;
            	$rowCount++;
            }
			
			// エラーチェック
			foreach ($itemList as &$eachRow) {
				$eachRow[0] = str_replace('.', '', $eachRow[0]);
				$eachRow[2] = str_replace('.', '', $eachRow[2]);
				
				$eachRow[5] = str_replace(' ', '', $eachRow[5]);
				$eachRow[7] = str_replace(' ', '', $eachRow[7]);
				
				if (empty($eachRow[2])) {
					continue;
				}
				
				if (!preg_match('/^([0-9]{8})$/i', $eachRow[0])) {
		        	$this->sendJson(array('result' => 'NG', 'message' => ($eachRow['row_count'] + 1) . '行目：利用日は「YYYYMMDD」形式(8桁)である必要があります'));
		        	return;
		        } else if (!empty($eachRow[2]) && !preg_match('/^([0-9]{6})$/i', $eachRow[2])) {
		        	$this->sendJson(array('result' => 'NG', 'message' => ($eachRow['row_count'] + 1) . '行目：支払開始年月は「YYYYMM」形式(6桁)である必要があります'));
		        	return;
		        } else if (!preg_match('/^-?[0-9]+(,-?[0-9]+)*$/', $eachRow[5])) {
			        //$this->sendJson(array('result' => 'NG', 'message' => '「' . $eachRow[5] . '」はだめです。'));
		        	$this->sendJson(array('result' => 'NG', 'message' => ($eachRow['row_count'] + 1) . '行目：利用金額は半角数字およびカンマである必要があります'));
		        	return;
		        } else if (!empty($eachRow[7]) && $eachRow[7] != '-' && !preg_match('/^-?[0-9]+(,-?[0-9]+)*$/', $eachRow[7])) {
		        	$this->sendJson(array('result' => 'NG', 'message' => ($eachRow['row_count'] + 1) . '行目：当月請求額は半角数字およびカンマ、または「-」である必要があります'));
		        	return;
		        }
			}

			$data = array(
		        'management_group_id' => $this->_adminProperty['management_group_id'], // 管理グループID
		        'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
		        'import_key'          => $key,                                         // ファイル名
		        
		        'paying_plan_date'    => $payingPlanDate,                              // 支払予定日
		        'card_id'             => $cardId,                                      // カードID
		        
		        'created_user_id'     => $this->_adminProperty['id'],                  // 取込実施者
		
	            'created'             => new Zend_Db_Expr('now()'),
	            'updated'             => new Zend_Db_Expr('now()'),
			);
			
            try {
				$cardHistoryTable->create($data);
				$importId = $cardHistoryTable->getLastInsertedId('id');
				
	            foreach ($itemList as &$eachRow) {
					$this->importData($eachRow['row_count'], $key, $importId, $eachRow);
				}
				
            } catch (Exception $e) {
				throw new Zend_exception($e);
            }
            
        } else {
	        $this->sendJson(array('result' => 'NG', 'message' => 'ファイルの読込に失敗しました'));
	        return;
        }

    	$this->sendJson(array('result' => 'OK', 'id' => $importId, 'count' => $rowCount));
    	return;
    }
    
	/*
	 * 1件取込
	*/
    private function importData($rowCount, $importKey, $importId, $csvRow)
    {
	    if (empty($csvRow[2])) {
			return;
		}
				
    	$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
		$amount = str_replace(array("\\", "¥", ','), '', $csvRow[7]);
		
		//$purchasedDate = str_replace(array('年', '月', '日'), '-', $csvRow[0]);
		
        $year  = substr($csvRow[0], 0, 4);
        $month = substr($csvRow[0], 4, 2);
        $day   = substr($csvRow[0], 6, 2);
		
		$purchasedDate = $year . '-' . $month . '-' . $day;
		
		if (empty($csvRow[7]) || empty($csvRow[7])) {
			return;
		}
		
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getBySymbol($this->_adminProperty['management_group_id'], '¥');
		
		$rowData = array(
	        'management_group_id' => $this->_adminProperty['management_group_id'], // 管理グループID
			'status'              => Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE,
	        'card_history_id'     => $importId,                   // クレジットカード取込CSVID
	        
	        'row_count'           => $rowCount,                   // 行番号
	        'purchased_date'      => $purchasedDate,              // 利用日(購入日)
	        'name'                => $csvRow[1],                  // 項目名

	        'start_month'         => $csvRow[2],                  // 支払開始月
	        'times'               => $csvRow[3],                  // 支払回数
	        'time_count'          => $csvRow[4],                  // 支払今回回数
	        'charge'              => $csvRow[6],                  // 手数料
	        'balance'             => $csvRow[8],                  // 残り残高
        
	        'currency_id'         => $currencyData['id'],         // 通貨ID
	        'amount'              => $amount,                     // 今月支払額
	        
	        'payable_id'          => 0,                           // 買掛ID    
	
            'created'             => new Zend_Db_Expr('now()'),
            'updated'             => new Zend_Db_Expr('now()'),
		);
		
		$cardHistoryItemTable->create($rowData);
    }


    /* 割り当て複数対応 */
    public function updateAction()
    {
	    $cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
	    $selectObj = $cardHistoryItemTable->select();
	    $dataList = $selectObj->query()->fetchAll();
	    
	    foreach ($dataList as $each) {
		    $payableIds = NULL;
		    if (!empty($each['payable_id'])) {
			    $cardHistoryItemTable->updateById($each['id'], array(
			    'payable_ids' => serialize(array($each['payable_id'])),
			    ));
		    } 
	    }
	    
	    echo 'OK;';
	    exit;
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/add-card-import-item                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード明細取込詳細                               |
    +----------------------------------------------------------------------------*/
    public function addCardImportItemAction()
    {
	    exit;
	    
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getBySymbol($this->_adminProperty['management_group_id'], '¥');
		
        $data = array(
	        'management_group_id' => $this->_adminProperty['management_group_id'],     // 管理グループID
	        'card_history_id'     => '31',                                             // クレジットカード取込CSVID
	        'status'              => Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE, // ステータス
	        
	        'row_count'           => '22',                     // 行番号
	        'purchased_date'      => '2019-09-28',             // 利用日(購入日)
	        'name'                => 'ラクテントラベル　コクナイシュクハク', // 項目名
	        
	        'start_month'         => '201910',                 // 支払開始月
	        'times'               => '1',                      // 支払回数
	        'time_count'          => '1',                      // 支払今回回数
	        'charge'              => '',                       // 手数料
	        'balance'             => '0',                      // 残り残高
	         
	        'currency_id'         => $currencyData['id'],      // 通貨ID
	        'amount'              => '-8832',                  // 今月支払額

            'created'             => new Zend_Db_Expr('now()'),
            'updated'             => new Zend_Db_Expr('now()'),
        );
        
	    $cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
	    $cardHistoryItemTable->create($data);
	    echo 1;
	    exit;
	}
	  
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-import-detail                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード明細取込詳細                               |
    +----------------------------------------------------------------------------*/
    public function cardImportDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
  
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$request  = $this->getRequest();
		$this->view->id        = $id         = $request->getParam('id');
		$this->view->posTop    = $posTop     = $request->getParam('pos'); 
		$this->view->direct    = $direct     = $request->getParam('direct');
		$this->view->targetRow = $targetRow  = $request->getParam('target_row');

		$cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $this->view->historyData = $historyData = $cardHistoryTable->getById($id);

		if (empty($direct)) {
			$this->view->backUrl = '/transaction-paid/card-import-history?id=' . $historyData['card_id'];
		}
		 
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $this->view->items = $cardHistoryItemTable->getList($id);

		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList   = array();
        $accountTitleItems = $accountTitleTable->getAllList();
        
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
		
		// 組織一覧
		$groupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupList = $groupTable->getList();
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/remove-attach                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 割当解除(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function removeAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');
		$payableId    = $request->getParam('payable_id');
		
        if (empty($rowId)) {
        	throw new Zend_Exception('/transaction-bank/remove-attach - no row id');
        }

		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $rowData = $cardHistoryItemTable->getById($rowId);
		
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-paid/remove-attach - no target data');
        }
        
        $payableIds = $rowData['payable_ids'];
        
        $newIds = array();
        foreach ($payableIds as $eachId) {
	        if ($payableId != $eachId) {
		        $newIds[] = $eachId;
	        }
        }
        
        $cardHistoryItemTable->updateById($rowId, array(
        	'payable_ids' => serialize($newIds),
        	'status'      => Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE,
        ));

	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-finish-attach                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 割当完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function cardFinishAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');

        if (empty($rowId)) {
        	throw new Zend_Exception('/transaction-bank/card-finish-attach - no row id');
        }

		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
		$payableTable         = new Shared_Model_Data_AccountPayable();
		$receivableTable      = new Shared_Model_Data_AccountReceivable();	
		
        $rowData = $cardHistoryItemTable->getById($rowId);

        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-paid/card-finish-attach - no target data');
        }
		
		$multipleComplete = false;
		
		$rowTotal    = -(int)$rowData['amount'];
        $total       = 0;
        $payableIds     = $rowData['payable_ids'];
        $receivableIds  = $rowData['receivable_ids'];

        if (!empty($payableIds)) {
			foreach ($payableIds as $eachId) {
				$payableData = $payableTable->getByIdForAnyGroup($eachId);
				
				$total = $total - $payableData['total_amount'];
				
				// 対象の支払予定の割当合計が一致していたら消込完了可能
				$historyItems = $cardHistoryItemTable->getListByPayableId($eachId);
				
				$paidTotal = 0;
				if (count($historyItems) > 1) {
					foreach ($historyItems as $eachHistory) {
						$paidTotal -= (int)$eachHistory['amount'];
					}
					
					if (-(int)$payableData['total_amount'] === $paidTotal) {
						$multipleComplete = true;
						break;
					}
				}
			}
        }

        if (!empty($receivableIds)) {
			foreach ($receivableIds as $eachId) {
				$receivableData = $receivableTable->getByIdForAnyGroup($eachId);

				$total = $total + $receivableData['total_amount'];

				// 対象の支払予定の割当合計が一致していたら消込完了可能
				$historyItems = $cardHistoryItemTable->getListByReceivableId($eachId);
				
				$recievedTotal = 0;
				if (count($historyItems) > 1) {
					foreach ($historyItems as $eachHistory) {
						$recievedTotal += (int)$eachHistory['received_amount'];
					}
					
					if ((int)$receivableData['total_amount'] === $recievedTotal) {
						$multipleComplete = true;
						break;
					}
				}
			}
        }  
        

		// 金額不一致
		if ($multipleComplete === false && (int)$total !== $rowTotal) {
			$this->sendJson(array('result' => 'NG', 'message' => $rowType . 'と割当額が一致しません'));
			return;
		}	

		// 割当完了にする
		$cardHistoryItemTable->updateById($rowId, array('status' => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_ATTACHED));	
		
		if (!empty($payableIds)) {
			foreach ($payableIds as $eachId) {
				$payableTable->updateById($eachId, array(
					'is_attached'    => '1',
					'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,
				));
			}
		}
		
		
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-detach-payable                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定割当解除(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function cardDetachPayableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');
		$payableId    = $request->getParam('payable_id');

        if (empty($payableId)) {
        	throw new Zend_Exception('/transaction-paid/card-detach-receivable - no receivable id');
        }
        
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $rowData = $cardHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-paid/card-detach-receivable - no target data');
        }
        
        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $historyData = $cardHistoryTable->getById($rowData['card_history_id']);
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$payableData = $payableTable->getById($this->_adminProperty['management_group_id'], $payableId);

		$payableIds = $rowData['payable_ids'];
		
		if (empty($payableIds)) {
			throw new Zend_Exception('/transaction-paid/card-detach-receivable - no payable ids');
		}

		if (!in_array($payableId, $payableIds)) {
			throw new Zend_Exception('/transaction-paid/card-detach-receivable - no target payable ids');
		}

		$newIds = array();
		
		foreach ($payableIds as $each) {
			if ($each !== $payableId) {
				$newIds[] = $each;
			}
		}
		
		
		$cardHistoryItemTable->getAdapter()->beginTransaction();
		
		try {
			$cardHistoryItemTable->updateById($rowId, array(
				'payable_ids' => serialize($newIds),
			));
			
			// commit
            $cardHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $cardHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-paid/card-detach-receivable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-cancel-finish-attach                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 割当完了解除(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function cardCancelFinishAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');

        if (empty($rowId)) {
        	throw new Zend_Exception('/transaction-bank/card-cancel-finish-attach - no row id');
        }
        
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
		$payableTable         = new Shared_Model_Data_AccountPayable();
		
        $rowData = $cardHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-paid/finish-attach - no target data');
        }
        
        
        $cardHistoryItemTable->getAdapter()->beginTransaction(); 
        
		try {
	        $cardHistoryItemTable->updateById($rowId, array('status' => Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE));
			
	        $payableIds = $rowData['payable_ids'];
	        
			if (!empty($payableIds)) {
				foreach ($payableIds as $eachId) {
					$payableTable->updateById($eachId, array(
						'is_attached'    => '0',
						//'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,
					));
				}
			}
		
			// commit
            $cardHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $cardHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-paid/card-detach-receivable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-select-payable                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 消込選択                                                   |
    +----------------------------------------------------------------------------*/
    public function cardSelectPayableAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request  = $this->getRequest();
		$this->view->rowId = $rowId = $request->getParam('row_id');

		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $this->view->rowData = $rowData = $cardHistoryItemTable->getById($rowId);

        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $this->view->historyData = $cardHistoryTable->getById($rowData['card_history_id']);
        
		$this->view->backUrl = '/transaction-paid/card-import-detail?id=' . $rowData['card_history_id'];
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		
		// 消込有力候補(金額が一致)
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		$selectObj->where('frs_account_payable.status = ?', Shared_Model_Code::PAYABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_payable.paying_method = ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
        $selectObj->where('frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID . ' OR frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED);
        $selectObj->where('frs_account_payable.total_amount = ?', $rowData['amount']);
        $this->view->mainItems = $selectObj->query()->fetchAll();
		
		// その他候補
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		$selectObj->where('frs_account_payable.status = ?', Shared_Model_Code::PAYABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_payable.paying_method = ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$selectObj->where('frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID . ' OR frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED);
        $selectObj->where('frs_account_payable.total_amount != ?', $rowData['amount']);
        $selectObj->order('frs_account_payable.paying_plan_date ASC');
        $this->view->otherItems = $selectObj->query()->fetchAll();

		// 毎月支払項目
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
        $selectObj = $payableTemplateTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable_template.target_connection_id = frs_connection.id', array($payableTemplateTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_payable_template.created_user_id = frs_user.id',array($payableTemplateTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->where('frs_account_payable_template.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        $selectObj->where('frs_account_payable_template.template_type = ?', Shared_Model_Code::PAYABLE_TEMPLATE_TYPE_FIXED);
        $selectObj->where('frs_account_payable_template.status = ?', Shared_Model_Code::PAYABLE_STATUS_APPROVED);
        $selectObj->where('frs_account_payable_template.paying_method = ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
		$selectObj->order('frs_account_payable_template.account_title_id ASC');
		$selectObj->order('frs_account_payable_template.id ASC');
		$this->view->monthlyItems = $selectObj->query()->fetchAll();
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList   = array();
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
    |  action_URL    * /transaction-paid/card-attach                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定に割当(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function cardAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request   = $this->getRequest();
		$rowId     = $request->getParam('row_id');
		$payableId = $request->getParam('payable_id');

        if (empty($payableId)) {
        	throw new Zend_Exception('/transaction-paid/card-attach - no payable id');
        }
        
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $rowData = $cardHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-paid/card-attach - no target data');
        }
        
        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $historyData = $cardHistoryTable->getById($rowData['card_history_id']);
		
		$payableTable = new Shared_Model_Data_AccountPayable();

		$payableIds = $rowData['payable_ids'];
		if (!empty($payableIds)) {
			if (!in_array($payableId, $payableIds)) {
				$payableIds[] = $payableId;
			}
			
		} else {
			$payableIds = array($payableId);
		}
		
		try {
			$cardHistoryItemTable->getAdapter()->beginTransaction();
			
			$cardHistoryItemTable->updateById($rowId, array(
				'payable_id'  => $payableId,
				'payable_ids' => serialize($payableIds),
			));
		
			// commit
            $cardHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $cardHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-paid/card-add-payable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-attach-monthly                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理項目割当(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function cardAttachMonthlyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$rowId      = $request->getParam('row_id');
		$templateId = $request->getParam('payable_template_id');

		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $rowData = $cardHistoryItemTable->getById($rowId);
        
        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $historyData = $cardHistoryTable->getById($rowData['card_history_id']);
        
		$payableTable    = new Shared_Model_Data_AccountPayable();
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$templateData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $templateId);
		
    	//$displayId = $payableTable->getNextDisplayId();

		$payableIds = $rowData['payable_ids'];
		if (!empty($payableIds)) {
		    $this->sendJson(array('result' => 'NG', 'message' => '既に割当があるため、登録できません'));
    		return;
		}
		
   		$cardHistoryItemTable->getAdapter()->beginTransaction();
   		
    	try {
			$payableData = array(
		        'management_group_id'     => $this->_adminProperty['management_group_id'],
		        'status'                  => Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY, // クレジット請求明細から追加
		        
		        'template_id'             => $templateId,                                // 毎月支払テンプレートID
		        
		        'order_form_ids'          => serialize(array()),                         // 発注IDリスト
		        
				'account_title_id'        => $templateData['account_title_id'],          // 会計科目ID
				'target_connection_id'    => $templateData['target_connection_id'],      // 支払先
				
				'paying_type'             => Shared_Model_Code::PAYABLE_PAYING_TYPE_MONTHLY, // 支払種別(請求支払/カード支払/自動振替)
	
				'file_list'               => json_encode(array()),                       // 請求書ファイルアップロード
				
				'paid_user_id'            => 0,                                          // 支払処理担当者
				'paid_date'               => NULL,                                       // 支払完了日
	
				'memo'                    => $templateData['description'],               // 摘要
				
				'paying_plan_date'        => $historyData['paying_plan_date'],           // 支払予定日
				
				'paying_method'           => $templateData['paying_method'],             // 支払方法
				'paying_bank_id'          => $templateData['paying_bank_id'],            // 支払元銀行口座
				'paying_card_id'          => $templateData['paying_card_id'],            // 支払元クレジットカード
				'paying_method_memo'      => '',                                         // 支払方法メモ
				
				'total_amount'            => $templateData['total_amount'],              // 支払額
				'currency_id'             => $rowData['currency_id'],                    // 通貨単位
				'tax_division'            => $templateData['tax_division'],              // 税区分
				'tax'                     => $templateData['tax'],                       // 消費税
				
				'created_user_id'         => $this->_adminProperty['id'],                // 支払申請者
				'approval_user_id'        => 0,                                          // 承認者
				
	            'created'                 => new Zend_Db_Expr('now()'),
	            'updated'                 => new Zend_Db_Expr('now()'),
			);

			$payableTable->create($payableData);
			$payableId = $payableTable->getLastInsertedId('id');
		
			$cardHistoryItemTable->updateById($rowId, array(
				'payable_id' => $payableId,
				'payable_ids' => serialize(array($payableId)),
			));
			
			// commit
            $cardHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $cardHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-paid/card-add-payable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-add-payable                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定に追加(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function cardAddPayableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request   = $this->getRequest();
		$rowId     = $request->getParam('row_id');

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
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払先取引先」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
		        $rowData = $cardHistoryItemTable->getById($rowId);
		        
		        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
		        $historyData = $cardHistoryTable->getById($rowData['card_history_id']);
				
				$payableTable = new Shared_Model_Data_AccountPayable();

				$payableIds = $rowData['payable_ids'];
				if (!empty($payableIds)) {
				    $this->sendJson(array('result' => 'NG', 'message' => '既に割当があるため、登録できません'));
		    		return;
				}
		
   		
				$cardHistoryItemTable->getAdapter()->beginTransaction();
				
				try {
					$payableData = array(
				        'management_group_id'     => $this->_adminProperty['management_group_id'],
				        'status'                  => Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY, // クレジット請求明細から追加
				        'payment_status'          => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,       // 支払ステータス (支払済)
				        'order_form_ids'          => serialize(array()),                    // 発注IDリスト
						'account_title_id'        => $success['account_title_id'],          // 会計科目ID
						
						'target_connection_id'    => $success['target_connection_id'],      // 支払先
						
						'purchased_date'          => $rowData['purchased_date'],            // クレジット利用日
						
						'paying_plan_date'        => $historyData['paying_plan_date'],      // 支払予定日
		
						'total_amount'            => $rowData['amount'],                    // 支払額
						'currency_id'             => $rowData['currency_id'],               // 通貨単位
						'tax_division'            => Shared_Model_Code::TAX_DIVISION_TAXATION,  // 税区分
						'tax'                     => '',                                    // 消費税
						'memo'                    => $success['memo'],
						
						'paying_type'             => Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD, // 支払種別(請求支払/カード支払/自動振替)
						'paying_method'           => Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT,    // 支払方法
		
						'paying_method_memo'      => '',                                    // 支払方法メモ
		
						'paying_bank_id'          => 0,                                     // 支払元銀行口座
						'paying_card_id'          => $historyData['card_id'],               // 支払元クレジットカード
						
						'file_list'               => json_encode(array()),                  // 請求書ファイルアップロード
						
						'paid_user_id'            => 0,                                     // 支払処理担当者
						'paid_date'               => NULL,                                  // 支払完了日
						
						'created_user_id'         => $this->_adminProperty['id'],           // 支払申請者
						'approval_user_id'        => 0,                                     // 承認者
						
		                'created'                 => new Zend_Db_Expr('now()'),
		                'updated'                 => new Zend_Db_Expr('now()'),
			        );
					
					$payableTable->create($payableData);
					$payableId = $payableTable->getLastInsertedId('id');
					
					$cardHistoryItemTable->updateById($rowId, array(
						'payable_id' => $payableId,
						'payable_ids' => serialize(array($payableId)),
					));
					
					// commit
		            $cardHistoryItemTable->getAdapter()->commit();
		            
		        } catch (Exception $e) {
		            $cardHistoryItemTable->getAdapter()->rollBack();
		            throw new Zend_Exception('/transaction-paid/card-add-payable transaction failed: ' . $e);
		            
		        }
		        
			    $this->sendJson(array('result' => 'OK'));
		    	return;
		    }
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));

    }
 
 
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-paid/card-select-receivable                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 消込選択                                                   |
    +----------------------------------------------------------------------------*/
    public function cardSelectReceivableAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request  = $this->getRequest();
		$this->view->rowId = $rowId = $request->getParam('row_id');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
			
		}
		
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $this->view->rowData = $rowData = $cardHistoryItemTable->getById($rowId);

        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $this->view->historyData = $cardHistoryTable->getById($rowData['card_history_id']);
        
		$this->view->backUrl = '/transaction-paid/card-import-detail?id=' . $rowData['card_history_id'];
		
		$receivableTable = new Shared_Model_Data_AccountReceivable();
		
		// 消込有力候補(金額が一致)
		$selectObj = $receivableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        $selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_CARD);
		$selectObj->where('frs_account_receivable.status = ?', Shared_Model_Code::RECEIVABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_receivable.payment_status != ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_CANCELED);
		$selectObj->where('frs_account_receivable.is_attached = 0');
        $selectObj->where('frs_account_receivable.total_amount = ?', -(int)$rowData['amount']);
        $this->view->mainItems = $selectObj->query()->fetchAll();
		
		// その他候補
		$selectObj = $receivableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        $selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_CARD);
		$selectObj->where('frs_account_receivable.status = ?', Shared_Model_Code::RECEIVABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_receivable.payment_status != ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_CANCELED);
		$selectObj->where('frs_account_receivable.is_attached = 0');
        $selectObj->where('frs_account_receivable.total_amount != ?', -(int)$rowData['received_amount']);
        $selectObj->order('frs_account_receivable.receive_plan_date ASC');
        $this->view->otherItems = $selectObj->query()->fetchAll();

		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList   = array();
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
    |  action_URL    * /transaction-paid/card-attach-receivable                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定に割当(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function cardAttachReceivableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request   = $this->getRequest();
		$rowId     = $request->getParam('row_id');
		$receivableId = $request->getParam('receivable_id');

        if (empty($receivableId)) {
        	throw new Zend_Exception('/transaction-paid/card-attach-receivable - no payable id');
        }
        
		$cardHistoryItemTable = new Shared_Model_Data_AccountCreditCardHistoryItem();
        $rowData = $cardHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-paid/card-attach - no target data');
        }
        
        $cardHistoryTable = new Shared_Model_Data_AccountCreditCardHistory();
        $historyData = $cardHistoryTable->getById($rowData['card_history_id']);
		
		$receivableTable = new Shared_Model_Data_AccountReceivable();

		$receivableIds = $rowData['receivable_ids'];
		if (!empty($receivableIds)) {
			if (!in_array($receivableId, $receivableIds)) {
				$receivableIds[] = $receivableId;
			}
			
		} else {
			$receivableIds = array($receivableId);
		}
		
		try {
			$cardHistoryItemTable->getAdapter()->beginTransaction();
			
			$cardHistoryItemTable->updateById($rowId, array(
				'receivable_ids' => serialize($receivableIds),
			));
		
			// commit
            $cardHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $cardHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-paid/card-add-payable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }  
    
}

