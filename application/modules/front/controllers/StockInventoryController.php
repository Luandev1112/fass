<?php
/**
 * class StockInventoryController
 */
 
class StockInventoryController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '出荷・在庫管理';
		$this->view->menuCategory     = 'shipment';
		$this->view->menu             = 'item';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/update-stock-count                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 実施履歴                                              |
    +----------------------------------------------------------------------------*/
    public function updateStockCountAction()
    {
	    $warehoouseItemId = '5';
	    $stockCount       = '302';
	    $itemTable          = new Shared_Model_Data_WarehouseItem();
	    
		$itemTable->updateById($this->_adminProperty['management_group_id'], $warehoouseItemId, array(
			'stock_count'     => $stockCount,             // 在庫数                 (frs_warehouse_itemに移行)
			'useable_count'   => $stockCount,             // 引当可能在庫数         (frs_warehouse_itemに移行)
		));
		
		echo 'OK';
		exit;
	}			
					
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/update-amount                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 実施履歴                                              |
    +----------------------------------------------------------------------------*/
    public function updateAmountAction()
    {
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		
		$inventoryItemTable->updateById('1547', array(
			'theory_stock' => '6.011',
		));
		
		exit;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/delete                                    |
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
			$inventoryTable     = new Shared_Model_Data_Inventory();

			try {
				$inventoryTable->getAdapter()->beginTransaction();
				
				$inventoryTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => Shared_Model_Code::INVENTORY_STATUS_DELETED,
				));
			
                // commit
                $inventoryTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $inventoryTable->getAdapter()->rollBack();
                throw new Zend_Exception('/stock-inventory/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
		
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/debug                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 実施履歴                                              |
    +----------------------------------------------------------------------------*/
    public function debugAction()
    {
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		$itemTable          = new Shared_Model_Data_WarehouseItem();
	    
		// 棚卸しリストを取得
		$selectObj = $itemTable->select();
		$selectObj->where('frs_warehouse_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_warehouse_item.warehouse_id = ?', $this->_warehouseSession->warehouseId);
		$selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
		$selectObj->order('frs_warehouse_item.stock_type ASC');
		$selectObj->order('frs_warehouse_item.id ASC');
		$targetItemList = $selectObj->query()->fetchAll();
		
		foreach ($targetItemList as $each) {
			echo $each['id'];
		}
		
		exit;
	}	
				
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/history                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 実施履歴                                              |
    +----------------------------------------------------------------------------*/
    public function historyAction()
    {
	    $request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
	    $inventoryTable = new Shared_Model_Data_Inventory();
		
		$dbAdapter = $inventoryTable->getAdapter();
		
        $selectObj = $inventoryTable->select();
		$selectObj->joinLeft('frs_user', 'frs_inventory.created_user_id = frs_user.id', array('display_id', $inventoryTable->aesdecrypt('user_name', false) . 'AS user_name'));

		$selectObj->where('frs_inventory.status != ?', Shared_Model_Code::INVENTORY_STATUS_DELETED);
		$selectObj->order('target_date DESC');
		$selectObj->order('id DESC');
		
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
    |  action_URL    * /stock-inventory/export-csv                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC-Cube用伝票番号出力                                      |
    +----------------------------------------------------------------------------*/
    public function exportCsvAction()
    {
	    $request = $this->getRequest();
	    $id = $request->getParam('id');
		        	
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		
		$data = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$items = $inventoryItemTable->getListByInventoryId($id);
		
		// 棚卸数量単位
		$unitTypeTable = new Shared_Model_Data_StockUnitType();
		$unitTypeList = array();
		$unitTypeItems = $unitTypeTable->getList();
		foreach ($unitTypeItems as $each) {
			$unitTypeList[$each['id']] = $each;
		}

		
		
    	$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '.csv');
    	$fp = fopen($path, 'w');

		$csvRow = array(
			'0'  => '在庫資材名',
			'1'  => '棚卸単価(円)',
			// 理論在庫
			'2'  => '棚卸在庫数',
			'3'  => '数量単位',
			'4'  => '資産額(円・小数以下切り捨て)',
			'5'  => '備考',
		);
	
		mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);
		
		$stockTypeList = Shared_Model_Code::codes('item_type');
		
		$count = 0;
		$allTotal = 0;	
		foreach ($items as $row) {

			if ($row['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
				$itemName = $row['item_name'];
			} else if ($row['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
				$itemName = $row['supply_product_name'];
			} else if ($row['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
				$itemName = $row['supply_fixture_name'];
			} else {
				$itemName = $row['stock_name'];
			}
			
			$total = floor((float)$row['unit_price'] * (float)$row['input_amount']);
			$allTotal += (float)$total;
			
			$csvRow = array(
				'0'  => $itemName,
				'1'  => $row['unit_price'],
				'2'  => $row['input_amount'],
				'3'  => $unitTypeList[$row['unit_type']]['symbol'],
				'4'  => $total,
				'5'  => $row['memo'],
			);
		
			mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
			fputcsv($fp, $csvRow);
			
			$count++;
		}


		$csvRow = array(
			'0'  => '',
			'1'  => '',
			'2'  => '',
			'3'  => '合計：',
			'4'  => $allTotal,
			'5'  => '',
		);
	
		mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);


		fclose($fp);
		
        $this->_helper->binaryOutput(file_get_contents($path), array(
            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
        ));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/create                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸し 新規登録                                            |
    +----------------------------------------------------------------------------*/
    public function createAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/create-post                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 新規登録                                              |
    +----------------------------------------------------------------------------*/
    public function createPostAction()
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
                
                if (!empty($errorMessage['target_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「実施日」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['inventory_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['stock_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「在庫資材種別」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$inventoryTable     = new Shared_Model_Data_Inventory();
				$inventoryItemTable = new Shared_Model_Data_InventoryItem();
				$itemTable          = new Shared_Model_Data_WarehouseItem();
				
				$stockTypeList = Shared_Model_Code::codes('item_type');
				
				// 実施中があるか
				if ($inventoryTable->hasGoingWithStockType($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $success['stock_type'])) {
			    	$this->sendJson(array('result' => 'NG', 'message' => '実施中または承認待ちの「' . $stockTypeList[$success['stock_type']] . '」棚卸があります'));
			    	return;
			    }
	    
				// 棚卸しリストを取得
				$selectObj = $itemTable->select();
				$selectObj->where('frs_warehouse_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
				$selectObj->where('frs_warehouse_item.warehouse_id = ?', $this->_warehouseSession->warehouseId);
				$selectObj->where('frs_warehouse_item.stock_type = ?', $success['stock_type']);
				$selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
				$selectObj->order('frs_warehouse_item.stock_type ASC');
				$selectObj->order('frs_warehouse_item.id ASC');
				$targetItemList = $selectObj->query()->fetchAll();
				
				$inventoryTable->getAdapter()->beginTransaction();

				// 新規登録
				$data = array(
			        'management_group_id'      => $this->_adminProperty['management_group_id'],  // 管理グループID
			        'warehouse_id'             => $this->_warehouseSession->warehouseId,         // 倉庫ID
			        
			        'status'                   => Shared_Model_Code::INVENTORY_STATUS_DRAFT,     // ステータス 入力中
			        
			        'target_date'              => $success['target_date'],                       // 実施日
					'stock_type'               => $success['stock_type'],                        // 在庫管理種別
					
					'inventory_type'           => $success['inventory_type'],                    // 
					
					'created_user_id'          => $this->_adminProperty['id'],                   // 担当者

	                'created'                  => new Zend_Db_Expr('now()'),
	                'updated'                  => new Zend_Db_Expr('now()'),
				);

				try {
					$inventoryTable->create($data);
					$inventoryId = $inventoryTable->getLastInsertedId('id');
					
					
					foreach ($targetItemList as $each) {
						// 対象資材の登録
						$inventoryItemTable->create(array(
					        'inventory_id'        => $inventoryId,          // 棚卸しID
					        'warehouse_item_id'   => $each['id'],           // 倉庫資材ID
					        
					        'unit_price'          => $each['unit_price'],   // 棚卸単価
					        
					        'theory_stock'        => $each['stock_count'],  // 理論在庫
							'input_amount'        => NULL,                  // 入力値
							
							'memo'                => '',
							
			                'created'             => new Zend_Db_Expr('now()'),
			                'updated'             => new Zend_Db_Expr('now()'),
						));	
					}
					
	                // commit
	                $inventoryTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/stock/add-post/:type transaction faied: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $inventoryId));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/form                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 編集フォーム                                          |
    +----------------------------------------------------------------------------*/
    public function formAction()
    {
	    $request = $this->getRequest();
	    $this->view->id = $id = $request->getParam('id');

    	$this->_helper->layout->setLayout('back_menu_invoice');
    	$this->view->backUrl = 'javascript:void(0);';
    	$this->view->saveUrl = 'javascript:void(0);';
    	$this->view->saveButtonName = '承認申請';
		        	
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		
		$this->view->data = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$items = $inventoryItemTable->getListByInventoryId($id);
		
		$total = 0;
		foreach ($items as &$eachItem) {
			if ((float)$eachItem['unit_price'] !== 0.0 && (float)$eachItem['input_amount'] !== 0.0) {
				$eachItem['total'] = floor((float)$eachItem['unit_price'] * (float)$eachItem['input_amount']);
				$total += (float)$eachItem['total'];
			} else {
				$eachItem['total'] = '';
			}
		}
		
		$this->view->items = $items;
		$this->view->total = $total;
		
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
    |  action_URL    * /stock-inventory/save                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸し 保存                                                |
    +----------------------------------------------------------------------------*/
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$warehouseItemTable = new Shared_Model_Data_WarehouseItem();
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		
		// POST送信時
		if ($request->isPost()) {
			$items = $inventoryItemTable->getListByInventoryId($id);
			
			try {
				$inventoryItemTable->getAdapter()->beginTransaction();
			
				foreach ($items as $each) {
					$itemName = '';
					if ($each['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
						$itemName = $each['item_name'];
					} else if ($each['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
						$itemName = $each['supply_product_name'];
					} else if ($each['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
						$itemName = $each['supply_fixture_name'];
					} else {
						$itemName = $each['stock_name'];
					}
					
					$unitPrice = str_replace(',', '', $request->getParam($each['id'] . '_unit_price'));
					
					if ($unitPrice === '') {
						if (!is_numeric($unitPrice)) {
							$this->sendJson(array('result' => 'NG', 'message' => $itemName . '：棚卸単価を入力してください'));
							return;	
						}
						
					} else if (!empty($unitPrice)) {
						if (!is_numeric($unitPrice)) {
							$this->sendJson(array('result' => 'NG', 'message' => $itemName . '：棚卸単価は数字のみで入力してください'));
							return;	
						}
					}
	
					$inputAmount = str_replace(',', '', $request->getParam($each['id'] . '_input_amount'));
					if (!empty($inputAmount)) {
						if (!is_numeric($inputAmount)) {
							$this->sendJson(array('result' => 'NG', 'message' => $itemName . '：棚卸在庫数は数字のみで入力してください'));
							return;	
						}
					}
			            	
					// 棚卸単価を更新
					$warehouseItemTable->updateById($this->_adminProperty['management_group_id'], $each['warehouse_item_id'], array(
						'unit_price'   => $unitPrice,
					));
					
					// 棚卸情報更新
					$inventoryItemTable->updateById($each['id'], array(
						'unit_price'   => $unitPrice,
						//'theory_stock' => str_replace(',', '', $request->getParam($each['id'] . '_theory_stock')),
						'input_amount' => $inputAmount,
						'memo'         => $request->getParam($each['id'] . '_memo'),
					));
				}
				
				$inventoryItemTable->getAdapter()->commit();
				
            } catch (Exception $e) {
                $inventoryItemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/stock-inventory/save transaction faied: ' . $e);
            }
            
			
			$this->sendJson(array('result' => 'OK'));
			return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));	
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/apply-apploval                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認申請                                                   |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$inventoryTable     = new Shared_Model_Data_Inventory();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$oldData = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);

			try {
				$inventoryTable->getAdapter()->beginTransaction();
				
				$inventoryTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => Shared_Model_Code::INVENTORY_STATUS_PENDING,
				));
				
				$stockTypeList = Shared_Model_Code::codes('item_type');
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_INVENTORY,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'], // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => '実施日：' . $oldData['target_date'] . "\n"
											 . '在庫管理資材種別：' . $stockTypeList[$oldData['stock_type']],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);
				

				// メール送信 -------------------------------------------------------
				$content = '実施日：' . $oldData['target_date'] . "\n\n"
						 . '在庫管理資材種別：' . $stockTypeList[$oldData['stock_type']];
				
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
                $inventoryTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $inventoryTable->getAdapter()->rollBack();
                throw new Zend_Exception('/stock-inventory/apply-apploval transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;

		}
		
		$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
	    $this->sendJson($result);
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/detail                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 棚卸 詳細                                                  |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
	    $request = $this->getRequest();
	    $this->view->approvalId = $approvalId = $request->getParam('approval_id');
	    $this->view->id = $id = $request->getParam('id');

		if (!empty($approvalId)) {
			$this->_helper->layout->setLayout('back_menu_approval');
			$this->view->backUrl        = '/approval/list';
	        $this->view->saveUrl        = 'javascript:void(0);';
	        $this->view->saveButtonName = '保存';
		}  else {
			$this->_helper->layout->setLayout('back_menu');
			$this->view->backUrl        = '/stock-inventory/history';
		}
		        	
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		
		$this->view->data = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$items = $inventoryItemTable->getListByInventoryId($id);
		
		$total = 0;
		foreach ($items as &$eachItem) {
			if ((float)$eachItem['unit_price'] !== 0.0 && (float)$eachItem['input_amount'] !== 0.0) {
				$eachItem['total'] = floor((float)$eachItem['unit_price'] * (float)$eachItem['input_amount']);
				$total += (float)$eachItem['total'];
			} else {
				$eachItem['total'] = '';
			}
		}
		
		$this->view->items = $items;
		$this->view->total = $total;

		
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
    |  action_URL    * /stock-inventory/mod-request                               |
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
			$inventoryTable     = new Shared_Model_Data_Inventory();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
	        $oldData = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);
		
			try {
				$inventoryTable->getAdapter()->beginTransaction();
				
				$inventoryTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => Shared_Model_Code::INVENTORY_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));
				
				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
				
				$stockTypeList = Shared_Model_Code::codes('item_type');
				
				// メール送信 -------------------------------------------------------
				$content = '実施日：' . $oldData['target_date'] . "\n\n"
						 . '在庫管理資材種別：' . $stockTypeList[$oldData['stock_type']] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/stock-inventory/detail?id=' . $id;
	        
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
                $inventoryTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $inventoryTable->getAdapter()->rollBack();
                throw new Zend_Exception('/stock-inventory/mod-request transaction faied: ' . $e);
                
            }
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock-inventory/approve                                   |
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
			$inventoryTable     = new Shared_Model_Data_Inventory();
			$approvalTable      = new Shared_Model_Data_Approval();
			$userTable          = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
	        $oldData = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);
			
			$inventoryTable->getAdapter()->query("LOCK TABLES frs_warehouse_item WRITE, frs_item_stock WRITE, frs_item_stock_consumption WRITE")->execute();
			$inventoryTable->getAdapter()->beginTransaction();
				
			try {
				$inventoryTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => Shared_Model_Code::INVENTORY_STATUS_APPROVED,
				));

				$approvalTable->updateById($approvalId, array(
					'status' => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));
				
				$stockTypeList = Shared_Model_Code::codes('item_type');
				
				
				$this->updateTheory($id);
				
				
				// メール送信 -------------------------------------------------------
				$content = '実施日：' . $oldData['target_date'] . "\n\n"
						 . '在庫管理資材種別：' . $stockTypeList[$oldData['stock_type']] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/stock-inventory/detail?id=' . $id;
				
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
                $inventoryTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $inventoryTable->getAdapter()->rollBack();
                throw new Zend_Exception('/stock-inventory/approve transaction faied: ' . $e);   
            }
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 


	/* 棚卸 在庫調整 直接呼び出し */
	public function updateTheoryAction()
    {
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryTable->getAdapter()->query("LOCK TABLES frs_warehouse_item WRITE, frs_item_stock WRITE, frs_item_stock_consumption WRITE")->execute();
		$inventoryTable->getAdapter()->beginTransaction();
			
		try {
			$this->updateTheory($id);
			
            // commit
            $inventoryTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $inventoryTable->getAdapter()->rollBack();
            throw new Zend_Exception('/stock-inventory/update-theory transaction faied: ' . $e);   
        }
		
		echo 'OK';
		exit;
	}
	
	/* 棚卸 在庫調整 */
	private function updateTheory($id)
    {
		$inventoryTable     = new Shared_Model_Data_Inventory();
		$inventoryItemTable = new Shared_Model_Data_InventoryItem();
		$itemTable          = new Shared_Model_Data_WarehouseItem();
		$itemStockTable     = new Shared_Model_Data_ItemStock();
		$consumptionTable   = new Shared_Model_Data_ItemStockConsumption();
		
		$data  = $inventoryTable->getById($this->_adminProperty['management_group_id'], $id);
		$items = $inventoryItemTable->getListByInventoryId($id);
		
		if (!empty($items)) {
			foreach ($items as $each) {
				$warehouseItem = $itemTable->getById($data['management_group_id'], $data['warehouse_id'], $each['warehouse_item_id']);
	
				$diff = (float)$each['input_amount'];
	
				if ($diff > 0) {			
					$warehouseManageId = $itemStockTable->getNextId();
					
					// 理論在庫を追加
					$itemStockTable->create(array(
				        'item_id'             => 0,
				        'warehouse_item_id'   => $each['warehouse_item_id'],     // 倉庫管理アイテムID
				        'user_id'             => 0,
						'status'              => Shared_Model_Code::STOCK_STATUS_ACTIVE,
						
						'warehouse_manage_id' => $warehouseManageId,
						'lot_count'           => 1,
						'action_date'         => $data['target_date'] . ' 23:59:59',            // アクション日
						'action_code'         => Shared_Model_Code::STOCK_ACTION_WAREHOUSE,
						
						'expiration_date'     => NULL,
						
						'amount'              => abs($diff),
						'sub_count'           => 0,
						'last_count'          => abs($diff),
						
						'warehouse_id'        => 1,
						
						'order_id'            => 0,
						'memo'                => '棚卸調整',
	
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					));
										
					$itemTable->updateById($data['management_group_id'], $each['warehouse_item_id'], array(
						'stock_count'     => $each['input_amount'],             // 在庫数                 (frs_warehouse_itemに移行)
						'useable_count'   => $each['input_amount'],             // 引当可能在庫数         (frs_warehouse_itemに移行)
					));

				} else if ($diff < 0) {
					$amount =  abs($diff);
					
					
					while ($amount > 0) {
						$consumeCount = $amount;
		
						// 理論在庫を減らす
						$stockData = $itemStockTable->findFirstStock($each['warehouse_item_id']);
		
						if (empty($stockData)) {
							throw new Zend_Exception('/stock-inventory/update-theory transaction faied warehouse_item_id:' . $each['warehouse_item_id'] . 'error: ' . $e);   
						}
						
						if ((float)$consumeCount > (float)$stockData['last_count']) {
							$consumeCount = (float)$stockData['last_count'];
						}
						
						$itemStockTable->consumeStock($stockData['id'], $consumeCount);
						$itemTable->subStockInventry('1', '1', $each['warehouse_item_id'], $consumeCount);                 // 要確認★★★★★★
						
						$consumptionTable->create(array(
					        'item_id'           => 0, // (廃止)
					        'warehouse_item_id' => $each['warehouse_item_id'],     // 倉庫管理アイテムID
					        
					        'user_id'           => $this->_adminProperty['id'],
							'status'            => Shared_Model_Code::STOCK_STATUS_ACTIVE,
							
							'action_date'       => $data['target_date'] . ' 23:59:59',
							'action_code'       => Shared_Model_Code::STOCK_ACTION_ADJUSTMENT,
							
							'sub_count'         => $consumeCount,
							'target_stock_id'   => $stockData['id'],// 対象の在庫
							
							'order_id'          => 0,
							'memo'              => '棚卸調整',
			
			                'created'           => new Zend_Db_Expr('now()'),
			                'updated'           => new Zend_Db_Expr('now()'),
						));
						
						$amount = $amount - $consumeCount;
					}
				
					$itemTable->updateById($data['management_group_id'], $each['warehouse_item_id'], array(
						'stock_count'     => $each['input_amount'],             // 在庫数                 (frs_warehouse_itemに移行)
						'useable_count'   => $each['input_amount'],             // 引当可能在庫数         (frs_warehouse_itemに移行)
					));
				
				}	
			}
		}
		
	}	
	
}

