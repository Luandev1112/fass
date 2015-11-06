<?php
/**
 * class PriceExpenseController
 */
 
class PriceExpenseController extends Front_Model_Controller
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
		$this->view->menu = 'expense';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/package                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費                                  |
    +----------------------------------------------------------------------------*/
    public function packageAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$costPackageTable = new Shared_Model_Data_CostPackage();
		
		$dbAdapter = $costPackageTable->getAdapter();

        $selectObj = $costPackageTable->select();
		//$selectObj->joinLeft('frs_connection as connection1', 'frs_cost_package.client_connection_id = connection1.id', array($costPackageTable->aesdecrypt('connection1.company_name', false) . 'AS client_name'));
        //$selectObj->joinLeft('frs_connection as connection2', 'frs_cost_package.delivery_connection_id = connection2.id', array($costPackageTable->aesdecrypt('connection2.company_name', false) . 'AS delivery_name'));
		
		$selectObj->where('frs_cost_package.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->order('frs_cost_package.id DESC');
		
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
    |  action_URL    * /price-expense/package-select                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費                                  |
    +----------------------------------------------------------------------------*/
    public function packageSelectAction()
    {
		$this->_helper->layout->setLayout('blank');
		
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$costPackageTable = new Shared_Model_Data_CostPackage();
		
		$dbAdapter = $costPackageTable->getAdapter();

        $selectObj = $costPackageTable->select();
		//$selectObj->joinLeft('frs_connection as connection1', 'frs_cost_package.client_connection_id = connection1.id', array($costPackageTable->aesdecrypt('connection1.company_name', false) . 'AS client_name'));
        //$selectObj->joinLeft('frs_connection as connection2', 'frs_cost_package.delivery_connection_id = connection2.id', array($costPackageTable->aesdecrypt('connection2.company_name', false) . 'AS delivery_name'));
		
		$selectObj->where('frs_cost_package.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->order('frs_cost_package.id DESC');
		
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
    |  action_URL    * /price-expense/package-add                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費 新規登録                         |
    +----------------------------------------------------------------------------*/
    public function packageAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/package-add-post                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費 - 新規登録(Ajax)                 |
    +----------------------------------------------------------------------------*/
    public function packageAddPostAction()
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

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「名称」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costPackageTable = new Shared_Model_Data_CostPackage();
				
				// 新規登録	            
	            $costPackageTable->getAdapter()->beginTransaction();
            	
	            try {
	            	$packageCostList = array();
		            if (!empty($success['package_cost_list'])) {
		            	$packageCostIdList = explode(',', $success['package_cost_list']);
		            	$count = 1;
			            foreach ($packageCostIdList as $eachId) {
			                $packageCostList[] = array(
			                	'id'                       => $count,
			                	'package_title'            => $request->getParam($eachId . '_package_title'),
			                	'package_cost_per_item'    => $request->getParam($eachId . '_hidden_package_cost_per_item'),
			                	'package_total_cost'       => $request->getParam($eachId . '_package_total_cost'),
			                	'package_amount'           => $request->getParam($eachId . '_package_amount'),
			                	'package_amount_unit'      => $request->getParam($eachId . '_package_amount_unit'),
			                	'package_loss_rate'        => $request->getParam($eachId . '_package_loss_rate'),
			                	'package_amount_per_item'  => $request->getParam($eachId . '_package_amount_per_item'),
			                	'package_connection_id'    => $request->getParam($eachId . '_package_connection_id'),
			                	'package_supply_id'        => $request->getParam($eachId . '_package_supply_id'),
			                );
			                $count++;
			            }
		            }
	
	            	$operationCostList = array();
		            if (!empty($success['operation_cost_list'])) {
		            	$operationCostIdList = explode(',', $success['operation_cost_list']);
		            	$count = 1;
			            foreach ($operationCostIdList as $eachId) {
			                $operationCostList[] = array(
			                	'id'                       => $count,
			                	'operation_title'          => $request->getParam($eachId . '_operation_title'),
			                	'operation_cost_per_item'  => $request->getParam($eachId . '_hidden_operation_cost_per_item'),
			                	'operation_total_cost'     => $request->getParam($eachId . '_operation_total_cost'),
			                	'operation_amount'         => $request->getParam($eachId . '_operation_amount'),
			                	'operation_amount_unit'    => $request->getParam($eachId . '_operation_amount_unit'),
			                	'operation_loss_rate'      => $request->getParam($eachId . '_operation_loss_rate'),
			                	'operation_amount_per_item'=> $request->getParam($eachId . '_operation_amount_per_item'),
			                );
			                $count++;
			            }
		            }

					$data = array(
				        'management_group_id'      => $this->_adminProperty['management_group_id'],
				        'display_id'               => '',
						'status'                   => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						
						'title'                    => $success['title'],                // 案件名
						'total'                    => $success['total'],                // 梱包資材・作業費 合計
						
						'package_cost'             => $success['package_cost'],         // 梱包資材 合計
						'package_cost_list'        => json_encode($packageCostList),    // 梱包資材項目リスト
						
						'operation_cost'           => $success['operation_cost'],       // 作業費 合計
						'operation_cost_list'      => json_encode($operationCostList),  // 作業費項目リスト
						
						'memo'                     => $success['memo'],                // メモ
						
		                'created'                  => new Zend_Db_Expr('now()'),
		                'updated'                  => new Zend_Db_Expr('now()'),
					);

					$costPackageTable->create($data);
					$id = $costPackageTable->getLastInsertedId('id');
					
	                // commit
	                $costPackageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPackageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/package-add-post transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/package-detail                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費 詳細                             |
    +----------------------------------------------------------------------------*/
    public function packageDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		
		$costPackageTable = new Shared_Model_Data_CostPackage();
		
		$this->view->data = $data = $costPackageTable->getById($this->_adminProperty['management_group_id'], $id);

		//$this->view->clientData     = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['client_connection_id']);
		//$this->view->deliveryData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['delivery_connection_id']);

		if (empty($direct)) {
			$this->view->backUrl = '/price-expense/package';
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/package-update                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費 更新(Ajax)                       |
    +----------------------------------------------------------------------------*/
    public function packageUpdateAction()
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

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「名称」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costPackageTable = new Shared_Model_Data_CostPackage();
				
				// 新規登録	            
	            $costPackageTable->getAdapter()->beginTransaction();
            	
	            try {
	            	$packageCostList = array();
		            if (!empty($success['package_cost_list'])) {
		            	$packageCostIdList = explode(',', $success['package_cost_list']);
		            	$count = 1;
			            foreach ($packageCostIdList as $eachId) {
			                $packageCostList[] = array(
			                	'id'                       => $count,
			                	'package_title'            => $request->getParam($eachId . '_package_title'),
			                	'package_cost_per_item'    => $request->getParam($eachId . '_hidden_package_cost_per_item'),
			                	'package_total_cost'       => $request->getParam($eachId . '_package_total_cost'),
			                	'package_amount'           => $request->getParam($eachId . '_package_amount'),
			                	'package_amount_unit'      => $request->getParam($eachId . '_package_amount_unit'),
			                	'package_loss_rate'        => $request->getParam($eachId . '_package_loss_rate'),
			                	'package_amount_per_item'  => $request->getParam($eachId . '_package_amount_per_item'),
			                	'package_connection_id'    => $request->getParam($eachId . '_package_connection_id'),
			                	'package_supply_id'        => $request->getParam($eachId . '_package_supply_id'),
			                );
			                $count++;
			            }
		            }
	
	            	$operationCostList = array();
		            if (!empty($success['operation_cost_list'])) {
		            	$operationCostIdList = explode(',', $success['operation_cost_list']);
		            	$count = 1;
			            foreach ($operationCostIdList as $eachId) {
			                $operationCostList[] = array(
			                	'id'                       => $count,
			                	'operation_title'          => $request->getParam($eachId . '_operation_title'),
			                	'operation_cost_per_item'  => $request->getParam($eachId . '_hidden_operation_cost_per_item'),
			                	'operation_total_cost'     => $request->getParam($eachId . '_operation_total_cost'),
			                	'operation_amount'         => $request->getParam($eachId . '_operation_amount'),
			                	'operation_amount_unit'    => $request->getParam($eachId . '_operation_amount_unit'),
			                	'operation_loss_rate'      => $request->getParam($eachId . '_operation_loss_rate'),
			                	'operation_amount_per_item'=> $request->getParam($eachId . '_operation_amount_per_item'),
			                	'operation_worker'         => $request->getParam($eachId . '_operation_worker'),
			                );
			                $count++;
			            }
		            }

					$data = array(						
						'title'                    => $success['title'],                // 案件名
						'total'                    => $success['total'],                // 梱包資材・作業費 合計
						
						'package_cost'             => $success['package_cost'],         // 梱包資材 合計
						'package_cost_list'        => json_encode($packageCostList),    // 梱包資材項目リスト
						
						'operation_cost'           => $success['operation_cost'],       // 作業費 合計
						'operation_cost_list'      => json_encode($operationCostList),  // 作業費項目リスト
					);

					$costPackageTable->updateById($id, $data);
					
	                // commit
	                $costPackageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPackageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/package-update transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/package-update-memo                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費 メモ更新(Ajax)                   |
    +----------------------------------------------------------------------------*/
    public function packageUpdateMemoAction()
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
				$costPackageTable = new Shared_Model_Data_CostPackage();
				
				// 新規登録	            
	            $costPackageTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(						
						'memo' => $success['memo'],                // メモ
					);

					$costPackageTable->updateById($id, $data);
					
	                // commit
	                $costPackageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPackageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/package-update-memo transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料                                              |
    +----------------------------------------------------------------------------*/
    public function postageAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');

		$costPostageTable = new Shared_Model_Data_CostPostage();
		
		$dbAdapter = $costPostageTable->getAdapter();

        $selectObj = $costPostageTable->select();
		//$selectObj->joinLeft('frs_connection as connection1', 'frs_cost_package.client_connection_id = connection1.id', array($costPackageTable->aesdecrypt('connection1.company_name', false) . 'AS client_name'));
        //$selectObj->joinLeft('frs_connection as connection2', 'frs_cost_package.delivery_connection_id = connection2.id', array($costPackageTable->aesdecrypt('connection2.company_name', false) . 'AS delivery_name'));

		$selectObj->where('frs_cost_postage.management_group_id = ?', $this->_adminProperty['management_group_id']);

		$selectObj->order('frs_cost_postage.id DESC');
		
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
    |  action_URL    * /price-expense/postage-select                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 梱包資材・作業費                                  |
    +----------------------------------------------------------------------------*/
    public function postageSelectAction()
    {
		$this->_helper->layout->setLayout('blank');
		
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$costPostageTable = new Shared_Model_Data_CostPostage();
		
		$dbAdapter = $costPostageTable->getAdapter();

        $selectObj = $costPostageTable->select();
		//$selectObj->joinLeft('frs_connection as connection1', 'frs_cost_package.client_connection_id = connection1.id', array($costPackageTable->aesdecrypt('connection1.company_name', false) . 'AS client_name'));
        //$selectObj->joinLeft('frs_connection as connection2', 'frs_cost_package.delivery_connection_id = connection2.id', array($costPackageTable->aesdecrypt('connection2.company_name', false) . 'AS delivery_name'));
		
		$selectObj->where('frs_cost_postage.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->order('frs_cost_postage.id DESC');
		
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
    |  action_URL    * /price-expense/postage-add                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 新規登録                                     |
    +----------------------------------------------------------------------------*/
    public function postageAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage-add-post                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 - 新規登録(Ajax)                             |
    +----------------------------------------------------------------------------*/
    public function postageAddPostAction()
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

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「名称」を入力してください'));
                    return;
                } else if (!empty($errorMessage['size']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「標準サイズ(cm)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['country']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「国」を入力してください'));
                    return;
                } else if (!empty($errorMessage['supply_fixture_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「使用輸送箱資材」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['standard_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「原価計算用一律送料」を選択してください'));
                    return;
                } else if (!empty($errorMessage['standard_price']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「原価計算用一律送料」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['minimum_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 下限」を入力してください'));
                    return;
                } else if (!empty($errorMessage['minimum_price']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 下限」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['max_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 上限」を入力してください'));
                    return;
                } else if (!empty($errorMessage['max_price']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 上限」は半角数字のみで入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「委託先 - 取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['supply_subcontracting_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「委託先 - 業務委託ID」を選択してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costPostageTable = new Shared_Model_Data_CostPostage();
				
				// 新規登録	            
	            $costPostageTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
				        'management_group_id'      => $this->_adminProperty['management_group_id'],
				        'display_id'               => '',
						'status'                   => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						
						'title'                    => $success['title'],                        // 名称
						'description'              => $success['description'],                  // 内容
						'size'                     => $success['size'],                         // 標準サイズ(cm)
						'country'                  => $success['country'],                      // 国
						
						'supply_fixture_id'        => $success['supply_fixture_id'],            // 使用輸送箱資材
						'supply_subcontracting_id' => $success['supply_subcontracting_id'],     // 調達管理ID
						'connection_id'            => $success['connection_id'],                // 取引先
				
						'standard_price'           => $success['standard_price'],               // 原価計算用一律送料
						'minimum_price'            => $success['minimum_price'],                // 地域別・送料実費の範囲 下限
						'max_price'                => $success['max_price'],                    // 地域別・送料実費の範囲 上限
						'memo'                     => $success['memo'],                         // メモ
						
						'created_user_id'          => $this->_adminProperty['id'],              // 登録者ID
						'last_update_user_id'      => $this->_adminProperty['id'],              // 更新者ID
							
		                'created'                  => new Zend_Db_Expr('now()'),
		                'updated'                  => new Zend_Db_Expr('now()'),
					);

					$costPostageTable->create($data);
					$id = $costPostageTable->getLastInsertedId('id');
					
	                // commit
	                $costPostageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPostageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/postage-add-post transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage-detail                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 詳細                                         |
    +----------------------------------------------------------------------------*/
    public function postageDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		
		
		$costPostageTable = new Shared_Model_Data_CostPostage();
		
		$this->view->data = $data = $costPostageTable->getById($this->_adminProperty['management_group_id'], $id);

		//$this->view->clientData     = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['client_connection_id']);
		//$this->view->deliveryData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['delivery_connection_id']);
		
		if (empty($direct)) {
			$this->view->backUrl = '/price-expense/postage';
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage-update-basic                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 基本情報 更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function postageUpdateBasicAction()
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

				if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「名称」を入力してください'));
                    return;
                } else if (!empty($errorMessage['size']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「標準サイズ(cm)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['supply_fixture_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「使用輸送箱資材」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['country']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「国」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costPostageTable = new Shared_Model_Data_CostPostage();
				            
	            $costPostageTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'title'                    => $success['title'],                        // 名称
						'description'              => $success['description'],                  // 内容
						'size'                     => $success['size'],                         // 標準サイズ(cm)
						'supply_fixture_id'        => $success['supply_fixture_id'],            // 使用輸送箱資材
						'country'                  => $success['country'],                      // 国
					);

					$costPostageTable->updateById($id, $data);
					
	                // commit
	                $costPostageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPostageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/postage-update-basic transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage-update-price                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 送料 更新(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function postageUpdatePriceAction()
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

                if (!empty($errorMessage['standard_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「原価計算用一律送料」を選択してください'));
                    return;
                } else if (!empty($errorMessage['standard_price']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「原価計算用一律送料」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['minimum_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 下限」を入力してください'));
                    return;
                } else if (!empty($errorMessage['minimum_price']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 下限」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['max_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 上限」を入力してください'));
                    return;
                } else if (!empty($errorMessage['max_price']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「地域別・送料実費の範囲 上限」は半角数字のみで入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costPostageTable = new Shared_Model_Data_CostPostage();
				          
	            $costPostageTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'standard_price'           => $success['standard_price'],               // 原価計算用一律送料
						'minimum_price'            => $success['minimum_price'],                // 地域別・送料実費の範囲 下限
						'max_price'                => $success['max_price'],                    // 地域別・送料実費の範囲 上限
					);

					$costPostageTable->updateById($id, $data);
					
	                // commit
	                $costPostageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPostageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/postage-update-price transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage-update-subcontracting               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 委託先 更新(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function postageUpdateSubcontractingAction()
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

				if (!empty($errorMessage['connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「委託先 - 取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['supply_subcontracting_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「委託先 - 業務委託ID」を選択してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costPostageTable = new Shared_Model_Data_CostPostage();
					            
	            $costPostageTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'supply_subcontracting_id' => $success['supply_subcontracting_id'],     // 調達管理ID
						'connection_id'            => $success['connection_id'],                // 取引先
					);

					$costPostageTable->updateById($id, $data);
					
	                // commit
	                $costPostageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPostageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/postage-update-subcontracting transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/postage-update-memo                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位 - 送料 メモ 更新(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function postageUpdateMemoAction()
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
				$costPostageTable = new Shared_Model_Data_CostPostage();
				           
	            $costPostageTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'memo' => $success['memo'],     // メモ
					);

					$costPostageTable->updateById($id, $data);
					
	                // commit
	                $costPostageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costPostageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/postage-update-memo transaction faied: ' . $e);
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流                                           |
    +----------------------------------------------------------------------------*/
    public function exportAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
		
		$dbAdapter = $costOverseasTable->getAdapter();

        $selectObj = $costOverseasTable->select();
		$selectObj->joinLeft('frs_connection as connection1', 'frs_cost_delivery_overseas.client_connection_id = connection1.id', array($costOverseasTable->aesdecrypt('connection1.company_name', false) . 'AS client_name'));
        $selectObj->joinLeft('frs_connection as connection2', 'frs_cost_delivery_overseas.delivery_connection_id = connection2.id', array($costOverseasTable->aesdecrypt('connection2.company_name', false) . 'AS delivery_name'));
		
		$selectObj->where('frs_cost_delivery_overseas.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('frs_cost_delivery_overseas.id DESC');
		
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
    |  action_URL    * /price-expense/export-add                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 新規登録                                  |
    +----------------------------------------------------------------------------*/
    public function exportAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
        
		$request = $this->getRequest();

		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-add-post                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 - 新規登録(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function exportAddPostAction()
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['client_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「顧客」を選択してください'));
                    return;
                } else if (!empty($errorMessage['delivery_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「輸送業者」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「案件名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['export_country_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「輸出国」を選択してください'));
                    return;
                } else if (!empty($errorMessage['import_country_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「輸入国」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
				$versionTable      = new Shared_Model_Data_CostDeliveryOverseasVersion();
				
				// 新規登録	            
	            $costOverseasTable->getAdapter()->beginTransaction();
            	
	            try {
	            	$itemIds = array();
		            if (!empty($success['product_list'])) {
		            	$productIdList = explode(',', $success['product_list']);
		            	$count = 1;
			            foreach ($productIdList as $eachId) {
			                $itemIds[] = $request->getParam($eachId . '_item_id');
			                $count++;
			            }
		            }
	
	            	$supplyIds = array();
		            if (!empty($success['supply_list'])) {
		            	$supplyIdList = explode(',', $success['supply_list']);
		            	$count = 1;
			            foreach ($supplyIdList as $eachId) {
			                $supplyIds[] = $request->getParam($eachId . '_supply_id');
			                $count++;
			            }
		            }

					$data = array(
				        'management_group_id'      => $this->_adminProperty['management_group_id'],
				        'display_id'               => '',
						'status'                   => Shared_Model_Code::COMPETITION_STATUS_PROGRESS,
						
						'title'                    => $success['title'],                   // 案件名
						
						'type'                     => $success['type'],                    // 種別
						'type_other_text'          => $success['type_other_text'],         // 種別 その他テキスト
						
						'client_connection_id'     => $success['client_connection_id'],    // 顧客 取引先ID
						'delivery_connection_id'   => $success['delivery_connection_id'],  // 業者 取引先ID
						
						'target_item_ids'          => serialize($itemIds),           // 対象商品ID(リスト)
						
				        'export_country_id'        => $success['export_country_id'], // 輸出国ID
						'export_place'             => $success['export_place'],      // 出荷地
						'export_airport'           => $success['export_airport'],    // 輸出港・空港
						
						'import_country_id'        => $success['import_country_id'], // 輸入国ID
						'import_place'             => $success['import_place'],      // 最終到着地
						'import_airport'           => $success['import_airport'],    // 輸入港・空港
						
						'relational_supply_ids'    => serialize($supplyIds),           // 対象商品ID(リスト)	
						
		                'created'                  => new Zend_Db_Expr('now()'),
		                'updated'                  => new Zend_Db_Expr('now()'),
					);

					$costOverseasTable->create($data);
					$id = $costOverseasTable->getLastInsertedId('id');
					
					$versionData = array(
				        'management_group_id'      => $this->_adminProperty['management_group_id'],
				        'parent_id'                => $id,
						'version_id'               => $versionTable->getNextVersionId($id),
						'version_status'           => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
					);
					$versionTable->create($versionData);
					
	                // commit
	                $costOverseasTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costOverseasTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/export-add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-detail                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 詳細                                      |
    +----------------------------------------------------------------------------*/
    public function exportDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		
		$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
		$versionTable      = new Shared_Model_Data_CostDeliveryOverseasVersion();
		$connectionTable   = new Shared_Model_Data_Connection();
		$countryTable      = new Shared_Model_Data_Country();
		
		$this->view->data = $data = $costOverseasTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->clientData     = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['client_connection_id']);
		$this->view->deliveryData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['delivery_connection_id']);

		// 国リスト
		$this->view->countryList = $countryTable->getList();
		
		
		$this->view->versionItems = $versionTable->getListByParentId($id);
		
		$this->view->backUrl = '/price-expense/export';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-update-basic                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 基本情報更新(Ajax)                        |
    +----------------------------------------------------------------------------*/
    public function exportUpdateBasicAction()
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

                if (!empty($errorMessage['type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['client_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「顧客」を選択してください'));
                    return;
                } else if (!empty($errorMessage['delivery_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「輸送業者」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「案件名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
	            $costOverseasTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'title'                  => $success['title'],                   // 案件名
						
						'type'                   => $success['type'],                    // 種別
						'type_other_text'        => $success['type_other_text'],         // 種別 その他テキスト
						
						'client_connection_id'   => $success['client_connection_id'],    // 顧客 取引先ID
						'delivery_connection_id' => $success['delivery_connection_id'],  // 業者 取引先ID
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$costOverseasTable->updateById($id, $data);
						
	                // commit
	                $costOverseasTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costOverseasTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/update-basic transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-update-product-list                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 対象商品更新(Ajax)                        |
    +----------------------------------------------------------------------------*/
    public function exportUpdateProductListAction()
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
            	$itemIds = array();
				
	            if (!empty($success['product_list'])) {
	            	$productIdList = explode(',', $success['product_list']);
	            	$count = 1;
		            foreach ($productIdList as $eachId) {
		                $itemIds[] = $request->getParam($eachId . '_item_id');
		                $count++;
		            }
	            }
			
				$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
	            $costOverseasTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_item_ids'        => serialize($itemIds),           // 対象商品ID(リスト)						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);
					//var_dump($data);exit;

					$costOverseasTable->updateById($id, $data);
						
	                // commit
	                $costOverseasTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costOverseasTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/export-update-product-list transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-update-route-info                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 輸出・輸入拠点情報更新(Ajax)              |
    +----------------------------------------------------------------------------*/
    public function exportUpdateRouteInfoAction()
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
                
                if (!empty($errorMessage['export_country_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「輸出国」を選択してください'));
                    return;
                } else if (!empty($errorMessage['import_country_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「輸入国」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
	            $costOverseasTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
					    'export_country_id'      => $success['export_country_id'], // 輸出国ID
						'export_place'           => $success['export_place'],      // 出荷地
						'export_airport'         => $success['export_airport'],    // 輸出港・空港
						
						'import_country_id'      => $success['import_country_id'], // 輸入国ID
						'import_place'           => $success['import_place'],      // 最終到着地
						'import_airport'         => $success['import_airport'],    // 輸入港・空港
											
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$costOverseasTable->updateById($id, $data);
						
	                // commit
	                $costOverseasTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costOverseasTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/export-update-route-info transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-update-supply-list                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 関連業務委託情報 更新(Ajax)               |
    +----------------------------------------------------------------------------*/
    public function exportUpdateSupplyListAction()
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
            	$supplyIds = array();
				
	            if (!empty($success['supply_list'])) {
	            	$supplyIdList = explode(',', $success['supply_list']);
	            	$count = 1;
		            foreach ($supplyIdList as $eachId) {
		                $supplyIds[] = $request->getParam($eachId . '_supply_id');
		                $count++;
		            }
	            }
			
				$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
	            $costOverseasTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'relational_supply_ids'  => serialize($supplyIds),           // 対象商品ID(リスト)						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$costOverseasTable->updateById($id, $data);
						
	                // commit
	                $costOverseasTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $costOverseasTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-expense/export-update-supply-list transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-detail-version                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 物流費計算表                              |
    +----------------------------------------------------------------------------*/
    public function exportDetailVersionAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->versionId = $versionId = $request->getParam('version_id');
		$this->view->posTop = $request->getParam('pos');
		
		
		$costOverseasTable = new Shared_Model_Data_CostDeliveryOverseas();
		$versionTable      = new Shared_Model_Data_CostDeliveryOverseasVersion();
		//$connectionTable   = new Shared_Model_Data_Connection();
		//$countryTable      = new Shared_Model_Data_Country();
		
		//$this->view->data = $data = $costOverseasTable->getById($this->_adminProperty['management_group_id'], $id);

		//$this->view->clientData     = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['client_connection_id']);
		//$this->view->deliveryData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['delivery_connection_id']);

		// 国リスト
		//$this->view->countryList = $countryTable->getList();
		
		$this->view->parentData = $costOverseasTable->getById($this->_adminProperty['management_group_id'], $id);
		$this->view->data = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
		
		$this->view->backUrl = '/price-expense/export-detail?id=' . $id;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-expense/export-update-table                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原単位・輸出物流 基本情報更新(Ajax)                        |
    +----------------------------------------------------------------------------*/
    public function exportUpdateTableAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$versionId = $request->getParam('version_id');
		
		// POST送信時
		if ($request->isPost()) {

			$versionTable      = new Shared_Model_Data_CostDeliveryOverseasVersion();
            $versionTable->getAdapter()->beginTransaction();
        	
            try {
				$data = array();
				
				$columns = array(	
				'export_unit', 'import_unit', // 通貨単位
				'products_quantity_1','products_quantity_2','products_quantity_3','products_quantity_4','products_quantity_5', // 製品数量
				'quantity_per_box_1','quantity_per_box_2','quantity_per_box_3','quantity_per_box_4','quantity_per_box_5', // 箱入り数
				'box_quantity_1','box_quantity_2','box_quantity_3','box_quantity_4','box_quantity_5', // 箱数
				'volume_per_box_1','volume_per_box_2','volume_per_box_3','volume_per_box_4','volume_per_box_5', // 箱当たり荷量
				'total_volume_1','total_volume_2','total_volume_3','total_volume_4','total_volume_5', // 全体荷量
				'weight_per_box_1','weight_per_box_2','weight_per_box_3','weight_per_box_4','weight_per_box_5', // 箱当たり重量
				'total_weight_1','total_weight_2','total_weight_3','total_weight_4','total_weight_5', // 全重量
		
				'ex_works_unit_price_1','ex_works_unit_price_2','ex_works_unit_price_3','ex_works_unit_price_4','ex_works_unit_price_5', // 工場倉庫出荷単価
				'ex_works_amount_1','ex_works_amount_2','ex_works_amount_3','ex_works_amount_4','ex_works_amount_5', // 工場倉庫出荷総額
				'packing_cost_1','packing_cost_2','packing_cost_3','packing_cost_4','packing_cost_5', // 梱包費用
				'delivery_cost_1','delivery_cost_2','delivery_cost_3','delivery_cost_4','delivery_cost_5', // 国内輸送費
				'export_expense_1_label', 'export_expense_1_1','export_expense_1_2','export_expense_1_3','export_expense_1_4','export_expense_1_5', // 輸出諸掛1
				'export_expense_2_label', 'export_expense_2_1','export_expense_2_2','export_expense_2_3','export_expense_2_4','export_expense_2_5', // 輸出諸掛2
				'export_expense_3_label', 'export_expense_3_1','export_expense_3_2','export_expense_3_3','export_expense_3_4','export_expense_3_5', // 輸出諸掛3
				'export_expense_4_label', 'export_expense_4_1','export_expense_4_2','export_expense_4_3','export_expense_4_4','export_expense_4_5', // 輸出諸掛4
				'export_expense_5_label', 'export_expense_5_1','export_expense_5_2','export_expense_5_3','export_expense_5_4','export_expense_5_5', // 輸出諸掛5
				'export_expense_6_label', 'export_expense_6_1','export_expense_6_2','export_expense_6_3','export_expense_6_4','export_expense_6_5', // 輸出諸掛6
				'export_expense_7_label', 'export_expense_7_1','export_expense_7_2','export_expense_7_3','export_expense_7_4','export_expense_7_5', // 輸出諸掛7
				'export_expense_8_label', 'export_expense_8_1','export_expense_8_2','export_expense_8_3','export_expense_8_4','export_expense_8_5', // 輸出諸掛8
				
				'fob_amount_1','fob_amount_2','fob_amount_3','fob_amount_4','fob_amount_5', // 本船渡し総額
				'fob_unit_price_1','fob_unit_price_2','fob_unit_price_3','fob_unit_price_4','fob_unit_price_5', // 本船渡し単価
				'fob_index_1','fob_index_2','fob_index_3','fob_index_4','fob_index_5', // FOB指数
				'transport_cost_1','transport_cost_2','transport_cost_3','transport_cost_4','transport_cost_5', // 海上輸送費
				'transport_charge_1_1','transport_charge_1_2','transport_charge_1_3','transport_charge_1_4','transport_charge_1_5', // その他輸送諸掛1
				'rate_insurance_coverage_1','rate_insurance_coverage_2','rate_insurance_coverage_3','rate_insurance_coverage_4','rate_insurance_coverage_5', // 保険対象増幅率
		
				'insurance_coverage_amount_1','insurance_coverage_amount_2','insurance_coverage_amount_3','insurance_coverage_amount_4','insurance_coverage_amount_5', // 保険適用額
				'rate_insurance_1','rate_insurance_2','rate_insurance_3','rate_insurance_4','rate_insurance_5', // 保険率
				'insurance_price_1','insurance_price_2','insurance_price_3','insurance_price_4','insurance_price_5', // 保険料
				'rate_exchange_diffrence_1','rate_exchange_diffrence_2','rate_exchange_diffrence_3','rate_exchange_diffrence_4','rate_exchange_diffrence_5', // 金利利率
				'exchange_diffrence_1','exchange_diffrence_2','exchange_diffrence_3','exchange_diffrence_4','exchange_diffrence_5', // 金利
				'export_cif_cip_amount_1','export_cif_cip_amount_2','export_cif_cip_amount_3','export_cif_cip_amount_4','export_cif_cip_amount_5', // 運賃保険料込み総額
				'export_cif_cip_unt_price_1','export_cif_cip_unt_price_2','export_cif_cip_unt_price_3','export_cif_cip_unt_price_4','export_cif_cip_unt_price_5', // 運賃保険料込み単価
				'export_cif_cip_index_1','export_cif_cip_index_2','export_cif_cip_index_3','export_cif_cip_index_4','export_cif_cip_index_5', // CIF指数
				
				'basis_rate','rate',// 為替レートの根拠・為替レート
				'import_cif_cip_unit_price_1','import_cif_cip_unit_price_2','import_cif_cip_unit_price_3','import_cif_cip_unit_price_4','import_cif_cip_unit_price_5', // 運賃保険料込み単価
				'import_cif_cip_amount_1','import_cif_cip_amount_2','import_cif_cip_amount_3','import_cif_cip_amount_4','import_cif_cip_amount_5', // 運賃保険料込み総額
				'duty_rate_1','duty_rate_2','duty_rate_3','duty_rate_4','duty_rate_5', // 関税率
				'duty_amount_1','duty_amount_2','duty_amount_3','duty_amount_4','duty_amount_5', // 関税
				
				'import_expense_1_label','import_expense_1_1','import_expense_1_2','import_expense_1_3','import_expense_1_4', 'import_expense_1_5',// 輸入諸掛1
				'import_expense_2_label','import_expense_2_1','import_expense_2_2','import_expense_2_3','import_expense_2_4', 'import_expense_2_5',// 輸入諸掛2
				'import_expense_3_label','import_expense_3_1','import_expense_3_2','import_expense_3_3','import_expense_3_4', 'import_expense_3_5',// 輸入諸掛3
				'import_expense_4_label','import_expense_4_1','import_expense_4_2','import_expense_4_3','import_expense_4_4', 'import_expense_4_5',// 輸入諸掛4
				'import_expense_5_label','import_expense_5_1','import_expense_5_2','import_expense_5_3','import_expense_5_4', 'import_expense_5_5',// 輸入諸掛5
				'other_expense_1_1','other_expense_1_2','other_expense_1_3','other_expense_1_4', 'other_expense_1_5',// その他国内費用1
				'other_expense_2_1','other_expense_2_2','other_expense_2_3','other_expense_2_4', 'other_expense_2_5',// その他国内費用2
				'ddp_amount_1','ddp_amount_2','ddp_amount_3','ddp_amount_4', 'ddp_amount_5',// 関税込み持ち込み渡し総額
				'ddp_unit_price_1','ddp_unit_price_2','ddp_unit_price_3','ddp_unit_price_4', 'ddp_unit_price_5',// 関税込み持ち込み渡し単価
				'ddp_unit_price_rated_1','ddp_unit_price_rated_2','ddp_unit_price_rated_3','ddp_unit_price_rated_4', 'ddp_unit_price_rated_5',// 関税込み持ち込み渡し単価（輸出国通貨）
				'ddp_index_1','ddp_index_2','ddp_index_3','ddp_index_4', 'ddp_index_5',// DDP指数

				);	

				foreach ($columns as $each) {
					$data[$each] = $request->getParam($each);
				}

				//var_dump($data);exit;
				$versionTable->updateById($versionId, $data);
					
                // commit
                $versionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $versionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-expense/export-update-table transaction failed: ' . $e);
                
            }
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
}

