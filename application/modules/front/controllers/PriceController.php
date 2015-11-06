<?php
/**
 * class PriceController
 */
 
class PriceController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '商品価格決定・提出見積';
		$this->view->menuCategory     = 'price';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/cost                                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原価計算                                                   |
    +----------------------------------------------------------------------------*/
    public function costAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'cost';
		$this->view->posTop = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('price_cost_4');

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
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['cost_calc_status'] = $request->getParam('cost_calc_status', '');
			$session->conditions['keyword']          = $request->getParam('keyword', '');
			
		} else {
			$session->conditions['cost_calc_status'] = '';
			$session->conditions['keyword']          = '';
			
		}
		$this->view->conditions	= $session->conditions;

		
		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        $selectObj->where('frs_item.product_name_type != ?', Shared_Model_Code::PRODUCT_NAME_TYPE_SUPPLY);

		if (!empty($session->conditions['cost_calc_status']) && $session->conditions['cost_calc_status'] !== '') {
			//var_dump($session->conditions['cost_calc_status']);exit;
			$selectObj->where('frs_item.cost_calc_status = ?', $session->conditions['cost_calc_status']);
		}
		
		if (!empty($session->conditions['keyword'])) {
			$keywordArray = array();
			
        	$columns = array(
        		'item_name', 'item_name_en',
        	);
        	
        	foreach ($columns as $each) {
        		if ($itemTable->isCryptField($each)) {   
        			$keyword = $dbAdapter->quote('%' . $session->conditions['keyword'] . '%');     			
        			$keywordArray[] = $itemTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordArray[] = $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $session->conditions['keyword'] .'%');
        		}
        	}
        	
        	//var_dump(implode(' OR ', $keywordArray));exit;
        	$selectObj->where(implode(' OR ', $keywordArray));
		}
		

		$selectObj->order('frs_item.cost_calc_updated DESC');
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/version-list                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原価計算バージョン履歴                                     |
    +----------------------------------------------------------------------------*/
    public function versionListAction()
    {
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');

		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (empty($data)) {
		
		}
		
    	$costTable = new Shared_Model_Data_Cost();
    	$this->view->items = $items = $costTable->getVersionListByItemId($id);
    	
    	if (empty($items)) {
    		// 原価データがない場合は初期バージョン作成
    		$costTable->createFirstVersion($this->_adminProperty['management_group_id'], $id);
    		$this->view->items = $costTable->getVersionListByItemId($id);
    	}
    
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/price/cost';

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/version-copy                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原価計算バージョンコピー                                   |
    +----------------------------------------------------------------------------*/
    public function versionCopyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
		$request = $this->getRequest();
		$id = $request->getParam('id'); // 商品ID
		$baseCostId = $request->getParam('base_cost_id'); // コピーバージョン

		$itemTable = new Shared_Model_Data_Item();
    	$costTable = new Shared_Model_Data_Cost();

		$copyBaseData = $costTable->getById($this->_adminProperty['management_group_id'], $baseCostId);
		

		unset($copyBaseData['id']);
		$copyBaseData['version_id']     = (int)$copyBaseData['version_id'] + 1;
    	$copyBaseData['version_status'] = Shared_Model_Code::COST_CALC_STATUS_EDITING;
    	
    	
 		$copyBaseData['cost_material_list']    = json_encode($copyBaseData['cost_material_list']);
		$copyBaseData['cost_package_list']     = json_encode($copyBaseData['cost_package_list']);
    	$copyBaseData['cost_expendable_list']  = json_encode($copyBaseData['cost_expendable_list']);
    	$copyBaseData['cost_processing_list']  = json_encode($copyBaseData['cost_processing_list']);
    	
    	
    	$copyBaseData['created_user_id']     = $this->_adminProperty['id'];
    	$copyBaseData['last_update_user_id'] = $this->_adminProperty['id'];
    	
    	$copyBaseData['created'] = new Zend_Db_Expr('now()');
    	$copyBaseData['updated'] = new Zend_Db_Expr('now()');
    	
    	$costTable->create($copyBaseData);
    	
		$this->sendJson(array('result' => 'OK'));
    	return;
    	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/cost-profit                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原価計算詳細                                               |
    +----------------------------------------------------------------------------*/
    public function costProfitAction()
    { 
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->costId      = $costId     = $request->getParam('cost_id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$costTable = new Shared_Model_Data_Cost();
		$this->view->costData = $costData = $costTable->getById($this->_adminProperty['management_group_id'], $costId);

		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/price/version-list?id=' . $id;
			$this->_helper->layout->setLayout('back_menu_competition');
	        $this->view->saveUrl = 'javascript:void(0);';
	        
	        if ($costData['version_status'] === (string)Shared_Model_Code::COST_CALC_STATUS_APPROVAL_PENDDING
	        || $costData['version_status'] === (string)Shared_Model_Code::COST_CALC_STATUS_APPROVED) {
	        	$this->view->saveUrl = NULL;
	        }
		}
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/update-cost-profit                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 販売価格 更新(Ajax)                                        |
    +----------------------------------------------------------------------------*/
    public function updateCostProfitAction()
    {	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->costId = $costId = $request->getParam('cost_id');
		
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
				$costTable = new Shared_Model_Data_Cost();
				$itemTable = new Shared_Model_Data_Item();
	            $costTable->getAdapter()->beginTransaction();

	            try {
					$data = array(
						'version_status'       => Shared_Model_Code::COST_CALC_STATUS_EDITING,
						
						'memo_profit'  => $success['memo_profit'],
						
						'column_name_1' => $success['column_name_1'],
						'column_name_2' => $success['column_name_2'],
						'column_name_3' => $success['column_name_3'],
						'column_name_4' => $success['column_name_4'],
						'column_name_5' => $success['column_name_5'],
						'column_name_6' => $success['column_name_6'],
						'column_name_7' => $success['column_name_7'],
						'column_name_8' => $success['column_name_8'],
						'column_name_9' => $success['column_name_9'],
						'column_name_10' => $success['column_name_10'],
						'column_name_11' => $success['column_name_11'],
						'column_name_12' => $success['column_name_12'],
						'column_name_13' => $success['column_name_13'],
						'column_name_14' => $success['column_name_14'],
						'column_name_15' => $success['column_name_15'],
						
						'amount_per_package_1' => $success['amount_per_package_1'],  // 商品数／件
						'amount_per_package_2' => $success['amount_per_package_2'],
						'amount_per_package_3' => $success['amount_per_package_3'],
						'amount_per_package_4' => $success['amount_per_package_4'],
						'amount_per_package_5' => $success['amount_per_package_5'],
						'amount_per_package_6' => $success['amount_per_package_6'],
						'amount_per_package_7' => $success['amount_per_package_7'],
						'amount_per_package_8' => $success['amount_per_package_8'],
						'amount_per_package_9' => $success['amount_per_package_9'],
						'amount_per_package_10' => $success['amount_per_package_10'],
						'amount_per_package_11' => $success['amount_per_package_11'],
						'amount_per_package_12' => $success['amount_per_package_12'],
						'amount_per_package_13' => $success['amount_per_package_13'],
						'amount_per_package_14' => $success['amount_per_package_14'],
						'amount_per_package_15' => $success['amount_per_package_15'],
						
						
						'sales_price' => $success['sales_price'],
						
						'customer_delivery_cost_1' => $success['customer_delivery_cost_1'],  // 顧客送料総負担額
						'customer_delivery_cost_2' => $success['customer_delivery_cost_2'],
						'customer_delivery_cost_3' => $success['customer_delivery_cost_3'],
						'customer_delivery_cost_4' => $success['customer_delivery_cost_4'],
						'customer_delivery_cost_5' => $success['customer_delivery_cost_5'],
						'customer_delivery_cost_6' => $success['customer_delivery_cost_6'],
						'customer_delivery_cost_7' => $success['customer_delivery_cost_7'],
						'customer_delivery_cost_8' => $success['customer_delivery_cost_8'],
						'customer_delivery_cost_9' => $success['customer_delivery_cost_9'],
						'customer_delivery_cost_10' => $success['customer_delivery_cost_10'],
						'customer_delivery_cost_11' => $success['customer_delivery_cost_11'],
						'customer_delivery_cost_12' => $success['customer_delivery_cost_12'],
						'customer_delivery_cost_13' => $success['customer_delivery_cost_13'],
						'customer_delivery_cost_14' => $success['customer_delivery_cost_14'],
						'customer_delivery_cost_15' => $success['customer_delivery_cost_15'],

						'tax_percentage_1' => $success['tax_percentage_1'],  // 日本消費税・関税・現地課税
						'tax_percentage_2' => $success['tax_percentage_2'],
						'tax_percentage_3' => $success['tax_percentage_3'],
						'tax_percentage_4' => $success['tax_percentage_4'],
						'tax_percentage_5' => $success['tax_percentage_5'],
						'tax_percentage_6' => $success['tax_percentage_6'],
						'tax_percentage_7' => $success['tax_percentage_7'],
						'tax_percentage_8' => $success['tax_percentage_8'],
						'tax_percentage_9' => $success['tax_percentage_9'],
						'tax_percentage_10' => $success['tax_percentage_10'],
						'tax_percentage_11' => $success['tax_percentage_11'],
						'tax_percentage_12' => $success['tax_percentage_12'],
						'tax_percentage_13' => $success['tax_percentage_13'],
						'tax_percentage_14' => $success['tax_percentage_14'],
						'tax_percentage_15' => $success['tax_percentage_15'],
						
						'discount_percentage_1' => $success['discount_percentage_1'],  // 値引率
						'discount_percentage_2' => $success['discount_percentage_2'],
						'discount_percentage_3' => $success['discount_percentage_3'],
						'discount_percentage_4' => $success['discount_percentage_4'],
						'discount_percentage_5' => $success['discount_percentage_5'],
						'discount_percentage_6' => $success['discount_percentage_6'],
						'discount_percentage_7' => $success['discount_percentage_7'],
						'discount_percentage_8' => $success['discount_percentage_8'],
						'discount_percentage_9' => $success['discount_percentage_9'],
						'discount_percentage_10' => $success['discount_percentage_10'],
						'discount_percentage_11' => $success['discount_percentage_11'],
						'discount_percentage_12' => $success['discount_percentage_12'],
						'discount_percentage_13' => $success['discount_percentage_13'],
						'discount_percentage_14' => $success['discount_percentage_14'],
						'discount_percentage_15' => $success['discount_percentage_15'],
						
						'overseas_percentage_1' => $success['overseas_percentage_1'],  // 日本消費税・関税・現地課税
						'overseas_percentage_2' => $success['overseas_percentage_2'],
						'overseas_percentage_3' => $success['overseas_percentage_3'],
						'overseas_percentage_4' => $success['overseas_percentage_4'],
						'overseas_percentage_5' => $success['overseas_percentage_5'],
						'overseas_percentage_6' => $success['overseas_percentage_6'],
						'overseas_percentage_7' => $success['overseas_percentage_7'],
						'overseas_percentage_8' => $success['overseas_percentage_8'],
						'overseas_percentage_9' => $success['overseas_percentage_9'],
						'overseas_percentage_10' => $success['overseas_percentage_10'],
						'overseas_percentage_11' => $success['overseas_percentage_11'],
						'overseas_percentage_12' => $success['overseas_percentage_12'],
						'overseas_percentage_13' => $success['overseas_percentage_13'],
						'overseas_percentage_14' => $success['overseas_percentage_14'],
						'overseas_percentage_15' => $success['overseas_percentage_15'],
						
						'last_update_user_id'  => $this->_adminProperty['id'],
					);

					$costTable->updateById($costId, $data);
					
					$itemData = array(
						'cost_calc_status'  => Shared_Model_Code::COST_CALC_STATUS_EDITING,
						'cost_calc_updated' => new Zend_Db_Expr('now()'), // 原価計算更新日時
					);
					
					$itemTable->updateById($id, $itemData);
						
	                // commit
	                $costTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price/update-cost-profit transaction failed: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/cost-manufacture                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造原価                                                   |
    +----------------------------------------------------------------------------*/
    public function costManufactureAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->costId      = $costId     = $request->getParam('cost_id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');

		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

		$costTable = new Shared_Model_Data_Cost();
		$this->view->costData = $costData = $costTable->getById($this->_adminProperty['management_group_id'], $costId);
		
		
		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/price/version-list?id=' . $id;
			$this->_helper->layout->setLayout('back_menu_competition');
	        $this->view->saveUrl = 'javascript:void(0);';
	        
	        if ($costData['version_status'] === (string)Shared_Model_Code::COST_CALC_STATUS_APPROVAL_PENDDING
	        || $costData['version_status'] === (string)Shared_Model_Code::COST_CALC_STATUS_APPROVED) {
	        	$this->view->saveUrl = NULL;
	        }
		}
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/update-cost-manufacture                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造原価 更新(Ajax)                                        |
    +----------------------------------------------------------------------------*/
    public function updateCostManufactureAction()
    {	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$costId = $request->getParam('cost_id');
		
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
				$materialList = array();
	            if (!empty($success['cost_material_list'])) {
	            	$materialIdList = explode(',', $success['cost_material_list']);
	            	
		            foreach ($materialIdList as $eachId) {
		                $materialList[] = array(
							'id'                              => $eachId,
							'material_title'                  => $request->getParam($eachId . '_material_title'),
							'material_cost_per_item'          => $request->getParam($eachId . '_hidden_material_cost_per_item'),
							'material_total_cost'             => $request->getParam($eachId . '_material_total_cost'),
							'material_unit_price'             => $request->getParam($eachId . '_material_unit_price'),
							'material_amount'                 => $request->getParam($eachId . '_material_amount'),
							'material_amount_unit'            => $request->getParam($eachId . '_material_amount_unit'),
							'material_loss_rate'              => $request->getParam($eachId . '_material_loss_rate'),
							'material_amount_per_item'        => $request->getParam($eachId . '_material_amount_per_item'),

							'material_connection_id'          => $request->getParam($eachId . '_material_connection_id'),
							'material_product_id'             => $request->getParam($eachId . '_material_product_id'),
		                );
		            }
	            }
				

				$packageList = array();
	            if (!empty($success['cost_package_list'])) {
	            	$packageIdList = explode(',', $success['cost_package_list']);
	            	
		            foreach ($packageIdList as $eachId) {
		                $packageList[] = array(
							'id'                             => $eachId,
							'package_title'                  => $request->getParam($eachId . '_package_title'),
							'package_cost_per_item'          => $request->getParam($eachId . '_hidden_package_cost_per_item'),
							'package_total_cost'             => $request->getParam($eachId . '_package_total_cost'),
							'package_unit_price'             => $request->getParam($eachId . '_package_unit_price'),
							'package_amount'                 => $request->getParam($eachId . '_package_amount'),
							'package_amount_unit'            => $request->getParam($eachId . '_package_amount_unit'),
							'package_loss_rate'              => $request->getParam($eachId . '_package_loss_rate'),
							'package_amount_per_item'        => $request->getParam($eachId . '_package_amount_per_item'),
							
							'package_connection_id'          => $request->getParam($eachId . '_package_connection_id'),
							'package_fixture_id'             => $request->getParam($eachId . '_package_fixture_id'),
		                );
		            }
	            }

				
				$expendableList = array();
	            if (!empty($success['cost_expendable_list'])) {
	            	$expendableIdList = explode(',', $success['cost_expendable_list']);
	            	
		            foreach ($expendableIdList as $eachId) {
		                $expendableList[] = array(
							'id'                              => $eachId,
							'expendable_title'                  => $request->getParam($eachId . '_expendable_title'),
							'expendable_cost_per_item'          => $request->getParam($eachId . '_hidden_expendable_cost_per_item'),
							'expendable_total_cost'             => $request->getParam($eachId . '_expendable_total_cost'),
							'expendable_amount'                 => $request->getParam($eachId . '_expendable_amount'),
							'expendable_amount_unit'            => $request->getParam($eachId . '_expendable_amount_unit'),
							'expendable_loss_rate'              => $request->getParam($eachId . '_expendable_loss_rate'),
							'expendable_amount_per_item'        => $request->getParam($eachId . '_expendable_amount_per_item'),	
							
							'expendable_connection_id'          => $request->getParam($eachId . '_expendable_connection_id'),
							'expendable_fixture_id'             => $request->getParam($eachId . '_expendable_fixture_id'),
		                );
		            }
	            }

				$processingList = array();
	            if (!empty($success['cost_processing_list'])) {
	            	$processingIdList = explode(',', $success['cost_processing_list']);
	            	
		            foreach ($processingIdList as $eachId) {
		                $processingList[] = array(
							'id'                              => $eachId,
							'processing_type'                 => $request->getParam($eachId . '_processing_type'),
							'processing_title'                => $request->getParam($eachId . '_processing_title'),
							'processing_cost_per_item'        => $request->getParam($eachId . '_hidden_processing_cost_per_item'),
							'processing_unit_price'           => $request->getParam($eachId . '_processing_unit_price'),
							'processing_hourly'               => $request->getParam($eachId . '_processing_hourly'),
							'processing_loss_rate'            => $request->getParam($eachId . '_processing_loss_rate'),
							'processing_min_per_item'         => $request->getParam($eachId . '_processing_min_per_item'),
							
							'processing_connection_id'        => $request->getParam($eachId . '_processing_connection_id'),
							'processing_production_id'        => $request->getParam($eachId . '_processing_production_id'),
		                );
		            }
	            }
	            
				$costTable = new Shared_Model_Data_Cost();
				$itemTable = new Shared_Model_Data_Item();
	            $costTable->getAdapter()->beginTransaction();

	            try {
					$data = array(
						'version_status'                  => Shared_Model_Code::COST_CALC_STATUS_EDITING,
						
						'memo_manufacture'  => $success['memo_manufacture'],
						
						'summary_manufacture_total_cost'  => $success['summary_manufacture_total_cost'],  // 製造原価
						'summary_material_cost'           => $success['summary_material_cost'],           // A. 原料・製品調達費
						'summary_package_cost'            => $success['summary_package_cost'],            // B. 資材費
						'summary_expendable_cost'         => $success['summary_expendable_cost'],         // C. 消耗品費
						'summary_processing_cost'         => $success['summary_processing_cost'],         // D. 加工費用
						
						'cost_material_list'              => json_encode($materialList),                  // 原料・製品調達費 
						'cost_package_list'               => json_encode($packageList),                   // 資材費 
						'cost_expendable_list'            => json_encode($expendableList),                // 消耗品費 
						'cost_processing_list'            => json_encode($processingList),                // 加工費用
						
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$costTable->updateById($costId, $data);
					
					$itemData = array(
						'cost_calc_status'  => Shared_Model_Code::COST_CALC_STATUS_EDITING,
						'cost_calc_updated' => new Zend_Db_Expr('now()'), // 原価計算更新日時
					);
					
					$itemTable->updateById($id, $itemData);
						
	                // commit
	                $costTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price/update-cost-manufacture transaction failed: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/cost-shipping                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 送料                                                       |
    +----------------------------------------------------------------------------*/
    public function costShippingAction()
    {        
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->costId      = $costId     = $request->getParam('cost_id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');

		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

		$costTable = new Shared_Model_Data_Cost();
		$this->view->costData = $costData = $costTable->getById($this->_adminProperty['management_group_id'], $costId);
		
		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/price/version-list?id=' . $id;
			$this->_helper->layout->setLayout('back_menu_competition');
	        $this->view->saveUrl = 'javascript:void(0);';
	        
	        if ($costData['version_status'] === (string)Shared_Model_Code::COST_CALC_STATUS_APPROVAL_PENDDING
	        || $costData['version_status'] === (string)Shared_Model_Code::COST_CALC_STATUS_APPROVED) {
	        	$this->view->saveUrl = NULL;
	        }
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/update-cost-shipping                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発送費用（通販前提） 更新(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function updateCostShippingAction()
    {	
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->costId = $costId = $request->getParam('cost_id');
		
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
				$costTable = new Shared_Model_Data_Cost();
				$itemTable = new Shared_Model_Data_Item();
	            $costTable->getAdapter()->beginTransaction();

	            try {
					$data = array(
						'version_status'       => Shared_Model_Code::COST_CALC_STATUS_EDITING,
						
						'memo_shipping'  => $success['memo_shipping'],
						
						// 項目名
						'column_name_1'  => $success['column_name_1'],
						'column_name_2'  => $success['column_name_2'],
						'column_name_3'  => $success['column_name_3'],
						'column_name_4'  => $success['column_name_4'],
						'column_name_5'  => $success['column_name_5'],
						'column_name_6'  => $success['column_name_6'],
						'column_name_7'  => $success['column_name_7'],
						'column_name_8'  => $success['column_name_8'],
						'column_name_9'  => $success['column_name_9'],
						'column_name_10' => $success['column_name_10'],
						'column_name_11' => $success['column_name_11'],
						'column_name_12' => $success['column_name_12'],
						'column_name_13' => $success['column_name_13'],
						'column_name_14' => $success['column_name_14'],
						'column_name_15' => $success['column_name_15'],
						
						// 1件当たりの輸送個数
						'amount_per_package_1'  => $success['amount_per_package_1'],
						'amount_per_package_2'  => $success['amount_per_package_2'],
						'amount_per_package_3'  => $success['amount_per_package_3'],
						'amount_per_package_4'  => $success['amount_per_package_4'],
						'amount_per_package_5'  => $success['amount_per_package_5'],
						'amount_per_package_6'  => $success['amount_per_package_6'],
						'amount_per_package_7'  => $success['amount_per_package_7'],
						'amount_per_package_8'  => $success['amount_per_package_8'],
						'amount_per_package_9'  => $success['amount_per_package_9'],
						'amount_per_package_10' => $success['amount_per_package_10'],
						'amount_per_package_11' => $success['amount_per_package_11'],
						'amount_per_package_12' => $success['amount_per_package_12'],
						'amount_per_package_13' => $success['amount_per_package_13'],
						'amount_per_package_14' => $success['amount_per_package_14'],
						'amount_per_package_15' => $success['amount_per_package_15'],

						// 輸送費原単位名
						'cost_postage_id_1'    => $success['cost_postage_id_1'],
						'cost_postage_id_2'    => $success['cost_postage_id_2'],
						'cost_postage_id_3'    => $success['cost_postage_id_3'],
						'cost_postage_id_4'    => $success['cost_postage_id_4'],
						'cost_postage_id_5'    => $success['cost_postage_id_5'],
						'cost_postage_id_6'    => $success['cost_postage_id_6'],
						'cost_postage_id_7'    => $success['cost_postage_id_7'],
						'cost_postage_id_8'    => $success['cost_postage_id_8'],
						'cost_postage_id_9'    => $success['cost_postage_id_9'],
						'cost_postage_id_10'   => $success['cost_postage_id_10'],
						'cost_postage_id_11'   => $success['cost_postage_id_11'],
						'cost_postage_id_12'   => $success['cost_postage_id_12'],
						'cost_postage_id_13'   => $success['cost_postage_id_13'],
						'cost_postage_id_14'   => $success['cost_postage_id_14'],
						'cost_postage_id_15'   => $success['cost_postage_id_15'],
		
						// 梱包仕様名(原単位名)
						'cost_package_id_1'    => $success['cost_package_id_1'],
						'cost_package_id_2'    => $success['cost_package_id_2'],
						'cost_package_id_3'    => $success['cost_package_id_3'],
						'cost_package_id_4'    => $success['cost_package_id_4'],
						'cost_package_id_5'    => $success['cost_package_id_5'],
						'cost_package_id_6'    => $success['cost_package_id_6'],
						'cost_package_id_7'    => $success['cost_package_id_7'],
						'cost_package_id_8'    => $success['cost_package_id_8'],
						'cost_package_id_9'    => $success['cost_package_id_9'],
						'cost_package_id_10'   => $success['cost_package_id_10'],
						'cost_package_id_11'   => $success['cost_package_id_11'],
						'cost_package_id_12'   => $success['cost_package_id_12'],
						'cost_package_id_13'   => $success['cost_package_id_13'],
						'cost_package_id_14'   => $success['cost_package_id_14'],
						'cost_package_id_15'   => $success['cost_package_id_15'],

						'last_update_user_id'  => $this->_adminProperty['id'],
					);

					$costTable->updateById($costId, $data);
					
					$itemData = array(
						'cost_calc_status'  => Shared_Model_Code::COST_CALC_STATUS_EDITING,
						'cost_calc_updated' => new Zend_Db_Expr('now()'), // 原価計算更新日時
					);
					
					$itemTable->updateById($id, $itemData);
						
	                // commit
	                $costTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price/update-cost-manufacture transaction failed: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/apply-apploval                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認申請                                                   |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$costId     = $request->getParam('cost_id');

		// POST送信時
		if ($request->isPost()) {
			$itemTable     = new Shared_Model_Data_Item();
			$costTable     = new Shared_Model_Data_Cost();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
			$costData = $costTable->getById($this->_adminProperty['management_group_id'], $costId);

			try {
				$costTable->getAdapter()->beginTransaction();
				
				$itemTable->updateById($id, array(
					'cost_calc_status' => Shared_Model_Code::COST_CALC_STATUS_APPROVAL_PENDDING,
				));
				
				$costTable->updateById($costId, array(
					'version_status' => Shared_Model_Code::COST_CALC_STATUS_APPROVAL_PENDDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_COST,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'], // 申請者ユーザーID
					
					'target_id'             => $costId,
					
					'title'                 => '「' . $itemData['item_name'] . '」 バージョン' . $costData['version_id'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);
				

				// メール送信 -------------------------------------------------------
				$content = "商品名：\n" . $itemData['item_name'] . "\n\n"
				         . "バージョン：\n" . $costData['version_id'];
				
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
                $costTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $costTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price/apply-apploval transaction faied: ' . $e);
                
            }
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;

		}
		
		$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
	    $this->sendJson($result);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/mod-request                                         |
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
		$costId     = $request->getParam('cost_id');
		$approvalComment = $request->getParam('approval_comment');

		// POST送信時
		if ($request->isPost()) {
			$itemTable     = new Shared_Model_Data_Item();
			$costTable     = new Shared_Model_Data_Cost();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
	        $itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
	        $costData = $costTable->getById($this->_adminProperty['management_group_id'], $costId);
		
			try {
				$costTable->getAdapter()->beginTransaction();
				
				$itemTable->updateById($id, array(
					'cost_calc_status' => Shared_Model_Code::COST_CALC_STATUS_EDITING,
				));
				
				$costTable->updateById($costId, array(
					'version_status' => Shared_Model_Code::COST_CALC_STATUS_EDITING,
					'approval_comment' => $approvalComment,
				));
				
				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
				$content = "商品名：\n" . $itemData['item_name'] . "\n\n"
				         . "バージョン：\n" . $costData['version_id'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/price/cost-profit?id=' . $id . '&cost_id=' . $costId;
	        
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
                $costTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $costTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price/mod-request transaction faied: ' . $e);
                
            }
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price/approve                                             |
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
		$costId     = $request->getParam('cost_id');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$itemTable     = new Shared_Model_Data_Item();
			$costTable     = new Shared_Model_Data_Cost();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
	        $itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
	        $costData = $costTable->getById($this->_adminProperty['management_group_id'], $costId);

			try {
				$costTable->getAdapter()->beginTransaction();

				$itemTable->updateById($id, array(
					'cost_calc_status' => Shared_Model_Code::COST_CALC_STATUS_APPROVED,
				));
				
				$costTable->updateById($costId, array(
					'version_status' => Shared_Model_Code::COST_CALC_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status' => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));
				
				// メール送信 -------------------------------------------------------
				$content = "商品名：\n" . $itemData['item_name'] . "\n\n"
				         . "バージョン：\n" . $costData['version_id'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/price/cost-profit?id=' . $id . '&cost_id=' . $costId;
				
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
                $costTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $costTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price/approve transaction faied: ' . $e);   
            }
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }    
}

