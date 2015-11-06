<?php
/**
 * class TransactionBankController
 */
 
class TransactionBankController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '取引処理';
		$this->view->menuCategory     = 'transaction';
		$this->view->menu = 'history';  

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/gmo-update?id=                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * CSVデータのgmo_item_keyの修正(develop)                     |
    +----------------------------------------------------------------------------*/
    public function gmoUpdateAction()
    {
		$request = $this->getRequest();
		$id    = $request->getParam('id');
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		$selectObj = $bankHistoryItemTable->select();
		$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history.id = frs_account_bank_history_item.bank_history_id', array());
		$selectObj->where('frs_account_bank_history.bank_id = ?', $id);
		$selectObj->where('gmo_item_key IS NULL');
		
		$items = $selectObj->query()->fetchAll();
		
		foreach ($items as $each) {
			$bankHistoryItemTable->updateById($each['id'], array(
				'gmo_item_key' => str_replace('-', '', $each['target_date']) . '000000000000',
			));
		}
		
		echo 'OK';
		exit;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/gmo-update-check?id=                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * gmo_item_keyの調整確認(develop)                            |
    +----------------------------------------------------------------------------*/
    public function gmoUpdateCheckAction()
    {
		$request = $this->getRequest();
		$id    = $request->getParam('id');
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		$selectObj = $bankHistoryItemTable->select();
		$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history.id = frs_account_bank_history_item.bank_history_id', array());
		$selectObj->where('frs_account_bank_history.bank_id = ?', $id);
	
		$items = $selectObj->query()->fetchAll();
		
		foreach ($items as $each) {
			echo 'id:', $each['target_date'] . ' target_date:' . $each['target_date'] . ' gmo_item_key: ' . $each['gmo_item_key'] . "<br>";
		}
		
		echo 'OK';
		exit;
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/update-recieved                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * (DEBUG)入金割り当て修正                                    |
    +----------------------------------------------------------------------------*/
    public function updateRecievedAction()
    {
	    $bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		
		$managementGroupId = '1';
		$id = '3754';
		
		$targetData = $bankHistoryItemTable->getById($id);

		$bankHistoryItemTable->updateById($id, array(
        	'paid_amount'     => '166',                         // 出金額
			'received_amount' => '0',                     // 預かり額(入金額)
			//'payable_ids'    => serialize(array()),
			//'receivable_ids' => serialize(array()),
			//'paid_amount' =>  '225720',
		));
		
	    echo 'OK;';
	    exit;
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/update-history-item                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * (DEBUG)出金割り当て修正                                    |
    +----------------------------------------------------------------------------*/
    public function updateHistoryItemAction()
    {
	    $bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();

		$id = '3754';
		
		$targetData = $bankHistoryItemTable->getById($id);
		//var_dump($targetData);exit;
		
		$bankHistoryItemTable->updateById($id, array(
			'paid_amount'     => '166',
			'received_amount' => '0',
		));
		
	    echo 'OK;';
	    exit;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/update-paid                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * (DEBUG)出金割り当て修正                                    |
    +----------------------------------------------------------------------------*/
    public function updatePaidAction()
    {
	    $bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();

		$id = '728';
		
		$targetData = $bankHistoryItemTable->getById($id);
		var_dump($targetData['payable_ids']);exit;
		
		$bankHistoryItemTable->updateById($id, array(
			'payable_ids' => serialize(array()),
		));
		
	    echo 'OK;';
	    exit;
	}
	    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/list                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座一覧                                               |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$bankTable = new Shared_Model_Data_AccountBank();
		
		$dbAdapter = $bankTable->getAdapter();

        $selectObj = $bankTable->select();
		$selectObj->order('content_order ASC');
		
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
    |  action_URL    * /transaction-bank/import-history                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座明細取込履歴                                       |
    +----------------------------------------------------------------------------*/
    public function importHistoryAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/transaction-bank/list';

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$request  = $this->getRequest();
		$page     = $request->getParam('page', '1');
		$this->view->bankId = $bankId = $request->getParam('bank_id');
		
		$bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankTable->getById($bankId);

		$bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
		$dbAdapter = $bankHistoryTable->getAdapter();
		
		$selectObj = $bankHistoryTable->select();
		$selectObj->joinLeft('frs_user', 'frs_account_bank_history.created_user_id = frs_user.id', array($bankHistoryTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		$selectObj->where('frs_account_bank_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
		$selectObj->where('frs_account_bank_history.bank_id = ?', $bankId);
		$selectObj->order('frs_account_bank_history.term_form DESC');
		
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
    |  action_URL    * /transaction-bank/log-list                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座明細取込履歴                                       |
    +----------------------------------------------------------------------------*/
    public function logListAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/transaction-bank/list';

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$request = $this->getRequest();
		$this->view->bankId = $bankId = $request->getParam('bank_id');
		$this->view->posTop    = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('transaction_bank_log_list_3');

		if (empty($session->conditions)) {
			$session->conditions['page']          = '1';
			$session->conditions['year']          = '';
			$session->conditions['month']         = '';
			$session->conditions['status']        = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['year']          = $request->getParam('year', '');
			$session->conditions['month']         = $request->getParam('month', '');
			$session->conditions['status']        = $request->getParam('status', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		

        $bankTable = new Shared_Model_Data_AccountBank();
        $this->view->bankData = $bankData = $bankTable->getById($bankId);
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		$selectObj = $bankHistoryItemTable->select();
		$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history.id = frs_account_bank_history_item.bank_history_id', array());
		$selectObj->joinLeft('frs_currency', 'frs_account_bank_history_item.currency_id = frs_currency.id', 'name AS currency_name');
		$selectObj->where('frs_account_bank_history.bank_id = ?', $bankId);
		
		$selectObj->where('frs_account_bank_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
		
		if ($session->conditions['status'] !== '') {
			$selectObj->where('frs_account_bank_history_item.status = ?', $session->conditions['status']);
		} else {
			$selectObj->where('frs_account_bank_history_item.status != ?', Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_DELETED);
		}
		
		if (!empty($session->conditions['year']) && !empty($session->conditions['month'])) {
			$dateUtility = new Nutex_Date();
			
			$from  = $session->conditions['year'] . '-' . sprintf('%02d', $session->conditions['month']) . '-01';  // 集計期間 開始日		
		    $to    = $session->conditions['year'] . '-' . sprintf('%02d', $session->conditions['month']) . '-' . $dateUtility->getMonthEndDay($session->conditions['year'], $session->conditions['month']);    // 集計期間 期間終了日
		
			$selectObj->where('target_date >= ?', $from);
			$selectObj->where('target_date <= ?', $to);
		}
		
		if ($bankData['bank_code'] === '0310') {
			$selectObj->order('frs_account_bank_history_item.gmo_item_key DESC');
		} else {
			$selectObj->order('frs_account_bank_history_item.jnb_time DESC');
		}
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankTable->getById($bankId);
		
		// 会計科目(全てのグループ)
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
		
		
		
		// 割当状況
		$attachmentList = array();
		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		
		for ($count = 1; $count <= 4; $count++) {
			$year  = $zDate->get('yyyy');
			$month = $zDate->get('MM');
			
			$dateUtility = new Nutex_Date();
		    $from = $year . '-' . $month . '-01';
		    $to   = $year . '-' . $month . '-' . $dateUtility->getMonthEndDay($year, $month);
	    
			$result = $bankHistoryItemTable->haveNoneAttachWithTerm($bankId, $from, $to);

			$attachmentList[] = array(
				'year_month' => $year . '年' . $month . '月',
				'item_count' => $result,
			);
			
			$zDate->sub('1', Zend_Date::MONTH);
		}
		
		$this->view->attachmentList = $attachmentList;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/log-list-debug                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座明細取込履歴(DEBUG)                             |
    +----------------------------------------------------------------------------*/
    public function logListDebugAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/transaction-bank/list';

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$request = $this->getRequest();
		$this->view->bankId = $bankId = $request->getParam('bank_id');
		$this->view->posTop    = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('transaction_bank_log_list_2');

		if (empty($session->conditions)) {
			$session->conditions['page']          = '1';
			$session->conditions['year']          = '';
			$session->conditions['month']         = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['year']          = $request->getParam('year', '');
			$session->conditions['month']         = $request->getParam('month', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		

        $bankTable = new Shared_Model_Data_AccountBank();
        $this->view->bankData = $bankData = $bankTable->getById($bankId);
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		$selectObj = $bankHistoryItemTable->select();
		$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history.id = frs_account_bank_history_item.bank_history_id', array());
		$selectObj->joinLeft('frs_currency', 'frs_account_bank_history_item.currency_id = frs_currency.id', 'name AS currency_name');
		$selectObj->where('frs_account_bank_history.bank_id = ?', $bankId);
		$selectObj->where('frs_account_bank_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);

		if (!empty($session->conditions['year']) && !empty($session->conditions['month'])) {
			$dateUtility = new Nutex_Date();
			
			$from  = $session->conditions['year'] . '-' . sprintf('%02d', $session->conditions['month']) . '-01';  // 集計期間 開始日		
		    $to    = $session->conditions['year'] . '-' . sprintf('%02d', $session->conditions['month']) . '-' . $dateUtility->getMonthEndDay($session->conditions['year'], $session->conditions['month']);    // 集計期間 期間終了日
		
			$selectObj->where('target_date >= ?', $from);
			$selectObj->where('target_date <= ?', $to);
		}
		
		if ($bankData['bank_code'] === '0310') {
			$selectObj->order('frs_account_bank_history_item.gmo_item_key DESC');
		} else {
			$selectObj->order('frs_account_bank_history_item.jnb_time DESC');
		}
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankTable->getById($bankId);
		
		// 会計科目(全てのグループ)
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
		
		
		
		// 割当状況
		$attachmentList = array();
		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		
		for ($count = 1; $count <= 4; $count++) {
			$year  = $zDate->get('yyyy');
			$month = $zDate->get('MM');
			
			$dateUtility = new Nutex_Date();
		    $from = $year . '-' . $month . '-01';
		    $to   = $year . '-' . $month . '-' . $dateUtility->getMonthEndDay($year, $month);
	    
			$result = $bankHistoryItemTable->haveNoneAttachWithTerm($bankId, $from, $to);

			$attachmentList[] = array(
				'year_month' => $year . '年' . $month . '月',
				'item_count' => $result,
			);
			
			$zDate->sub('1', Zend_Date::MONTH);
		}
		
		$this->view->attachmentList = $attachmentList;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/history-item-delete                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 履歴1件破棄(管理権限あり)(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function historyItemDeleteAction()
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
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			
			$targetItem = $bankHistoryItemTable->getById($id);
			

			$targetItem['payable_ids'] = unserialize($targetItem['payable_ids']);
			$targetItem['receivable_ids'] = unserialize($targetItem['receivable_ids']);
			
			if (!empty($targetItem['payable_ids'])) {
				$this->sendJson(array('result' => 'NG', 'error' => '割当済みがあるため、削除できません'));
				return;
			} else if (!empty($targetItem['receivable_ids'])) {
				$this->sendJson(array('result' => 'NG', 'error' => '割当済みがあるため、削除できません'));
				return;
			}
				
			try {
				$bankHistoryItemTable->getAdapter()->beginTransaction();
				
				$bankHistoryItemTable->updateById($id, array(
					'status' => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_DELETED,
				));
			
                // commit
                $bankHistoryItemTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $bankHistoryItemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-product/history-item-delete transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/delete-history                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * CSV取り込み単位破棄(管理権限あり)(Ajax)                    |
    +----------------------------------------------------------------------------*/
    public function deleteHistoryAction()
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
			$bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			
			$items = $bankHistoryItemTable->getList($id);
			
			
			foreach ($items as $each) {
				$each['payable_ids'] = unserialize($each['payable_ids']);
				$each['receivable_ids'] = unserialize($each['receivable_ids']);
				
				if (!empty($each['payable_ids'])) {
					$this->sendJson(array('result' => 'NG', 'error' => '割当済みがあるため、削除できません'));
					return;
				} else if (!empty($each['receivable_ids'])) {
					$this->sendJson(array('result' => 'NG', 'error' => '割当済みがあるため、削除できません'));
					return;
				}
				
			}
			
			try {
				$bankHistoryTable->getAdapter()->beginTransaction();
				
				$bankHistoryTable->updateById($id, array(
					'status' => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
				));
				
				foreach ($items as $each) {
					$bankHistoryItemTable->updateById($each['id'], array(
						'status' => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_DELETED,
					));	
				}
				
                // commit
                $bankHistoryTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $bankHistoryTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-product/delete transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/import                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行 CSV取込                                               |
    +----------------------------------------------------------------------------*/
    public function importAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $request    = $this->getRequest();
        $this->view->bankId = $bankId = $request->getParam('bank_id');
        
		$bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankTable->getById($bankId);
		
        $this->view->backUrl = '/transaction-bank/import-history?bank_id=' . $bankId;		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/import-csv                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行 CSV取込(アップロード)                                 |
    +----------------------------------------------------------------------------*/
    public function importCsvAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$bankId = $request->getParam('bank_id');
		$bankImportFormat = $request->getParam('bank_import_format');
		
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
        
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData = $bankTable->getById($bankId);

		$bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		
		$data = array(
	        'management_group_id' => $this->_adminProperty['management_group_id'], // 管理グループID
	        'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
	        'import_key'          => $key,                        // ファイル名
	        
	        'bank_id'             => $bankId,                     // 銀行ID
	        
	        'created_user_id'     => $this->_adminProperty['id'], // 取込実施者

			'term_form'           => '',                            // 期間開始日
			'term_to'             => '',                            // 期間終了日

            'created'             => new Zend_Db_Expr('now()'),
            'updated'             => new Zend_Db_Expr('now()'),
		);

		$bankHistoryTable->create($data);
		
		$importId = $bankHistoryTable->getLastInsertedId('id');
		
		
        $csvFilePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($key . '.csv');;
		
		$errors = array();
		$successCount = 0;
        if (file_exists($csvFilePath)) {  
            $handle = fopen($csvFilePath, "r");
            
            // 説明行
            $csvRow = fgetcsv($handle, 0, ","); // 0
            
            // 注文データの登録
            $rowCount = 1;
            
            try {
	            while (($csvRow = fgetcsv($handle, 0, ",")) !== FALSE) {
		            
		            if (!empty($csvRow[0])) {
			            if ($bankData['bank_code'] === '0033') {
				            // ジャパンネット銀行
				            $result = $this->importJNBData($rowCount, $key, $importId, $csvRow);
			            } else if ($bankData['bank_code'] === '0310') {
							// GMOあおぞら
							$result = $this->importGMOData($rowCount, $key, $importId, $csvRow);
			            } else {
				            $result = $this->importData($rowCount, $key, $importId, $csvRow);
			            }
			            
			            if ($result === false) {
				            $errors[] = $rowCount;
			            } else {
				            $successCount++;
			            }
			            
			            $rowCount++;
		            }
	            }
	            
            } catch (Exception $e) {
            	var_dump($e);exit;
            }

        } else {
	        $this->sendJson(array('result' => 'NG', 'message' => 'ファイルの読込に失敗しました'));
	        return;
        }
		
		
		if ($successCount > 0) {
			$fromDate = $bankHistoryItemTable->getFirstDateByHistoryId($importId);
			$toDate   = $bankHistoryItemTable->getLastDateByHistoryId($importId);
			
			$bankHistoryTable->updateById($importId, array(
				'term_form' => $fromDate,
				'term_to'   => $toDate,
			));
		} else {
			$bankHistoryTable->updateById($importId, array(
				'status' => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
			));
		}
		
    	$this->sendJson(array('result' => 'OK', 'id' => $importId, 'count' => $rowCount));
    	return;
    }

	/*
	 * 1件取込(JNB形式) ジャパンネット銀行
	*/
    private function importJNBData($rowCount, $importKey, $importId, $csvRow)
    {
    	$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
    	
    	// 操作時間
    	$operationTime = $csvRow[0] . '-' . $csvRow[1] . '-' . $csvRow[2] . ' ' . $csvRow[3] . ':' . $csvRow[4] . ':' . $csvRow[5];
    	
    	$sameData = $bankHistoryItemTable->findJNBSameData($csvRow[7], $operationTime);

    	if (!empty($sameData)) {
	    	return false;
    	}
    	
		$paidAmount     = str_replace(array("\\", "¥", ','), '', $csvRow[8]); // 支払金額
		$recievedAmount = str_replace(array("\\", "¥", ','), '', $csvRow[9]); // 預入金額
		$balanceAmount  = str_replace(array("\\", "¥", ','), '', $csvRow[10]); // 残高

		$targetDate     = $csvRow[0] . '-' . $csvRow[1] . '-' . $csvRow[2]; // 取引日
		
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getBySymbol($this->_adminProperty['management_group_id'], '¥');
		
		$rowData = array(            
	        'management_group_id'   => $this->_adminProperty['management_group_id'],   // 管理グループID
	        'bank_history_id'       => $importId,             // 銀行取込CSVID
	        'status'                => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_NONE,   // ステータス
	        
	        'row_count'             => $rowCount,             // 行番号
	        'target_date'           => $targetDate,           // 対象日
	        'jnb_time'              => $operationTime,        // JNB操作時間
	        
	        'name'                  => $csvRow[7],            // 項目名
	         
	        'currency_id'           => $currencyData['id'],   // 通貨ID
	        'paid_amount'           => $paidAmount,           // 出金額
	        'received_amount'       => $recievedAmount,       // 預かり額(入金額)
	        'balance_amount'        => $balanceAmount,        // 残高
	        
	        'payable_id'            => 0,   // 買掛ID    
			'receivable_id'         => 0,   // 売掛ID
	
            'created'               => new Zend_Db_Expr('now()'),
            'updated'               => new Zend_Db_Expr('now()'),
		);
		
		$bankHistoryItemTable->create($rowData);
		return true;
    }

	/*
	 * 1件取込(GMOあおぞら形式) 
	*/
    private function importGMOData($rowCount, $importKey, $importId, $csvRow)
    {
    	$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
    	
		$paidAmount     = str_replace(array("\\", "¥", ','), '', $csvRow[3]); // 支払金額
		$recievedAmount = str_replace(array("\\", "¥", ','), '', $csvRow[2]); // 預入金額
		$balanceAmount  = str_replace(array("\\", "¥", ','), '', $csvRow[4]); // 残高

		$targetDate     = substr($csvRow[0], 0, 4) . '-' . substr($csvRow[0], 4, 2)  . '-' . substr($csvRow[0], 6, 2) ; // 取引日
		
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getBySymbol($this->_adminProperty['management_group_id'], '¥');
		
		$rowData = array(            
	        'management_group_id'   => $this->_adminProperty['management_group_id'],   // 管理グループID
	        'bank_history_id'       => $importId,             // 銀行取込CSVID
	        'status'                => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_NONE,   // ステータス
	        
	        'row_count'             => $rowCount,             // 行番号
	        'target_date'           => $targetDate,           // 対象日
	        
	        'name'                  => $csvRow[1],            // 項目名
	         
	        'currency_id'           => $currencyData['id'],   // 通貨ID
	        'paid_amount'           => $paidAmount,           // 出金額
	        'received_amount'       => $recievedAmount,       // 預かり額(入金額)
	        'balance_amount'        => $balanceAmount,        // 残高
	        
	        'gmo_item_key'          => str_replace('-', '', $targetDate) .'000000000000',
	        
	        'payable_id'            => 0,   // 買掛ID    
			'receivable_id'         => 0,   // 売掛ID
	
            'created'               => new Zend_Db_Expr('now()'),
            'updated'               => new Zend_Db_Expr('now()'),
		);
		
		$bankHistoryItemTable->create($rowData);
		return true;
    }
    
        
	/*
	 * 1件取込(JNB以外)
	*/
    private function importData($rowCount, $importKey, $importId, $csvRow)
    {
    	$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
    	
		$paidAmount     = str_replace(array("\\", "¥", ','), '', $csvRow[1]); // 支払金額
		$recievedAmount = str_replace(array("\\", "¥", ','), '', $csvRow[2]); // 預入金額
		$balanceAmount  = str_replace(array("\\", "¥", ','), '', $csvRow[3]); // 残高
		
		$targetDate     = str_replace('.', '-', $csvRow[0]); // 取引日
		$targetDate     = str_replace(array('年', '月', '日'), '-', $targetDate); // 取引日
		
		
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getBySymbol($this->_adminProperty['management_group_id'], '¥');
		
		$rowData = array(            
	        'management_group_id'   => $this->_adminProperty['management_group_id'],   // 管理グループID
	        'bank_history_id'       => $importId,             // 銀行取込CSVID
	        'status'                => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_NONE,   // ステータス
	        
	        'row_count'             => $rowCount,             // 行番号
	        'target_date'           => $targetDate,           // 対象日
	        'name'                  => $csvRow[4],            // 項目名
	         
	        'currency_id'           => $currencyData['id'],   // 通貨ID
	        'paid_amount'           => $paidAmount,           // 出金額
	        'received_amount'       => $recievedAmount,       // 預かり額(入金額)
	        'balance_amount'        => $balanceAmount,        // 残高
	        
	        'payable_id'            => 0,   // 買掛ID    
			'receivable_id'         => 0,   // 売掛ID
	
            'created'               => new Zend_Db_Expr('now()'),
            'updated'               => new Zend_Db_Expr('now()'),
		);
		
		$bankHistoryItemTable->create($rowData);
		
		return true;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/import-detail                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行 明細取込詳細                                          |
    +----------------------------------------------------------------------------*/
    public function importDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$request = $this->getRequest();
		$this->view->id        = $id         = $request->getParam('id');
		$this->view->posTop    = $request->getParam('pos');
		$this->view->direct    = $direct     = $request->getParam('direct');
		$this->view->targetRow = $targetRow  = $request->getParam('target_row');

		$bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $this->view->historyData = $historyData = $bankHistoryTable->getById($id);
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $this->view->items = $bankHistoryItemTable->getList($id);

		$bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankTable->getById($historyData['bank_id']);

		if (empty($direct)) {
			$this->view->backUrl = '/transaction-bank/import-history?bank_id=' . $historyData['bank_id'];
		}

		// 会計科目(全てのグループ)
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
    |  action_URL    * /transaction-bank/update-date                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 日付更新(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function updateDateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$rowCount  = $request->getParam('row_count');
		$targetDate  = $request->getParam('date_' . $rowCount);
		
		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();

			$bankHistoryItemTable->updateById($id, array(
				'target_date' => $targetDate,
			));

		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/update-name                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 項目名更新(Ajax)                                           |
    +----------------------------------------------------------------------------*/
    public function updateNameAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$rowCount   = $request->getParam('row_count');
		$name       = $request->getParam('name_' . $rowCount);
		
		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();

			$bankHistoryItemTable->updateById($id, array(
				'name' => $name,
			));

		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/finish-attach                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 割当完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function finishAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');

        if (empty($rowId)) {
        	throw new Zend_Exception('/transaction-bank/finish-attach - no row id');
        }
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		$payableTable         = new Shared_Model_Data_AccountPayable();
		$receivableTable      = new Shared_Model_Data_AccountReceivable();	
		
        $rowData = $bankHistoryItemTable->getById($rowId);

        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/finish-attach - no target data');
        }
        

		$rowTotal = 0;
		$rowType = '';
		if (!empty($rowData['received_amount'])) {
			$rowTotal = (int)$rowData['received_amount'];
			$rowType = '入金額';
		} else if (!empty($rowData['paid_amount'])) {
			$rowTotal = -(int)$rowData['paid_amount'];
			$rowType = '出金額';
		}

		$total = 0;
		
		$receivableIds = $rowData['receivable_ids'];
		$payableIds    = $rowData['payable_ids'];
		
		$multipleComplete = false;
		
		if (!empty($receivableIds)) {
			foreach ($receivableIds as $eachId) {
				$receivableData = $receivableTable->getByIdForAnyGroup($eachId);
				
				$total = $total + $receivableData['total_amount'];
				
				// 対象の入金予定の割当合計が一致していたら消込完了可能
				$historyItems = $bankHistoryItemTable->getListByReceivableId($eachId);
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
				
		if ($multipleComplete === false && !empty($payableIds)) {
			foreach ($payableIds as $eachId) {
				$payableData = $payableTable->getByIdForAnyGroup($eachId);
				
				$total = $total - $payableData['total_amount'];
				
				// 対象の支払予定の割当合計が一致していたら消込完了可能
				$historyItems = $bankHistoryItemTable->getListByPayableId($eachId);
				
				$paidTotal = 0;
				if (count($historyItems) > 1) {
					foreach ($historyItems as $eachHistory) {
						$paidTotal -= (int)$eachHistory['paid_amount'];
					}
					
					if (-(int)$payableData['total_amount'] === $paidTotal) {
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
		$bankHistoryItemTable->updateById($rowId, array('status' => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_ATTACHED));	
		
		
		if (!empty($receivableIds)) {
			foreach ($receivableIds as $eachId) {
				$receivableTable->updateById($eachId, array(
					'is_attached' => '1',
					'payment_status' => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_RECEIVED,
				));
				
				$receivableData = $receivableTable->getByIdForAnyGroup($eachId);
				if (!empty($receivableData['relational_id'])) {
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
						'relational_id' => $receivableData['relational_id'],
						'received_date' => $rowData['target_date'],
					);
					
					// 入金済み
					$result = Shared_Model_Gs_Account::updateToReceivedStatus($clientData, $data);
					
					if (empty($result['result'])) {
						$this->sendJson(array('result' => 'NG', 'message' => 'goosaへの接続に失敗しました'));
						return;
					}
				}
			}
		}
		
		if (!empty($payableIds)) {
			foreach ($payableIds as $eachId) {
				$payableTable->updateById($eachId, array(
					'is_attached'    => '1',
					'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,
				));
				
				$payableData = $payableTable->getByIdForAnyGroup($eachId);
				
				if (!empty($payableData['relational_id'])) {
					// Goosa連携
					$clientData = array(
						'management_web_use_basic_auth' => false,
						'management_web_basic_user' => 'goosa',
						'management_web_basic_pass' => 'goosa',
					);
					
					// goosaに入金状況同期
					$data = array(
						'relational_id' => $payableData['relational_id'],
						'paid_date'     => $rowData['target_date'],
					);
					
					// 入金済み
					$result = Shared_Model_Gs_Account::updateToPaidStatus($clientData, $data);
					
					if (empty($result['result'])) {
						$this->sendJson(array('result' => 'NG', 'message' => 'goosaへの接続に失敗しました'));
						return;
					}
				}
			}
		}
		
		
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/cancel-finish-attach                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 割当完了解除(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function cancelFinishAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');

        if (empty($rowId)) {
        	throw new Zend_Exception('/transaction-bank/finish-attach - no row id');
        }
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		$payableTable         = new Shared_Model_Data_AccountPayable();
		$receivableTable      = new Shared_Model_Data_AccountReceivable();	
		
        $rowData = $bankHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/finish-attach - no target data');
        }
        
        $bankHistoryItemTable->updateById($rowId, array('status' => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_NONE));
        
		$receivableIds = $rowData['receivable_ids'];
		$payableIds    = $rowData['payable_ids'];
		
		if (!empty($receivableIds)) {
			foreach ($receivableIds as $eachReceivableId) {
				$receivableTable->updateById($eachReceivableId, array(
					'is_attached' => '0',
				));
			}
		}
		
		if (!empty($payableIds)) {
			foreach ($payableIds as $eachPayableId) {
				$payableTable->updateById($eachPayableId, array(
					'is_attached' => '0',
				));
			}
		}

	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/select-receivable                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 売掛消込選択                                               |
    +----------------------------------------------------------------------------*/
    public function selectReceivableAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request  = $this->getRequest();
		$this->view->rowId = $rowId = $request->getParam('row_id');
		$this->view->from  = $from = $request->getParam('from');

		
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $this->view->rowData = $rowData = $bankHistoryItemTable->getById($rowId);
        
        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $this->view->historyData = $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
        
        $bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankData = $bankTable->getById($historyData['bank_id']);
        
        if ($from == 'all-list') {
        	$this->view->backUrl = '/transaction-bank/log-list?bank_id=' . $historyData['bank_id'];
        } else {
        	$this->view->backUrl = '/transaction-bank/import-detail?id=' . $rowData['bank_history_id'];
        }
		
		$receivableTable = new Shared_Model_Data_AccountReceivable();
		
		// 消込有力候補(金額が一致)
		$selectObj = $receivableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        $selectObj->where('frs_account_receivable.type != ?', Shared_Model_Code::RECEIVABLE_TYPE_CARD);
		$selectObj->where('frs_account_receivable.status = ?', Shared_Model_Code::RECEIVABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_receivable.payment_status != ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_CANCELED);
		$selectObj->where('frs_account_receivable.is_attached = 0');
        $selectObj->where('frs_account_receivable.total_amount = ?', $rowData['received_amount']);
        $this->view->mainItems = $selectObj->query()->fetchAll();
		
		// その他候補
		$selectObj = $receivableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_receivable.target_connection_id = frs_connection.id', array($receivableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        $selectObj->where('frs_account_receivable.type != ?', Shared_Model_Code::RECEIVABLE_TYPE_CARD);
		$selectObj->where('frs_account_receivable.status = ?', Shared_Model_Code::RECEIVABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_receivable.payment_status != ?', Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_CANCELED);
		$selectObj->where('frs_account_receivable.is_attached = 0');
        $selectObj->where('frs_account_receivable.total_amount != ?', $rowData['received_amount']);
        $selectObj->order('frs_account_receivable.receive_plan_date ASC');
        $this->view->otherItems = $selectObj->query()->fetchAll();

		// 毎月入金項目
		//*********************************************** 多分バグってた　修正中
		$receivableTemplateTable = new Shared_Model_Data_AccountReceivableTemplate();
        $selectObj = $receivableTemplateTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_receivable_template.target_connection_id = frs_connection.id', array($receivableTemplateTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_receivable_template.created_user_id = frs_user.id',array($receivableTemplateTable->aesdecrypt('user_name', false) . 'AS user_name'));
        $selectObj->where('frs_account_receivable_template.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        $selectObj->where('frs_account_receivable_template.template_type = ?', Shared_Model_Code::RECEIVABLE_TEMPLATE_TYPE_FIXED);
        $selectObj->where('frs_account_receivable_template.status = ?', Shared_Model_Code::RECEIVABLE_STATUS_APPROVED);
        //$selectObj->where('frs_account_receivable_template.paying_method = ' . Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK . ' OR frs_account_payable_template.paying_method = ' . Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO);
		$selectObj->order('frs_account_receivable_template.account_title_id ASC');
		$selectObj->order('frs_account_receivable_template.id ASC');
		$this->view->monthlyItems = $selectObj->query()->fetchAll();
		//***********************************************

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
    |  action_URL    * /transaction-bank/attach-receivable                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定に割当(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function attachReceivableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');
		$receivableId = $request->getParam('receivable_id');

        if (empty($receivableId)) {
        	throw new Zend_Exception('/transaction-paid/attach-receivable - no payable id');
        }
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/attach-receivable - no target data');
        }
        
        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
		
		$receivableTable = new Shared_Model_Data_AccountReceivable();
		$receivableData = $receivableTable->getById($this->_adminProperty['management_group_id'], $receivableId);
		
		$receivableIds = $rowData['receivable_ids'];
		if (!empty($receivableIds)) {
			if (!in_array($receivableId, $receivableIds)) {
				$receivableIds[] = $receivableId;
			}
			
		} else {
			$receivableIds = array($receivableId);
		}
		
		try {
			$bankHistoryItemTable->getAdapter()->beginTransaction();
			
			$bankHistoryItemTable->updateById($rowId, array(
				'receivable_id'  => $receivableId,
				'receivable_ids' => serialize($receivableIds),
			));
			
			// 金額が同じ場合は入金済みに変更
			/*
			if ((string)$rowData['received_amount'] === (string)$receivableData['total_amount'] && (string)$rowData['currency_id'] === (string)$receivableData['currency_id']) {					
				$receivableTable->updateById($receivableId, array(
					'payment_status'      => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_RECEIVED,
					'received_date'       => $rowData['target_date'],
					'confirm_user_id'     => $this->_adminProperty['id'],           // 入金確認者ユーザーID
					'confirm_datetime'    => new Zend_Db_Expr('now()'),             // 入金確認日
				));
			}
			*/
			
			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/attach-receivable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/add-receivable                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定に追加(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function addReceivableAction()
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		        $rowData = $bankHistoryItemTable->getById($rowId);
		        
		        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
		        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
				
				$receivableTable = new Shared_Model_Data_AccountReceivable();
				
				$receivableIds = $rowData['receivable_ids'];
				if (!empty($receivableIds)) {
				    $this->sendJson(array('result' => 'NG', 'message' => '既に割当があるため、登録できません'));
		    		return;
				}
				
				$bankHistoryItemTable->getAdapter()->beginTransaction();

				try {
					$receivableData = array(
				        'management_group_id'     => $this->_adminProperty['management_group_id'],
				        
				        'template_id'             => 0,                                    // テンプレートID
				        
				        'status'                  => Shared_Model_Code::RECEIVABLE_STATUS_ADDED_FROM_HISTORY, // 明細から追加
				        'payment_status'          => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_RECEIVED,   // ステータス
				        
						'type'                    => Shared_Model_Code::RECEIVABLE_TYPE_HISTORY,              // 売掛管理種別
						'invoice_id'              => 0,                          // 請求書ID
						'account_title_id'        => $success['account_title_id'],          // 会計科目ID
						
						'target_connection_id'    => $success['target_connection_id'],      // 支払先
						'currency_id'             => $rowData['currency_id'],               // 請求金額通貨ID
						'total_amount'            => $rowData['received_amount'],           // 入金予定額
						
						'bank_id'                 => $historyData['bank_id'],               // 入金予定口座
						'receive_plan_date'       => $rowData['target_date'],               // 入金予定日
						'received_date'           => $rowData['target_date'],               // 入金受取日
						
						'memo'                    => $success['memo'],                      // 摘要
						
						'created_user_id'         => $this->_adminProperty['id'],           // 登録者ユーザーID
						
						'confirm_user_id'         => $this->_adminProperty['id'],           // 入金確認者ユーザーID
						'confirm_datetime'        => new Zend_Db_Expr('now()'),             // 入金確認日
		                'created'                 => new Zend_Db_Expr('now()'),
		                'updated'                 => new Zend_Db_Expr('now()'),
			        );
					
					$receivableTable->create($receivableData);
					$receivableId = $receivableTable->getLastInsertedId('id');
					
					$bankHistoryItemTable->updateById($rowId, array(
						'receivable_id'  => $receivableId,
						'receivable_ids' => serialize(array($receivableId)),
					));
					
					// commit
		            $bankHistoryItemTable->getAdapter()->commit();
		            
		        } catch (Exception $e) {
		            $bankHistoryItemTable->getAdapter()->rollBack();
		            throw new Zend_Exception('/transaction-bank/add-receivable transaction failed: ' . $e);
		            
		        }
		        
			    $this->sendJson(array('result' => 'OK'));
		    	return;
		    }
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/detach-receivable                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定割当解除(Ajax)                                   |     // リリースまだ
    +----------------------------------------------------------------------------*/
    public function detachReceivableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$rowId        = $request->getParam('row_id');
		$receivableId = $request->getParam('receivable_id');

        if (empty($receivableId)) {
        	throw new Zend_Exception('/transaction-paid/detach-receivable - no receivable id');
        }
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/detach-receivable - no target data');
        }
        
        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
		
		$receivableTable = new Shared_Model_Data_AccountReceivable();
		$receivableData = $receivableTable->getById($this->_adminProperty['management_group_id'], $receivableId);

		$receivableIds = $rowData['receivable_ids'];
		
		if (empty($receivableIds)) {
			throw new Zend_Exception('/transaction-paid/detach-receivable - no receivable ids');
		}

		if (!in_array($receivableId, $receivableIds)) {
			throw new Zend_Exception('/transaction-paid/detach-receivable - no target payable ids');
		}

		$newIds = array();
		
		foreach ($receivableIds as $each) {
			if ($each !== $receivableId) {
				$newIds[] = $each;
			}
		}
		
		
		try {
			$bankHistoryItemTable->getAdapter()->beginTransaction();
			
			$bankHistoryItemTable->updateById($rowId, array(
				'receivable_ids' => serialize($newIds),
			));
			
			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/detach-receivable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/select-gmo                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO総合 割当選択                                           |
    +----------------------------------------------------------------------------*/
    public function selectGmoAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request  = $this->getRequest();
		$this->view->rowId = $rowId = $request->getParam('row_id');
		$this->view->from  = $from = $request->getParam('from');
		
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $this->view->rowData = $rowData = $bankHistoryItemTable->getById($rowId);

        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $this->view->historyData = $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
        

        $bankTable = new Shared_Model_Data_AccountBank();
        $this->view->bankData = $bankTable->getById($historyData['bank_id']);
        //var_dump($bankData);
        
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '割当実行';

        if ($from == 'all-list') {
        	$this->view->backUrl = '/transaction-bank/log-list?bank_id=' . $historyData['bank_id'];
        } else {
        	$this->view->backUrl = '/transaction-bank/import-detail?id=' . $rowData['bank_history_id'];
        }
        
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		$dbAdapter = $transferTable->getAdapter();
		
		$selectObj = $transferTable->select(array('apply_no'));
		$selectObj->joinLeft('frs_account_payable', 'frs_account_gmo_transfer.payable_id = frs_account_payable.id', array('target_connection_id', 'transfer_to_bank_code', 'bank_registered_type', 'paying_plan_date', new Zend_Db_Expr('SUM(total_amount) AS apply_total')));
		//$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($transferTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->joinLeft('frs_account_bank', 'frs_account_gmo_transfer.account_id = frs_account_bank.gmo_bank_account_id', array());
		$selectObj->where('frs_account_bank.id = ?' , $historyData['bank_id']);
		$selectObj->group('frs_account_gmo_transfer.apply_no');
		$selectObj->where('frs_account_gmo_transfer.result_code = ?', Shared_Model_Code::GMO_API_TRANSFER_RESULT_CODE_APPLOVED);
		$selectObj->order('frs_account_gmo_transfer.id DESC');
		
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
    |  action_URL    * /transaction-bank/select-gmo-attach                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO総合 割当選択                                           |
    +----------------------------------------------------------------------------*/
    public function selectGmoAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request       = $this->getRequest();
		$rowId         = $request->getParam('row_id');
		$applyNoList   = $request->getParam('apply_no');
		
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);

        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
        
        
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		
		
		$payableIds = $rowData['payable_ids'];
		if (empty($payableIds)) {
			$payableIds = array();
		}
		$applyNos   = $rowData['apply_nos'];
		if (empty($applyNos)) {
			$applyNos = array();
		}
		
		
		if (empty($applyNoList)) {
			$this->sendJson(array('result' => 'NG', 'messsage' => '総合振込を一件以上選択してください'));
	    	return;
		}
		
		foreach ($applyNoList as $eachApplyNo) {
			$applyNos[] = $eachApplyNo;
			
			$payableList = $transferTable->getListByApplyNo($eachApplyNo);
			
			foreach ($payableList as $each) {
				if (!in_array($each['payable_id'], $payableIds)) {
					$payableIds[] = $each['payable_id'];
				}
			}
		}
		
		//var_dump($payableIds);exit;
		
		try {
			$bankHistoryItemTable->getAdapter()->beginTransaction();
			
			$bankHistoryItemTable->updateById($rowId, array(
				'payable_ids' => serialize($payableIds),
				'apply_nos' => serialize($applyNos),
			));

			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/select-gmo-attach transaction failed: ' . $e);
            
        }
        
		
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/remove-gmo-attach                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO総合 割当解除                                           |
    +----------------------------------------------------------------------------*/
    public function removeGmoAttachAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request       = $this->getRequest();
		$rowId         = $request->getParam('row_id');
		$applyNo       = $request->getParam('apply_no');

		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);

        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
        
        
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		
		$applyNos = $rowData['apply_nos'];
		
		$payableIds = $rowData['payable_ids'];
		if (empty($payableIds)) {
			$payableIds = array();
		}
		$applyNoList   = $rowData['apply_nos'];
		if (empty($applyNoList)) {
			$this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
		$newPayableIds = array();
		$newApplyNos = array();
		
		foreach ($applyNoList as $eachApplyNo) {
			
			if ((string)$eachApplyNo !== (string)$applyNo) {
				$newApplyNos[] = $eachApplyNo;
			}
			
			$payableList = $transferTable->getListByApplyNo($eachApplyNo);
			
			foreach ($payableIds as $eachPayableId) {
				$isExist = false;
				foreach ($payableList as $eachPayable) {
					if ((string)$eachPayableId === (string)$eachPayable['payable_id']) {
						$isExist = true;
					}
				}
				
				if ($isExist === false) {
					$newPayableIds[] = $eachPayableId;
				}
			}
		}

		try {
			$bankHistoryItemTable->getAdapter()->beginTransaction();
			
			$bankHistoryItemTable->updateById($rowId, array(
				'payable_ids' => serialize($newPayableIds),
				'apply_nos' => serialize($newApplyNos),
			));

			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/remove-gmo-attach transaction failed: ' . $e);
            
        }
        
		
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/select-payable                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 買掛割当選択                                               |
    +----------------------------------------------------------------------------*/
    public function selectPayableAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request  = $this->getRequest();
		$this->view->rowId = $rowId = $request->getParam('row_id');
		$this->view->from  = $from = $request->getParam('from');
		
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $this->view->rowData = $rowData = $bankHistoryItemTable->getById($rowId);

        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $this->view->historyData = $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
        
        $bankTable = new Shared_Model_Data_AccountBank();
		$this->view->bankData = $bankData = $bankTable->getById($historyData['bank_id']);
        
        if ($from == 'all-list') {
        	$this->view->backUrl = '/transaction-bank/log-list?bank_id=' . $historyData['bank_id'];
        } else {
        	$this->view->backUrl = '/transaction-bank/import-detail?id=' . $rowData['bank_history_id'];
        }
		
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		
		// 消込有力候補(金額が一致)
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		$selectObj->where('frs_account_payable.status = ?', Shared_Model_Code::PAYABLE_STATUS_APPROVED);
        $selectObj->where('frs_account_payable.is_attached = 0');
        $selectObj->where('frs_account_payable.total_amount = ?', $rowData['paid_amount']);
        $this->view->mainItems = $selectObj->query()->fetchAll();

		// その他候補
		$selectObj = $payableTable->select();
		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
		$selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		
		$selectObj->where('frs_account_payable.paying_type != ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD);
		
		$selectObj->where('frs_account_payable.status = ?', Shared_Model_Code::PAYABLE_STATUS_APPROVED);
		$selectObj->where('frs_account_payable.is_attached = 0');
        $selectObj->where('frs_account_payable.total_amount != ?', $rowData['paid_amount']);
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
        $selectObj->where('frs_account_payable_template.paying_method = ' . Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK . ' OR frs_account_payable_template.paying_method = ' . Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO);
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
    |  action_URL    * /transaction-bank/attach-payable                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定に割当(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function attachPayableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request   = $this->getRequest();
		$rowId     = $request->getParam('row_id');
		$payableId = $request->getParam('payable_id');

        if (empty($payableId)) {
        	throw new Zend_Exception('/transaction-paid/attach-payable - no payable id');
        }
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/attach-payable - no target data');
        }
        
        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$payableData = $payableTable->getById($this->_adminProperty['management_group_id'], $payableId);

		$payableIds = $rowData['payable_ids'];
		if (!empty($payableIds)) {
			if (!in_array($payableId, $payableIds)) {
				$payableIds[] = $payableId;
			}
			
		} else {
			$payableIds = array($payableId);
		}
		
		try {
			$bankHistoryItemTable->getAdapter()->beginTransaction();
			
			$bankHistoryItemTable->updateById($rowId, array(
				'payable_id'  => $payableId,
				'payable_ids' => serialize($payableIds),
			));

			// 金額が同じ場合は支払い済みに変更
			/*
			if ((string)$rowData['paid_amount'] === (string)$payableData['total_amount'] && (string)$rowData['currency_id'] === (string)$payableData['currency_id']) {
				$payableTable->updateById($payableId, array(
					'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,
					'paid_date'      => $rowData['target_date'],
				));
			}
			*/
			
			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/attach-payable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/detach-payable                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定割当解除(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function detachPayableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request   = $this->getRequest();
		$rowId     = $request->getParam('row_id');
		$payableId = $request->getParam('payable_id');

        if (empty($payableId)) {
        	throw new Zend_Exception('/transaction-paid/detach-payable - no payable id');
        }
        
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/detach-payable - no target data');
        }
        
        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$payableData = $payableTable->getById($this->_adminProperty['management_group_id'], $payableId);

		$payableIds = $rowData['payable_ids'];
		
		if (empty($payableIds)) {
			throw new Zend_Exception('/transaction-paid/detach-payable - no payable ids');
		}

		if (!in_array($payableId, $payableIds)) {
			throw new Zend_Exception('/transaction-paid/detach-payable - no target payable ids');
		}

		$newIds = array();
		
		foreach ($payableIds as $each) {
			if ($each !== $payableId) {
				$newIds[] = $each;
			}
		}
		
		try {
			$bankHistoryItemTable->getAdapter()->beginTransaction();
			
			$bankHistoryItemTable->updateById($rowId, array(
				'payable_ids' => serialize($newIds),
			));
			
			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/detach-payable transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/payable-attach-monthly                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理項目割当(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function payableAttachMonthlyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$rowId      = $request->getParam('row_id');
		$templateId = $request->getParam('payable_template_id');

		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
        $rowData = $bankHistoryItemTable->getById($rowId);
        
        if (empty($rowData)) {
        	throw new Zend_Exception('/transaction-bank/payable-attach-monthly - no target data');
        }
        
        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
        
		$payableTable    = new Shared_Model_Data_AccountPayable();
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$templateData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $templateId);
		
    	//$displayId = $payableTable->getNextDisplayId();

   		$bankHistoryItemTable->getAdapter()->beginTransaction();
   		
    	try {
			$payableData = array(
		        'management_group_id'     => $this->_adminProperty['management_group_id'],
		        'status'                  => Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY, // 明細から追加
		        'payment_status'          => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID, // 支払済
		        'template_id'             => $templateId,                                // 毎月支払テンプレートID
		        
		        'order_form_ids'          => serialize(array()),                         // 発注IDリスト
		        
				'account_title_id'        => $templateData['account_title_id'],          // 会計科目ID
				'target_connection_id'    => $templateData['target_connection_id'],      // 支払先
				
				'paying_type'             => Shared_Model_Code::PAYABLE_PAYING_TYPE_MONTHLY, // 支払種別(請求支払/カード支払/自動振替)
	
				'file_list'               => json_encode(array()),                       // 請求書ファイルアップロード
				
				'paid_user_id'            => $this->_adminProperty['id'],                // 支払処理担当者
				'paid_date'               => $rowData['target_date'],                    // 支払完了日
	
				'memo'                    => $templateData['description'],               // 摘要
				
				'paying_plan_date'        => $rowData['target_date'],                    // 支払予定日
				
				'paying_method'           => $templateData['paying_method'],             // 支払方法
				'paying_bank_id'          => $templateData['paying_bank_id'],            // 支払元銀行口座
				'paying_card_id'          => $templateData['paying_card_id'],            // 支払元クレジットカード
				'paying_method_memo'      => '',                                         // 支払方法メモ
				
				'total_amount'            => $templateData['total_amount'],              // 支払額
				'currency_id'             => $templateData['currency_id'],               // 通貨単位
				'tax_division'            => Shared_Model_Code::TAX_DIVISION_TAXATION,   // 税区分
				'tax'                     => '',                                         // 消費税
				
				'created_user_id'         => $this->_adminProperty['id'],                // 支払申請者
				'approval_user_id'        => 0,                                          // 承認者
				
	            'created'                 => new Zend_Db_Expr('now()'),
	            'updated'                 => new Zend_Db_Expr('now()'),
			);

			$payableTable->create($payableData);
			$payableId = $payableTable->getLastInsertedId('id');

			$payableIds = $rowData['payable_ids'];
			if (!empty($payableIds)) {
				if (!in_array($payableId, $payableIds)) {
					$payableIds[] = $payableId;
				}
				
			} else {
				$payableIds = array($payableId);
			}		

			$bankHistoryItemTable->updateById($rowId, array(
				'payable_id' => $payableId,
				'payable_ids' => serialize($payableIds),
			));
			
			// commit
            $bankHistoryItemTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $bankHistoryItemTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-bank/payable-attach-monthly transaction failed: ' . $e);
            
        }
        
	    $this->sendJson(array('result' => 'OK'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-bank/add-payable                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払い予定に追加(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function addPayableAction()
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
				$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		        $rowData = $bankHistoryItemTable->getById($rowId);
		        
		        $bankHistoryTable = new Shared_Model_Data_AccountBankHistory();
		        $historyData = $bankHistoryTable->getById($rowData['bank_history_id']);
				
				$payableTable = new Shared_Model_Data_AccountPayable();

				$payableIds = $rowData['payable_ids'];
				if (!empty($payableIds)) {
				    $this->sendJson(array('result' => 'NG', 'message' => '既に割当があるため、登録できません'));
		    		return;
				}
				
				$bankHistoryItemTable->getAdapter()->beginTransaction();
				
				try {
					$payableData = array(
				        'management_group_id'     => $this->_adminProperty['management_group_id'],
				        'status'                  => Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY,  // 口座明細から追加
				        'payment_status'          => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PAID,        // 支払ステータス - 支払済
				        'order_form_ids'          => serialize(array()),                    // 発注IDリスト
						'account_title_id'        => $success['account_title_id'],          // 会計科目ID
						
						'target_connection_id'    => $success['target_connection_id'],      // 支払先
						
						'purchased_date'          => NULL,                                  // クレジット利用日
						
						'paying_plan_date'        => $rowData['target_date'],               // 支払予定日
		
						'total_amount'            => $rowData['paid_amount'],               // 支払額
						'currency_id'             => $rowData['currency_id'],               // 通貨単位   ★
						'tax_division'            => Shared_Model_Code::TAX_DIVISION_TAXATION,  // 税区分
						'tax'                     => '',                                    // 消費税
						'memo'                    => $success['memo'],
						
						'paying_type'             => Shared_Model_Code::PAYABLE_PAYING_TYPE_INVOICE,   // 支払種別(請求支払/カード支払/自動振替)
						'paying_method'           => $success['paying_method'],                        // 支払方法   ★
		
						'paying_method_memo'      => '',                                               // 支払方法メモ
		
						'paying_bank_id'          => $historyData['bank_id'],                          // 支払元銀行口座
						'paying_card_id'          => 0,                                                // 支払元クレジットカード
						
						'file_list'               => json_encode(array()),                  // 請求書ファイルアップロード
						
						'paid_user_id'            => $this->_adminProperty['id'],           // 支払処理担当者
						'paid_date'               => $rowData['target_date'],               // 支払完了日
						
						'created_user_id'         => $this->_adminProperty['id'],           // 支払申請者
						'approval_user_id'        => 0,                                     // 承認者
						
		                'created'                 => new Zend_Db_Expr('now()'),
		                'updated'                 => new Zend_Db_Expr('now()'),
			        );

					$payableTable->create($payableData);
					
					$payableId = $payableTable->getLastInsertedId('id');
					
					$bankHistoryItemTable->updateById($rowId, array(
						'payable_id'  => $payableId,
						'payable_ids' => serialize(array($payableId)),
					));
					
					// commit
		            $bankHistoryItemTable->getAdapter()->commit();
		            
		        } catch (Exception $e) {
		            $bankHistoryItemTable->getAdapter()->rollBack();
		            throw new Zend_Exception('/transaction-bank/add-payable transaction failed: ' . $e);
		            
		        }
		        
			    $this->sendJson(array('result' => 'OK'));
		    	return;
		    }
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));

    }

    
}

