<?php
/**
 * class TransactionReceivableController
 */
 
class TransactionReceivableController extends Front_Model_Controller
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
		$this->view->menu = 'receivable';
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/update-to-draft                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 下書きに戻す(管理権限あり)(Ajax)                           |
    +----------------------------------------------------------------------------*/
    public function updateToDraftAction()
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
					'status' => Shared_Model_Code::RECEIVABLE_STATUS_DRAFT,
				));
			
                // commit
                $receivableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $receivableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-receivable/update-to-draft transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/delete                             |
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
    |  action_URL    * /transaction-receivable/list                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * その他入金予定申請                                         |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_receivable_list_1s');

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
		$selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_OTHER);
		$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);  // グループID
        
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

		$items = array(); 
		

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
		
		// 通貨リスト
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
    |  action_URL    * /transaction-receivable/add                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定申請 - 登録                                        |
    +----------------------------------------------------------------------------*/
    public function addAction()
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
    |  action_URL    * /transaction-receivable/add-post                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定申請 - 登録(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
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
				
				if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['memo']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「摘要」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                
                } else if (!empty($errorMessage['accrual_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                    return;
                       
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金元取引先」を入力してください'));
                    return;
                
                } else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['total_amount']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金額」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				
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
			        'management_group_id'       => $this->_adminProperty['management_group_id'],
			        
			        'template_id'               => 0,                                  // テンプレートID
			        
			        'status'                    => Shared_Model_Code::RECEIVABLE_STATUS_DRAFT,                // ステータス
			        'payment_status'            => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED,   // 入金ステータス
			        'is_attached'               => 0,                                  // 割当完了
			        
			        'relational_id'             => 0,                                  // 連携ID
			        'relational_display_id'     => '',                                 // 連携表示ID
			        
			        'accrual_date'              => $success['accrual_date'],           // 発生日
			        
					'type'                      => Shared_Model_Code::RECEIVABLE_TYPE_OTHER,   // 売掛管理種別
					'invoice_id'                => 0,                                      // 請求書ID
					
					'account_title_id'          => $success['account_title_id'],           // 会計科目ID
					'account_totaling_group_id' => $success['account_totaling_group_id'],  // 採算コード
					
					'target_connection_id'      => $success['target_connection_id'],       // 支払元取引先ID
					'bank_sender_name'          => '',                                     // 振込人名義(全角カタカナ)
					
					'currency_id'               => $success['currency_id'],            // 通貨ID
					'total_amount'              => $success['total_amount'],           // 入金予定額
					
					'bank_id'                   => $success['bank_id'],                // 入金予定口座
					'receive_plan_date'         => $success['receive_plan_date'],      // 入金予定日
					'received_date'             => NULL,                               // 入金受取日
					
					'file_list'                 => json_encode($fileList),             // 添付資料リスト
					
					'created_user_id'           => $this->_adminProperty['id'],        // 支払申請者
					'approval_user_id'          => 0,                                  // 承認者
					'approval_comment'          => '',                                 // 修正依頼コメント

					
					'memo'                      => $success['memo'],                   // メモ

	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);

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
	                throw new Zend_Exception('/transaction-receivable/add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/detail                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定 - 詳細                                            |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->direct      = $direct     = $request->getParam('direct');
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$this->view->data = $data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			if (empty($direct)) {
				$this->view->backUrl = '/transaction-receivable/list';
			}
			$this->_helper->layout->setLayout('back_menu');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        	$this->view->saveButtonName = '承認申請';
		        }
	        }
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
    |  action_URL    * /transaction-receivable/update-basic                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定 - 基本情報更新(Ajax)                              |
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
	                throw new Zend_Exception('/transaction-receivable/update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/update-summary                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定 - 概要更新(Ajax)                                  |
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
                if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「支払元取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['accrual_date']['isEmpty'])) {
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
						'target_connection_id'   => $success['target_connection_id'],     // 支払元取引先
						'accrual_date'           => $success['accrual_date'],             // 発生日
					);
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-receivable/update-summary transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/update-payment                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定 - 入金予定情報更新(Ajax)                          |
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
				} else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を選択してください'));
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
					$data = array(
						'bank_id'              => $success['bank_id'],            // 入金予定口座
						'receive_plan_date'    => $success['receive_plan_date'],  // 入金予定日
						'received_date'        => NULL,                           // 入金完了日
						'total_amount'         => $success['total_amount'],       // 入金予定額
						'currency_id'          => $success['currency_id'],        // 通貨ID
					);
					
					if (!empty($success['received_date'])) {
						$data['received_date'] = $success['received_date'];      // 入金完了日
					}
					
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-receivable/update-payment transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/update-file-list                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定 - 添付ファイルアップロード更新(Ajax)              |
    +----------------------------------------------------------------------------*/
    public function updateFileListAction()
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
						'file_list' => json_encode($fileList), // 請求書ファイルアップロード
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
	                throw new Zend_Exception('/transaction-receivable/update-file-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/apply-apploval                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 承認申請                                      |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
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
			
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);


			if (empty($oldData['account_title_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return;
            } else if (empty($oldData['memo'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「摘要」を入力してください'));
                return;
            } else if (empty($oldData['account_totaling_group_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                return;
            } else if (empty($oldData['accrual_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                return;
			} else if (empty($oldData['target_connection_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金元取引先」を入力してください'));
                return; 
			} else if (empty($oldData['receive_plan_date'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                return;
			} else if (empty($oldData['total_amount'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金額」を入力してください'));
                return;
			} else if (empty($oldData['currency_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                return; 
			
            }

			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $oldData['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $oldData['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $oldData['currency_id']);

            $templateData = NULL;
            
			try {
				$receivableTable->getAdapter()->beginTransaction();

				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_RECEIVABLE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $connectionData['company_name'] . "\n支払総額：" .  number_format($oldData['total_amount']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);

				$receivableTable->updateById($id, array(
					'status' => Shared_Model_Code::RECEIVABLE_STATUS_PENDING,
				));
				
				
				$approvalTable->create($approvalData);
				
				// メール送信 -------------------------------------------------------
				$content = "支払先取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'];
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);
				
				// 承認者
				$selectObj = $userTable->select();
		    	$selectObj->where('id = ?', $userData['approver_c1_user_id']);
		        $authorizerUserData = $selectObj->query()->fetch();
	        
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
                throw new Zend_Exception('/transaction-receivable/apply-apploval transaction faied: ' . $e);
                
            }
			
		    $this->sendJson(array('result' => 'OK', 'message' => '承認申請しました'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/mod-request                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 修正依頼(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function modRequestAction()
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
			
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

			try {
				$receivableTable->getAdapter()->beginTransaction();
				
				$receivableTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
				
				$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-receivable/invoice-detail?id=' . $id;

				// メール送信 -------------------------------------------------------
				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . $url;
	        
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
                throw new Zend_Exception('/transaction-receivable/mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/approve                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function approveAction()
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
			
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
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

				$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-receivable/detail?id=' . $id;
				
				// メール送信 -------------------------------------------------------
				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . $url;
	        
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
                throw new Zend_Exception('/transaction-receivable/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }




    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/card-list                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード返金予定申請                                         |
    +----------------------------------------------------------------------------*/
    public function cardListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_receivable_card_list_1');

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
		$selectObj->where('frs_account_receivable.type = ?', Shared_Model_Code::RECEIVABLE_TYPE_CARD);
		$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
        $selectObj->where('frs_account_receivable.management_group_id = ?', $this->_adminProperty['management_group_id']);  // グループID
        
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

		$items = array(); 
		

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
		
		// 通貨リスト
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
    |  action_URL    * /transaction-receivable/card-add                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定申請 - 登録                                        |
    +----------------------------------------------------------------------------*/
    public function cardAddAction()
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
    |  action_URL    * /transaction-receivable/card-add-post                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定申請 - 登録(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function cardAddPostAction()
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

				if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['memo']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「摘要」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                
                } else if (!empty($errorMessage['accrual_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                    return;
                       
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金元取引先」を入力してください'));
                    return;
                
                } else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['card_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金予定クレジットカード」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['total_amount']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「入金額」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				
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
			        'management_group_id'       => $this->_adminProperty['management_group_id'],
			        
			        'template_id'               => 0,                                  // テンプレートID
			        
			        'status'                    => Shared_Model_Code::RECEIVABLE_STATUS_DRAFT,                // ステータス
			        'payment_status'            => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED,   // 入金ステータス
			        'is_attached'               => 0,                                  // 割当完了
			        
			        'relational_id'             => 0,                                  // 連携ID
			        'relational_display_id'     => '',                                 // 連携表示ID
			        
			        'accrual_date'              => $success['accrual_date'],           // 発生日
			        
					'type'                      => Shared_Model_Code::RECEIVABLE_TYPE_CARD,   // 売掛管理種別
					'invoice_id'                => 0,                                      // 請求書ID
					
					'account_title_id'          => $success['account_title_id'],           // 会計科目ID
					'account_totaling_group_id' => $success['account_totaling_group_id'],  // 採算コード
					
					'target_connection_id'      => $success['target_connection_id'],       // 支払元取引先ID
					'bank_sender_name'          => '',                                     // 振込人名義(全角カタカナ)
					
					'currency_id'               => $success['currency_id'],            // 通貨ID
					'total_amount'              => $success['total_amount'],           // 入金予定額
					
					'card_id'                   => $success['card_id'],                // 入金予定口座
					'receive_plan_date'         => $success['receive_plan_date'],      // 入金予定日
					'received_date'             => NULL,                               // 入金受取日
					
					'file_list'                 => json_encode($fileList),             // 添付資料リスト
					
					'created_user_id'           => $this->_adminProperty['id'],        // 支払申請者
					'approval_user_id'          => 0,                                  // 承認者
					'approval_comment'          => '',                                 // 修正依頼コメント

					
					'memo'                      => $success['memo'],                   // メモ

	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);

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
	                throw new Zend_Exception('/transaction-receivable/card-add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/card-detail                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード入金予定 - 詳細                                      |
    +----------------------------------------------------------------------------*/
    public function cardDetailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->direct      = $direct     = $request->getParam('direct');
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$this->view->data = $data = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			if (empty($direct)) {
				$this->view->backUrl = '/transaction-receivable/card-list';
			}
			$this->_helper->layout->setLayout('back_menu');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        	$this->view->saveButtonName = '承認申請';
		        }
	        }
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
		

		if (!empty($data['card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['card_id']);
		}

		// 銀行口座
		/*
		if (!empty($data['bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['bank_id']);
			
			// 割当情報
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			$this->view->historyItems = $historyItemData = $bankHistoryItemTable->getListByReceivableId($id);
			//var_dump($historyItemData);exit;
		}
		*/
		
	}
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/update-card-payment                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定 - 入金予定情報更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function updateCardPaymentAction()
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
				} else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を選択してください'));
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
					$data = array(
						'card_id'              => $success['card_id'],            // 入金予定カード
						'receive_plan_date'    => $success['receive_plan_date'],  // 入金予定日
						'received_date'        => NULL,                           // 入金完了日
						'total_amount'         => $success['total_amount'],       // 入金予定額
						'currency_id'          => $success['currency_id'],        // 通貨ID
					);
					
					if (!empty($success['received_date'])) {
						$data['received_date'] = $success['received_date'];      // 入金完了日
					}
					
					
					$receivableTable->updateById($id, $data);

	                // commit
	                $receivableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $receivableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-receivable/update-card-payment transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }   
    
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/card-apply-apploval                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 承認申請                                      |
    +----------------------------------------------------------------------------*/
    public function cardApplyApplovalAction()
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
			
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$oldData = $receivableTable->getById($this->_adminProperty['management_group_id'], $id);


			if (empty($oldData['account_title_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return;
            } else if (empty($oldData['memo'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「摘要」を入力してください'));
                return;
            } else if (empty($oldData['account_totaling_group_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                return;
            } else if (empty($oldData['accrual_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                return;
			} else if (empty($oldData['target_connection_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金元取引先」を入力してください'));
                return; 
			} else if (empty($oldData['receive_plan_date'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                return;
			} else if (empty($oldData['total_amount'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金額」を入力してください'));
                return;
			} else if (empty($oldData['currency_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                return; 
			} else if (empty($oldData['card_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「入金予定クレジットカード」を選択してください'));
                return; 
            }

			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $oldData['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $oldData['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $oldData['currency_id']);

            $templateData = NULL;
            
			try {
				$receivableTable->getAdapter()->beginTransaction();

				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_RECEIVABLE_CARD,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $connectionData['company_name'] . "\n支払総額：" .  number_format($oldData['total_amount']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);

				$receivableTable->updateById($id, array(
					'status' => Shared_Model_Code::RECEIVABLE_STATUS_PENDING,
				));
				
				
				$approvalTable->create($approvalData);
				
				// メール送信 -------------------------------------------------------
				$content = "支払先取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'];
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);
				
				// 承認者
				$selectObj = $userTable->select();
		    	$selectObj->where('id = ?', $userData['approver_c1_user_id']);
		        $authorizerUserData = $selectObj->query()->fetch();
	        
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
                throw new Zend_Exception('/transaction-receivable/card-apply-apploval transaction faied: ' . $e);
                
            }
			
		    $this->sendJson(array('result' => 'OK', 'message' => '承認申請しました'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/card-mod-request                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 修正依頼(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function cardModRequestAction()
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
			
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

			try {
				$receivableTable->getAdapter()->beginTransaction();
				
				$receivableTable->updateById($id, array(
					'status'           => Shared_Model_Code::RECEIVABLE_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
				
				$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-receivable/card-detail?id=' . $id;

				// メール送信 -------------------------------------------------------
				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . $url;
	        
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
                throw new Zend_Exception('/transaction-receivable/card-mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/card-approve                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function cardApproveAction()
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
			
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
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

				$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-receivable/invoice-detail?id=' . $id;
				
				// メール送信 -------------------------------------------------------
				$content = "支払元取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "入金予定額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . $url;
	        
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
                throw new Zend_Exception('/transaction-receivable/card-approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-receivable/upload                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 添付ファイルアップロード(Ajax)                             |
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

