<?php
/**
 * class TransactionInvoiceController
 * 請求書発行
 */
 
class TransactionInvoiceController extends Front_Model_Controller
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
		$this->view->menu             = 'receivable';

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/update-data                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function updateDataAction()
    {
	    $invoiceTable  = new Shared_Model_Data_Invoice();
	    $invoiceTable->updateById('104', array(
		   'total_with_tax' => '335340',
	    ));
	    echo 'OK';
	    exit;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/list                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書発行リスト                                           |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$session = new Zend_Session_Namespace('transaction_invoice_list_2');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']              = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['language']            = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			$session->conditions['order_recieved_id']   = '';
			$session->conditions['keyword']             = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['status']              = $request->getParam('status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['language']            = $request->getParam('language', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			$session->conditions['order_recieved_id']   = $request->getParam('order_recieved_id', '');
			$session->conditions['keyword']             = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$invoiceTable  = new Shared_Model_Data_Invoice();
		
		$dbAdapter = $invoiceTable->getAdapter();

        $selectObj = $invoiceTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_invoice.target_connection_id = frs_connection.id', array($invoiceTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_invoice.created_user_id = frs_user.id',array($invoiceTable->aesdecrypt('user_name', false) . 'AS user_name'));

        // グループID
        $selectObj->where('frs_invoice.management_group_id = ?', $this->_adminProperty['management_group_id']);

		if ($session->conditions['status'] !== '') {
			if ($session->conditions['status'] === (string)Shared_Model_Code::INVOICE_STATUS_PAYABLED_NOT_ADDED) {
				$selectObj->where('frs_invoice.status != ' . Shared_Model_Code::INVOICE_STATUS_PAYABLED_ADDED . ' AND frs_invoice.status != ' . Shared_Model_Code::INVOICE_STATUS_DELETED);
			} else {
				$selectObj->where('frs_invoice.status = ?', $session->conditions['status']);
			}
		} else {
			$selectObj->where('frs_invoice.status != ?', Shared_Model_Code::INVOICE_STATUS_DELETED);
		}
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_invoice.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['language'] !== '') {
			$selectObj->where('frs_invoice.language = ?', $session->conditions['language']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_invoice.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_invoice.target_connection_id = ?', $session->conditions['connection_id']);
		}
		
		if ($session->conditions['order_recieved_id'] !== '') {
			$directOrderTable = new Shared_Model_Data_DirectOrder();
			$directOrderData = $directOrderTable->getByDisplayId($this->_adminProperty['management_group_id'], $session->conditions['order_recieved_id']);
			$selectObj->where('`direct_order_ids` LIKE ?', '%"' . $directOrderData['id'] .'"%');
		}
		

        $selectObj->order(new Zend_Db_Expr('frs_invoice.invoice_date IS NULL DESC'));
		$selectObj->order('frs_invoice.invoice_date DESC');
		$selectObj->order('frs_invoice.id DESC');
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator);
        
        
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
    |  action_URL    * /transaction-invoice/update-to-draft                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 下書きに戻す(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateToDraftAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		// POST送信時
		if ($request->isPost()) {
			$invoiceTable     = new Shared_Model_Data_Invoice();
			$receivableTable  = new Shared_Model_Data_AccountReceivable();
			
			$data =  $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);
			
			try {
				$invoiceTable->getAdapter()->beginTransaction();
				
				// 入金予定登録済みの場合は入金予定を削除
				if ($data['status'] === (string)Shared_Model_Code::INVOICE_STATUS_PAYABLED_ADDED) {
					$receivableList = $receivableTable->getListByInvoiceId($this->_adminProperty['management_group_id'], $id);
					
					if (!empty($receivableList)) {
						//var_dump($receivableList);
						//exit;
						
						foreach ($receivableList as $each) {
							$receivableTable->updateById($each['id'], array(
								'status' => Shared_Model_Code::RECEIVABLE_STATUS_DELETED,
							));
						} 
					}
				}
			
				$invoiceTable->updateById($id, array(
					'status' => Shared_Model_Code::INVOICE_STATUS_DRAFT,
				));
			
                // commit
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/update-to-draft transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/delete                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		// POST送信時
		if ($request->isPost()) {
			$invoiceTable  = new Shared_Model_Data_Invoice();

			try {
				$invoiceTable->getAdapter()->beginTransaction();
				
				$invoiceTable->updateById($id, array(
					'status' => Shared_Model_Code::INVOICE_STATUS_DELETED,
				));
			
                // commit
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/submit                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 提出完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		// POST送信時
		if ($request->isPost()) {
			$invoiceTable  = new Shared_Model_Data_Invoice();

			try {
				$invoiceTable->getAdapter()->beginTransaction();
				
				$invoiceTable->updateById($id, array(
					'status' => Shared_Model_Code::INVOICE_STATUS_SUBMITTED,
				));
			
                // commit
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/submit transaction faied: ' . $e);
                
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/add-receivable                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定登録(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function addReceivableAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request  = $this->getRequest();
		$this->view->invoiceId = $invoiceId = $request->getParam('invoice_id');
    	
    	// 請求書情報
    	$invoiceTable  = new Shared_Model_Data_Invoice();
    	$invoiceData = $invoiceTable->getById($this->_adminProperty['management_group_id'], $invoiceId);
    	$invoiceData['currency_id'] = '1';
		$this->view->invoiceData = $invoiceData;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);		
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/add-receivable-post                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定登録(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function addReceivablePostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$invoiceId  = $request->getParam('invoice_id');
		
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
                } else if (!empty($errorMessage['bank_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「入金予定口座」を選択してください'));
                    return;
                } else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				$invoiceTable     = new Shared_Model_Data_Invoice();
				
				// 請求書情報
		    	$invoiceData = $invoiceTable->getById($this->_adminProperty['management_group_id'], $invoiceId);
		    	$invoiceData['currency_id'] = '1'; // 仮
	    	
				try {
					$invoiceTable->getAdapter()->beginTransaction();
					
					$data = array(
				        'management_group_id'    => $this->_adminProperty['management_group_id'],            // 管理グループID
				        'status'                 => Shared_Model_Code::RECEIVABLE_STATUS_APPROVED,           // ステータス
				        'payment_status'         => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED, // 入金ステータス
				        
				        'accrual_date'           => $invoiceData['invoice_date'],                        // 発生日(請求日)
				        
						'type'                   => Shared_Model_Code::RECEIVABLE_TYPE_INVOICE,  // 売掛管理種別
						'invoice_id'             => $invoiceData['id'],                          // 請求書ID
						'account_title_id'       => $success['account_title_id'],                // 会計科目ID
						
						'account_totaling_group_id' => $success['account_totaling_group_id'],    // 採算コード
						
						'target_connection_id'   => $invoiceData['target_connection_id'],  // 請求先取引先ID
						'currency_id'            => $invoiceData['currency_id'],           // 請求金額通貨ID
						'total_amount'           => $invoiceData['total_with_tax'],        // 請求金額
						
						'bank_id'                => $success['bank_id'],                   // 入金予定口座
						'receive_plan_date'      => $success['receive_plan_date'],         // 入金予定日
						'received_date'          => NULL,                                  // 入金受取日
						
						'created_user_id'        => $this->_adminProperty['id'],           // 登録者ユーザーID
						
						'confirm_user_id'        => 0,                                     // 入金確認者ユーザーID
						'confirm_datetime'       => NULL,                                  // 入金確認日
						
						'memo'                   => $success['memo'],                      // メモ
	
		                'created'                 => new Zend_Db_Expr('now()'),
		                'updated'                 => new Zend_Db_Expr('now()'),
					);
					
					$receivableTable->create($data);
					
					$invoiceTable->updateById($invoiceId, array(
						'status' => Shared_Model_Code::INVOICE_STATUS_PAYABLED_ADDED,
					));
				
	                // commit
	                $invoiceTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $invoiceTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-invoice/submit transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/create                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書 新規作成                                            |
    +----------------------------------------------------------------------------*/
    public function createAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
		$request = $this->getRequest();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/add-post                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書新規作成(Ajax)                                       |
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

                if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引先」を入力してください'));
                    return;  
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$invoiceTable     = new Shared_Model_Data_Invoice();
				$connectionTable  = new Shared_Model_Data_Connection();
				$currencyTable    = new Shared_Model_Data_Currency();
				
				// 取引先が有効か
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $success['target_connection_id']);
				if (empty($connectionData)) {
					throw new Zend_Exception('/transaction-invoice/add-post connection data is empty');
				}
				
	            $defaultItems = array();
	            $defaultItems[] = array(
		            'id'         => '1',
		            'item_name'  => '',
		            'spec'       => '',
		            'unit_price' => '',
		            'amount'     => '',
		            'price'      => '',
	            );
            
				$nextInvoiceId = $invoiceTable->getNextDisplayId();

				$data = array(
			        'management_group_id'               => $this->_adminProperty['management_group_id'],
			        'display_id'                        => $nextInvoiceId,
					'status'                            => Shared_Model_Code::INVOICE_STATUS_DRAFT,
					'invoice_type'                      => $success['invoice_type'],                        // 請求書形式
					
					'language'                          => '1', // 言語選択
					'target_connection_id'              => $success['target_connection_id'],
					
					'to_name'                           => $connectionData['company_name'] . ' 御中',       // 宛先
					'title'                             => '請求書',
					
					'labels'                            => json_encode($invoiceTable->getDefaultLabels(Shared_Model_Code::LANGUAGE_JP)),  // テーブル項目ラベル
					'item_list'                         => json_encode($defaultItems),
		
					'memo'                              => '',         // 備考
					'memo_private'                      => '',         // 社内メモ
					'approval_comment'                  => '',         // 承認コメント
			
					'subtotal'                          => 0,          // 小計
					'tax_percentage'                    => 0,          // 消費税率
					'tax'                               => 0,          // 消費税
					'total_with_tax'                    => 0,          // 合計
					'currency_mark'                     => '¥',
					
					'created_user_id'                   => $this->_adminProperty['id'],                     // 作成者ユーザーID
					'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID
					'approval_user_id'                  => 0,
						
	                'created'                           => new Zend_Db_Expr('now()'),
	                'updated'                           => new Zend_Db_Expr('now()'),
				);

				$currencyData = $currencyTable->getByName($this->_adminProperty['management_group_id'], 'JPY');
				if (!empty($currencyData)) {
					$data['currency_id'] = $currencyData['id'];
				}
			
				$invoiceTable->getAdapter()->beginTransaction();
            	  
	            try {
					$invoiceTable->create($data);
					$id = $invoiceTable->getLastInsertedId('id');

	                // commit
	                $invoiceTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $invoiceTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-invoice/add-post transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/create-with-order                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注に基づいて作成(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function createWithOrderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$orderId = $request->getParam('order_id');
		
		// POST送信時
		if ($request->isPost()) {
			$directOrderTable = new Shared_Model_Data_DirectOrder();
			$invoiceTable     = new Shared_Model_Data_Invoice();
			$connectionTable  = new Shared_Model_Data_Connection();
			
			$orderData = $directOrderTable->getById($this->_adminProperty['management_group_id'], $orderId);
			
			if (empty($orderData)) {
				throw new Zend_Exception('/transaction-invoice/create-with-order no order data'); 
			}
			
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $orderData['target_connection_id']);
			
			$nextInvoiceId = $invoiceTable->getNextDisplayId();
            
            $defaultItems = array();
            
            foreach ($orderData['items'] as $each) {
	            $defaultItems[] = array(
		            'id'         => $each['id'],
		            'item_id'    => $each['item_id'],
		            'item_name'  => $each['item_name'],
			        'spec'       => '',
		            'unit_price' => $each['unit_price'],
		            'amount'     => $each['amount'],
		            'price'      => $each['price'],
	            );
			}
            
			$data = array(
		        'management_group_id'               => $this->_adminProperty['management_group_id'],
		        'display_id'                        => $nextInvoiceId,
				'status'                            => Shared_Model_Code::INVOICE_STATUS_DRAFT,
				'invoice_type'                      => Shared_Model_Code::INVOICE_TYPE_CREATE,  // 請求書形式
				
				'target_connection_id'              => $orderData['target_connection_id'],   // 注文者ID

				'direct_order_id'                   => $orderId,                             // 受注管理ID

				'invoice_date'                      => date('Y-m-d'),
				'to_name'                           => $connectionData['company_name'] . ' 御中',
				'title'                             => '請求書',
				'labels'                            => json_encode($invoiceTable->getDefaultLabels(Shared_Model_Code::LANGUAGE_JP)),  // テーブル項目ラベル
				'item_list'                         => json_encode($defaultItems),
	
				'memo'                              => '',         // 備考
				'memo_private'                      => '',         // 社内メモ
				'approval_comment'                  => '',         // 承認コメント

				'subtotal'                          => 0,          // 小計
				'tax_percentage'                    => 0,          // 消費税率
				'tax'                               => 0,          // 消費税
				'total_with_tax'                    => 0,          // 合計
				'currency_mark'                     => NULL,
				'currency_id'                       => '1',        // 受注管理に通貨を加えたのちに対応
					
				'created_user_id'                   => $this->_adminProperty['id'],        // 作成者ユーザーID
				'last_update_user_id'               => $this->_adminProperty['id'],        // 最終更新者ユーザーID
				'approval_user_id'                  => 0,                              // 承認者ユーザーID
				
                'created'                           => new Zend_Db_Expr('now()'),
                'updated'                           => new Zend_Db_Expr('now()'),
			);
				
			$invoiceTable->getAdapter()->beginTransaction();
        	  
            try {
				$invoiceTable->create($data);
				$invoiceId = $invoiceTable->getLastInsertedId('id');
				
				
				$orderData['invoice_ids'][] = $invoiceId;
				
				
				// 受注管理データに請求書IDを保存
				$directOrderTable->updateById($orderId, array(
					'invoice_id'  => $invoiceId,
					'invoice_ids' => serialize($orderData['invoice_ids']),
				));
				
                // commit
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/create-with-order transaction faied: ' . $e);  
            }

		    $this->sendJson(array('result' => 'OK', 'id' => $invoiceId));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/form                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書作成フォーム                                         |
    +----------------------------------------------------------------------------*/
    public function formAction()
    {
        $this->_helper->layout->setLayout('back_menu_invoice');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->previewUrl = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$invoiceTable     = new Shared_Model_Data_Invoice();
		$this->view->data = $data =  $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);

		$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
		
		// 請求書作成者
		$userTable       = new Shared_Model_Data_User();
    	if (!empty($data['created_user_id'])) {
    		$this->view->createdUser = $userTable->getById($data['created_user_id']);
    	}
    	
    	// 提出先
    	$connectionTable     = new Shared_Model_Data_Connection();
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	
    	$directOrderTable = new Shared_Model_Data_DirectOrder();
    	if (!empty($data['direct_order_id'])) {
    		$this->view->directOrderData = $directOrderTable->getById($this->_adminProperty['management_group_id'], $data['direct_order_id']);
    	}

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
    |  action_URL    * /transaction-invoice/update                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書作成フォーム 保存                                    |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		$reload = false;
		$reloadBy = '';		
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
                	$this->sendJson(array('result' => 'NG', 'message' => '「提出先取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['to_name']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「宛先」を入力してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「表題」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$invoiceTable     = new Shared_Model_Data_Invoice();
				$connectionTable  = new Shared_Model_Data_Connection();
				$currencyTable    = new Shared_Model_Data_Currency();
					
				// 取引先が有効か
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $success['target_connection_id']);
				if (empty($connectionData)) {
					throw new Zend_Exception('/transaction-invoice/add-post connection data is empty');
				}
				
				$oldData = $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);

				$fileList = array();
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'target_date'      => $request->getParam($eachId . '_target_date'),
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }

				$data = array(
					'language'                   => $success['language'],             // 言語選択
					'including_tax'              => $success['including_tax'],        // 税込価格
					
					'direct_order_id'            => $success['direct_order_id'],      // 受注管理ID
					'document_id'                => 0,                                // ドキュメントID
					
					'target_connection_id'       => $success['target_connection_id'], // 提出先取引ID
					'to_name'                    => $success['to_name'],              // 宛先

					'title'                      => $success['title'],                // タイトル

					'subtotal'                   => $success['subtotal'],             // 小計
					'tax_percentage'             => $success['tax_percentage'],       // 消費税率
					'tax'                        => $success['tax'],                  // 消費税
					'total_with_tax'             => $success['total_with_tax'],       // 合計
					
					'file_list'                  => json_encode($fileList),           // 添付資料リスト
					
					'memo'                       => $success['memo'],                 // 備考
					'memo_private'               => $success['memo_private'],         // 社内メモ
					
					'created_user_id'            => $success['created_user_id'],      // 作成者
				);

				if ($oldData['language'] != $success['language']) {
					// 言語切り替え時
					$reload = true;
					$reloadBy = 'language';
					
					$data['labels'] = json_encode($invoiceTable->getDefaultLabels($success['language']));
					
					if ($success['language'] == Shared_Model_Code::LANGUAGE_EN) {
						$data['title']         = 'INVOICE';
						$data['currency_mark'] = NULL;
						$data['to_name']       = 'MESSRS: ' . $connectionData['company_name'];
					} else {
						$data['title']         = '請求書';
						$data['currency_mark'] = NULL;
						$data['to_name']       = $connectionData['company_name'] . ' 御中';
					}
					
				} else {
					if ($oldData['including_tax'] !== $success['including_tax']) {
						// 税込価格設定切り替え
						$reload = true;
						$reloadBy = 'including_tax';
					}
					
					$data['currency_mark'] = NULL;
					$data['currency_id']   = $success['currency_id'];
					
					$invoiceDate = NULL;
	            	if (!empty($success['invoice_date'])) {
		            	if ($success['language'] == Shared_Model_Code::LANGUAGE_EN) {
							$year  = mb_substr($success['invoice_date'], 7, 4);
			            	$month = mb_substr($success['invoice_date'], 0, 2);
			            	$date  = mb_substr($success['invoice_date'], 3, 2);
							$invoiceDate = $year . '-' . $month . '-' . $date;
						} else {
							$year  = mb_substr($success['invoice_date'], 0, 4);
			            	$month = mb_substr($success['invoice_date'], 5, 2);
			            	$date  = mb_substr($success['invoice_date'], 8, 2);
			            	$invoiceDate = $year . '-' . $month . '-' . $date;
		            	}
	            	}
	            	
					$data['invoice_date'] = $invoiceDate;

					// ラベル
					$labels = $invoiceTable->getDefaultLabels($success['language']);
					
					foreach ($labels as $key => &$val) {
						$val = $request->getParam($key);
					}
					
					$data['labels'] = json_encode($labels);
				}
				
				// 受注管理ID
				$directOrderIdList = array();
				
				if (!empty($success['direct_order_id_list'])) {
					$directOrderList = explode(',', $success['direct_order_id_list']);
					
					foreach ($directOrderList as $eachId) {
						$directOrderId    = $request->getParam($eachId . '_direct_order_id');
					
						if (!empty($directOrderId)) {
							$directOrderIdList[] = $directOrderId;
						}
					}
				}
				
				$data['direct_order_ids'] = serialize($directOrderIdList);
				
				
				// テーブル中身
				if (!empty($success['invoice_item_list'])) {
					$invoiceItemList = explode(',', $success['invoice_item_list']);

					$itemList = array();
					$count = 1;
	            
		            foreach ($invoiceItemList as $eachId) {
		            	$itemId    = $request->getParam($eachId . '_item_id');
		            	$itemName  = $request->getParam($eachId . '_item_name');
		            	$spec      = $request->getParam($eachId . '_spec');
		            	$unitPrice = $request->getParam($eachId . '_unit_price');
		            	$amount    = $request->getParam($eachId . '_amount');
						$price     = $request->getParam($eachId . '_price');
						
						/*
	                	if (empty($itemName)) {
						    $this->sendJson(array('result' => 'NG', 'message' => 'No.' . $count . ' - 項目名が空欄です'));
				    		return;
	                	}
	                	*/
            
		                $itemList[] = array(
							'id'           => $count,
							'item_id'      => $itemId,
							'item_name'    => $itemName,
							'unit_price'   => $unitPrice,
							'amount'       => $amount,
							'price'        => $price,
		                );
		                
		            	$count++;
		            }
		            
		            $data['item_list']   = json_encode($itemList);
	            } else {
	            	$data['item_list']   = json_encode(array());
	            }
		            
				try {
					$invoiceTable->getAdapter()->beginTransaction();
					
					$invoiceTable->updateById($id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);

			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

			            	if (!empty($tempFileName)) {
				            	
				            	if (Shared_Model_Resource_TemporaryPrivate::isExist($tempFileName)) {
				            		// 正式保存
				            		Shared_Model_Resource_Invoice::makeResource($id, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
				            		
					            	// tempファイルを削除
									Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								}
								
							}
						}
					}

	                // commit
	                $invoiceTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $invoiceTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-invoice/update transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK', 'reload' => $reload, 'reload_by' => $reloadBy));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));				
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/update-reference                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理引用 保存                                          |
    +----------------------------------------------------------------------------*/
    public function updateReferenceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
	    
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$directOrderList = $request->getParam('direct_order_id_list');
		
		// POST送信時
		if ($request->isPost()) {
			$invoiceTable = new Shared_Model_Data_Invoice();

			// 受注管理ID
			$directOrderIdList = array();
			
			if (!empty($directOrderList)) {
				$directOrderList = explode(',', $directOrderList);
				
				foreach ($directOrderList as $eachId) {
					$directOrderId    = $request->getParam($eachId . '_direct_order_id');
				
					if (!empty($directOrderId)) {
						$directOrderIdList[] = $directOrderId;
					}
				}
			}
			
			$invoiceTable->updateById($id, array(
				'direct_order_ids' => serialize($directOrderIdList),
			));
			
			$this->sendJson(array('result' => 'OK'));
		    return;
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));	
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/preview                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書フォーム PDFプレビュー                               |
    +----------------------------------------------------------------------------*/
    public function previewAction()
    {
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$invoiceTable = new Shared_Model_Data_Invoice();
		
		$data = $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (empty($data)) {
			throw new Zend_Exception('/transaction-invoice/preview - no target data');
		}

    	$connectionTable = new Shared_Model_Data_Connection();
    	$userTable = new Shared_Model_Data_User();
    	
    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	$data['company_name'] = $connectionData['company_name'];
    	
    	$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
    	
		$companyData = array(
			'company_name' => $groupData['organization_name'],
			'address'      => '〒' . $groupData['postal_code'] . ' ' . $groupData['prefecture'] . $groupData['city'] . $groupData['address'],
			'tel'          => $groupData['tel'],
			'fax'          => $groupData['fax'],
			'user_name'    => '',
		);
		
		if ($data['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$companyData = array(
				'company_name' => $groupData['organization_name_en'],
				'address'      => $groupData['address_en'] . ' ' . $groupData['city_en'] . ", " . $groupData['prefecture_en'] . ' JAPAN'. ' (Zip: ' . $groupData['postal_code'] . ')',
				'tel'          => $groupData['tel'],
				'fax'          => $groupData['fax'],
				'user_name'    => '',
			);
		}
		
		// 作成者
    	if (!empty($data['created_user_id'])) {
    		$createdUser = $userTable->getById($data['created_user_id']);
    		
    		$companyData['user_name'] = $createdUser['department_name'] . '　' . $createdUser['user_name'];
    		
    		if ($data['language'] == Shared_Model_Code::LANGUAGE_EN) {
    			$companyData['user_name'] = $createdUser['department_name_en'] . '　' . $createdUser['user_name_en'];
    		}
    	}
    	
    	$helper = $this->view->getHelper('numberFormat');

		Shared_Model_Pdf_Invoice::makeSingle($data, $companyData, $helper);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/upload                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 添付資料アップロード(Ajax)                                 |
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
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/apply-apploval                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書 承認申請                                            |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$invoiceTable  = new Shared_Model_Data_Invoice();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			// 申請者情報
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        	
			$data = $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);
			
        	if (empty($data['invoice_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「請求書発行日」を入力してください'));
                return;
            } else if (empty($data['currency_id'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「通貨」を選択してください'));
                return;
            }

	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

			try {
				$invoiceTable->getAdapter()->beginTransaction();
				$invoiceTable->updateById($id, array(
					'status' => Shared_Model_Code::INVOICE_STATUS_PENDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_INVOICE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $connectionData['company_name'] . "\n請求額：" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);
				
				
				// メール送信 -------------------------------------------------------
				$content = "請求先：\n" . $connectionData['company_name'] . "\n\n" 
				         . "請求額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'];
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);

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
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/apply-apploval transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/confirm                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書 承認確認                                            |
    +----------------------------------------------------------------------------*/
    public function confirmAction()
    {
        $this->view->previewUrl     = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->approvalId = $approvalId = $request->getParam('approval_id');
		$this->view->id  = $id  = $request->getParam('id');
		
		
		if (!empty($approvalId)) {
			$this->_helper->layout->setLayout('back_menu_approval');
			$this->view->backUrl        = '/approval/list';
	        $this->view->saveUrl        = 'javascript:void(0);';
	        $this->view->saveButtonName = '保存';
		}  else {
			$this->_helper->layout->setLayout('back_menu');
			$this->view->backUrl        = '/transaction-invoice/list';
		}
		
		$invoiceTable  = new Shared_Model_Data_Invoice();
    	$this->view->data = $data = $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);
    	
    	$connectionTable = new Shared_Model_Data_Connection();
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
		
		$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
		
		$userTable       = new Shared_Model_Data_User();
    	// 見積作成者
    	if (!empty($data['created_user_id'])) {
    		$this->view->createdUser = $userTable->getById($data['created_user_id']);
    	}
    	
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
    	$directOrderTable = new Shared_Model_Data_DirectOrder();
    	if (!empty($data['direct_order_id'])) {
    		$this->view->directOrderData = $directOrderTable->getById($this->_adminProperty['management_group_id'], $data['direct_order_id']);
    	}
    	
		$currencyTable    = new Shared_Model_Data_Currency();
		$this->view->currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/mod-request                           |
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
		$memoPrivate     = $request->getParam('memo_private');
		
		// POST送信時
		if ($request->isPost()) {
			$invoiceTable  = new Shared_Model_Data_Invoice();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);
			
	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			try {
				$invoiceTable->getAdapter()->beginTransaction();
				
				$invoiceTable->updateById($id, array(
					'status' => Shared_Model_Code::INVOICE_STATUS_MOD_REQUEST,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
				$content = "請求先：\n" . $connectionData['company_name'] . "\n\n" 
				         . "請求額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-invoice/confirm?id=' . $id;
	        
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
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/approve                               |
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
		$memoPrivate     = $request->getParam('memo_private');
		
		// POST送信時
		if ($request->isPost()) {
			$invoiceTable  = new Shared_Model_Data_Invoice();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $invoiceTable->getById($this->_adminProperty['management_group_id'], $id);
			
	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			try {
				$invoiceTable->getAdapter()->beginTransaction();
				
				$invoiceTable->updateById($id, array(
					'status' => Shared_Model_Code::INVOICE_STATUS_APPROVED,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
				$content = "請求先：\n" . $connectionData['company_name'] . "\n\n" 
				         . "請求額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-invoice/confirm?id=' . $id;
	        
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
                $invoiceTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $invoiceTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-invoice/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
     
}

