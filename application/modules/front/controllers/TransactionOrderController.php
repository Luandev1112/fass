<?php
/**
 * class TransactionOrderController
 * 発注管理
 */
 
class TransactionOrderController extends Front_Model_Controller
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
		$this->view->menu = 'order';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/mod                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  *  修正(DEVELOP)                                             |
    +----------------------------------------------------------------------------*/
    public function modAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = '125';
		

		$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
		$data =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
		var_dump($data);
		exit;
		
		$orderFormTable->updateById($id, array(
			'tax' => '17120',
		));
			
		echo 'OK';
		exit;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/mod-online                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  *  修正(DEVELOP)                                             |
    +----------------------------------------------------------------------------*/
    public function modOnlineAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = '163';
		
		$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
		$data =  $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
		//var_dump($data);
		//exit;
		
		$onlinePurchaseTable->updateById($id, array(
			//'subtotal'        => '1715',          // 小計
			//'tax'             => '137',           // 税額
			'total_with_tax'  => '6134',          // 合計
		));
			
		echo 'OK';
		exit;
    }
    
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/list                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注管理                                                   |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$session = new Zend_Session_Namespace('transaction_order_listsss');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']              = '';
			$session->conditions['payable_status']      = '';
			$session->conditions['order_form_type']     = '';
			$session->conditions['language']            = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
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
			$session->conditions['payable_status']      = $request->getParam('payable_status', '');
			$session->conditions['order_form_type']     = $request->getParam('order_form_type', '');
			$session->conditions['language']            = $request->getParam('language', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			$session->conditions['keyword']             = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
    	$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
		
		$dbAdapter = $orderFormTable->getAdapter();

        $selectObj = $orderFormTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_direct_order_form.target_connection_id = frs_connection.id', array($orderFormTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_direct_order_form.created_user_id = frs_user.id',array($orderFormTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
        $selectObj->where('frs_direct_order_form.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if (!empty($session->conditions['status'])) {
        	if ($session->conditions['status'] === (string)Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID_PENDDING) {
	        	$selectObj->where('frs_direct_order_form.status != ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID
	        	           . ' AND frs_direct_order_form.status != ' . Shared_Model_Code::ORDER_FORM_STATUS_DELETED
	        	           . ' AND frs_direct_order_form.status != ' . Shared_Model_Code::ORDER_FORM_STATUS_CANCELED);
        	} else {
        		$selectObj->where('frs_direct_order_form.status = ?', $session->conditions['status']);
        	}
        } else {
        	$selectObj->where('frs_direct_order_form.status != ?', Shared_Model_Code::ORDER_FORM_STATUS_DELETED);
        }

        if (!empty($session->conditions['order_form_type'])) {
        	$selectObj->where('frs_direct_order_form.order_form_type = ?', $session->conditions['order_form_type']);
        }

        if (!empty($session->conditions['language'])) {
        	$selectObj->where('frs_direct_order_form.language = ?', $session->conditions['language']);
        }
        
        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('frs_direct_order_form.target_connection_id = ?', $session->conditions['connection_id']);
        }

        if ($session->conditions['payable_status'] !== '') {
        	$selectObj->where('frs_direct_order_form.order_form_payable_status = ?', $session->conditions['payable_status']);
        }  

		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_direct_order_form.created_user_id = ?', $session->conditions['applicant_user_id']);
		}
		
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        
		$selectObj->order(new Zend_Db_Expr('frs_direct_order_form.order_date IS NULL DESC'));
		$selectObj->order('frs_direct_order_form.order_date DESC');
		$selectObj->order('frs_direct_order_form.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
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
    |  action_URL    * /transaction-order/delete                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  *  破棄(Ajax)                                                |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		// POST送信時
		if ($request->isPost()) {
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();

			try {
				$orderFormTable->getAdapter()->beginTransaction();
				
				$orderFormTable->updateById($id, array(
					'status' => Shared_Model_Code::ORDER_FORM_STATUS_DELETED,
				));
			
                // commit
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/delete transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/update-to-cancel                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  *  キャンセル(Ajax)                                          |
    +----------------------------------------------------------------------------*/
    public function updateToCancelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();

			try {
				$orderFormTable->getAdapter()->beginTransaction();
				
				$orderFormTable->updateById($id, array(
					'status' => Shared_Model_Code::ORDER_FORM_STATUS_CANCELED,
				));
			
                // commit
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/update-to-cancel transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
       
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/deliveried                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 納品完了入力                                               |
    +----------------------------------------------------------------------------*/
    public function deliveriedAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl          = 'javascript:void(0);';
        $this->view->saveUrl          = 'javascript:void(0);';
        $this->view->saveButtonName   = '登録';
        
		$request = $this->getRequest();	
    	$this->view->id = $id = $request->getParam('id');
		
		$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
		$connectionTable = new Shared_Model_Data_Connection();
		
		// 発注データ
		$this->view->data = $data = $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
		
	    // 発注元
	    $this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/deliveried-post                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 納品完了入力(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function deliveriedPostAction()
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

                if (!empty($errorMessage['deliveried_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「納品日」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$orderFormTable  = new Shared_Model_Data_DirectOrderForm();

				$data = array(
					'deliveried_status'  => Shared_Model_Code::ORDER_FORM_DELIVERIED_STATUS_RECIEVED,
					'deliveried_date'    => $success['deliveried_date'], // 納品日
				);

				$orderFormTable->getAdapter()->beginTransaction();
            	  
	            try {
					$orderFormTable->updateById($id, $data);
					
	                // commit
	                $orderFormTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $orderFormTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/deliveried-post transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/list-select                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注管理(ポップアップ用)                                   |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		// 検索条件
		$conditions = array();
		$conditions['status']         = $request->getParam('status', '');
		$conditions['connection_id']  = $request->getParam('connection_id', '');
		$conditions['type']           = $request->getParam('type', '');
		$this->view->conditions       = $conditions;
		
    	$orderFormTable   = new Shared_Model_Data_DirectOrderForm();
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $conditions['connection_id']);
		
		$dbAdapter = $orderFormTable->getAdapter();

        $selectObj = $orderFormTable->select();
        $selectObj->where('status = ?', Shared_Model_Code::ORDER_FORM_STATUS_SUBMITTED);
	    $selectObj->where('target_connection_id = ?', $conditions['connection_id']);
				
        if (!empty($conditions['status'])) {
        	$selectObj->where('frs_direct_order_form.status = ?', $conditions['status']);
        } else {
        	$selectObj->where('frs_direct_order_form.status != ?', Shared_Model_Code::ESTIMATE_STATUS_DELETED);
        }
		
		$selectObj->order(new Zend_Db_Expr('frs_direct_order_form.order_date IS NULL DESC'));
		$selectObj->order('frs_direct_order_form.order_date DESC');
		$selectObj->order('frs_direct_order_form.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator, 'javascript:pageOrder($page);');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/submit                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 提出完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
			
			try {
				$orderFormTable->getAdapter()->beginTransaction();
				
				$orderFormTable->updateById($id, array(
					'status' => Shared_Model_Code::ORDER_FORM_STATUS_SUBMITTED,
				));
				
                // commit
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/submit transaction failed: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/reservation                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫予約                                                   |
    +----------------------------------------------------------------------------*/
    public function reservationAction()
    {
    
    
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/create                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書 新規作成                                            |
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
    |  action_URL    * /transaction-order/add-post                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書 新規作成(Ajax)                                      |
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
				$orderFormTable   = new Shared_Model_Data_DirectOrderForm();
				$connectionTable  = new Shared_Model_Data_Connection();
				
				// 取引先が有効か
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $success['target_connection_id']);
				if (empty($connectionData)) {
					throw new Zend_Exception('/transaction-order/add-post connection data is empty');
				}
				
	            $defaultItems = array();
	            $defaultItems[] = array(
		            'id'         => '1',
		            'supply_type'  => '',
		            'supply_id'    => '',
		            'item_name'  => '',
		            'spec'       => '',
		            'unit_price' => '',
		            'amount'     => '',
		            'price'      => '',
	            );
 
 	            $defaultConditionItems = array();
	            $defaultConditionItems[] = array(
		            'id'         => '1', 'label'      => '', 'content' => '',
	            );
	            
				$nextOrderFormId = $orderFormTable->getNextDisplayId();
	            
				$data = array(
					'language'                          => '1', // 言語選択
			        'management_group_id'               => $this->_adminProperty['management_group_id'],
			        'display_id'                        => $nextOrderFormId,
					'status'                            => Shared_Model_Code::ORDER_FORM_STATUS_DRAFT,
					'order_form_type'                   => $success['order_form_type'],
					'target_connection_id'              => $success['target_connection_id'],
					'to_name'                           => $connectionData['company_name'] . ' 御中',  // 宛先
					
					'order_date'                        => NULL,                             // 発注日
						
					'title'                             => '発注書',
						
					'labels'                            => json_encode($orderFormTable->getDefaultLabels($success['language'])),     // テーブル項目ラベル
					'item_list'                         => json_encode($defaultItems),
					'warehouse_id'                      => 0,                                      // 納入希望先倉庫ID
					'conditions'                        => json_encode($defaultConditionItems),    // 前提条件
					
					'memo'                              => '',         // 備考
					'memo_private'                      => '',         // 社内メモ
					'approval_comment'                  => '',         // 承認コメント
			
					'subtotal'                          => 0,          // 小計
					'tax_percentage'                    => 0,          // 消費税率
					'tax'                               => 0,          // 消費税
					'total_with_tax'                    => 0,          // 合計
				
					'created_user_id'                   => $this->_adminProperty['id'],    // 作成者ユーザーID
					'last_update_user_id'               => $this->_adminProperty['id'],    // 最終更新者ユーザーID
					'approval_user_id'                  => 0,                              // 承認者ユーザーID
					
	                'created'                           => new Zend_Db_Expr('now()'),
	                'updated'                           => new Zend_Db_Expr('now()'),
				);

				$data['labels'] = json_encode($orderFormTable->getDefaultLabels($success['language']));
				$data['currency_mark'] = '¥';
					
				$orderFormTable->getAdapter()->beginTransaction();
            	  
	            try {
					$orderFormTable->create($data);
					$id = $orderFormTable->getLastInsertedId('id');

	                // commit
	                $orderFormTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $orderFormTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/add-post transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/form                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書作成フォーム                                         |
    +----------------------------------------------------------------------------*/
    public function formAction()
    {
        $this->_helper->layout->setLayout('back_menu_invoice');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		
		$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
		$this->view->data = $data =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if ($data['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_CREATE) {
			// 作成の場合 PDFプレビューを表示
			$this->view->previewUrl = 'javascript:void(0);';
		}

		$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
		
		// 発注書作成者
		$userTable       = new Shared_Model_Data_User();
    	if (!empty($data['created_user_id'])) {
    		$this->view->createdUser = $userTable->getById($data['created_user_id']);
    	}
    	
    	// 提出先
    	$connectionTable     = new Shared_Model_Data_Connection();
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
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
    |  action_URL    * /transaction-order/update                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書作成フォーム 保存                                    |
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
                } else if (!empty($errorMessage['subtotal']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「合計金額(税別)」は半角数字のみ(カンマ/ピリオドを含む)で入力してください'));
                    return;
                } else if (!empty($errorMessage['tax_percentage']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「税率」は半角数字のみ(カンマ/ピリオドを含む)で入力してください'));
                    return;
                } else if (!empty($errorMessage['tax']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「税額」は半角数字のみ(カンマ/ピリオドを含む)で入力してください'));
                    return;
                } else if (!empty($errorMessage['total_with_tax']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「合計金額(税込)」は半角数字のみ(カンマ/ピリオドを含む)で入力してください'));
                    return;
				}
				
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
				$connectionTable  = new Shared_Model_Data_Connection();
					
				// 取引先が有効か
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $success['target_connection_id']);
				if (empty($connectionData)) {
					throw new Zend_Exception('/transaction-order/add-post connection data is empty');
				}
						
				$oldData =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
				
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

				$supplyList = array();
				if ($success['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
		            if (!empty($success['supply_list'])) {
		            	$supplyIdList = explode(',', $success['supply_list']);
		            	
			            foreach ($supplyIdList as $eachId) {
			                $supplyList[] = array(
								'id'                  => $eachId,
								'reference_type'      => $request->getParam($eachId . '_reference_type'),
								'reference_target_id' => $request->getParam($eachId . '_reference_target_id'),
			                );
			            }
		            }
	            }
	            
				$data = array(
					'order_form_type'            => $success['order_form_type'],      // 形式
					'language'                   => $success['language'],             // 言語選択
					'including_tax'              => $success['including_tax'],        // 税込価格
					
					'target_connection_id'       => $success['target_connection_id'], // 提出先取引ID
					'to_name'                    => $success['to_name'],              // 宛先
										
					'title'                      => $success['title'],                // タイトル
					
					'subtotal'                   => $success['subtotal'],             // 小計
					'tax_percentage'             => $success['tax_percentage'],       // 消費税率
					'tax'                        => $success['tax'],                  // 消費税
					'total_with_tax'             => $success['total_with_tax'],       // 合計
					
					'memo'                       => $success['memo'],                 // 備考
					'memo_private'               => $success['memo_private'],         // 社内メモ
					
					'file_list'                  => json_encode($fileList),           // 添付資料リスト
					'supply_list'                => json_encode($supplyList),           // 添付資料リスト
					
					'created_user_id'            => $success['created_user_id'],      // 作成者
				);
				
				if (!empty($success['is_delivery_plan_date_unknown'])) {
					$data['is_delivery_plan_date_unknown'] = 1;
					$data['delivery_plan_date'] = date('Y-m-d', strtotime('+7 day'));
				} else {
					if (empty($success['delivery_plan_date'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「納品予定日」を入力してください'));
						return;
					}
					
					$data['is_delivery_plan_date_unknown'] = 0;
					$data['delivery_plan_date'] = $success['delivery_plan_date'];   // 納品予定日
				}


				if ($oldData['order_form_type'] != $success['order_form_type']) {
					// 形式切り替え時
					$reload = true;
					$reloadBy = 'order_form_type';
					
					if ($success['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
						$data['to_name']       = $connectionData['company_name'];
						$data['title']         = '';
					} else {
						$data['title']         = '発注書';
						$data['currency_mark'] = '¥';
						$data['to_name']       = $connectionData['company_name'] . ' 御中';
						$data['labels']        = json_encode($orderFormTable->getDefaultLabels(Shared_Model_Code::LANGUAGE_JP));
						$data['language']      = Shared_Model_Code::LANGUAGE_JP;
					}
				
				} else if ($success['order_form_type'] !== (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD && $oldData['language'] != $success['language']) {
					// 言語切り替え時
					$reload = true;
					$reloadBy = 'language';
					
					$data['labels'] = json_encode($orderFormTable->getDefaultLabels($success['language']));
					
					if ($success['language'] == Shared_Model_Code::LANGUAGE_EN) {
						$data['title']         = 'ORDER FORM';
						$data['currency_mark'] = '$';
						$data['to_name']       = 'MESSRS: ' . $connectionData['company_name'];
					} else {
						$data['title']         = '発注書';
						$data['currency_mark'] = '¥';
						$data['to_name']       = $connectionData['company_name'] . ' 御中';
					}
					
				} else {
					$data['currency_id'] = $success['currency_id'];
					
					if ($success['order_form_type'] !== (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
						if ($oldData['including_tax'] !== $success['including_tax']) {
							// 税込価格設定切り替え
							$reload = true;
							$reloadBy = 'including_tax';
						}
					}
					
					$orderDate = NULL;
	            	if (empty($success['order_date'])) {
		                $this->sendJson(array('result' => 'NG', 'message' => '「注文書発行日」を入力してください'));
		                return;
		            } else {
		            	if ($success['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
		            		$orderDate = $success['order_date'];
		            	
						} else if ($success['language'] == Shared_Model_Code::LANGUAGE_EN) {
							$year  = mb_substr($success['order_date'], 7, 4);
			            	$month = mb_substr($success['order_date'], 0, 2);
			            	$date  = mb_substr($success['order_date'], 3, 2);
							$orderDate = $year . '-' . $month . '-' . $date;
						} else {
							$year  = mb_substr($success['order_date'], 0, 4);
			            	$month = mb_substr($success['order_date'], 5, 2);
			            	$date  = mb_substr($success['order_date'], 8, 2);
			            	$orderDate = $year . '-' . $month . '-' . $date;
		            	}
	            	}
	            	
					$data['order_date'] = $orderDate;

					$labels = $orderFormTable->getDefaultLabels($success['language']);
					
					foreach ($labels as $key => &$val) {
						$val = $request->getParam($key, '');
					}
					
					$data['labels'] = json_encode($labels);
				}
				
				// テーブル中身
				if (!empty($success['order_item_list'])) {
					$orderItemList = explode(',', $success['order_item_list']);

					$itemList = array();
					$count = 1;
	            
		            foreach ($orderItemList as $eachId) {
		            	$supplyType  = $request->getParam($eachId . '_supply_type');
		            	$supplyId    = $request->getParam($eachId . '_supply_id');
		            	$itemName    = $request->getParam($eachId . '_item_name');
		            	$unitPrice   = $request->getParam($eachId . '_unit_price');
		            	$amount      = $request->getParam($eachId . '_amount');
						$price       = $request->getParam($eachId . '_price');
						
						/*
	                	if (empty($itemName)) {
						    $this->sendJson(array('result' => 'NG', 'message' => 'No.' . $count . ' - 項目名が空欄です'));
				    		return;
	                	}
	                	*/
            
		                $itemList[] = array(
							'id'           => $count,
							'supply_type'  => $supplyType,
							'supply_id'    => $supplyId,
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
					$orderFormTable->getAdapter()->beginTransaction();
					
					$orderFormTable->updateById($id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);

			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

			            	if (!empty($tempFileName)) {
				            	
				            	if (Shared_Model_Resource_TemporaryPrivate::isExist($tempFileName)) {
				            		// 正式保存
				            		Shared_Model_Resource_OrderForm::makeResource($id, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
				            		
					            	// tempファイルを削除
									Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								}
							}
						}
					}
					
	                // commit
	                $orderFormTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $orderFormTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/update transaction failed: ' . $e);
	                
	            }
			    $this->sendJson(array('result' => 'OK', 'reload' => $reload, 'reload_by' => $reloadBy));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));				
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/preview                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書 PDFプレビュー                                       |
    +----------------------------------------------------------------------------*/
    public function previewAction()
    {
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
		$data = $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (empty($data)) {
			throw new Zend_Exception('/transaction-order/preview - no target data');
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
    	
		Shared_Model_Pdf_DirectOrderForm::makeSingle($data, $companyData, $this->view);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/complete-payable                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書 支払申請完了                                        |
    +----------------------------------------------------------------------------*/
    public function completePayableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
			$payableTable    = new Shared_Model_Data_AccountPayable();
			
			$oldData =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
			
			if (empty($oldData)) {
				throw new Zend_Exception('/transaction-order/complete-payable - no target order data');
			}
			
			/*
			foreach ($oldData['payable_ids'] as $eachPayableId) {
				$payableData = $payableTable->getById($this->_adminProperty['management_group_id'], $eachPayableId);
				
				if ($payableData['status'] == (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT) {
					$this->sendJson(array('result' => 'NG', 'message' => '未承認の支払申請があります'));
				}
			}
			*/
			
			try {
				$orderFormTable->getAdapter()->beginTransaction();

				$orderFormTable->updateById($id, array(
					'order_form_payable_status' => Shared_Model_Code::ORDER_FORM_PAYABLE_COMPLETED,
				));
 
                 // commit
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/complete-payable transaction failed: ' . $e); 
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/apply-apploval                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発注書 承認申請                                            |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
			$approvalTable   = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			// 申請者情報
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$data = $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
			
            if (empty($data['order_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「注文書発行日」を入力してください'));
                return;
            } else if (empty($data['to_name'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「宛先」を入力してください'));
                return;
			}

			if (empty($data['delivery_plan_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「納品予定日」を入力してください'));
                return;
            }

			
			if ($data['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
				if (empty($data['currency_id'])) {
	                $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
	                return;
	            }
	        }

	        // 通貨
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$orderFormTypeList = Shared_Model_Code::codes('order_form_type');
			$title   = '';
			$content = '';
			
			if ($data['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
				$title   = $connectionData['company_name'] . " 合計金額：" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n" . str_replace("\r\n", " ", $data['title']);
				$content = "発注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注先：\n" . $connectionData['company_name'] . "\n\n"
				         . "形式：\n" . $orderFormTypeList[$data['order_form_type']] . "\n\n"
				         . "内容：\n" . $data['title']. "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'];
			} else {
				$text = '';
				$itemList = $data['item_list'];
				if (!empty($itemList)) {
					$textList = array();
					foreach ($itemList as $eachItem) {
						$exploded = explode("\n", $eachItem['item_name']);
						if (!empty($exploded[0])) {
							$textList[] = str_replace("\n", '', $exploded[0]);
						}
					}
					$text = implode(" / ", $textList);
				}
				$title   = $connectionData['company_name'] . " 合計金額：" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n" . $text;
				$content = "発注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注先：\n" . $connectionData['company_name'] . "\n\n"
				         . "形式：\n" . $orderFormTypeList[$data['order_form_type']] . "\n\n"
				         . "内容：\n" . $text . "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'];
			}
				
			try {
				$orderFormTable->getAdapter()->beginTransaction();
				
				$orderFormTable->updateById($id, array(
					'status' => Shared_Model_Code::ORDER_FORM_STATUS_PENDING,
				));

				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_ORDERFORM,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $title,
					
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
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/apply-apploval transaction failed: ' . $e);
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/confirm                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書 確認                                                |
    +----------------------------------------------------------------------------*/
    public function confirmAction()
    {
		$request = $this->getRequest();
		$this->view->approvalId = $approvalId = $request->getParam('approval_id');
		$this->view->id      = $id      = $request->getParam('id');
		$this->view->posTop  = $request->getParam('pos');
		$this->view->direct  = $direct  = $request->getParam('direct', 0);

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
    	$this->view->data = $data = $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
    	
		if (!empty($approvalId)) {
	        $this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->backUrl        = '/approval/list';
	        $this->view->saveUrl        = 'javascript:void(0);';
	        $this->view->saveButtonName = '保存';
	        $this->view->showRejectButton = false;
		} else {
			if (empty($direct)) {
				$this->view->backUrl = '/transaction-order/list';
			}
			$this->_helper->layout->setLayout('back_menu');
	        $this->view->saveUrl = '';
		}
	
        if ($data['order_form_type'] !== (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
        	$this->view->previewUrl = 'javascript:void(0);';
        }
    	
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
    |  action_URL    * /transaction-order/mod-request                             |
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
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
			$approvalTable   = new Shared_Model_Data_Approval();
			$userTable       = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
			
	        // 通貨
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$orderFormTypeList = Shared_Model_Code::codes('order_form_type');
			
			if ($data['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
				$content = "発注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注先：\n" . $connectionData['company_name'] . "\n\n"
				         . "形式：\n" . $orderFormTypeList[$data['order_form_type']] . "\n\n"
				         . "内容：\n" . $data['title']. "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-order/confirm?id=' . $id;
			} else {
				$text = '';
				$itemList = $data['item_list'];
				if (!empty($itemList)) {
					$textList = array();
					foreach ($itemList as $eachItem) {
						$exploded = explode("\n", $eachItem['item_name']);
						if (!empty($exploded[0])) {
							$textList[] = str_replace("\n", '', $exploded[0]);
						}
					}
					$text = implode(" / ", $textList);
				}
				$content = "発注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注先：\n" . $connectionData['company_name'] . "\n\n"
				         . "形式：\n" . $orderFormTypeList[$data['order_form_type']] . "\n\n"
				         . "内容：\n" . $text . "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-order/confirm?id=' . $id;
			}
			
			try {
				$orderFormTable->getAdapter()->beginTransaction();
				
				$orderFormTable->updateById($id, array(
					'status'           => Shared_Model_Code::ORDER_FORM_STATUS_MOD_REQUEST,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
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
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/mod-request transaction failed: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/approve                                 |
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
			$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
			$approvalTable   = new Shared_Model_Data_Approval();
			$userTable       = new Shared_Model_Data_User();

			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        	
			$data = $orderFormTable->getById($this->_adminProperty['management_group_id'], $id);
			
	        // 通貨
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$orderFormTypeList = Shared_Model_Code::codes('order_form_type');
			
			if ($data['order_form_type'] === (string)Shared_Model_Code::ORDER_FORM_TYPE_UPLOAD) {
				$content = "発注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注先：\n" . $connectionData['company_name'] . "\n\n"
				         . "形式：\n" . $orderFormTypeList[$data['order_form_type']] . "\n\n"
				         . "内容：\n" . $data['title']. "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-order/confirm?id=' . $id;
			} else {
				$text = '';
				$itemList = $data['item_list'];
				if (!empty($itemList)) {
					$textList = array();
					foreach ($itemList as $eachItem) {
						$exploded = explode("\n", $eachItem['item_name']);
						if (!empty($exploded[0])) {
							$textList[] = str_replace("\n", '', $exploded[0]);
						}
					}
					$text = implode(" / ", $textList);
				}
				$content = "発注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注先：\n" . $connectionData['company_name'] . "\n\n"
				         . "形式：\n" . $orderFormTypeList[$data['order_form_type']] . "\n\n"
				         . "内容：\n" . $text . "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-order/confirm?id=' . $id;
			}
			
			try {
				$orderFormTable->getAdapter()->beginTransaction();
				
				$orderFormTable->updateById($id, array(
					'status'           => Shared_Model_Code::ORDER_FORM_STATUS_APPROVED,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
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
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/approve transaction failed: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/upload                                  |
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
    |  action_URL    * /transaction-order/online-fix                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入とpayableの紐付け確認                            |
    +----------------------------------------------------------------------------*/
    public function onlineFixAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
        $selectObj = $onlinePurchaseTable->select();
	    $selectObj->order('frs_online_purchase.id ASC');
	    $onlineList = $selectObj->query()->fetchAll();
	    
	    $payableTable         = new Shared_Model_Data_AccountPayable();
	    
	    foreach ($onlineList as $each) {
		    echo 'online_purchase_id:' . $each['id'] . ' payable_id' . $each['payable_id'] . '<br>';
		    //$payableTable->updateById($each['payable_id'], array('online_purchase_id' => $each['id']));
	    }
	    exit;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-list                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理                                         |
    +----------------------------------------------------------------------------*/
    public function onlineListAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$session = new Zend_Session_Namespace('transaction_order_online_list');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']              = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
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
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			$session->conditions['keyword']             = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
		
		$dbAdapter = $onlinePurchaseTable->getAdapter();

        $selectObj = $onlinePurchaseTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_online_purchase.target_connection_id = frs_connection.id', array($onlinePurchaseTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_online_purchase.created_user_id = frs_user.id',array($onlinePurchaseTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
        $selectObj->where('frs_online_purchase.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if (!empty($session->conditions['status'])) {
	        if ($session->conditions['status'] === (string)Shared_Model_Code::ONLINE_PURCHASE_STATUS_NOT_APPROVED) {
	        	$selectObj->where('frs_online_purchase.status != ' . Shared_Model_Code::ONLINE_PURCHASE_STATUS_APPROVED . ' AND frs_online_purchase.status != ' . Shared_Model_Code::ONLINE_PURCHASE_STATUS_DELETED . ' AND frs_online_purchase.status != ' . Shared_Model_Code::ONLINE_PURCHASE_STATUS_CANCEL);
		    } else {
        		$selectObj->where('frs_online_purchase.status = ?', $session->conditions['status']);
        	}
        } else {
        	$selectObj->where('frs_online_purchase.status != ?', Shared_Model_Code::ONLINE_PURCHASE_STATUS_DELETED);
        }

        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('frs_online_purchase.target_connection_id = ?', $conditions['connection_id']);
        }

		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_online_purchase.created_user_id = ?', $session->conditions['applicant_user_id']);
		}
		
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        
		$selectObj->order(new Zend_Db_Expr('frs_online_purchase.purchased_date IS NULL DESC'));
		$selectObj->order('frs_online_purchase.purchased_date DESC');
		$selectObj->order('frs_online_purchase.id DESC');
		
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
    |  action_URL    * /transaction-order/online-delete                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  *  破棄(Ajax)                                                |
    +----------------------------------------------------------------------------*/
    public function onlineDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		// POST送信時
		if ($request->isPost()) {
			$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();

			try {
				$onlinePurchaseTable->getAdapter()->beginTransaction();
				
				$onlinePurchaseTable->updateById($id, array(
					'status' => Shared_Model_Code::ORDER_FORM_STATUS_DELETED,
				));
			
                // commit
                $onlinePurchaseTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $onlinePurchaseTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/delete transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-list-select                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理(ポップアップ用)                         |
    +----------------------------------------------------------------------------*/
    public function onlineListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request       = $this->getRequest();
		$page          = $request->getParam('page', '1');
		$coonnectionId = $request->getParam('connection_id');
		
		// 検索条件
		$conditions = array();
		$conditions['status']                   = $request->getParam('status', '');
		$conditions['connection_id']            = $request->getParam('connection_id', '');
		$conditions['applicant_user_id']        = $request->getParam('applicant_user_id', '');
		$this->view->conditions                 = $conditions;
		
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $coonnectionId);
		
		$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
		
		$dbAdapter = $onlinePurchaseTable->getAdapter();

        $selectObj = $onlinePurchaseTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_online_purchase.target_connection_id = frs_connection.id', array($onlinePurchaseTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_online_purchase.created_user_id = frs_user.id',array($onlinePurchaseTable->aesdecrypt('user_name', false) . 'AS user_name'));

        if (!empty($conditions['status'])) {
        	$selectObj->where('frs_online_purchase.status = ?', $conditions['status']);
        } else {
        	$selectObj->where('frs_online_purchase.status != ?', Shared_Model_Code::ONLINE_PURCHASE_STATUS_DELETED);
        }

        if (!empty($conditions['connection_id'])) {
        	$selectObj->where('frs_online_purchase.target_connection_id = ?', $conditions['connection_id']);
        }

		if ($conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_online_purchase.created_user_id = ?', $conditions['applicant_user_id']);
		}
		
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        
		$selectObj->order(new Zend_Db_Expr('frs_online_purchase.purchased_date IS NULL DESC'));
		$selectObj->order('frs_online_purchase.purchased_date DESC');
		$selectObj->order('frs_online_purchase.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator, 'javascript:pageOnline($page);');
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-add                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 新規登録                              |
    +----------------------------------------------------------------------------*/
    public function onlineAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
		$request = $this->getRequest();
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-add-post                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 新規登録(Ajax)                        |
    +----------------------------------------------------------------------------*/
    public function onlineAddPostAction()
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
                	$this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['order_plan_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「注文予定日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['total_with_tax']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注合計金額(税込)」を選択してください'));
                    return;
                } else if (!empty($errorMessage['item_list']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「注文内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['paying_method']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を入力してください'));
                    return; 
                }
                
                if (!empty($errorMessage['currency_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                    return; 
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payingBankId = 0;
				$payingCardId = 0;
				$payingPlanDate = NULL;
				
	            if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK) {
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	                $payingCardId = $success['paying_card_id'];
	                
	                // 支払日自動計算
	                $cardTable = new Shared_Model_Data_AccountCreditCard();	
					$cardData  = $cardTable->getById($payingCardId);
					
					$zPurchaedDate = new Zend_Date($success['order_plan_date'], NULL, 'ja_JP');
					$purchasedYear   = $zPurchaedDate->get(Zend_Date::YEAR);
					$purchasedMonth  = $zPurchaedDate->get(Zend_Date::MONTH);
					$purchasedDay    = $zPurchaedDate->get(Zend_Date::DAY);
					
					$zClosingDate = NULL;
					$zPaymentDate = NULL;
					if ($cardData['closing_day'] === '99') {
						$nDate = Nutex_Date::getDefaultInstance();
						$monthEndDay = $nDate->getMonthEndDay($purchasedYear, $purchasedMonth);
						$zClosingDate = new Zend_Date($purchasedYear . '-' . $purchasedMonth . '-' . $monthEndDay, NULL, 'ja_JP');
					} else {
						$zClosingDate = new Zend_Date($purchasedYear . '-' . $purchasedMonth . '-' . $cardData['closing_day'], NULL, 'ja_JP');
					}
					$zPaymentDate = new Zend_Date($purchasedYear . '-' . $purchasedMonth . '-' . $cardData['payment_day'], NULL, 'ja_JP');
					
					if ($zPurchaedDate->isEarlier($zClosingDate) || $zPurchaedDate->equals($zClosingDate)) {
						$zPaymentDate->add('1', Zend_Date::MONTH);
					} else {
						$zPaymentDate->add('2', Zend_Date::MONTH);
					}
					
					$payingPlanDate = $zPaymentDate->get('yyyy-MM-dd');
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            }
				            
				$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
				
				$nextDirectOrderId = $onlinePurchaseTable->getNextDisplayId();
	            
	            $itemList = array();
	            
				$orderItemList = explode(',', $success['item_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($orderItemList)) {
		            foreach ($orderItemList as $eachId) {
		            	$itemName   = $request->getParam($eachId . '_item_name');
		            	$includingCount   = $request->getParam($eachId . '_including_count');
		            	$itemId     = $request->getParam($eachId . '_item_id');
		            	$unitPrice  = $request->getParam($eachId . '_unit_price');
		            	$amount     = $request->getParam($eachId . '_amount');
		            	$amountUnit = $request->getParam($eachId . '_amount_unit');
		            	$price      = $request->getParam($eachId . '_price');
		            	$referenceType      = $request->getParam($eachId . '_reference_type');
		            	$referenceTargetId  = $request->getParam($eachId . '_reference_target_id');


		            	if (empty($includingCount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': セット数を入力してください'));
                    		return;
                    	} else if (!is_numeric($includingCount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': セット数は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($unitPrice)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 単価を入力してください'));
                    		return;
                    	} else if (!is_numeric($unitPrice)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 単価は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 数量を入力してください'));
                    		return;
                    	} else if (!is_numeric($amount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 数量は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amountUnit)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 数量単位を入力してください'));
                    		return;
		            	} else if (empty($price)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 小計を入力してください'));
                    		return;
                    	} else if (!is_numeric($price)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 小計は半角数字のみで入力してください'));
                    		return;
                    		
		            	}/*else if (empty($referenceTargetId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 調達管理を引用してください'));
                    		return;
		            	}*/
		            
		                $itemList[] = array(
							'id'                    => $count,
							'item_name'             => $itemName,
							'including_count'       => $includingCount,
							'item_id'               => $itemId,
							'unit_price'            => $unitPrice,
							'amount'                => $amount,
							'amount_unit'           => $amountUnit,
							'price'                 => $price,
							'reference_type'        => $referenceType,
							'reference_target_id'   => $referenceTargetId,
		                );
		            	$count++;
		            }
	            }
	            
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
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'display_id'              => $nextDirectOrderId,
					'status'                  => Shared_Model_Code::ONLINE_PURCHASE_STATUS_DRAFT,
					
					'target_connection_id'    => $success['target_connection_id'], // 注文先取引先
					
					'order_plan_date'         => $success['order_plan_date'],      // 注文予定日
					'purchased_date'          => $success['order_plan_date'],      // 注文日
					
					'subtotal'                => $success['subtotal'],             // 受注金額(税抜)
					'tax'                     => $success['tax'],                  // 税額
					'total_with_tax'          => $success['total_with_tax'],       // 受注合計金額(税込)
					'currency_id'             => $success['currency_id'],          // 通貨ID
					
					'memo'                    => $success['memo'],                 // 備考

					'item_list'               => json_encode($itemList),           // 注文内容
					'file_list'               => json_encode($fileList),           // 添付ファイルリスト
					
					'created_user_id'         => $this->_adminProperty['id'],      // 作成者ユーザーID
					'last_update_user_id'     => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					'approval_user_id'        => 0,
					
					'payable_id'              => 0,                                // 支払申請ID

					'paying_method'           => $success['paying_method'],        // 支払方法
					'paying_method_memo'      => $success['paying_method_memo'],   // 支払方法メモ
					
					'paying_bank_id'          => $payingBankId,          // 支払元銀行口座
					'paying_card_id'          => $payingCardId,          // 支払元クレジットカード
		
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);
				
				if (!empty($payingPlanDate)) {
					$data['paying_plan_date'] = $payingPlanDate;
				}
        	
				$onlinePurchaseTable->getAdapter()->beginTransaction();
            	  
	            try {
					$onlinePurchaseTable->create($data);
					$id = $onlinePurchaseTable->getLastInsertedId('id');

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);

			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

			            	if (!empty($tempFileName)) {
			            		// 正式保存
			            		Shared_Model_Resource_OnlinePurchase::makeResource($id, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								
							}
						}
					}
					
	                // commit
	                $onlinePurchaseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $onlinePurchaseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/online-add-post transaction failed: ' . $e); 
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-detail                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 詳細                                  |
    +----------------------------------------------------------------------------*/
    public function onlineDetailAction()
    {
		$request = $this->getRequest();	
    	$this->view->id = $id = $request->getParam('id');
		$this->view->posTop  = $request->getParam('pos');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->direct      = $direct     = $request->getParam('direct', 0);

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
		$connectionTable      = new Shared_Model_Data_Connection();
		$userTable            = new Shared_Model_Data_User();

		// 注文データ
		$this->view->data = $data = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);

		if (!empty($approvalId)) {
	        $this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->backUrl          = '/approval/list';
	        $this->view->saveUrl          = 'javascript:void(0);';
	        $this->view->saveButtonName   = '保存';
	        $this->view->showRejectButton = false;
	            
		} else {
			if (!empty($direct)) {
				$this->_helper->layout->setLayout('back_menu');
				$this->view->backUrl = '';
			} else {
				$this->view->backUrl = '/transaction-order/online-list';

				if ((int)$data['status'] < Shared_Model_Code::ONLINE_PURCHASE_STATUS_APPROVED) {
					$this->_helper->layout->setLayout('back_menu_competition');
					
					if ($data['status'] === (string)Shared_Model_Code::ONLINE_PURCHASE_STATUS_DRAFT || $data['status'] === (string)Shared_Model_Code::ONLINE_PURCHASE_STATUS_MOD_REQUEST) {
						$this->view->saveUrl = 'javascript:void(0);';
					}
					
				} else {
					$this->_helper->layout->setLayout('back_menu');
			        //$this->view->saveUrl = 'javascript:void(0);';
			        //$this->view->saveButtonName = '請求書作成';
				}
			}
		}
		
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		if (!empty($data['account_title_id'])) {
			$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		}
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 銀行口座
		if (!empty($data['paying_bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
		}
		
		// クレジットカード
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-update-basic                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 基本情報更新(Ajax)                    |
    +----------------------------------------------------------------------------*/
    public function onlineUpdateBasicAction()
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
                	$this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['order_plan_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「注文予定日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['total_with_tax']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注合計金額(税込)」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
	            
	            $onlinePurchaseTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_connection_id'    => $success['target_connection_id'], // 注文先取引先
						'order_plan_date'         => $success['order_plan_date'],      // 注文予定日
						'purchased_date'          => $success['order_plan_date'],      // 注文日
						
						'subtotal'                => $success['subtotal'],             // 受注金額(税抜)
						'tax'                     => $success['tax'],                  // 税額
						'total_with_tax'          => $success['total_with_tax'],       // 受注合計金額(税込)
						'currency_id'             => $success['currency_id'],          // 通貨ID
						
						'memo'                    => $success['memo'],                 // 備考
					);

					if (!empty($success['purchased_date'])) {
						$data['purchased_date'] = $success['purchased_date']; // 購入日	
					}

					if (!empty($success['is_delivery_plan_date_unknown'])) {
						$data['is_delivery_plan_date_unknown'] = 1;
						$data['delivery_plan_date'] = date('Y-m-d', strtotime('+7 day'));
					} else {
						if (empty($success['delivery_plan_date'])) {
							$this->sendJson(array('result' => 'NG', 'message' => '「納品予定日」を入力してください'));
							return;
						}
						
						$data['is_delivery_plan_date_unknown'] = 0;
						$data['delivery_plan_date'] = $success['delivery_plan_date'];   // 納品予定日
					}
				
					$onlinePurchaseTable->updateById($id, $data);

	                // commit
	                $onlinePurchaseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $onlinePurchaseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/online-update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-update-items                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 注文内容更新(Ajax)                    |
    +----------------------------------------------------------------------------*/
    public function onlineUpdateItemsAction()
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
				
                if (!empty($errorMessage['item_list']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「注文内容」を入力してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
	            $itemList = array();
	            
				$orderItemList = explode(',', $success['item_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($orderItemList)) {
		            foreach ($orderItemList as $eachId) {
		            	$itemName   = $request->getParam($eachId . '_item_name');
		            	$includingCount   = $request->getParam($eachId . '_including_count');
		            	$itemId     = $request->getParam($eachId . '_item_id');
		            	$unitPrice  = $request->getParam($eachId . '_unit_price');
		            	$amount     = $request->getParam($eachId . '_amount');
		            	$amountUnit = $request->getParam($eachId . '_amount_unit');
		            	$price      = $request->getParam($eachId . '_price');
		            	$referenceType      = $request->getParam($eachId . '_reference_type');
		            	$referenceTargetId  = $request->getParam($eachId . '_reference_target_id');

		            	if (empty($includingCount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': セット数を入力してください'));
                    		return;
                    	} else if (!is_numeric($includingCount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': セット数は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($unitPrice)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 単価を入力してください'));
                    		return;
                    	} else if (!is_numeric($unitPrice)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 単価は半角数字のみで入力してください'));
                    		return;
	
		            	} else if (empty($amount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 数量を入力してください'));
                    		return;
                    	} else if (!is_numeric($amount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 数量は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amountUnit)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 数量単位を入力してください'));
                    		return;
		            	} else if (empty($price)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 小計を入力してください'));
                    		return;
                    	} else if (!is_numeric($price)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 小計は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($referenceTargetId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '注文内容' . $count . ': 調達管理を引用してください'));
                    		return;
		            	}
		            
		                $itemList[] = array(
							'id'                    => $count,
							
							'item_name'             => $itemName,
							'including_count'       => $includingCount,
							
							'item_id'               => $itemId,
							'unit_price'            => $unitPrice,
							'amount'                => $amount,
							'amount_unit'           => $amountUnit,
							'price'                 => $price,
							'reference_type'        => $referenceType,
							'reference_target_id'   => $referenceTargetId,
		                );
		            	$count++;
		            }
	            }
	            
				$data = array(
					'item_list' => json_encode($itemList),
				);

				$onlinePurchaseTable->getAdapter()->beginTransaction();
            	  
	            try {
					$onlinePurchaseTable->updateById($id, $data);
					
	                // commit
	                $onlinePurchaseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $onlinePurchaseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-recieved/update-items transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-update-paying                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 支払方法更新(Ajax)                    |
    +----------------------------------------------------------------------------*/
    public function onlineUpdatePayingAction()
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
                    
                } else if (!empty($errorMessage['memo_for_payable']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「摘要」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['paying_plan_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['paying_method']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                    return; 
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				if (empty($success['account_totaling_group_id'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                }
				
				
				$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
				$oldData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
				
				$payingBankId = 0;
				$payingCardId = 0;
	            if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK) {
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	            	$payingCardId = $success['paying_card_id'];
					
	                
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	                
	            }
	            
	            $onlinePurchaseTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'account_title_id'          => $success['account_title_id'],          // 会計科目ID
						'account_totaling_group_id' => $success['account_totaling_group_id'], // 採算コードID
						'memo_for_payable'          => $success['memo_for_payable'],          // 摘要
						
						'paying_plan_date'          => $success['paying_plan_date'],          // 支払予定日
						
						'paying_method'             => $success['paying_method'],             // 支払方法
						
						'paying_bank_id'            => $payingBankId,                         // 支払元銀行口座
						'paying_card_id'            => $payingCardId,                         // 支払元クレジットカード
						
						'paying_method_memo'        => $success['paying_method_memo'],        // 支払方法メモ
					);

					if (!empty($success['purchased_date'])) {
						$data['purchased_date'] = $success['purchased_date']; // 購入日	
					}
					
					$onlinePurchaseTable->updateById($id, $data);

	                // commit
	                $onlinePurchaseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $onlinePurchaseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/online-update-paying transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-update-file-list                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 - 添付資料 更新(Ajax)                   |
    +----------------------------------------------------------------------------*/
    public function onlineUpdateFileListAction()
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
				$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
				
				$oldData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $onlinePurchaseTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');

						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_OnlinePurchase::makeResource($id, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
			            	// tempファイルを削除
							Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
		                }
		                
		                $fileList[] = array(
							'id'               => $eachId,
							'target_date'      => $request->getParam($eachId . '_target_date'),
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
	            try {
					$data = array(
						'file_list'              => json_encode($fileList),           // 入手見積書
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$onlinePurchaseTable->updateById($id, $data);
					
	                // commit
	                $onlinePurchaseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $onlinePurchaseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/online-update-file-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-apply-apploval                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 承認申請                                |
    +----------------------------------------------------------------------------*/
    public function onlineApplyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
			$approvalTable        = new Shared_Model_Data_Approval();
			$userTable            = new Shared_Model_Data_User();
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$data = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);

            if (empty($data['target_connection_id'])) {
            	$this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                return;
            } else if (empty($data['order_plan_date'])) {
            	$this->sendJson(array('result' => 'NG', 'message' => '「注文予定日」を入力してください'));
                return;
            } else if (empty($data['total_with_tax'])) {
            	$this->sendJson(array('result' => 'NG', 'message' => '「受注合計金額(税込)」を選択してください'));
                return;   
			} else if (empty($data['currency_id'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                return;
            }

			if (empty($data['account_title_id'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return;

			} else if (empty($data['memo_for_payable'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「摘要」を入力してください'));
                return;
                
			} else if (empty($data['account_totaling_group_id'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                return;
                
			} else if (empty($data['paying_plan_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を選択してください'));
                return;
                        
			} else if (empty($data['paying_method'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                return;
                
            } else if ($data['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK) {
            	// 銀行振込
            	if (empty($data['paying_bank_id'])) {
				    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
		    		return;
            	}
            } else if ($data['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
            	// クレジットカード
            	if (empty($data['paying_card_id'])) {
				    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
		    		return;
            	}
            } else if ($data['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
            	// 自動振替
            	if (empty($data['paying_bank_id'])) {
				    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
		    		return;
            	}
            }

			if (empty($data['delivery_plan_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「納品予定日」を入力してください'));
                return;
            }

	        // 通貨
			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			$text = '';
			$itemList = $data['item_list'];
			if (!empty($itemList)) {
				$textList = array();
				foreach ($itemList as $eachItem) {
					$exploded = explode("\n", $eachItem['item_name']);
					if (!empty($exploded[0])) {
						$textList[] = str_replace("\n", '', $exploded[0]);
					}
				}
				$text = implode(" / ", $textList);
			}
				
			try {
				$onlinePurchaseTable->getAdapter()->beginTransaction();
				
				$onlinePurchaseTable->updateById($id, array(
					'status' => Shared_Model_Code::ONLINE_PURCHASE_STATUS_PENDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_ONLINE_PURCHASE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $text . ' / 合計金額：' . $data['total_with_tax'] . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);
				
				// メール送信 -------------------------------------------------------
				$content = "注文内容：\n" . $text . "\n\n" 
				         . "注文金額：\n" . $data['total_with_tax'] . ' ' . $currencyData['name'];
				
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
                $onlinePurchaseTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $onlinePurchaseTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/online-apply-apploval transaction failed: ' . $e);
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-mod-request                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 修正依頼(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function onlineModRequestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$memoPrivate     = $request->getParam('memo_private');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
			$approvalTable        = new Shared_Model_Data_Approval();
			$userTable            = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			$data = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();

	        // 通貨
			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			$text = '';
			$itemList = $data['item_list'];
			if (!empty($itemList)) {
				$textList = array();
				foreach ($itemList as $eachItem) {
					$exploded = explode("\n", $eachItem['item_name']);
					if (!empty($exploded[0])) {
						$textList[] = str_replace("\n", '', $exploded[0]);
					}
				}
				$text = implode(" / ", $textList);
			}
			
			try {
				$onlinePurchaseTable->getAdapter()->beginTransaction();
				
				$onlinePurchaseTable->updateById($id, array(
					'status'           => Shared_Model_Code::ONLINE_PURCHASE_STATUS_MOD_REQUEST,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
				$content = "注文内容：\n" . $text . "\n\n" 
				         . "注文金額：\n" . $data['total_with_tax'] . ' ' . $currencyData['name'];
	        
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
                $onlinePurchaseTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $onlinePurchaseTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/online-mod-request transaction failed: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-approve                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 承認(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function onlineApproveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$memoPrivate     = $request->getParam('memo_private');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
			$approvalTable        = new Shared_Model_Data_Approval();
			$payableTable         = new Shared_Model_Data_AccountPayable();
			$userTable            = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			$data = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
			$oldData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();

	        // 通貨
			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			$text = '';
			$itemList = $data['item_list'];
			if (!empty($itemList)) {
				$textList = array();
				foreach ($itemList as $eachItem) {
					$exploded = explode("\n", $eachItem['item_name']);
					if (!empty($exploded[0])) {
						$textList[] = str_replace("\n", '', $exploded[0]);
					}
				}
				$text = implode(" / ", $textList);
			}
			
			
			try {
				$onlinePurchaseTable->getAdapter()->beginTransaction();

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				$payableData = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'status'                  => Shared_Model_Code::PAYABLE_STATUS_APPROVED, // 承認済み
			        
			        'order_form_ids'          => serialize(array()),                 // 発注IDリスト
			        'online_purchase_id'      => $id,
			        
			        
					'account_title_id'          => $data['account_title_id'],          // 会計科目ID
					'account_totaling_group_id' => $data['account_totaling_group_id'], // 採算コード
					'memo'                      => $data['memo_for_payable'],          // 摘要
					'target_connection_id'      => $data['target_connection_id'],      // 支払先

					'file_list'               => json_encode(array()),               // 請求書ファイルアップロード
					
					'purchased_date'          => $data['purchased_date'],            // クレジット利用日
					
					'paying_plan_date'        => $data['paying_plan_date'],          // 支払予定日
					'total_amount'            => $data['total_with_tax'],            // 支払額
					'currency_id'             => $data['currency_id'],               // 通貨単位
					'tax_division'            => Shared_Model_Code::TAX_DIVISION_TAXATION, // 税区分
					'tax'                     => $data['tax'],                       // 消費税
		
					'paid_user_id'            => 0,                                     // 支払処理担当者
					'paid_date'               => NULL,                                  // 支払完了日
					
					'paying_method'           => $data['paying_method'],             // 支払方法
					'paying_bank_id'          => $data['paying_bank_id'],            // 支払元銀行口座
					'paying_card_id'          => $data['paying_card_id'],            // 支払元クレジットカード
					'paying_method_memo'      => $data['paying_method_memo'],        // 支払方法メモ

					'created_user_id'         => $data['created_user_id'],           // 支払申請者
					'approval_user_id'        => $this->_adminProperty['id'],        // 承認者
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);
				
				// 支払種別(請求支払/カード支払)
				if ($data['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
					$payableData['paying_type'] = Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD;
				} else {
					$payableData['paying_type'] = Shared_Model_Code::PAYABLE_PAYING_TYPE_INVOICE;
				}
				
				$payableTable->create($payableData);
				$payableId = $payableTable->getLastInsertedId('id');
				
				$onlinePurchaseTable->updateById($id, array(
					'status'           => Shared_Model_Code::ONLINE_PURCHASE_STATUS_APPROVED,
					'memo_private'     => $request->getParam('memo_private'),
					'approval_comment' => $request->getParam('approval_comment'),
					'approval_user_id' => $this->_adminProperty['id'],
					'payable_id'       => $payableId,
				));
				
				// メール送信 -------------------------------------------------------
				$content = "注文内容：\n" . $text . "\n\n" 
				         . "注文金額：\n" . $data['total_with_tax'] . ' ' . $currencyData['name'];
	        
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
                $onlinePurchaseTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $onlinePurchaseTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order/online-approve transaction failed: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-deliveried                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 納品完了入力                                               |
    +----------------------------------------------------------------------------*/
    public function onlineDeliveriedAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl          = 'javascript:void(0);';
        $this->view->saveUrl          = 'javascript:void(0);';
        $this->view->saveButtonName   = '登録';
        
		$request = $this->getRequest();	
    	$this->view->id = $id = $request->getParam('id');
		
		$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();
		$connectionTable = new Shared_Model_Data_Connection();
		
		// 発注データ
		$this->view->data = $data = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $id);
		
	    // 発注元
	    $this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order/online-deliveried-post                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 納品完了入力(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function onlineDeliveriedPostAction()
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

                if (!empty($errorMessage['deliveried_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「納品日」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$onlinePurchaseTable  = new Shared_Model_Data_OnlinePurchase();

				$data = array(
					'deliveried_status'  => Shared_Model_Code::ORDER_FORM_DELIVERIED_STATUS_RECIEVED,
					'deliveried_date'    => $success['deliveried_date'], // 納品日
				);

				$onlinePurchaseTable->getAdapter()->beginTransaction();
            	  
	            try {
					$onlinePurchaseTable->updateById($id, $data);
					
	                // commit
	                $onlinePurchaseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $onlinePurchaseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order/online-deliveried-post transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
}

