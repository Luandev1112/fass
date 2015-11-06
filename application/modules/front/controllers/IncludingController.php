<?php
/**
 * class IncludingController
 */
 
class IncludingController extends Front_Model_Controller
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
		$this->view->menu             = 'including';
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
    }
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/old                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱施策一覧                                               |
    +----------------------------------------------------------------------------*/
    public function oldAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$planTable = new Shared_Model_Data_IncludingPlan();
		
		$dbAdapter = $planTable->getAdapter();

        $selectObj = $planTable->select();
		$selectObj->order('id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
        $itemTable = new Shared_Model_Data_Item();
        
		// 商品リスト(選択用)
        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        $selectObj->order('frs_item.id DESC');
        $productItemList = $selectObj->query()->fetchAll();
		
		$newProductItemList = array();
		foreach ($productItemList as $eachProductItem) {
			$newProductItemList[$eachProductItem['id']] = $eachProductItem;
		}
		
		$this->view->productItems = $newProductItemList;

		// 同梱品リスト
		$includingList = $itemTable->getItemList(Shared_Model_Code::ITEM_TYPE_INCLUDING);
		$newIncludingList = array();
		foreach ($includingList as $eachIncluding) {
			$newIncludingList[$eachIncluding['id']] = $eachIncluding;
		}

		$this->view->includingItemList = $newIncludingList;
    }
       
    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/index                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱施策一覧                                               |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$planTable = new Shared_Model_Data_IncludingPlan();
		
		$dbAdapter = $planTable->getAdapter();

        $selectObj = $planTable->select();
        $selectObj->where('frs_including_plan.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_including_plan.status != ?', Shared_Model_Code::INCLUDING_PLAN_STATUS_REMOVE);
		$selectObj->order('frs_including_plan.id ASC');
		
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
    |  action_URL    * /including/add                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱施策新規登録                                           |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/add-post                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱施策新規登録(Ajax)                                     |
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

                if (!empty($errorMessage['name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'error' => '「施策名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$planTable = new Shared_Model_Data_IncludingPlan();
				
				// 新規登録
				$data = array(
					'status'        => Shared_Model_Code::INCLUDING_PLAN_STATUS_ACTIVE,
					
					'warehouse_id'  => $this->_warehouseSession->warehouseId,
					'name'          => $success['name'],

	                'created'       => new Zend_Db_Expr('now()'),
	                'updated'       => new Zend_Db_Expr('now()'),
				);
				
				if (!empty($success['start_date'])) {
					$data['start_date'] = $success['start_date'];
					$data['end_date']   = $success['end_date'];
				}

				$planTable->getAdapter()->beginTransaction();
            	  
	            try {
					$planTable->create($data);
					
	                // commit
	                $planTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $planTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/including/add-post transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
    	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/detail                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱施策詳細                                               |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/including';
    
		$request    = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$itemTable = new Shared_Model_Data_WarehouseItem();
		$planTable = new Shared_Model_Data_IncludingPlan();
		
		// 同梱施策
		$this->view->data = $planTable->getById($this->_warehouseSession->warehouseId, $id);
		
		// 商品リスト(選択用)
        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));

        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        $selectObj->order('frs_warehouse_item.id DESC');
        $productItemList = $selectObj->query()->fetchAll();
		
		$newProductItemList = array();
		foreach ($productItemList as $eachProductItem) {
			$newProductItemList[$eachProductItem['id']] = $eachProductItem;
		}
		
		$this->view->productItems = $newProductItemList;

		// 同梱品リスト
        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));

        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_INCLUDING);
        $selectObj->order('frs_warehouse_item.id DESC');
        
		$includingList = $selectObj->query()->fetchAll();

		$newIncludingList = array();
		foreach ($includingList as $eachIncluding) {
			$newIncludingList[$eachIncluding['id']] = $eachIncluding;
		}

		$this->view->includingItemList = $newIncludingList;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/update-basic                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報                                                   |
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

                if (!empty($errorMessage['condition_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「施策名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else { 
				$planTable = new Shared_Model_Data_IncludingPlan();
				
				$data = array(
					'status'         => $success['status'],
					'name'           => $success['name'],
					'shelf_no'       => $success['shelf_no'],
					'term_type'      => $success['term_type'],
					'start_date'     => NULL,
					'end_date'       => NULL,
				);
				
				if (!empty($success['start_date'])) {
					$data['start_date'] = str_replace('/', '-', $success['start_date']);
				}
				
				if (!empty($success['end_date'])) {
					$data['end_date'] = str_replace('/', '-', $success['end_date']);
				}

	            $planTable->updateById($this->_warehouseSession->warehouseId, $id, $data);
			}

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/update-condition                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 条件更新                                                   |
    +----------------------------------------------------------------------------*/
    public function updateConditionAction()
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

                if (!empty($errorMessage['condition_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「同梱条件」を選択してください'));
                    return;
                } else if (!empty($errorMessage['condition_subscription_start']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「開始回数」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['condition_subscription_end']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「終了回数」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['condition_subscription_intervals']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「間隔(回ごと)」は半角数字のみで入力してください'));
                    return;
                }
                  
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$conditionItemIds = array();
				
                if ($success['condition_type'] == Shared_Model_Code::INCLUDING_PLAN_CONDITION_TYPE_ORDER_ITEM) {
					$itemList = explode(',', $success['item_list']);				
					
					$items = array();
					$count = 1;
		            foreach ($itemList as $eachItem) {
		            	$conditionItemId = $request->getParam($eachItem . '_condition_item_id');
	                    if (empty($conditionItemId)) {
		                    $this->sendJson(array('result' => 'NG', 'message' => '「商品名」を選択してください'));
		                    return;
	                    }
	                    
		                $conditionItemIds[] = array(
		                	'id'                => $count++,
							'condition_item_id' => $request->getParam($eachItem . '_condition_item_id'),
		                );
		            }
		            
                }

                if ($success['condition_type'] == Shared_Model_Code::INCLUDING_PLAN_CONDITION_TYPE_SUBSCRIPTION_ORDER) {
                    if (empty($success['condition_subscription_intervals'])) {
	                    $this->sendJson(array('result' => 'NG', 'message' => '「間隔(回ごと)」を入力してください'));
	                    return;
                    }
	             	if ((int)$success['condition_subscription_end'] < (int)$success['condition_subscription_start']) {
	                    $this->sendJson(array('result' => 'NG', 'message' => '「終了回数」は「開始回数」をより大きい数字を指定してください'));
	                    return;
	                } 
                }
                
				$planTable = new Shared_Model_Data_IncludingPlan();
				
	            $planTable->updateById($this->_warehouseSession->warehouseId, $id, array(
					'condition_type'                    => $success['condition_type'],                     // 条件種別
					'condition_item_ids'                => json_encode($conditionItemIds),                 // 条件 対象商品ID
					'condition_subscription_start'      => $success['condition_subscription_start'],       // 条件 定期開始回数
					'condition_subscription_end'        => $success['condition_subscription_end'],         // 条件 定期終了回数
					'condition_subscription_intervals'  => $success['condition_subscription_intervals'],   // 条件 定期間隔
	            ));
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /including/update-items                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱品リスト更新                                           |
    +----------------------------------------------------------------------------*/
    public function updateItemsAction()
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

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$planTable = new Shared_Model_Data_IncludingPlan();
				
				$itemList = explode(',', $success['item_list']);				
				
				$items = array();
				$count = 1;
	            foreach ($itemList as $eachItem) {
	            	$eachItemId     = $request->getParam($eachItem . '_including_item_id');
	            	$eachItemAmount = $request->getParam($eachItem . '_including_item_amount');
                    if (empty($eachItemId)) {
	                    $result['result'] = 'NG';
	                    $result['message'] = $count . '行目：「同梱品」を選択してください';
	                    $this->sendJson($result);
	                    return;
                    } else if (empty($eachItemAmount)) {
	                    $result['result'] = 'NG';
	                    $result['message'] = $count . '行目：「数量」を入力してください';
	                    $this->sendJson($result);
	                    return;
                    }
	                $items[] = array(
	                	'id'            => $count++,
						'item_id'       => $request->getParam($eachItem . '_including_item_id'),
						'item_amount'   => $request->getParam($eachItem . '_including_item_amount'),
						'item_unique'   => $request->getParam($eachItem . '_including_item_unique'),
	                );
	            }
	            
	            $planTable->updateById($this->_warehouseSession->warehouseId, $id, array(
	            	'including_items' => json_encode($items),
	            ));
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
}

