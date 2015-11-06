<?php
/**
 * class TransactionOrderRecievedController
 * 受注管理
 */
 
class TransactionOrderRecievedController extends Front_Model_Controller
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
		$this->view->menu             = 'order-recieved';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/update-order-data              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注データ更新(開発用)                                     |
    +----------------------------------------------------------------------------*/
    public function updateOrderDataAction()
    {
	    $directOrderTable = new Shared_Model_Data_DirectOrder();
	    
	    $invoiceIds = array('60');

		$directOrderTable->updateById('50', array(
			'invoice_ids' => serialize($invoiceIds),
		));

	    echo 'OK';
	    exit;
	}
	    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/list                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理                                                   |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$this->view->column = $column = $request->getParam('column', 'recieved_date');
		$this->view->order  = $order = $request->getParam('order', 'desc');
		
		$session = new Zend_Session_Namespace('transaction_order_received_list_2');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']              = '';
			$session->conditions['shipment_status']     = '';
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
			$session->conditions['shipment_status']     = $request->getParam('shipment_status', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			$session->conditions['keyword']             = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
    	$directOrderTable = new Shared_Model_Data_DirectOrder();

		$dbAdapter = $directOrderTable->getAdapter();

        $selectObj = $directOrderTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_direct_order.target_connection_id = frs_connection.id', array($directOrderTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_direct_order.created_user_id = frs_user.id',array($directOrderTable->aesdecrypt('user_name', false) . 'AS user_name'));
        
        
        $selectObj->where('frs_direct_order.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        
        // ステータス
        if (!empty($session->conditions['status'])) {
	        if ($conditions['status'] === (string)Shared_Model_Code::DIRECT_ORDER_STATUS_NOT_COMPLETED) {
		        $selectObj->where('frs_direct_order.status != ' . Shared_Model_Code::DIRECT_ORDER_STATUS_INVOICE_COMPLETED
		        		   . ' AND frs_direct_order.status != ' . Shared_Model_Code::DIRECT_ORDER_STATUS_CANCELED
		                   . ' AND frs_direct_order.status != ' . Shared_Model_Code::DIRECT_ORDER_STATUS_DELETED);
	        } else {
        		$selectObj->where('frs_direct_order.status = ?', $session->conditions['status']);
        	}
        } else {
        	$selectObj->where('frs_direct_order.status != ?', Shared_Model_Code::DIRECT_ORDER_STATUS_DELETED);
        }
        
        // 出荷状況
        if (!empty($session->conditions['shipment_status'])) {
	        
	        
	        
	    }
	    
		
		/*
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_direct_order.currency_id = ?', $session->conditions['currency_id']);
		}
		*/
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_direct_order.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('frs_direct_order.target_connection_id = ?', $conditions['connection_id']);
        }
        
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        
        if ($column == 'id') {
            if ($order == 'asc') {
                $selectObj->order('frs_direct_order.id ASC');
            } else if ($order == 'desc'){
                $selectObj->order('frs_direct_order.id DESC');
            }
	        
        } else if ($column == 'recieved_date') {
	        if ($order == 'asc') {
				$selectObj->order('frs_direct_order.order_recieved_date ASC');
				$selectObj->order('frs_direct_order.id ASC');
			} else {
				$selectObj->order('frs_direct_order.order_recieved_date DESC');
				$selectObj->order('frs_direct_order.id DESC');
			}
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
    |  action_URL    * /transaction-order-recieved/list-select                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理(ポップアップ選択用)                               |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page          = $request->getParam('page', '1');
		$coonnectionId = $request->getParam('connection_id');
		
		$conditions = array();
		$conditions['condition_name'] = $request->getParam('condition_name', '');
		$this->view->conditions       = $conditions;
		
		
    	$directOrderTable = new Shared_Model_Data_DirectOrder();
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $coonnectionId);
		
		
		$dbAdapter = $directOrderTable->getAdapter();

        $selectObj = $directOrderTable->select();
        $selectObj->where('target_connection_id = ?', $coonnectionId);
        $selectObj->where('frs_direct_order.status != ?', Shared_Model_Code::DIRECT_ORDER_STATUS_DELETED);
		$selectObj->order('frs_direct_order.order_recieved_date DESC');
		$selectObj->order('frs_direct_order.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator, 'javascript:pageOrderRecieved($page);');	
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/add                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 新規受注登録                                               |
    +----------------------------------------------------------------------------*/
    public function addAction()
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
    |  action_URL    * /transaction-order-recieved/add-post                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 新規受注登録(Ajax)                                         |
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
                	$this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['order_recieved_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['shipment_timing']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「出荷タイミング」を選択してください'));
                    return;
                } else if (!empty($errorMessage['payment_method']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「入金条件」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['delivery_cost']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「送料負担」を選択してください'));
                    return;
                } else if (!empty($errorMessage['total_with_tax']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注合計金額(税込)」を選択してください'));
                    return;
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                    return;
                } else if (!empty($errorMessage['item_list']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注内容」を入力してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
	            // 発送なし以外
	            if ($success['shipment_timing'] !== (string)Shared_Model_Code::SHIPMENT_TIMING_NONE) {	            	
	            	if (empty($success['warehouse_id'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
                    	return; 
	            	}
	            }
	            
				$directOrderTable         = new Shared_Model_Data_DirectOrder();
				$directOrderShipmentTable = new Shared_Model_Data_DirectOrderShipment();
				
				$nextDirectOrderId = $directOrderTable->getNextDisplayId();
	            
	            $itemList = array();
	            
				$orderItemList = explode(',', $success['item_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($orderItemList)) {
		            foreach ($orderItemList as $eachId) {
		            	$itemName   = $request->getParam($eachId . '_item_name');
		            	$itemId     = $request->getParam($eachId . '_item_id');
		            	$unitPrice  = $request->getParam($eachId . '_unit_price');
		            	$amount     = $request->getParam($eachId . '_amount');
		            	$amountUnit = $request->getParam($eachId . '_amount_unit');
		            	$price      = $request->getParam($eachId . '_price');
		            	$itemTargetId      = $request->getParam($eachId . '_reference_item_target_id');
		            	$estimateTargetId  = $request->getParam($eachId . '_reference_estimate_target_id');
		            	$estimateTargetRow = $request->getParam($eachId . '_reference_estimate_target_row');
		            	
		            	if (empty($unitPrice)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 単価を入力してください'));
                    		return;
                    	} else if (!is_numeric($unitPrice)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 単価は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量を入力してください'));
                    		return;
                    	} else if (!is_numeric($amount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amountUnit)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量単位を入力してください'));
                    		return;
		            	} else if (empty($price)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 小計を入力してください'));
                    		return;
                    	} else if (!is_numeric($price)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 小計は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($itemTargetId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 商品を引用してください'));
                    		return;
		            	} else if (empty($estimateTargetId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 見積を入力してください'));
                    		return;
		            	}
		            
		                $itemList[] = array(
							'id'                    => $count,
							'item_name'             => $itemName,
							'item_id'               => $itemId,
							'unit_price'            => $unitPrice,
							'amount'                => $amount,
							'amount_unit'           => $amountUnit,
							'price'                 => $price,
							'reference_item_target_id'      => $itemTargetId,
							'reference_estimate_target_id'  => $estimateTargetId,
							'reference_estimate_target_row' => $estimateTargetRow,
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
	            
	            /*
	            // 出荷なし
	            $status = Shared_Model_Code::DIRECT_ORDER_STATUS_NO_SHIPPING;
	            
	            // 登録と同時に出荷指示
	            if ($success['shipment_timing'] == Shared_Model_Code::SHIPMENT_TIMING_SOON) {
	            	$status = Shared_Model_Code::DIRECT_ORDER_STATUS_SHIPMENT_DIRECTED;
	            
	            // 入金確認後
	            } else if ($success['shipment_timing'] == Shared_Model_Code::SHIPMENT_TIMING_AFTER_PAYMENT) {
	            	$status = Shared_Model_Code::DIRECT_ORDER_STATUS_WAIT_FOR_PAYMENT;
	            
	            // 保留
	            } else if ($success['shipment_timing'] == Shared_Model_Code::SHIPMENT_TIMING_PENDING) {
	            	$status = Shared_Model_Code::DIRECT_ORDER_STATUS_PENDING;
	            }
	            */
	           	 
				$data = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'display_id'              => $nextDirectOrderId,
					'status'                  => Shared_Model_Code::DIRECT_ORDER_STATUS_DRAFT,
					'target_connection_id'    => $success['target_connection_id'], // 発注元取引先
					'order_recieved_date'     => $success['order_recieved_date'],  // 受注日
					
					'payment_method'          => $success['payment_method'],       // 入金条件
					
					'shipment_timing'         => $success['shipment_timing'],      // 出荷タイミング
					'delivery_cost'           => $success['delivery_cost'],        // 送料負担
					
					'subtotal'                => $success['subtotal'],             // 受注金額(税抜)
					'tax'                     => $success['tax'],                  // 税額
					'total_with_tax'          => $success['total_with_tax'],       // 受注合計金額(税込)
					'currency_id'             => $success['currency_id'],          // 通貨ID
					
					'memo'                    => $success['memo'],                 // 備考

					'items'                   => json_encode($itemList),           // 受注内容
					'file_list'               => json_encode($fileList),           // 添付ファイルリスト
					
					'warehouse_id'            => $success['warehouse_id'],         // 出荷元倉庫ID
					'base_id'                 => $success['base_id'],              // 納入先拠点
					
					'shipment_request_date'   => NULL,
					'delivery_method'         => $success['delivery_method'],      // 配送方法指示
					'shipment_memo'           => $success['shipment_memo'],        // 伝達事項
					
					'created_user_id'         => $this->_adminProperty['id'],      // 作成者ユーザーID
					'last_update_user_id'     => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					'approval_user_id'        => 0,  
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);
				
				if (!empty($success['shipment_request_date'])) {
					$data['shipment_request_date'] = $success['shipment_request_date'];   // 出荷希望日
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
				
				
				// 入金条件その他
				if ($data['payment_method'] == '10') {
					$data['other_payment_condition'] = $success['other_payment_condition'];
					
					if ($data['other_payment_condition'] == (string)Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY
					|| $data['other_payment_condition'] == (string)Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_CLAIM) {
						$data['other_payment_condition_close'] = $success['other_payment_condition_close'];
						$data['other_payment_condition_month'] = $success['other_payment_condition_month'];
						$data['other_payment_condition_pay']   = $success['other_payment_condition_pay'];
					} else {
						$data['other_payment_condition_other'] = $success['other_payment_condition_other'];
					}
				}
				
					
				$directOrderTable->getAdapter()->beginTransaction();
            	  
	            try {
					$directOrderTable->create($data);
					$directOrderId = $directOrderTable->getLastInsertedId('id');

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);

			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

			            	if (!empty($tempFileName)) {
			            		// 正式保存
			            		Shared_Model_Resource_DirectOrder::makeResource($directOrderId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								
							}
						}
					}
					
	                // commit
	                $directOrderTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $directOrderTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-recieved/add-post transaction faied: ' . $e); 
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $directOrderId));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/complete-invoice               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書発行完了                                             |
    +----------------------------------------------------------------------------*/
    public function completeInvoiceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$orderTable  = new Shared_Model_Data_DirectOrder();
			
			$oldData =  $orderTable->getById($this->_adminProperty['management_group_id'], $id);
			
			if (empty($oldData)) {
				throw new Zend_Exception('/transaction-order-recieved/complete-invoice - no target order data');
			} else if ($oldData['status'] !== (string)Shared_Model_Code::DIRECT_ORDER_STATUS_APPROVED) {
				throw new Zend_Exception('/transaction-order-recieved/complete-invoice - invaild status');
				
			}
			
			try {
				$orderTable->getAdapter()->beginTransaction();

				$orderTable->updateById($id, array(
					'status' => Shared_Model_Code::DIRECT_ORDER_STATUS_INVOICE_COMPLETED,
				));
 
                 // commit
                $orderTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order-recieved/complete-invoice transaction faied: ' . $e); 
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/detail                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注詳細                                                   |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
		$request = $this->getRequest();	
    	$this->view->id          = $id = $request->getParam('id');
		$this->view->posTop      = $request->getParam('pos');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->direct      = $direct     = $request->getParam('direct', 0);

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$directOrderTable         = new Shared_Model_Data_DirectOrder();
		$directOrderShipmentTable = new Shared_Model_Data_DirectOrderShipment();
		$connectionTable          = new Shared_Model_Data_Connection();
		$connectionBaseTable      = new Shared_Model_Data_ConnectionBase();
		$warehouseTable           = new Shared_Model_Data_Warehouse();
		$userTable                = new Shared_Model_Data_User();

		// 受注データ
		$this->view->data = $data = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);
		
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
				$this->view->backUrl = '/transaction-order-recieved/list';
				
				if ((int)$data['status'] < Shared_Model_Code::DIRECT_ORDER_STATUS_APPROVED) {
					// 承認前
					$this->_helper->layout->setLayout('back_menu_competition');
					
					if ($this->view->allowEditing === true) {
						if ($data['status'] === (string)Shared_Model_Code::DIRECT_ORDER_STATUS_DRAFT || $data['status'] === (string)Shared_Model_Code::DIRECT_ORDER_STATUS_MOD_REQUEST) {
							$this->view->saveUrl = 'javascript:void(0);';
						}
					}
				} else {
					$this->_helper->layout->setLayout('back_menu');
					
					if ($this->view->allowEditing === true) {
						$this->view->cancelUrl        = 'javascript:void(0);';
						$this->view->cancelButtonName = '受注キャンセル';

				        $this->view->saveUrl = 'javascript:void(0);';
				        $this->view->saveButtonName = '請求書作成';
					}
					
					if ($data['shipment_timing'] != Shared_Model_Code::SHIPMENT_TIMING_NONE) {
						// 出荷指示情報(EC以外の直接取引) 
						$this->view->shipmentData = $shipmentData = $directOrderShipmentTable->getByDirectOrderId($this->_adminProperty['management_group_id'], $id);
					}
				}
			}
		}
		
		// 発注元
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 納入先拠点
		if (!empty($data['base_id'])) {
			$this->view->baseData = $connectionBaseTable->getById($this->_adminProperty['management_group_id'], $data['base_id']);
		}
		
		// 出荷元倉庫
		if (!empty($data['warehouse_id'])) {
			$this->view->warehouseData =  $warehouseTable->getById($this->_adminProperty['management_group_id'], $data['warehouse_id']);
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
    |  action_URL    * /transaction-order-recieved/cancel-order                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 受注キャンセル(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function cancelOrderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {  
            $directOrderTable         = new Shared_Model_Data_DirectOrder();
			
			$directOrderTable->updateById($id, array(
				'status' => Shared_Model_Code::DIRECT_ORDER_STATUS_CANCELED,
			));
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/update-basic                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 基本情報 更新(Ajax)                               |
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

                if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['order_recieved_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['shipment_timing']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「出荷タイミング」を選択してください'));
                    return;
                } else if (!empty($errorMessage['payment_method']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「入金条件」を選択してください'));
                    return;
                } else if (!empty($errorMessage['delivery_cost']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「送料負担」を選択してください'));
                    return;
                } else if (!empty($errorMessage['total_with_tax']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注合計金額(税込)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$directOrderTable         = new Shared_Model_Data_DirectOrder();

				$data = array(
					'target_connection_id'    => $success['target_connection_id'], // 発注元取引先
					'order_recieved_date'     => $success['order_recieved_date'],  // 受注日
					
					'payment_method'          => $success['payment_method'],       // 入金条件
					
					'shipment_timing'         => $success['shipment_timing'],      // 出荷タイミング
					'delivery_cost'           => $success['delivery_cost'],        // 送料負担
					
					'subtotal'                => $success['subtotal'],             // 受注金額(税抜)
					'tax'                     => $success['tax'],                  // 税額
					'total_with_tax'          => $success['total_with_tax'],       // 受注合計金額(税込)
					'currency_id'             => $success['currency_id'],          // 通貨ID
					
					'memo'                    => $success['memo'],                 // 備考
				);

				// 入金条件その他
				if ($data['payment_method'] == '10') {
					$data['other_payment_condition'] = $success['other_payment_condition'];
					
					if ($data['other_payment_condition'] == (string)Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY
					|| $data['other_payment_condition'] == (string)Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_CLAIM) {
						$data['other_payment_condition_close'] = $success['other_payment_condition_close'];
						$data['other_payment_condition_month'] = $success['other_payment_condition_month'];
						$data['other_payment_condition_pay']   = $success['other_payment_condition_pay'];
					} else {
						$data['other_payment_condition_other'] = $success['other_payment_condition_other'];
					}
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
				
				$directOrderTable->getAdapter()->beginTransaction();
            	  
	            try {
					$directOrderTable->updateById($id, $data);
					
	                // commit
	                $directOrderTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $directOrderTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-recieved/update-basic transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/update-items                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 受注内容 更新(Ajax)                               |
    +----------------------------------------------------------------------------*/
    public function updateItemsAction()
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
                	$this->sendJson(array('result' => 'NG', 'message' => '「受注内容」を入力してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$directOrderTable = new Shared_Model_Data_DirectOrder();
	            $itemList = array();
	            
				$orderItemList = explode(',', $success['item_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($orderItemList)) {
		            foreach ($orderItemList as $eachId) {
		            	$itemName   = $request->getParam($eachId . '_item_name');
		            	$itemId     = $request->getParam($eachId . '_item_id');
		            	$unitPrice  = $request->getParam($eachId . '_unit_price');
		            	$amount     = $request->getParam($eachId . '_amount');
		            	$amountUnit = $request->getParam($eachId . '_amount_unit');
		            	$price      = $request->getParam($eachId . '_price');
		            	$itemTargetId      = $request->getParam($eachId . '_reference_item_target_id');
		            	$estimateTargetId  = $request->getParam($eachId . '_reference_estimate_target_id');
		            	$estimateTargetRow = $request->getParam($eachId . '_reference_estimate_target_row');
		            	
		            	if (empty($unitPrice)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 単価を入力してください'));
                    		return;
                    	} else if (!is_numeric($unitPrice)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 単価は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量を入力してください'));
                    		return;
                    	} else if (!is_numeric($amount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($amountUnit)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量単位を入力してください'));
                    		return;
		            	} else if (empty($price)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 小計を入力してください'));
                    		return;
                    	} else if (!is_numeric($price)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 小計は半角数字のみで入力してください'));
                    		return;
                    		
		            	} else if (empty($itemTargetId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 商品を引用してください'));
                    		return;
		            	} else if (empty($estimateTargetId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 見積を入力してください'));
                    		return;
		            	}
		            
		                $itemList[] = array(
							'id'                    => $count,
							'item_name'             => $itemName,
							'item_id'               => $itemId,
							'unit_price'            => $unitPrice,
							'amount'                => $amount,
							'amount_unit'           => $amountUnit,
							'price'                 => $price,
							'reference_item_target_id'      => $itemTargetId,
							'reference_estimate_target_id'  => $estimateTargetId,
							'reference_estimate_target_row' => $estimateTargetRow,
		                );
		            	$count++;
		            }
	            }
	            
				$data = array(
					'items' => json_encode($itemList),
				);

				$directOrderTable->getAdapter()->beginTransaction();
            	  
	            try {
					$directOrderTable->updateById($id, $data);
					
	                // commit
	                $directOrderTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $directOrderTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-recieved/update-items transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/update-shipment                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 出荷指示情報 更新(Ajax)                           |
    +----------------------------------------------------------------------------*/
    public function updateShipmentAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
		
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
   
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['base_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「納入先拠点」を選択してください'));
                    return;
                } else if (!empty($errorMessage['warehouse_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
                    return;
                }
	            
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$directOrderTable = new Shared_Model_Data_DirectOrder();

				$oldData = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);
	            
	            // 発送なし以外
	            if ($oldData['shipment_timing'] !== (string)Shared_Model_Code::SHIPMENT_TIMING_NONE) {
	            	if (empty($success['base_id'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「納入先拠点」を選択してください'));
                    	return; 
	            	}
	            	
	            	if (empty($success['warehouse_id'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
                    	return; 
	            	}
	            }
	            
				$data = array(
					'warehouse_id'            => $success['warehouse_id'],            // 出荷元倉庫ID
					'base_id'                 => $success['base_id'],                 // 納入先拠点
					'delivery_method'         => $success['delivery_method'],         // 配送方法指示
					'shipment_request_date'   => NULL,
					'shipment_memo'           => $success['shipment_memo'],           // 伝達事項
				);
				
				if (!empty($success['shipment_request_date'])) {
					$data['shipment_request_date'] = $success['shipment_request_date'];   // 出荷希望日
				}

				$directOrderTable->getAdapter()->beginTransaction();
            	  
	            try {
					$directOrderTable->updateById($id, $data);
					
	                // commit
	                $directOrderTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $directOrderTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-shipment/update-shipment transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/update-file-list               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 - 添付資料 更新(Ajax)                             |
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
				$directOrderTable = new Shared_Model_Data_DirectOrder();
				
				$oldData = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $directOrderTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');

						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_DirectOrder::makeResource($id, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
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

					$directOrderTable->updateById($id, $data);
					
	                // commit
	                $directOrderTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $directOrderTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-recieved/update-file-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-recieved/apply-apploval                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注 承認申請                                              |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

            
		// POST送信時
		if ($request->isPost()) {
			$directOrderTable   = new Shared_Model_Data_DirectOrder();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$data = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);

			
			if (empty($data['delivery_plan_date'])) {
	            $this->sendJson(array('result' => 'NG', 'message' => '「納品予定日」を入力してください'));
	            return;
	        }


            // 発送なし以外
            if ($data['shipment_timing'] !== (string)Shared_Model_Code::SHIPMENT_TIMING_NONE) {
            	if (empty($data['base_id'])) {
            		$this->sendJson(array('result' => 'NG', 'message' => '「納入先拠点」を選択してください'));
                	return; 
            	}
            	
            	if (empty($data['warehouse_id'])) {
            		$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
                	return; 
            	}
            }
	            
	            
			/*
            if (empty($data['order_date'])) {
                $result['result'] = 'NG';
                $result['message'] = '「注文書発行日」を入力してください';
                $this->sendJson($result);
                return;
            } else if (empty($data['to_name'])) {
                $result['result'] = 'NG';
                $result['message'] = '「宛先」を入力してください';
                $this->sendJson($result);
                return; 
			}
			*/

	        // 通貨
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			// 取引先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$text = '';
			$itemList = $data['items'];
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
			$content = "受注管理ID：\n" . $data['display_id'] . "\n\n"
			         . "発注元取引先：\n" . $connectionData['company_name'] . "\n\n"
			         . "内容：\n" . $text . "\n\n"
			         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'];
				         
			try {
				$directOrderTable->getAdapter()->beginTransaction();
				
				$directOrderTable->updateById($id, array(
					'status' => Shared_Model_Code::DIRECT_ORDER_STATUS_PENDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_ORDER,
					
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
                $directOrderTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $directOrderTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-recieved/apply-apploval transaction faied: ' . $e);
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/mod-request                    |
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
			$directOrderTable   = new Shared_Model_Data_DirectOrder();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);

	        // 通貨
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			// 取引先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$text = '';
			$itemList = $data['items'];
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
				$directOrderTable->getAdapter()->beginTransaction();
				
				$directOrderTable->updateById($id, array(
					'status' => Shared_Model_Code::DIRECT_ORDER_STATUS_MOD_REQUEST,
					'approval_comment' => $request->getParam('approval_comment'),
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));


				// メール送信 -------------------------------------------------------
				$content = "受注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注元取引先：\n" . $connectionData['company_name'] . "\n\n"
				         . "内容：\n" . $text . "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-order-recieved/detail?id=' . $id;
	        
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
                $directOrderTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $directOrderTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order-recieved/mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/approve                        |
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
			$directOrderTable   = new Shared_Model_Data_DirectOrder();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);

	        // 通貨
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			// 取引先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$text = '';
			$itemList = $data['items'];
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
				$oldData = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);
				
				$directOrderTable->getAdapter()->beginTransaction();

				$updateData = array(
					'status'           => Shared_Model_Code::DIRECT_ORDER_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				);
				
				/*
				if ($oldData['shipment_timing'] === (string)Shared_Model_Code::SHIPMENT_TIMING_SOON) {
					// 出荷タイミング：承認と同時に出荷指示
					$data['shipment_status'] = Shared_Model_Code::DIRECT_ORDER_STATUS_SHIPMENT_DIRECTED;
					
				} else if ($oldData['shipment_timing'] === (string)Shared_Model_Code::SHIPMENT_TIMING_AFTER_PAYMENT) {
					// 出荷タイミング：入金確認後
					$data['shipment_status'] = Shared_Model_Code::DIRECT_ORDER_STATUS_WAIT_FOR_PAYMENT;
					
				} else if ($oldData['shipment_timing'] === (string)Shared_Model_Code::SHIPMENT_TIMING_PENDING) {
					// 出荷タイミング：保留
					$data['shipment_status'] = Shared_Model_Code::DIRECT_ORDER_STATUS_SHIPMENT_PENDING;
					
				} else if ($oldData['shipment_timing'] === (string)Shared_Model_Code::SHIPMENT_TIMING_NONE) {
					// 出荷タイミング：発送なし
					$data['shipment_status'] = Shared_Model_Code::DIRECT_ORDER_STATUS_NO_SHIPPING;
					
				}
				*/
				
				$directOrderTable->updateById($id, $updateData);

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
				$content = "受注管理ID：\n" . $data['display_id'] . "\n\n"
				         . "発注元取引先：\n" . $connectionData['company_name'] . "\n\n"
				         . "内容：\n" . $text . "\n\n"
				         . "合計金額：\n" . number_format($data['total_with_tax']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-order-recieved/detail?id=' . $id;
	        
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
                $directOrderTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $directOrderTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-order-recieved/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/deliveried                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 納品完了入力                                               |
    +----------------------------------------------------------------------------*/
    public function deliveriedAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl          = 'javascript:void(0);';
        $this->view->saveUrl          = 'javascript:void(0);';
        $this->view->saveButtonName   = '登録';
        $this->view->showRejectButton = false;
        
		$request = $this->getRequest();	
    	$this->view->id = $id = $request->getParam('id');
		
		$directOrderTable         = new Shared_Model_Data_DirectOrder();
		$connectionTable          = new Shared_Model_Data_Connection();
		
		// 受注データ
		$this->view->data = $data = $directOrderTable->getById($this->_adminProperty['management_group_id'], $id);
	    
	    // 発注元
	    $this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/deliveried-post                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 基本情報 更新(Ajax)                               |
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
				$directOrderTable         = new Shared_Model_Data_DirectOrder();

				$data = array(
					'shipment_status'    => Shared_Model_Code::DIRECT_ORDER_STATUS_SHIPPED,
					'deliveried_date'    => $success['deliveried_date'], // 発注元取引先
				);

				$directOrderTable->getAdapter()->beginTransaction();
            	  
	            try {
					$directOrderTable->updateById($id, $data);
					
	                // commit
	                $directOrderTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $directOrderTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-order-recieved/deliveried-post transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	  
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/add-shipment                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷指示登録(仮)                                           |
    +----------------------------------------------------------------------------*/
    public function addShipmentAction()
    {
		$directOrderShipmentTable = new Shared_Model_Data_DirectOrderShipment();
		
		
		$shipmentData = array(
	        'management_group_id'     => $this->_adminProperty['management_group_id'],
	        'direct_order_id'         => $directOrderId,                   // 受注ID
			'status'                  => Shared_Model_Code::SHIPMENT_STATUS_NEW, // 出荷ステータス 新規注文
			
			'target_connection_id'    => $success['target_connection_id'],    // 発注元取引先ID
			'base_id'                 => $success['base_id'],                 // 納入先拠点
			
			'inspection_datetime'     => NULL,                             // 検品日時
			'inspection_user_id'      => NULL,                             // 検品者ユーザーID
			
			'shipment_plan_date'      => $success['shipment_plan_date'],   // 出荷予定日
			'shipment_datetime'       => NULL,                             // 出荷日時
			
			'delivery_method'         => $success['delivery_method'],      // 配送方法
			
			'memo'                    => $success['memo'],                 // メモ
	
			'created_user_id'         => $this->_adminProperty['id'],      // 作成者ユーザーID
			'last_update_user_id'     => $this->_adminProperty['id'],      // 最終更新者ユーザーID

            'created'                 => new Zend_Db_Expr('now()'),
            'updated'                 => new Zend_Db_Expr('now()'),
		);
		
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-order-recieved/upload                         |
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
}

