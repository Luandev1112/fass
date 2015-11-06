<?php
/**
 * class StockController
 */
 
class StockController extends Front_Model_Controller
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
    |  action_URL    * /stock/update2                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材開発用                                             |
    +----------------------------------------------------------------------------*/
    /*
    public function update2Action()
    {
	   
		$itemId = '50';
		$warehouseItemId = '57';
		
		$itemTable          = new Shared_Model_Data_Item();
		
		$itemData = $itemTable->getById('1', $itemId);
		
		
		$warehouseItemTable = new Shared_Model_Data_WarehouseItem();
		$itemStockTable     = new Shared_Model_Data_ItemStock();
		$consumptionTable   = new Shared_Model_Data_ItemStockConsumption();

		$warehouseItemTable->updateById('1', $warehouseItemId, array(
			'stock_count'        => $itemData['stock_count'],            // 在庫数                 (frs_warehouse_itemに移行)
			'useable_count'      => $itemData['useable_count'],          // 引当可能在庫数         (frs_warehouse_itemに移行)
			'alert_count'        => $itemData['alert_count'],            // アラート在庫数         (frs_warehouse_itemに移行)
			'minimum_count'      => $itemData['minimum_count'],          // 最低在庫数             (frs_warehouse_itemに移行)
			'safety_count'       => $itemData['safety_count'],           // 安全在庫数             (frs_warehouse_itemに移行)
		));
		

		// ItemStock
		$selectObj = $itemStockTable->select();
		$selectObj->where('item_id = ?', $itemId);
		$stockList = $selectObj->query()->fetchAll();
		
		foreach ($stockList as $eachStock) {
			$itemStockTable->updateById($eachStock['id'], array('warehouse_item_id' => $warehouseItemId));
			echo 'stock：' . $eachStock['id'] . '<br>';
		}

		// ItemStockConsumption
		$selectObj = $consumptionTable->select();
		$selectObj->where('item_id = ?', $itemId);
		$consumptionList = $selectObj->query()->fetchAll();
		
		foreach ($consumptionList as $eachConsumption) {
			
			$consumptionTable->updateById($eachConsumption['id'], array('warehouse_item_id' => $warehouseItemId));
			echo 'stock consumption：' . $eachConsumption['id'] . '<br>';
		}
		exit;
	}
	*/
	    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/update                                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材開発用                                             |
    +----------------------------------------------------------------------------*/
    /*
    public function updateAction()
    {
		$request = $this->getRequest();
		
		$itemTable          = new Shared_Model_Data_Item();
		
		$warehouseItemTable = new Shared_Model_Data_WarehouseItem();
		$itemStockTable     = new Shared_Model_Data_ItemStock();
		$consumptionTable   = new Shared_Model_Data_ItemStockConsumption();
		
		
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
		$selectObj->where('item_type = ?', Shared_Model_Code::ITEM_TYPE_PACKAGE);
		$itemList = $selectObj->query()->fetchAll();

		//var_dump($itemList);
		
		foreach ($itemList as $eachItem) {
			
			echo $eachItem['id'] . '：' . $eachItem['item_name'] , '<br>';
			
	        $selectObj = $warehouseItemTable->select();
			$selectObj->where('target_item_id = ?', $eachItem['id']);
			$warehouseItem = $selectObj->query()->fetch();

			echo $eachItem['id'] . '：' . $eachItem['item_name'] . ' warehouse_id：' . $warehouseItem['id'] . '<br>';
			
			if (!empty($warehouseItem)) {
				
				$warehouseItemTable->updateById($eachItem['management_group_id'], $warehouseItem['id'], array(
					'stock_count'        => $eachItem['stock_count'],            // 在庫数                 (frs_warehouse_itemに移行)
					'useable_count'      => $eachItem['useable_count'],          // 引当可能在庫数         (frs_warehouse_itemに移行)
					'alert_count'        => $eachItem['alert_count'],            // アラート在庫数         (frs_warehouse_itemに移行)
					'minimum_count'      => $eachItem['minimum_count'],          // 最低在庫数             (frs_warehouse_itemに移行)
					'safety_count'       => $eachItem['safety_count'],           // 安全在庫数             (frs_warehouse_itemに移行)
				));
				

				// ItemStock
				$selectObj = $itemStockTable->select();
				$selectObj->where('item_id = ?', $eachItem['id']);
				$stockList = $selectObj->query()->fetchAll();
				
				foreach ($stockList as $eachStock) {
					$itemStockTable->updateById($eachStock['id'], array('warehouse_item_id' => $warehouseItem['id']));
					echo 'stock：' . $eachStock['id'] . '<br>';
				}
		
				// ItemStockConsumption
				$selectObj = $consumptionTable->select();
				$selectObj->where('item_id = ?', $eachItem['id']);
				$consumptionList = $selectObj->query()->fetchAll();
				
				foreach ($consumptionList as $eachConsumption) {
					
					$consumptionTable->updateById($eachConsumption['id'], array('warehouse_item_id' => $warehouseItem['id']));
					echo 'stock consumption：' . $eachConsumption['id'] . '<br>';
				}
			}
		}
		
		
		exit;
    }
	*/

    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/waste-list                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄管理                                                   |
    +----------------------------------------------------------------------------*/
    public function wasteListAction()
    {
		$request = $this->getRequest();

		$page    = $request->getParam('page', '1');

		
    	$sampleTable = new Shared_Model_Data_DirectOrderSample();

		$dbAdapter = $sampleTable->getAdapter();

        $selectObj = $sampleTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_direct_order_sample.target_connection_id = frs_connection.id', array($sampleTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_direct_order_sample.created_user_id = frs_user.id',array($sampleTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->where('frs_direct_order_sample.type IN (?)', array(Shared_Model_Code::STOCK_ACTION_DEFECTIVE, Shared_Model_Code::STOCK_ACTION_LOST));
		$selectObj->where('frs_direct_order_sample.warehouse_id = ?', $this->_warehouseSession->warehouseId);
		$selectObj->where('frs_direct_order_sample.status = ?', Shared_Model_Code::DIRECT_ORDER_STATUS_APPROVED);
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
    |  action_URL    * /stock/old-list                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材開発用                                             |
    +----------------------------------------------------------------------------*/
    public function oldListAction()
    {
		$request = $this->getRequest();
		
		$itemTable = new Shared_Model_Data_Item();
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item_base', 'frs_item.id = frs_item_base.item_id', array('shelf_no'));
		$this->view->itemList = $selectObj->query()->fetchAll();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/all-list                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材                                                   |
    +----------------------------------------------------------------------------*/
    public function allListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$itemTable = new Shared_Model_Data_WarehouseItem();

        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
		$selectObj->order('frs_warehouse_item.id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(1000);
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
    |  action_URL    * /stock/list                                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材                                                   |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->type = $type = $request->getParam('type');

		$page    = $request->getParam('page', '1');
		
		if (empty($type)) {
			throw new Zend_Exception('/stock/list/:type type is empty');
		}
		
		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		$this->view->typeCode = $typeCode;
		
		$itemTable = new Shared_Model_Data_WarehouseItem();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
		$selectObj->where('frs_warehouse_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_warehouse_item.warehouse_id = ?', $this->_warehouseSession->warehouseId);

        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', $typeCode);
        
        /*
        if (!empty($conditions['id'])) {
        	$selectObj->where('fbc_item.id = ?', $conditions['id']);
        }
        
        if (!empty($conditions['category_id'])) {
        	$selectObj->where('fbc_item.category_id = ?', $conditions['category_id']);
        }
        
        if (!empty($conditions['machine_id'])) {
        	$selectObj->where('fbc_item.machine_id = ?', $conditions['machine_id']);	
        }
        
        if (!empty($conditions['purpose'])) {
        	$selectObj->where('fbc_item.purpose = ?', $conditions['purpose']);	
        }

        if (!empty($conditions['status'])) {
        	$selectObj->where('fbc_item.status = ?', $conditions['status']);	
        }
        
        if (!empty($conditions['keyword'])) {
        	$keywordString = '';
        	
        	$columns = array(
        		'maker_name', 'maker_name_en', 'model_name', 'model_year', 'spec_main_jp', 'spec_main_en', 'spec_main_en', 'spec_jp', 'spec_en',
        		'owner_name', 'owner_name_in_charge', 'info_from', 'info_from_in_charge', 'storage_place', 'storage_state',
        		'production_number', 'season_stop_using', 'season_limit', 'buying_in_requirement', 'buying_in_price', 'buying_in_price',
        		'sale_requirement', 'sale_price', 'bland_new_price', 'memo',
        	);
        	
        	foreach ($columns as $each) {
        		if ($keywordString !== '') {
        			$keywordString .= ' OR ';
        		}

        		if ($itemTable->isCryptField($each)) {   
        			$keyword = $dbAdapter->quote('%' . $conditions['keyword'] . '%');     			
        			$keywordString .= $itemTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordString .= $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where($keywordString);
        }
           
        if (!empty($conditions['user_id_in_charge'])) {
        	$selectObj->where('fbc_item.user_id_in_charge = ?', $conditions['user_id_in_charge']);	
        }
        */
        
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
    |  action_URL    * /stock/list-analytics                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 分析                                                       |
    +----------------------------------------------------------------------------*/
    public function listAnalyticsAction()
    {
		$request = $this->getRequest();
		$this->view->type = $type = $request->getParam('type');

		$page    = $request->getParam('page', '1');
		
		if (empty($type)) {
			throw new Zend_Exception('/stock/list-analytics/:type type is empty');
		}
		
		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		$this->view->typeCode = $typeCode;
		
		$itemTable = new Shared_Model_Data_WarehouseItem();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
		$selectObj->where('frs_warehouse_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_warehouse_item.warehouse_id = ?', $this->_warehouseSession->warehouseId);

        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', $typeCode);

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
		
		
		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		$this->view->today = $zDate->get('yyyy-MM-dd');
		
		$zDate->sub('2', Zend_Date::DAY);
		
		$this->view->twoDaysAgo = $zDate->get('yyyy-MM-dd');
		
		
		// 4週間前
		$zDate->sub('28', Zend_Date::DAY);
		$this->view->fourWeekAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->sub('3', Zend_Date::DAY);
		$this->view->oneMonthAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->add('3', Zend_Date::DAY);
		$zDate->sub('31', Zend_Date::DAY);
		$this->view->twoMonthAgo = $zDate->get('yyyy-MM-dd');
		
		
    }
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/add/:type                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材 新規登録                                          |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->type = $type = $request->getParam('type');
		
		if (empty($type)) {
			throw new Zend_Exception('/stock/add/:type type is empty');
		}
		
		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		$this->view->typeCode = $typeCode;

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
    |  action_URL    * /stock/add-post                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材 新規登録(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$type    = $request->getParam('type');

		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		
		if (empty($typeCode)) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		$this->view->typeCode = $typeCode;
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['unit_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「棚卸数量単位」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable = new Shared_Model_Data_WarehouseItem();

				$itemTable->getAdapter()->beginTransaction();
				//$itemTypeId = $itemTable->getNextItemTypeId($typeCode);
				
				// 新規登録
				$data = array(
			        'management_group_id'      => $this->_adminProperty['management_group_id'],  // 管理グループID
			        'warehouse_id'             => $this->_warehouseSession->warehouseId,         // 倉庫ID
			        'status'                   => Shared_Model_Code::ITEM_STATUS_ACTIVE,         // ステータス
			        
			        'stock_type'               => $typeCode,                                     // 在庫管理種別
			        
			        'target_type'              => $success['target_type'], // 対象種別
					'target_item_id'           => 0,  // 商品ID
					'target_supply_product_id' => 0,  // 調達管理-原料製品ID
					'target_supply_fixture_id' => 0,  // 調達管理-備品ID
					
					'unit_price'               => NULL,                    // 棚卸単価
					'unit_type'                => $success['unit_type'],   // 棚卸数量単位
					
	                'created'                  => new Zend_Db_Expr('now()'),
	                'updated'                  => new Zend_Db_Expr('now()'),
				);
				
				if ($success['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
					// 商品
					$data['target_item_id'] = $success['reference_target_id'];
					
					// すでに登録済み
					$isExist = $itemTable->itemIsExist($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $data['target_item_id']);
					if ($isExist) {
						$this->sendJson(array('result' => 'NG', 'message' => 'この商品は既に在庫管理資材として登録されています'));
						return;
					}
					
				} else if ($success['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
					// 原料製品
					$data['target_supply_product_id'] = $success['reference_target_id'];

					// すでに登録済み
					$isExist = $itemTable->supplyProductIsExist($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $data['target_supply_product_id']);
					if ($isExist) {
						$this->sendJson(array('result' => 'NG', 'message' => 'この原料製品は既に在庫管理資材として登録されています'));
						return;
					}

				} else if ($success['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
					// 備品資材
					$data['target_supply_fixture_id'] = $success['reference_target_id'];

					// すでに登録済み
					$isExist = $itemTable->supplyFixtureIsExist($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $data['target_supply_fixture_id']);
					if ($isExist) {
						$this->sendJson(array('result' => 'NG', 'message' => 'この備品資材は既に在庫管理資材として登録されています'));
						return;
					}
					
				} else {
				    $data['stock_name'] = $success['stock_name'];
				}
	
				try {
					$itemTable->create($data);
					$itemId = $itemTable->getLastInsertedId('id');
					
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/stock/add-post/:type transaction faied: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/basic                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材 基本情報                                          |
    +----------------------------------------------------------------------------*/
    public function basicAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop   = $request->getParam('pos');
		$this->view->from     = $request->getParam('from');
		
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		$typeCodeList = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/stock/' . $this->view->from . '/' . $typeCodeList[$data['stock_type']];

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
    |  action_URL    * /stock/update-basic                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材 基本情報更新(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function updateBasicAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$id      = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
			
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				
				if (!empty($errorMessage['stock_name']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「在庫資材名」を入力してください'));
                    return;

				} else if (!empty($errorMessage['unit_price']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「棚卸単価」は数字のみ(カンマ/ピリオドを含む)で入力してください'));
                    return;

				} else if (!empty($errorMessage['minimum_base_month']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「警告基準期間」を入力してください'));
                    return;

				} else if (!empty($errorMessage['minimum_base_month']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「警告基準期間」は数字のみ(カンマを含む)で入力してください'));
                    return;
                    
				} else if (!empty($errorMessage['minimum_count']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「最低在庫数」を入力してください'));
                    return;

				} else if (!empty($errorMessage['minimum_count']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「最低在庫数」は数字のみ(カンマを含む)で入力してください'));
                    return;

				} else if (!empty($errorMessage['safety_base_month']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「注意基準期間」を入力してください'));
                    return;

				} else if (!empty($errorMessage['safety_base_month']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「注意基準期間」は数字のみ(カンマを含む)で入力してください'));
                    return;
                    
				} else if (!empty($errorMessage['safety_count']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「安全在庫数」を入力してください'));
                    return;
                    
				} else if (!empty($errorMessage['safety_count']['notNumeric'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「安全在庫数」は数字のみ(カンマを含む)で入力してください'));
                    return;
                    
				}

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				/*
				if ((int)$success['safety_count'] <= (int)$success['minimum_count']) {
					$this->sendJson(array('result' => 'NG', 'message' => '「安全在庫数」は「最低在庫数」より大きい数値を入力してください'));
                    return;
				}
				*/
						
				$itemTable = new Shared_Model_Data_WarehouseItem();
				
				// 更新
				$data = array(
					'stock_name'         => $success['stock_name'],           // 在庫資材名
					'shelf_no'           => $success['shelf_no'],
					'status'             => $success['status'],
					'unit_price'         => $success['unit_price'],           // 棚卸単価
					'minimum_base_month' => $success['minimum_base_month'],   // 警告基準期間
					'safety_base_month'  => $success['safety_base_month'],    // 注意基準期間
					'use_dm'             => $success['use_dm'],
				);
				
				$itemTable->updateById($this->_adminProperty['management_group_id'], $id, $data);

			}

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/upload-image                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材 画像アップロード                                  |
    +----------------------------------------------------------------------------*/
    public function uploadImageAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
        
		if (empty($_FILES['image']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		$key = uniqid();
		
		// jpgに変換して保存
		$filePath = Shared_Model_Resource_WarehouseItem::getResourceObjectPath($id, $key);
		//var_dump($filePath);exit;
		
		/*
		$img =  $this->imageCreateFromAny($_FILES['image']['tmp_name']);

        if (empty($img)) {
        	throw new Zend_Exception('/stock/crop no object image');
        }
        
        $width = ImageSx($img);
        $height = ImageSy($img);
        
        $resizedWidth = 840;
        $out = ImageCreateTrueColor($resizedWidth, $height/ $width * $resizedWidth);
        ImageCopyResampled($out, $img, 0,0,0,0, $resizedWidth, floor($height/ $width * $resizedWidth), $width, $height);
        
        // 画像を保存
        $result = ImageJPEG($out, $filePath);
        */
        
        $result = Shared_Model_Resource_WarehouseItem::makeResource($id, $key, file_get_contents($_FILES['image']['tmp_name']));
        
        
        if ($result === false) {
        	throw new Zend_Exception('/stock/crop save failed');
        }
        
        $itemTable = new Shared_Model_Data_WarehouseItem();
        $itemTable->updateById($this->_adminProperty['management_group_id'], $id, array('image_key' => $key));
        
        
        
        $this->sendJson(array('result' => true, 'key' => $key, 'image_url' => Shared_Model_Resource_WarehouseItem::getResourceUrl($id, $key)));
        return;

	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/sum                                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 集計確認                                                   |
    +----------------------------------------------------------------------------*/
    public function sumAction()
    {
		$request = $this->getRequest();
		$this->view->id   = $id   = $request->getParam('id');
		
		// 商品データ
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
	    
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/stock/' . $this->view->from . '/' . $typeCodeList[$data['stock_type']];
	    
		$itemStockTable = new Shared_Model_Data_ItemStock();
		$selectObj = $itemStockTable->getActiveList($id, true);
		$selectObj->where('action_code < ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT);
		$selectObj->order('action_date DESC');
		$this->view->stockItems = $selectObj->query()->fetchAll();
		
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		$selectObj = $consumptionTable->getActiveList($id, true);
		$selectObj->joinLeft('frs_order', 'frs_item_stock_consumption.order_id = frs_order.id', array('relational_order_id'));
		$selectObj->order('frs_item_stock_consumption.action_date DESC');
		$this->view->stockConsumptionItems = $selectObj->query()->fetchAll();

	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/warehouse                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function warehouseAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id   = $id   = $request->getParam('id');
		$this->view->page = $page = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		$this->view->from   = $request->getParam('from');
		
		// 商品データ
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/stock/' . $this->view->from . '/' . $typeCodeList[$data['stock_type']];
		
		
		$itemStockTable = new Shared_Model_Data_ItemStock();
		$selectObj = $itemStockTable->getActiveList($id, true);
		$selectObj->where('action_code < ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT);
		$selectObj->order('action_date DESC');
		
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
    |  action_URL    * /stock/warehouse-cancel                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫予定キャンセル(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function warehouseCancelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$stockId  = $request->getParam('target_id');
		
		if (empty($stockId)) {
			throw new Zend_Exception('/stock/warehouse-cancel target_id is empty');
		}

		// POST送信時
		if ($request->isPost()) {
			$itemStockTable  = new Shared_Model_Data_ItemStock();
			
			$planData = $itemStockTable->getById($stockId);

			if (empty($planData)) {
				throw new Zend_Exception('/stock/warehouse-cancel plan data not found');
			}
		
			$itemStockTable->updateById($stockId, array('status' => Shared_Model_Code::STOCK_STATUS_INACTIVE));

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/warehouse-add                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫追加                                                   |
    +----------------------------------------------------------------------------*/
    public function warehouseAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->planStockId = $planStockId = $request->getParam('plan_stock_id', '');
		$this->view->from = $request->getParam('from');
		
		// 商品データ
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		
		$this->view->backUrl = 'javascript:void(0)';
		$this->view->today = date('Y-m-d H:i:s');
		
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
    |  action_URL    * /stock/warehouse-add-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫追加(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function warehouseAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id');
		$planStockId = $request->getParam('plan_stock_id', '');
		
		if (empty($itemId)) {
			throw new Zend_Exception('/stock/warehouse-add-post item_id is empty');
		}

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['action_time_day']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(日)」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['action_time_hour']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(時)」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['action_time_min']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(分)」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['warehouse_action']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アクション」を選択してください'));
                    return;

                } else if (!empty($errorMessage['lot']['isEmpty'])) {
                    $result['result'] = 'NG';
                    if ($success['warehouse_action'] === (string)Shared_Model_Code::STOCK_ACTION_PLAN_WAREHOUSE) {
                    	$result['message'] = '「入庫予定数」を入力してください';
                    } else {
                    	$result['message'] = '「ロット単位」を入力してください';
                    }
                    $this->sendJson($result);
                    return;
                    
                } else if (!empty($errorMessage['lot']['notDigits'])) {
                    $result['result'] = 'NG';
                    if ($success['warehouse_action'] === (string)Shared_Model_Code::STOCK_ACTION_PLAN_WAREHOUSE) {
                    	$result['message'] = '「入庫予定数」は半角数字のみで入力してください';
                    } else {
                    	$result['message'] = '「ロット単位」は半角数字のみで入力してください';
                    }
                    $this->sendJson($result);
                    return;
                    
                } else if (!empty($errorMessage['number_of_lot']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ケース数」は半角数字のみで入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$actionTimeString    = str_replace('/', '-', $success['action_time_day']) . ' ' . $success['action_time_hour'] . ':' . $success['action_time_min'] . ':00';
				
				$itemTable       = new Shared_Model_Data_WarehouseItem();
				$itemStockTable  = new Shared_Model_Data_ItemStock();
				
				
				$lotNumbers = (int)$success['number_of_lot'];
				
				if ($success['warehouse_action'] == Shared_Model_Code::STOCK_ACTION_PLAN_WAREHOUSE) {
					$lotNumbers = 1;
				}
				
				// テーブルロック
				$itemStockTable->getAdapter()->query("LOCK TABLES frs_item WRITE, frs_item_stock WRITE")->execute();
				$itemStockTable->getAdapter()->beginTransaction();
				
	            try {
	            	$warehouseManageId = $itemStockTable->getNextId();

					for ($count = 0; $count < $lotNumbers; $count++) {
						$data = array(
					        'item_id'             => 0,
					        'warehouse_item_id'   => $itemId,     // 倉庫管理アイテムID
					        'user_id'             => 0,
							'status'              => Shared_Model_Code::STOCK_STATUS_ACTIVE,
							
							'warehouse_manage_id' => $warehouseManageId,
							'lot_count'           => $count + 1,
							'action_date'         => $actionTimeString,           // アクション日
							'action_code'         => $success['warehouse_action'],
							
							'expiration_date'     => NULL,
							
							'amount'              => $success['lot'],
							'sub_count'           => 0,
							'last_count'          => $success['lot'],
							
							'warehouse_id'        => 1,
							
							'order_id'            => 0,
							'memo'                => $success['memo'],
		
			                'created'             => new Zend_Db_Expr('now()'),
			                'updated'             => new Zend_Db_Expr('now()'),
						);
						
						if (!empty($success['expiration_date'])) {
							$data['expiration_date'] = str_replace('/', '-',$success['expiration_date']);
						}
					
						$itemStockTable->create($data);
					}
					
					if ($success['warehouse_action'] == Shared_Model_Code::STOCK_ACTION_WAREHOUSE) {
						// 入庫の場合 在庫資材テーブルの在庫数更新
						$itemTable->addStock($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $itemId, (float)$success['lot'] * $lotNumbers);	
					}
					
					// 入庫予定からの入庫登録の場合は入庫予定を削除
					if (!empty($planStockId)) {
						$itemStockTable->updateById($planStockId, array('status' => Shared_Model_Code::STOCK_STATUS_INACTIVE));
					}
					
	                // commit
	                $itemStockTable->getAdapter()->commit();
	                $itemStockTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	            } catch (Exception $e) {
	                $itemStockTable->getAdapter()->rollBack();
	                $itemStockTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	                throw new Zend_Exception('/stock/warehouse-add-post transaction faied: ' . $e);   
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/consumption-all                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 全出庫履歴                                                 |
    +----------------------------------------------------------------------------*/
    public function consumptionAllAction()
    {
		$request = $this->getRequest();
		$this->view->page = $page = $request->getParam('page', '1');
		
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		$selectObj = $consumptionTable->select();

		$selectObj->joinLeft('frs_warehouse_item', 'frs_warehouse_item.id = frs_item_stock_consumption.warehouse_item_id', array('target_type', $consumptionTable->aesdecrypt('stock_name', false) . 'AS stock_name'));
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($consumptionTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($consumptionTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($consumptionTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
        
		$selectObj->joinLeft('frs_item_stock', 'frs_item_stock_consumption.target_stock_id = frs_item_stock.id', array('warehouse_manage_id'));
		$selectObj->joinLeft('frs_order', 'frs_item_stock_consumption.order_id = frs_order.id', array('relational_order_id'));

    	$selectObj->where('frs_item_stock_consumption.status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
		$selectObj->order('frs_item_stock_consumption.action_date DESC');
		
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
    |  action_URL    * /stock/consumption                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出庫履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function consumptionAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->page = $page = $request->getParam('page', '1');
		$this->view->from = $request->getParam('from');
		
		// 商品データ
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/stock/' . $this->view->from . '/' . $typeCodeList[$data['stock_type']];
		
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		$selectObj = $consumptionTable->getActiveList($id, true);
		$selectObj->joinLeft('frs_item_stock', 'frs_item_stock_consumption.target_stock_id = frs_item_stock.id', array('warehouse_manage_id'));
		$selectObj->joinLeft('frs_order', 'frs_item_stock_consumption.order_id = frs_order.id', array('relational_order_id'));
		$selectObj->order('frs_item_stock_consumption.action_date DESC');
		
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
    |  action_URL    * /stock/consumption-add                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出庫追加                                                   |
    +----------------------------------------------------------------------------*/
    public function consumptionAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id      = $id      = $request->getParam('id');
		$this->view->stockId = $stockId = $request->getParam('stock_id');
		$this->view->from = $request->getParam('from');

		// 商品データ
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		$stockTable    = new Shared_Model_Data_ItemStock();
		
		// 対象在庫情報
		$this->view->stockData = $stockTable->getById($stockId);
		

		
		$this->view->backUrl = 'javascript:void(0)';		
		$this->view->today = date('Y-m-d H:i:s');
		
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
    |  action_URL    * /stock/consumption-add-post                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫消費追加(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function consumptionAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id'); // 倉庫管理アイテムID
		
		$stockId = $request->getParam('stock_id');
		
		if (empty($itemId)) {
			throw new Zend_Exception('/stock/consumption-add-post item_id is empty');
		}

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['action_time_day']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'error' => '「日時(日)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['action_time_hour']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'error' => '「日時(時)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['action_time_min']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'error' => '「日時(分)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['consumption_action']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'error' => '「アクション」を選択してください'));
                    return;
                } else if (!empty($errorMessage['amount']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'error' => '「数量」は半角数字のみで入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {

				$actionTimeString    = str_replace('/', '-', $success['action_time_day']) . ' ' . $success['action_time_hour'] . ':' . $success['action_time_min'] . ':00';
				
				$itemTable         = new Shared_Model_Data_WarehouseItem();
				$itemStockTable    = new Shared_Model_Data_ItemStock();
				$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
				
				// 新規登録
				$data = array(
			        'item_id'             => 0,
			        'warehouse_item_id'   => $itemId,     // 倉庫管理アイテムID
					'user_id'             => 0,
					'status'              => Shared_Model_Code::STOCK_STATUS_ACTIVE,
					
					'action_date'         => $actionTimeString,           // アクション日
					'action_code'         => $success['consumption_action'],
					
					'sub_count'           => $success['amount'],
					'target_stock_id'     => $stockId,// 対象の在庫
					
					'order_id'            => 0,
					'memo'                => $success['memo'],

	                'created'             => new Zend_Db_Expr('now()'),
	                'updated'             => new Zend_Db_Expr('now()'),
				);

				// テーブルロック
				$consumptionTable->getAdapter()->query("LOCK TABLES frs_item WRITE, frs_item_stock WRITE, frs_item_stock_consumption WRITE")->execute();
				
				$consumptionTable->getAdapter()->beginTransaction();
            	
	            try {
	            	// 消費データの追加
					$consumptionTable->create($data);
					$consumptionId = $consumptionTable->getLastInsertedId('id');
				
					// 在庫データの在庫数を減らす
					$itemStockTable->consumeStock($stockId, $success['amount']);
					
					// 在庫資材データの在庫数を減らす
					$itemTable->subStock($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $itemId, (float)$success['amount']);	
					
	                // commit
	                $consumptionTable->getAdapter()->commit();
	                $consumptionTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	            } catch (Exception $e) {
	                $consumptionTable->getAdapter()->rollBack();
	                $consumptionTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	                throw new Zend_Exception('/stock/consumption-add-post transaction faied: ' . $e);   
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /stock/data-shipping                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * データ分析 - 出荷                                          |
    +----------------------------------------------------------------------------*/
    public function dataShippingAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->tagetMonth = $tagetMonth = $request->getParam('target_month');
		$this->view->from = $request->getParam('from');
		
		
		// 商品データ
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/stock/' . $this->view->from . '/' . $typeCodeList[$data['stock_type']];

        if (!empty($tagetMonth)) {
            $startDate   = date('Y-m', strtotime($tagetMonth)) . '-01'; // 月初日
            $targetYear  = date('Y', strtotime($tagetMonth));
            $targetMonth = date('m', strtotime($tagetMonth));
        } else {
            $startDate   = date('Y-m-' . '01'); // 月初日
            $targetYear  = date('Y');
            $targetMonth = date('m');
        }
			
		// 月末日を取得
        $endDate = date($targetYear . '-' . $targetMonth . '-' . Nutex_Date::getMonthEndDay($targetYear, $targetMonth));
        

        // 月末または今日までの日数
        $dayCount = 0;
        if ($startDate === date('Y-m-01')) {
	        $endZDate = new Zend_Date(NULL, NULL, 'ja_JP');
	        $zDate = new Zend_Date($startDate, NULL, 'ja_JP');
	        
	        while ($zDate->isEarlier($endZDate)) {
		        $dayCount++;
		        $zDate->add('1', Zend_Date::DAY);
	        }
	        
        } else {
	        $endZDate = new Zend_Date($endDate, NULL, 'ja_JP');
	        $zDate = new Zend_Date($startDate, NULL, 'ja_JP');
	        
	        while ($zDate->isEarlier($endZDate)) {
		        $dayCount++;
		        $zDate->add('1', Zend_Date::DAY);
	        }
        }
        $this->view->dayCount = $dayCount;
        
        
        $consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
        
        $monthlyList = array();
        
        // 月間
        $zDate = new Zend_Date($startDate, NULL, 'ja_JP');
        $zDate->sub('12', Zend_Date::MONTH);
        
        $monthlyList[] = array(
	        'target_month' => $zDate->get('yyyy/MM'),
	        'count'        => 10,
        );
        
        
        for($count = 0; $count < 12; $count++) {
	        $monthlyList[] = array(
		        'target_month' => $zDate->get('yyyy/MM'),
		        'count'        => 10,
	        );
          
	        $zDate->add('1', Zend_Date::MONTH);
	        
        }
        
        $this->view->monthlyList = $monthlyList;
        
        
        
        
		
        // 期間データ初期化
        $period = $this->_createMonthPeriod($startDate, $endDate, array('count'));
        
		
        $zendDateToday = new Zend_Date(NULL, NULL, 'ja_JP');
        
        $totalCount = 0;
        $dateCountForMonth = 0;
        foreach ($period as $eachDate => &$eachCount) {
            $eachCount['count'] = $consumptionTable->getDailyCount($id, $eachDate);
            
            $totalCount += $eachCount['count'];
            
            $zendDate = new Zend_Date($eachDate, NULL, 'ja_JP');
            if ($zendDate->isEarlier($zendDateToday)) {
                $dateCountForMonth++;
            }
        }
        
        
        $this->view->dataList   = $period;
        $this->view->totalCount = $totalCount;
        

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
    |  action_URL    * /stock/data-stock                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * データ分析 - 在庫推移                                      |
    +----------------------------------------------------------------------------*/
    public function dataStockAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->from = $request->getParam('from');
		
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $this->_warehouseSession->warehouseId, $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/stock/' . $this->view->from . '/'. $typeCodeList[$data['stock_type']];

        if (!empty($targetDate)) {
            $startDate   = date('Y-m', strtotime($targetDate)) . '-01'; // 月初日
            $targetYear  = date('Y', strtotime($targetDate));
            $targetMonth = date('m', strtotime($targetDate));
        } else {
            $startDate   = date('Y-m-' . '1'); // 月初日
            $targetYear  = date('Y');
            $targetMonth = date('m');
        }
		
		// 月末日を取得
        $endDate = date($targetYear . '-' . $targetMonth . '-' . Nutex_Date::getMonthEndDay($targetYear, $targetMonth));
        
		
        // 期間データ初期化
        $period = $this->_createMonthPeriod($startDate, $endDate, array('count'));
        
        $zendDateToday = new Zend_Date(NULL, NULL, 'ja_JP');
		
		$historyTable = new Shared_Model_Data_WarehouseItemHistory();
		
		$dateCountForMonth = 0;
        foreach ($period as $eachDate => &$eachCount) {
            $eachCount['count'] = $historyTable->getCountOfDate($id, $eachDate);
            $eachCount['available'] = true;
            
            $zendDate = new Zend_Date($eachDate, NULL, 'ja_JP');
            if ($zendDate->isEarlier($zendDateToday)) {
                $dateCountForMonth++;
            } else {
            	$eachCount['available'] = false;
            }
        }

        $this->view->dataList = $period;
		
		$zDate = new Zend_Date($startDate, NULL, 'ja_JP');
		$this->view->max1Month  = $historyTable->getMaxWithTerm($id, $startDate, $endDate);
		$this->view->min1Month  = $historyTable->getMinWithTerm($id, $startDate, $endDate);
		
		
		$zDate->sub('2', Zend_Date::MONTH);
		
		$this->view->max3Month  = $historyTable->getMaxWithTerm($id, $zDate->get('yyyy-MM-dd'), $endDate);
		$this->view->min3Month  = $historyTable->getMinWithTerm($id, $zDate->get('yyyy-MM-dd'), $endDate);
		
		$zDate->sub('3', Zend_Date::MONTH);
		
		$this->view->max6Month  = $historyTable->getMaxWithTerm($id, $zDate->get('yyyy-MM-dd'), $endDate);
		$this->view->min6Month  = $historyTable->getMinWithTerm($id, $zDate->get('yyyy-MM-dd'), $endDate);
		
		$zDate->sub('6', Zend_Date::MONTH);
		
		$this->view->max12Month = $historyTable->getMaxWithTerm($id, $zDate->get('yyyy-MM-dd'), $endDate);
		$this->view->min12Month = $historyTable->getMinWithTerm($id, $zDate->get('yyyy-MM-dd'), $endDate);		
		
		
		
		// 棚卸数量単位
		$unitTypeTable = new Shared_Model_Data_StockUnitType();
		$unitTypeList = array();
		$unitTypeItems = $unitTypeTable->getList();
		foreach ($unitTypeItems as $each) {
			$unitTypeList[$each['id']] = $each;
		}
		
		$this->view->unitTypeList = $unitTypeList;
    }   
    
}

