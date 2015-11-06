<?php
/**
 * class TransactionSampleController
 * サンプル出荷
 */
 
class TransactionSampleController extends Front_Model_Controller
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
		$this->view->menu             = 'sample';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}

    }

	    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/list                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷/在庫破棄                                      |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('connection_sample_2');

		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']              = '';
			$session->conditions['shipment_status']     = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
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
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
    	$sampleTable = new Shared_Model_Data_DirectOrderSample();

		$dbAdapter = $sampleTable->getAdapter();

        $selectObj = $sampleTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_direct_order_sample.target_connection_id = frs_connection.id', array($sampleTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_direct_order_sample.created_user_id = frs_user.id',array($sampleTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		/*
        $selectObj->where('frs_direct_order_sample.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_direct_order_sample.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('frs_direct_order_sample.target_connection_id = ?', $conditions['connection_id']);
        }
        
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        */
        
        $selectObj->order('frs_direct_order_sample.id DESC');

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
    |  action_URL    * /transaction-sample/list-select                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品・資材選択                                             |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
	    $this->_helper->layout->setLayout('blank');
	    
		$request = $this->getRequest();
		
		$conditions = array();
		$conditions['type']     = $request->getParam('type', (string)Shared_Model_Code::ITEM_TYPE_PRODUCT);
		$conditions['status']   = $request->getParam('status', '');
		$conditions['keyword']  = $request->getParam('keyword');
		$this->view->conditions = $conditions;
		
		$this->view->page = $page = $request->getParam('page', '1');
		$warehouseId = $request->getParam('warehouse_id');
		
		if (empty($warehouseId)) {
			throw new Zend_Exception('/transaction-sample/list-select type is empty');
		}

		$itemTable = new Shared_Model_Data_WarehouseItem();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
		$selectObj->where('frs_warehouse_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_warehouse_item.warehouse_id = ?', $warehouseId);

        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        
        
        if (!empty($conditions['type'])) {
	        $selectObj->where('frs_warehouse_item.stock_type = ?', $conditions['type']);
        }
        
        if ($conditions['status'] !== '') {
	        $selectObj->where('frs_warehouse_item.status = ?', $conditions['status']);
        }
        
		$selectObj->order('frs_warehouse_item.id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		// 棚卸数量単位
		$unitTypeTable = new Shared_Model_Data_StockUnitType();
		$unitTypeList = array();
		$unitTypeItems = $unitTypeTable->getList();
		foreach ($unitTypeItems as $each) {
			$unitTypeList[$each['id']] = $each;
		}
		
		$this->view->unitTypeList = $unitTypeList;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/add                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 新規サンプル出荷/在庫破棄登録                              |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
		$request = $this->getRequest();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/add-post                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 新規サンプル出荷/在庫破棄登録(Ajax)                        |
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

                if (!empty($errorMessage['type']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (empty($success['warehouse_id'])) {
            		$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
                	return; 
            	} else if (!empty($errorMessage['item_list']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「対象商品資材」を入力してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {          	
            	if ($success['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $success['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
	            	if (empty($success['target_connection_id'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「依頼元取引先」を選択してください'));
						return;
					}
					
					if (empty($success['base_id'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「納入先拠点」を選択してください'));
						return;
					}
					
					$success['reason'] = '';
					
            	} else {
	            	if (empty($success['reason'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「破棄理由」を入力してください'));
						return;
					}
					
					$success['target_connection_id'] = 0;
            	}
	            
				$sampleTable              = new Shared_Model_Data_DirectOrderSample();
				$directOrderShipmentTable = new Shared_Model_Data_DirectOrderShipment();
				
				$nextSampleId = $sampleTable->getNextDisplayId();
	            
	            $itemList = array();
	            
				$orderItemList = explode(',', $success['item_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($orderItemList)) {
		            foreach ($orderItemList as $eachId) {
		            	$itemName       = $request->getParam($eachId . '_item_name');
		            	$itemId         = $request->getParam($eachId . '_item_id');
		            	$productName    = $request->getParam($eachId . '_product_name');
		            	$amount         = $request->getParam($eachId . '_amount');
		            	
		            	if (empty($itemId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 商品・資材を引用してください'));
                    		return;
		            	} else if (empty($amount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量を入力してください'));
                    		return;
                    	} else if (!is_numeric($amount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量は半角数字のみで入力してください'));
                    		return;
		            	}  		            
		                $itemList[] = array(
							'id'                        => $count,
							'item_name'                 => $itemName,
							'product_name'              => $productName,
							'item_id'                   => $itemId,
							'amount'                    => $amount,
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
			        'display_id'              => $nextSampleId,
			        'type'                    => $success['type'],
					'status'                  => Shared_Model_Code::DIRECT_ORDER_STATUS_DRAFT,
					'target_connection_id'    => $success['target_connection_id'],              // 発注元取引先
					'reason'                  => $success['reason'],                            // 破棄理由
					'memo'                    => $success['memo'],                              // 備考
					'warehouse_id'            => $success['warehouse_id'],                      // 出荷元倉庫ID

					'items'                   => json_encode($itemList),           // 対象商品資材
					'file_list'               => json_encode($fileList),           // 添付ファイルリスト
					
					
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
				
				
				// 納品予定日
				if ($success['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $success['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
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
					
					if (!empty($success['shipment_request_date'])) {
						$data['shipment_request_date'] = $success['shipment_request_date'];   // 出荷希望日
					}
				}
					
				$sampleTable->getAdapter()->beginTransaction();
            	  
	            try {
					$sampleTable->create($data);
					$sampleOrderId = $sampleTable->getLastInsertedId('id');

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);

			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

			            	if (!empty($tempFileName)) {
			            		// 正式保存
			            		Shared_Model_Resource_DirectOrderSample::makeResource($directOrderId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								
							}
						}
					}
					
	                // commit
	                $sampleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $sampleTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-sample/add-post transaction faied: ' . $e); 
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $sampleOrderId));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
	
	    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/detail                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷/在庫破棄 - 詳細                               |
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
		
		$sampleTable              = new Shared_Model_Data_DirectOrderSample();
		$connectionTable          = new Shared_Model_Data_Connection();
		$connectionBaseTable      = new Shared_Model_Data_ConnectionBase();
		$warehouseTable           = new Shared_Model_Data_Warehouse();
		$userTable                = new Shared_Model_Data_User();

		// 受注データ
		$this->view->data = $data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);
		
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
				$this->view->backUrl = '/transaction-sample/list';
				
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
						$this->view->cancelButtonName = 'キャンセル';
					}
				}
			}
		}
		
		// 依頼元取引先
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 納入先拠点
		if (!empty($data['base_id'])) {
			$this->view->baseData = $connectionBaseTable->getById($this->_adminProperty['management_group_id'], $data['base_id']);
		}
		
		// 出荷元倉庫
		if (!empty($data['warehouse_id'])) {
			$this->view->warehouseData = $warehouseTable->getById($this->_adminProperty['management_group_id'], $data['warehouse_id']);
		}

		// 棚卸数量単位
		$unitTypeTable = new Shared_Model_Data_StockUnitType();
		$unitTypeList = array();
		$unitTypeItems = $unitTypeTable->getList();
		foreach ($unitTypeItems as $each) {
			$unitTypeList[$each['id']] = $each;
		}
		$this->view->unitTypeList = $unitTypeList;
    }
    
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/shipment                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function shipmentAction()
    {
		$request = $this->getRequest();	
    	$this->view->id          = $id = $request->getParam('id');
		$this->view->posTop      = $request->getParam('pos');

		$this->_helper->layout->setLayout('back_menu');
		
		$this->view->backUrl = '/transaction-sample/list';
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$sampleTable              = new Shared_Model_Data_DirectOrderSample();
		$connectionTable          = new Shared_Model_Data_Connection();
		$connectionBaseTable      = new Shared_Model_Data_ConnectionBase();
		$warehouseTable           = new Shared_Model_Data_Warehouse();
		$userTable                = new Shared_Model_Data_User();

		// 受注データ
		$this->view->data = $data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if ($data['shipment_status'] !== (string)Shared_Model_Code::SHIPMENT_WHOLESALE_STATUS_SHIPPED) {
			$this->view->saveUrl         = 'javascript:void(0);';
			
			if ($data['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $data['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
				$this->view->saveButtonName  = '出荷完了';
			} else {
				$this->view->saveButtonName  = '破棄実施';
			}
		}
		
		// 依頼元取引先
    	$this->view->connectionData  = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 納入先拠点
		if (!empty($data['base_id'])) {
			$this->view->baseData = $connectionBaseTable->getById($this->_adminProperty['management_group_id'], $data['base_id']);
		}
		
		// 出荷元倉庫
		if (!empty($data['warehouse_id'])) {
			$this->view->warehouseData = $warehouseTable->getById($this->_adminProperty['management_group_id'], $data['warehouse_id']);
		}

		// 棚卸数量単位
		$unitTypeTable = new Shared_Model_Data_StockUnitType();
		$unitTypeList = array();
		$unitTypeItems = $unitTypeTable->getList();
		foreach ($unitTypeItems as $each) {
			$unitTypeList[$each['id']] = $each;
		}
		$this->view->unitTypeList = $unitTypeList;
		   
	
	}
	    
	/*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/shipped                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function shippedAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
        $id = $request->getParam('id');
		
		$sampleTable       = new Shared_Model_Data_DirectOrderSample();
		$itemTable         = new Shared_Model_Data_WarehouseItem();
		$itemStockTable    = new Shared_Model_Data_ItemStock();
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		
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
				$data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);
				
				if ($data['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $data['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
					if (empty($success['delivery_agent'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「配送業者」を選択してください'));
						return;
					} else if (empty($success['delivery_code'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「出荷伝票番号」を入力してください'));
						return;
					}
				}
				
				$sampleTable->getAdapter()->beginTransaction();
            	  
	            try {
					$sampleTable->updateById($id, array(
						'delivery_agent'   => $success['delivery_agent'],
						'delivery_code'    => $success['delivery_code'],
						'shipped_memo'     => $success['shipped_memo'],
						'shipment_status'  => Shared_Model_Code::DIRECT_ORDER_STATUS_SHIPPED,
						'deliveried_date'  => date('Y-m-d H;i:s'), // 発注元取引先
					));
				
				
					if (!empty($data['items'])) {
						foreach ($data['items'] as $each) {
							
							$itemData = $itemTable->getById($data['management_group_id'], $data['warehouse_id'], $each['item_id']);

							$amount = $each['amount'];
							
							while ($amount > 0) {
								$consumeCount = $amount;
				
								// 理論在庫を減らす
								$stockData = $itemStockTable->findFirstStock($each['item_id']);
								
								if (empty($stockData)) {
									throw new Zend_Exception('/transaction-sample/shipped transaction failed');   
								}
								
								if ((float)$consumeCount > (float)$stockData['last_count']) {
									$consumeCount = (float)$stockData['last_count'];
								}
								
								$itemStockTable->consumeStock($stockData['id'], $consumeCount);
								
								$itemTable->subStock('1', '1', $each['item_id'], $consumeCount);
								
								$consumptionTable->create(array(
							        'item_id'           => 0, // (廃止)
							        'warehouse_item_id' => $each['item_id'],     // 倉庫管理アイテムID
							        
							        'user_id'           => $this->_adminProperty['id'],
									'status'            => Shared_Model_Code::STOCK_STATUS_ACTIVE,
									
									'action_date'       => new Zend_Db_Expr('now()'),
									'action_code'       => $data['type'],
									
									'sub_count'         => $consumeCount,
									'target_stock_id'   => $stockData['id'],// 対象の在庫
									
									'order_id'          => 0,
									'memo'              => $data['display_id'],
					
					                'created'           => new Zend_Db_Expr('now()'),
					                'updated'           => new Zend_Db_Expr('now()'),
								));
								
								$amount = $amount - $consumeCount;
							}

							$itemTable->updateById($data['management_group_id'], $each['item_id'], array(
								'stock_count'     => $itemData['stock_count']   - $each['amount'],             // 在庫数
								'useable_count'   => $itemData['useable_count'] - $each['amount'],             // 引当可能在庫数
							));
								
						}
				    }
				    
			    	// commit
	                $sampleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $sampleTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-sample/update-basic transaction failed: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return; 
			    
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/update-basic                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷/在庫破棄 基本情報 更新(Ajax)                  |
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

                if (empty($success['warehouse_id'])) {
            		$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
                	return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$sampleTable = new Shared_Model_Data_DirectOrderSample();
				$oldData = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);
				
            	if ($oldData['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $oldData['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
	            	if (empty($success['target_connection_id'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「依頼元取引先」を選択してください'));
						return;
					}
					
					$success['reason'] = '';
					
            	} else {
	            	if (empty($success['reason'])) {
                		$this->sendJson(array('result' => 'NG', 'message' => '「破棄理由」を入力してください'));
						return;
					}
					
					$success['target_connection_id'] = 0;
            	}
				
				$sampleTable = new Shared_Model_Data_DirectOrderSample();
				
				$data = array(
					'target_connection_id'    => $success['target_connection_id'],              // 発注元取引先
					'reason'                  => $success['reason'],                            // 破棄理由
					'memo'                    => $success['memo'],                              // 備考
					'warehouse_id'            => $success['warehouse_id'],                      // 出荷元倉庫ID
					'memo'                    => $success['memo'],                              // 備考
				);
				
				// 納品予定日
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
				
					
				$sampleTable->getAdapter()->beginTransaction();
            	  
	            try {
					$sampleTable->updateById($id, $data);
					
	                // commit
	                $sampleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $sampleTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-sample/update-basic transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/update-items                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷/在庫破棄 対象商品資材 更新(Ajax)              |
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
				$sampleTable              = new Shared_Model_Data_DirectOrderSample();
	            $itemList = array();
	            
				$orderItemList = explode(',', $success['item_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($orderItemList)) {
		            foreach ($orderItemList as $eachId) {
		            	$itemName       = $request->getParam($eachId . '_item_name');
		            	$itemId         = $request->getParam($eachId . '_item_id');
		            	$productName    = $request->getParam($eachId . '_product_name');
		            	$amount         = $request->getParam($eachId . '_amount');
		            	
		            	if (empty($itemId)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 商品・資材を引用してください'));
                    		return;
		            	} else if (empty($amount)) {
		            		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量を入力してください'));
                    		return;
                    	} else if (!is_numeric($amount)) {
                    		$this->sendJson(array('result' => 'NG', 'message' => '受注内容' . $count . ': 数量は半角数字のみで入力してください'));
                    		return;
		            	}  		            
		                $itemList[] = array(
							'id'                        => $count,
							'item_name'                 => $itemName,
							'item_id'                   => $itemId,
							'product_name'              => $productName,
							'amount'                    => $amount,
		                );
		            	$count++;
		            }
	            }
	            
				$data = array(
					'items' => json_encode($itemList),
				);

				$sampleTable->getAdapter()->beginTransaction();
            	
	            try {
					$sampleTable->updateById($id, $data);
					
	                // commit
	                $sampleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $sampleTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-sample/update-items transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/update-shipment                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷/在庫破棄 出荷指示情報 更新(Ajax)              |
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
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$sampleTable = new Shared_Model_Data_DirectOrderSample();

				$oldData = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);
	            
            	if ($oldData['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $oldData['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
					if (empty($success['base_id'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「納入先拠点」を選択してください'));
						return;
					}
            	}
            	
				$data = array(
					'base_id'                 => $success['base_id'],                     // 納入先拠点
					'delivery_method'         => $success['delivery_method'],             // 配送方法指示
					'shipment_request_date'   => NULL,
					'shipment_memo'           => $success['shipment_memo'],               // 伝達事項
				);
				
				if (!empty($success['shipment_request_date'])) {
					$data['shipment_request_date'] = $success['shipment_request_date'];   // 出荷希望日
				}

				$sampleTable->getAdapter()->beginTransaction();
            	  
	            try {
					$sampleTable->updateById($id, $data);
					
	                // commit
	                $sampleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $sampleTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-sample/update-shipment transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/update-file-list                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷/在庫破棄 - 添付資料 更新(Ajax)                |
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
				$sampleTable = new Shared_Model_Data_DirectOrderSample();
				
				$oldData = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $sampleTable->getAdapter()->beginTransaction();
 
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

					$sampleTable->updateById($id, $data);
					
	                // commit
	                $sampleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $sampleTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-sample/update-file-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/apply-apploval                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷 承認申請                                      |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

            
		// POST送信時
		if ($request->isPost()) {
			$sampleTable        = new Shared_Model_Data_DirectOrderSample();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);


            if (empty($data['type'])) {
            	$this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                return;
            } else if (empty($data['warehouse_id'])) {
        		$this->sendJson(array('result' => 'NG', 'message' => '「出荷元倉庫」を選択してください'));
            	return; 
        	} else if (empty($data['items'])) {
            	$this->sendJson(array('result' => 'NG', 'message' => '「対象商品資材」を入力してください'));
                return; 
            }

        	if ($data['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE || $data['type'] === (string)Shared_Model_Code::STOCK_ACTION_SHIPMENT_USE) {
            	if (empty($data['target_connection_id'])) {
            		$this->sendJson(array('result' => 'NG', 'message' => '「依頼元取引先」を選択してください'));
					return;
				}

				if (empty($data['base_id'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「納入先拠点」を選択してください'));
					return;
				}
				
        	} else {
            	if (empty($data['reason'])) {
            		$this->sendJson(array('result' => 'NG', 'message' => '「破棄理由」を入力してください'));
					return;
				}
        	}

			// 取引先
			if (!empty($data['target_connection_id'])) {
	    		$connectionTable = new Shared_Model_Data_Connection();
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
			}
			
			$transactionSampleTypeList = Shared_Model_Code::codes('transaction_sample_type');
			
			$text = '';
			$itemList = $data['items'];
			$itemTable = new Shared_Model_Data_WarehouseItem();
			if (!empty($itemList)) {
				$textList = array();
				foreach ($itemList as $eachItem) {
					$itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $data['warehouse_id'], $eachItem['item_id']);
					
					if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
						$name = $itemData['item_name'];
					} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
						$name = $itemData['supply_product_name'];
					} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
						$name = $itemData['supply_fixture_name'];
					} else {
						$name = $itemData['stock_name'];
					}
					
					$textList[] = $name;
				}
				$text = implode(" / ", $textList);
			}
			
			$title   = $transactionSampleTypeList[$data['type']] . "\n" . $text;
			
			$content = "管理ID：\n" . $data['display_id'] . "\n\n";
			$content.= "種別：\n" . $transactionSampleTypeList[$data['type']] . "\n\n";
			
			if (!empty($connectionData)) {
				$content.= "依頼元取引先：\n" . $connectionData['company_name'] . "\n\n";
			}
			
			$content.= "対象商品・資材：\n" . $text . "\n\n";
				
				         
			try {
				$sampleTable->getAdapter()->beginTransaction();
				
				$sampleTable->updateById($id, array(
					'status' => Shared_Model_Code::DIRECT_ORDER_STATUS_PENDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_SAMPLE_WASTE,
					
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
                $sampleTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $sampleTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-sample/apply-apploval transaction faied: ' . $e);
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/mod-request                            |
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
			$sampleTable        = new Shared_Model_Data_DirectOrderSample();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);

			// 取引先
			if (!empty($data['target_connection_id'])) {
		    	$connectionTable = new Shared_Model_Data_Connection();
		    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);			
			}

			$text = '';
			$itemList = $data['items'];
			$itemTable = new Shared_Model_Data_WarehouseItem();
			if (!empty($itemList)) {
				$textList = array();
				foreach ($itemList as $eachItem) {
					$itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $data['warehouse_id'], $eachItem['item_id']);
					
					if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
						$name = $itemData['item_name'];
					} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
						$name = $itemData['supply_product_name'];
					} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
						$name = $itemData['supply_fixture_name'];
					} else {
						$name = $itemData['stock_name'];
					}
					
					$textList[] = $name;
				}
				$text = implode(" / ", $textList);
			}
			
			try {
				$sampleTable->getAdapter()->beginTransaction();
				
				$sampleTable->updateById($id, array(
					'status' => Shared_Model_Code::DIRECT_ORDER_STATUS_MOD_REQUEST,
					'approval_comment' => $request->getParam('approval_comment'),
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
				
				$transactionSampleTypeList = Shared_Model_Code::codes('transaction_sample_type');

				// メール送信 -------------------------------------------------------
				$content = "管理ID：\n" . $data['display_id'] . "\n\n";
				$content.= "種別：\n" . $transactionSampleTypeList[$data['type']] . "\n\n";
				
				if (!empty($connectionData)) {
					$content.= "依頼元取引先：\n" . $connectionData['company_name'] . "\n\n";
				}
				
				$content.= "対象商品・資材：\n" . $text . "\n\n";
				$content.= "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-sample/detail?id=' . $id;
	        
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
                $sampleTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $sampleTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-sample/mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/approve                                |
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
			$sampleTable        = new Shared_Model_Data_DirectOrderSample();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);

			
			// 取引先
			if (!empty($data['target_connection_id'])) {
	    		$connectionTable = new Shared_Model_Data_Connection();
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
			}
			
			$text = '';
			$itemList = $data['items'];
			$itemTable = new Shared_Model_Data_WarehouseItem();
			if (!empty($itemList)) {
				$textList = array();
				foreach ($itemList as $eachItem) {
					$itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $data['warehouse_id'], $eachItem['item_id']);
					
					if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
						$name = $itemData['item_name'];
					} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
						$name = $itemData['supply_product_name'];
					} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
						$name = $itemData['supply_fixture_name'];
					} else {
						$name = $itemData['stock_name'];
					}
					
					$textList[] = $name;
				}
				$text = implode(" / ", $textList);
			}

			try {
				$sampleTable->getAdapter()->beginTransaction();

				$updateData = array(
					'status'           => Shared_Model_Code::DIRECT_ORDER_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				);
				
				$sampleTable->updateById($id, $updateData);

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));
				
				$transactionSampleTypeList = Shared_Model_Code::codes('transaction_sample_type');
				
				// メール送信 -------------------------------------------------------
				$content = "管理ID：\n" . $data['display_id'] . "\n\n";
				$content.= "種別：\n" . $transactionSampleTypeList[$data['type']] . "\n\n";
				
				if (!empty($connectionData)) {
					$content.= "依頼元取引先：\n" . $connectionData['company_name'] . "\n\n";
				}
				
				$content.= "対象商品・資材：\n" . $text . "\n\n";
				$content.= "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-sample/detail?id=' . $id;
				
	        
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
                $sampleTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $sampleTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-sample/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-sample/upload                                 |
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

