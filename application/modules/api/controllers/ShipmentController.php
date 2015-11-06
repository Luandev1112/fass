<?php
/**
 * class Api_ShipmentController
 */

class Api_ShipmentController extends Api_Model_Controller
{
    /**
     * planListAction
     * 検品リスト
     * /api/shipment/plan-list?sessionId=P1s0kRRrhxTsdDTZ6nQR4ZHuUlOHqWYbTcFm1k3y7g0fIK0SqGDDNBoz5RdL4NHHFc_b2sWFUr081w-d2HJGQb
     */
    public function planListAction()
    {
    	$request = $this->getRequest();
    	$warehouseId = '1';
    	
    	$items    = array();
    	$userData = $this->_getUserData();

		$orderTable = new Shared_Model_Data_Order();
		$shipmentStatusList = Shared_Model_Code::codes('shipment_status');
		
		// 対象検品者の検品リスト
    	$items = $orderTable->getListForInspectionUser($warehouseId, $userData['id']);
    	
    	// 未対応件数
    	$notInspectionCount = 0;
    	
    	foreach ($items as &$each) {
    		$each['status_string'] = $shipmentStatusList[$each['status']];
    		$each['status_color']  = '000000';
    		if ($each['status'] === (string)Shared_Model_Code::SHIPMENT_STATUS_NEW) {
    			$each['status_color'] = 'ff0000';
    			$notInspectionCount++;
    		}
    	}
    	
        $params = array (
            'result'             => true,
            'items'              => $items,              // 本日の検品アイテムリスト
            'notInspectionCount' => $notInspectionCount, // 未検品数
            'inspectionUserId'   => $userData['id'],     // 検品者ユーザーID
        );
            
		return $this->sendJson($params);  
    }


    /**
     * detailAction
     * 検品ピッキングデータ(DEBUG)
     */
    public function detailDebugAction()
    {
    	$request     = $this->getRequest();
    	$orderId     = 'TY2100055';
    	$warehouseId = '1';
    	
    	if (empty($orderId)) {
    		throw new Zend_Exception('/api/shipment/detail NO TARGET ID');
    	}
		
		$itemTable           = new Shared_Model_Data_WarehouseItem();
		$orderTable          = new Shared_Model_Data_Order();
		$orderItemTable      = new Shared_Model_Data_OrderItem();
		
    	$packageTable        = new Shared_Model_Data_ItemPackage();
    	$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
    	$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();
    	$packageShippingpackTable  = new Shared_Model_Data_ItemPackageShippingpack();
    	
    	$includingPlanTable  = new Shared_Model_Data_IncludingPlan();
    	
    	// 注文データ
		$orderData = $orderTable->getByOrderId($warehouseId, $orderId);
		$orderData['pdf_url'] = '';
		
		if (empty($orderData)) {
			throw new Zend_Exception('/api/shipment/detail - no target order');
		}
		
		//var_dump($orderData);
		
		
		// 購入商品リスト
		$purchasedList = $orderItemTable->getListByOrderId($orderData['id']);
		
		var_dump($purchasedList);
		
		// 同梱施策リスト
		$planList = $includingPlanTable->getActivePlanList($warehouseId);
		
		// 発送用梱包資材リスト
		$packageItemList = $itemTable->getItemList(Shared_Model_Code::ITEM_TYPE_PACKAGE);

		$activePlanList = array();	
		
		$inspectionProductList = array();
		$includingIemList      = array();
		
		foreach ($purchasedList as $eachPurchasedItem) {
			// 商品パッケージ
			$packageData = $packageTable->getByProductCode($eachPurchasedItem['product_code']);

			if (empty($packageData)) {
				throw new Zend_Exception('/api/shipment/detail - no packageData found');
			}
			
			// 構成商品
			$productList = $packageProductTable->getProductItemsByPackageId($packageData['id']);
			//var_dump($productList);
		}
		
		exit;
    }


    /**
     * detailAction
     * 検品ピッキングデータ
     */
    public function detailAction()
    {
    	$request     = $this->getRequest();
    	$orderId     = $request->getParam('order_id');
    	$warehouseId = '1';
    	
    	if (empty($orderId)) {
    		throw new Zend_Exception('/api/shipment/detail NO TARGET ID');
    	}
		
		$itemTable           = new Shared_Model_Data_WarehouseItem();
		$orderTable          = new Shared_Model_Data_Order();
		$orderItemTable      = new Shared_Model_Data_OrderItem();
		
    	$packageTable        = new Shared_Model_Data_ItemPackage();
    	$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
    	$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();
    	$packageShippingpackTable  = new Shared_Model_Data_ItemPackageShippingpack();
    	
    	$includingPlanTable  = new Shared_Model_Data_IncludingPlan();
    	
    	// 注文データ
		$orderData = $orderTable->getByOrderId($warehouseId, $orderId);
		$orderData['pdf_url'] = '';
		
		if (empty($orderData)) {
			throw new Zend_Exception('/api/shipment/detail - no target order');
		}

		// 購入商品リスト
		$purchasedList = $orderItemTable->getListByOrderId($orderData['id']);
		
		// 同梱施策リスト
		$planList = $includingPlanTable->getActivePlanList($warehouseId);
		
		// 発送用梱包資材リスト
		$packageItemList = $itemTable->getItemList(Shared_Model_Code::ITEM_TYPE_PACKAGE);

		$activePlanList = array();	
		
		$inspectionProductList = array();
		$includingIemList      = array();
		
		foreach ($purchasedList as $eachPurchasedItem) {
			// 商品パッケージ
			$packageData = $packageTable->getByProductCode($eachPurchasedItem['product_code']);

			if (empty($packageData)) {
				throw new Zend_Exception('/api/shipment/detail - no packageData found');
			}
			
			// 構成商品
			$productList = $packageProductTable->getProductItemsByPackageId($packageData['id']);
			
			if (!empty($productList)) {
				foreach ($productList as $eachProduct) {
	        		$itemName = '';
	        		if ($eachProduct['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
						$itemName = $eachProduct['item_name'];
					} else if ($eachProduct['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
						$itemName = $eachProduct['supply_product_name'];
					} else if ($eachProduct['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
						$itemName = $eachProduct['supply_fixture_name'];
					} else {
						$itemName = $eachProduct['stock_name'];
					}

					$inspectionProductList[] = array(
			    		'item_type_color' => 'ff0000',
			    		'item_id'         => $eachProduct['product_item_id'],
			    		'item_type_name'  => '商品',
			    		'item_type_id'    => '',//$eachProduct['item_type_id'],
			    		'item_name'       => $itemName,
			    		'jan_code'        => '', //$eachProduct['jan_code'],
			    		'shelf_no'        => $eachProduct['shelf_no'],
			    		'amount'          => (int)$eachProduct['product_item_amount'] * (int)$eachPurchasedItem['amount'],
			    		'image_url'       => HTTPS_PROTOCOL . APPLICATION_DOMAIN . Shared_Model_Resource_WarehouseItem::getResourceUrl($eachProduct['warehouse_item_id'], $eachProduct['image_key']),
			    		//'image_file_name' => $eachProduct['image_key'],
					);
					
					//var_dump($eachProduct['product_item_id'] . ':' . $itemName . ':' . $eachProduct['image_key']);exit;
				}
			}
			
			// 付属品
			$bundleList = $packageBundleTable->getBundleItemsByPackageId($packageData['id']);
			
			if (!empty($bundleList)) {
				foreach ($bundleList as $eachBundle) {
	        		if ($eachBundle['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
						$itemName = $eachBundle['item_name'];
					} else if ($eachBundle['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
						$itemName = $eachBundle['supply_product_name'];
					} else if ($eachBundle['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
						$itemName = $eachBundle['supply_fixture_name'];
					} else {
						$itemName = $eachBundle['stock_name'];
					}
					
					$inspectionProductList[] = array(
			    		'item_type_color' => '4a8bf5',
			    		'item_id'         => $eachBundle['bundle_item_id'],
			    		'item_type_name'  => '付属品',
			    		'item_type_id'    => '',//$eachBundle['item_type_id'],
			    		'item_name'       => $itemName,
			    		'jan_code'        => '', //$eachBundle['jan_code'],
			    		'shelf_no'        => $eachBundle['shelf_no'],
			    		'amount'          => (int)$eachBundle['bundle_item_amount'] * $eachPurchasedItem['amount'],
			    		'image_url'       => HTTPS_PROTOCOL . APPLICATION_DOMAIN .Shared_Model_Resource_WarehouseItem::getResourceUrl($eachBundle['warehouse_item_id'], $eachBundle['image_key']),
			    		//'image_file_name' => $eachBundle['image_key'],
					);
				}
			}
			
			
			foreach ($productList as $eachProduct) {
				foreach ($planList as $eachPlan) {
					
					// 適用期間確認
					if ($eachPlan['term_type'] == Shared_Model_Code::INCLUDING_PLAN_TERM_TYPE_ORDER) {
						// 注文日
						$orderDateTimestamp = strtotime($orderData['order_datetime']);
						
						if (!empty($eachPlan['start_date'])) {
							$planStartTimestamp = strtotime($eachPlan['start_date'] . ' 00:00:00');
							
							if ($orderDateTimestamp < $planStartTimestamp) {
								continue;
							}
						}
						
						if (!empty($eachPlan['end_date'])) {
							$planEndTimestamp = strtotime($eachPlan['end_date'] . ' 23:59:59');
							if ($planEndTimestamp < $orderDateTimestamp) {
								continue;
							}
						}
					
					} else if ($eachPlan['term_type'] == Shared_Model_Code::INCLUDING_PLAN_TERM_TYPE_SHIPPING) {
						// 発送日
						//$shipmentTimestamp = strtotime($orderData['shipment_plan_date'] . ' 12:00:00');
						$shipmentTimestamp = time();
					
						if (!empty($eachPlan['start_date'])) {
							$planStartTimestamp = strtotime($eachPlan['start_date'] . ' 00:00:00');
							
							if ($shipmentTimestamp < $planStartTimestamp) {
								continue;
							}
						}
						
						if (!empty($eachPlan['end_date'])) {
							$planEndTimestamp   = strtotime($eachPlan['end_date'] . ' 23:59:59');
							
							if ($planEndTimestamp < $shipmentTimestamp) {
								continue;
							}
						}
					}
					
					// 条件を確認
					$isMatchedCondition = false;
					
					if ($eachPlan['condition_type'] == Shared_Model_Code::INCLUDING_PLAN_CONDITION_TYPE_ORDER_ALL) {
						// 全ての注文
						$isMatchedCondition = true;
						
					} else if ($eachPlan['condition_type'] == Shared_Model_Code::INCLUDING_PLAN_CONDITION_TYPE_ORDER_ITEM) {
						// 特定の商品
						$conditionItems = json_decode($eachPlan['condition_item_ids'], true);
					
						foreach ($conditionItems as $eachConditionItem) {
							if ($eachProduct['product_item_id'] == $eachConditionItem['condition_item_id']) {
								$isMatchedCondition = true;
							}
						}
						
					} else if ($eachPlan['condition_type'] == Shared_Model_Code::INCLUDING_PLAN_CONDITION_TYPE_SINGLE_ORDER) {
						// 単発注文
						if (empty($orderData['subscription_count'])) {
							$isMatchedCondition = true;
						}
						
					} else if ($eachPlan['condition_type'] == Shared_Model_Code::INCLUDING_PLAN_CONDITION_TYPE_SUBSCRIPTION_ORDER) {
						// 定期注文
						if (!empty($orderData['subscription_count'])) {
							
							if (!empty($eachPlan['condition_subscription_start'])) {
								// 開始回数
								if ((int)$orderData['subscription_count'] < (int)$eachPlan['condition_subscription_start']) {
									continue;
								}
							} else if (!empty($data['condition_subscription_start'])) {
								// 終了回数
								if ((int)$orderData['condition_subscription_end'] < (int)$eachPlan['subscription_count']) {
									continue;
								}
							}
							
							if (empty($eachPlan['condition_subscription_start'])) {
								$eachPlan['condition_subscription_start'] = 1;
							}
							
							// (今回回数) - (初回回数)
							$diff = (int)$orderData['subscription_count'] - (int)$eachPlan['condition_subscription_start'];
							
							if ($diff === 0) {
								$isMatchedCondition = true;
							} else {
								if ($diff % (int)$eachPlan['condition_subscription_intervals'] === 0) {
									$isMatchedCondition = true;
								}
							}
						}
					}
					
					
				    if ($isMatchedCondition === true) {
				    
				    	$activePlanList[$eachPlan['id']] = $eachPlan;
				    
						// 同梱品リスト
						$includingItemList = json_decode($eachPlan['including_items'], true);
						
						foreach ($includingItemList as $eachIncluding) {
							
							// 修正必要
							$itemData = $itemTable->getById('1', $warehouseId, $eachIncluding['item_id']);
							
							/*
							if ($eachIncluding['item_id'] == '34') {
								var_dump($eachPlan);exit;
							}
			        		echo $eachIncluding['item_id'] . '<br>';
			        		*/
			        		
			        		if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_ITEM) {
								$itemName = $itemData['item_name'];
							} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
								$itemName = $itemData['supply_product_name'];
							} else if ($itemData['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
								$itemName = $itemData['supply_fixture_name'];
							} else {
								$itemName = $itemData['stock_name'];
							}

							if ($eachIncluding['item_unique'] == Shared_Model_Code::INCLUDING_PLAN_ITEM_UNIQUE) {
								// 同梱をまとめる
								
								if (!array_key_exists($eachIncluding['item_id'], $includingIemList)) {
									$includingIemList[$eachIncluding['item_id']] = array(
							    		'item_type_color' => '009745',
							    		'item_id'         => $itemData['id'],
							    		'item_type_name'  => '同梱品',
							    		'',//'item_type_id'    => $itemData['item_type_id'],
							    		'item_name'       => $itemName,
							    		'shelf_no'        => $itemData['shelf_no'],
							    		'amount'          => $eachIncluding['item_amount'],
									);
								}
							} else {
								// 同梱をまとめない
								if (!array_key_exists($eachIncluding['item_id'], $includingIemList)) {
									$includingIemList[$eachIncluding['item_id']] = array(
							    		'item_type_color' => '009745',
							    		'item_id'         => $itemData['id'],
							    		'item_type_name'  => '同梱品',
							    		'',//'item_type_id'    => $itemData['item_type_id'],
							    		'item_name'       => $itemName,
							    		'shelf_no'        => $itemData['shelf_no'],
							    		'amount'          => $eachIncluding['item_amount'] * $eachProduct['product_item_amount'],
									);
								} else {
									$includingIemList[$eachIncluding['item_id']]['amount'] = $includingIemList[$eachIncluding['item_id']]['amount'] + $eachIncluding['item_amount'] * $eachProduct['product_item_amount'];
								}
							}	
						}
					}
					
				}  // $planList
			} // $productList
			
		} // $purchasedList

		//var_dump($includingIemList);exit;
    	$newIncludingItems = array();
    	
    	foreach ($includingIemList as $each) {
    		$newIncludingItems[] = $each;
    	}
    	
    	$newIncludingPlanItems = array();
    	
    	foreach ($activePlanList as $eachPlan) {
    		$newIncludingPlanItems[] = $eachPlan;
    	}
    	

    	
    	// デフォルト梱包資材(1商品のみの注文の場合)
    	if (count($purchasedList) <= 1) {
    		// カウント初期化
    		foreach ($packageItemList as &$eachPackageItem) {
				$eachPackageItem['amount'] = 0;
				
				$itemName = '';
        		if ($eachPackageItem['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_PRODUCT) {
					$eachPackageItem['item_name'] = $eachPackageItem['supply_product_name'];
				} else if ($eachPackageItem['target_type'] === (string)Shared_Model_Code::WAREHOUSE_ITEM_TARGET_TYPE_SUPPLY_FIXTURE) {
					$eachPackageItem['item_name'] = $eachPackageItem['supply_fixture_name'];
				} else {
					$eachPackageItem['item_name'] = $eachPackageItem['stock_name'];
				}
			}
				
    		foreach ($purchasedList as $eachPurchasedItem) {
				// 商品パッケージ
				$packageData = $packageTable->getByProductCode($eachPurchasedItem['product_code']);
				
				// パッケージの標準梱包
				$shippingPackList = $packageShippingpackTable->getShippingpackItemsByPackageId($packageData['id']);
				
				foreach ($packageItemList as &$eachPackageItem) {
					$eachPackageItem['amount'] = 0;
						
					foreach ($shippingPackList as $eachShippingPack) {
						if ($eachShippingPack['shippingpack_item_id'] == $eachPackageItem['id']) {
							$eachPackageItem['amount'] = $eachShippingPack['shippingpack_item_amount'];
						}
					}
				}
			}
    	} else {
			foreach ($packageItemList as &$eachPackageItem) {
				$eachPackageItem['amount'] = 0;
			}
    	}

		//var_dump($packageItemList);exit;
				
        $params = array (
            'result'                => true,
            'summary'               => $orderData,
            'items'                 => $inspectionProductList,
            'including_items'       => $newIncludingItems,
            'plan_items'            => $newIncludingPlanItems,
            'order_item_count'      => count($purchasedList),
            
            //DEBUG
            'purchasedList'         => $purchasedList,
            'plan_list'             => $planList,
            'package_items'         => $packageItemList,
            //'inspectionProductList' => $inspectionProductList,
        );
        
        //var_dump($params);exit;
           
		return $this->sendJson($params);
        
    }
    
    /**
     * finishAction
     * 検品終了
     */
    public function finishAction()
    {
    	$request         = $this->getRequest();
    	$orderId         = $request->getParam('order_id');
    	$items           = json_decode($request->getParam('items'), true);
    	$includingItems  = json_decode($request->getParam('including_items'), true);
    	$packageItems    = json_decode($request->getParam('package_items'), true);
    	
    	$warehouseId     = '1';
    	$userData        = $this->_getUserData();
    	
    	$itemTable        = new Shared_Model_Data_WarehouseItem();
    	$orderTable       = new Shared_Model_Data_Order();
    	$stockTable       = new Shared_Model_Data_ItemStock();
    	$consumptionTable = new Shared_Model_Data_ItemStockConsumption();
    	
    	$orderData = $orderTable->getByOrderId($warehouseId, $orderId);

		if (empty($orderData) || empty($userData)) {
			throw new Zend_Exception('/api/shipment/finish - no target order');
		}
		
		$now = date('Y-m-d H:i:s');
		
		$orderTable->getAdapter()->beginTransaction();
            	  
	    try {
			// 実在庫を減らす
			if (!empty($items)) {
				foreach ($items as $eachItem) {
					$amount = (int)$eachItem['amount'];
		
					while ($amount > 0) {
						$consumeCount = $amount;
		
						// 使用する在庫
						$stockData = $stockTable->findFirstStock($eachItem['item_id']);
		
						if (empty($stockData)) {
							$orderTable->getAdapter()->rollBack();
							$params = array (
					            'result'  => false,
					            'message' => '『' . $eachItem['item_name'] . "』\n 在庫が取得できません",
					            'items'   => $items,
					        );
							return $this->sendJson($params);
						}
						
						if ($consumeCount > (int)$stockData['last_count']) {
							$consumeCount = (int)$stockData['last_count'];
						}
						
						$stockTable->consumeStock($stockData['id'], $consumeCount);
						$itemTable->subStock('1', '1', $eachItem['item_id'], $consumeCount);
						$consumptionTable->create(array(
					        'item_id'           => 0, // (廃止)
					        'warehouse_item_id' => $eachItem['item_id'],     // 倉庫管理アイテムID
					        
					        'user_id'           => $userData['id'],
							'status'            => Shared_Model_Code::STOCK_STATUS_ACTIVE,
							
							'action_date'       => $now,
							'action_code'       => Shared_Model_Code::STOCK_ACTION_SHIPMENT,
							
							'sub_count'         => $consumeCount,
							'target_stock_id'   => $stockData['id'],// 対象の在庫
							
							'order_id'          => $orderData['id'],
							'memo'              => '',
			
			                'created'           => new Zend_Db_Expr('now()'),
			                'updated'           => new Zend_Db_Expr('now()'),
						));
						
						$amount = $amount - $consumeCount;
					}
				}
			}
			
			if (!empty($includingItems)) {
				foreach ($includingItems as $eachIncluding) {
					$amount = (int)$eachIncluding['amount'];
		
					while ($amount > 0) {
						$consumeCount = $amount;
		
						// 使用する在庫
						$stockData = $stockTable->findFirstStock($eachIncluding['item_id']);
		
						if (empty($stockData)) {
							$orderTable->getAdapter()->rollBack();
							$params = array (
					            'result'  => false,
					            'message' => '『' . $eachIncluding['item_name'] . "』\n 在庫が取得できません",
					        );
							return $this->sendJson($params);
						}
						
						if ($consumeCount > (int)$stockData['last_count']) {
							$consumeCount = (int)$stockData['last_count'];
						}
						
						$stockTable->consumeStock($stockData['id'], $consumeCount);
						$itemTable->subStock('1', '1', $eachIncluding['item_id'], $consumeCount);
						$consumptionTable->create(array(
					        'item_id'         => 0, // (廃止)
					        'warehouse_item_id' => $eachIncluding['item_id'],     // 倉庫管理アイテムID
					        
					        'user_id'         => $userData['id'],
							'status'          => Shared_Model_Code::STOCK_STATUS_ACTIVE,
							
							'action_date'     => $now,
							'action_code'     => Shared_Model_Code::STOCK_ACTION_SHIPMENT,
							
							'sub_count'       => $consumeCount,
							'target_stock_id' => $stockData['id'],// 対象の在庫
							
							'order_id'        => $orderData['id'],
							'memo'            => '',
			
			                'created'         => new Zend_Db_Expr('now()'),
			                'updated'         => new Zend_Db_Expr('now()'),
						));
						
						$amount = $amount - $consumeCount;
					}
				}
			}
			
			if (!empty($packageItems)) {
				foreach ($packageItems as $eachPackage) {
					if ($eachPackage['amount'] >= 0) {      ////   ここでUndefined Index
						$amount = (int)$eachPackage['amount'];
			
						while ($amount > 0) {
							$consumeCount = $amount;
			
							// 使用する在庫
							$stockData = $stockTable->findFirstStock($eachPackage['id']);
			
							if (empty($stockData)) {
								$orderTable->getAdapter()->rollBack();
								$params = array (
						            'result'  => false,
						            'message' => '『' . $eachPackage['item_name'] . "』\n 在庫が取得できません",
						        );
								return $this->sendJson($params);
							}
							
							if ($consumeCount > (int)$stockData['last_count']) {
								$consumeCount = (int)$stockData['last_count'];
							}
							
							$stockTable->consumeStock($stockData['id'], $consumeCount);
							$itemTable->subStock('1', '1', $eachPackage['id'], $consumeCount);
							$consumptionTable->create(array(
						        'item_id'         => 0, // (廃止)
						        'warehouse_item_id' => $eachPackage['id'],     // 倉庫管理アイテムID
						        'user_id'         => $userData['id'],
								'status'          => Shared_Model_Code::STOCK_STATUS_ACTIVE,
								
								'action_date'     => $now,
								'action_code'     => Shared_Model_Code::STOCK_ACTION_SHIPMENT,
								
								'sub_count'       => $consumeCount,
								'target_stock_id' => $stockData['id'],// 対象の在庫
								
								'order_id'        => $orderData['id'],
								'memo'            => '',
				
				                'created'         => new Zend_Db_Expr('now()'),
				                'updated'         => new Zend_Db_Expr('now()'),
							));
							
							$amount = $amount - $consumeCount;
						}
					}
				}
			}

	    	// ステータスを検品済みに
	    	$orderTable->updateById($warehouseId, $orderData['id'], array(
	    		'inspection_datetime' => new Zend_Db_Expr('now()'),
	    		'inspection_user_id'  => $userData['id'],
	    		'status'              => Shared_Model_Code::SHIPMENT_STATUS_INSPECTED,
	    	));

            // commit
            $orderTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $orderTable->getAdapter()->rollBack();
            throw new Zend_Exception('/api/shipment/finish transaction failed: ' . $e);
            
        }
	            
        $params = array ('result'  => true);
            
		return $this->sendJson($params);

    }
    
    /**
     * dateListAction
     * 検品履歴日付リスト
     */
    public function dateListAction()
    {
	    $warehouseId     = '1';
		$userData        = $this->_getUserData();
		

		$orderTable = new Shared_Model_Data_Order();
		
		$selectObj  = $orderTable->getSelectObjOfInspectionHistory($warehouseId, $userData['id']);
        $items      = $selectObj->query()->fetchAll();
        
		foreach ($items as &$eachItem) {
			$eachItem['inspection_date_display'] = date('Y年m月d日', strtotime($eachItem['inspection_datetime']));
		}
    	
        $params = array (
            'result'  => true,
            'items'   => $items,
        );
            
		return $this->sendJson($params);
    }
    
    
    /**
     * historyDateItemAction
     * 日付別検品済みリスト
     */
	/*
    public function historyDateItemAction()
    {
    	$request = $this->getRequest();
    	$targetDate  = $request->getParam('target-date');
    	
    	//if (empty($targetDate)) {
    	//	throw new Zend_Exception('/api/shipment/index NO TARGET DATE');
    	//}
    	
    	$items = array();
    	
    	$items[] = array(
    		 'id'           =>  606666,
    		 'order_name'   => '山田　太郎 様',
    		 'status'       => '検品済み',
    		 'status_color' => '000000',
    	);

    	$items[] = array(
    		 'id'           =>  606666,
    		 'order_name'   => '河野　智子 様',
    		 'status'       => '保留',
    		 'status_color' => '18b516',
    	);
    	
     	$items[] = array(
    		 'id'           =>  606666,
    		 'order_name'   => '篠田　貴子 様',
    		 'status'       => '未検品',
    		 'status_color' => 'ff0000',
    	);
    	
        $params = array (
            'result'  => true,
            'items'   => $items,
        );
            
		return $this->sendJson($params);
        
    }
	*/
  
    /**
     * テキスト置換
     */
    private function replaceText($text, $orderData)
    {
    	// 名前
    	$text = str_replace('@@name@@', $orderData['name'], $text);
    	
    	return $text;
    	
    }
}
