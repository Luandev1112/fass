<?php
/**
 * class EcController
 */
 
class EcController extends Front_Model_Controller
{
    const PER_PAGE = 200;
    
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
		$this->view->mainCategoryName = 'EC商品パッケージ管理';
		$this->view->menuCategory     = 'ec';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/old-package-list                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC商品パッケージ管理                                       |
    +----------------------------------------------------------------------------*/
    public function oldPackageListAction()
    {
		$request = $this->getRequest();

		$page    = $request->getParam('page', '1');
		
		$packageTable        = new Shared_Model_Data_ItemPackage();
		$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
		$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();

		// パッケージリスト
		$dbAdapter = $packageTable->getAdapter();
        $selectObj = $packageTable->select();
        
        /*
		foreach ($productCodeList as &$each) {
			// プロダクトコードに対する付属品
			$each['bundle_items'] = $bundleTable->getBundleItemsByProductCodeId($each['id']);
		}

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
        

		$selectObj->order('frs_item_package.id DESC');
		
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
    |  action_URL    * /ec/package-list                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC商品パッケージ管理                                       |
    +----------------------------------------------------------------------------*/
    public function packageListAction()
    {
		$request = $this->getRequest();

		$page    = $request->getParam('page', '1');
		
		$packageTable        = new Shared_Model_Data_ItemPackage();
		$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
		$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();

		// パッケージリスト
		$dbAdapter = $packageTable->getAdapter();
        $selectObj = $packageTable->select();
        
        /*
		foreach ($productCodeList as &$each) {
			// プロダクトコードに対する付属品
			$each['bundle_items'] = $bundleTable->getBundleItemsByProductCodeId($each['id']);
		}

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
        

		$selectObj->order('frs_item_package.id DESC');
		
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
    |  action_URL    * /ec/package-add                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC商品パッケージ管理 - 新規登録                            |
    +----------------------------------------------------------------------------*/
    public function packageAddAction()
    {  
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/ec/package-list';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
        $itemTable = new Shared_Model_Data_WarehouseItem();
        
		// 商品リスト(選択用)
        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        $selectObj->order('frs_warehouse_item.id ASC');
        $this->view->productItems = $selectObj->query()->fetchAll();
        
        // 付属品リスト
        $selectObj = $itemTable->select();
        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_BUNDLE);
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
        $selectObj->order('frs_warehouse_item.id ASC');
        $this->view->bundleItems = $selectObj->query()->fetchAll();
        
        // 発送用資材
        $selectObj = $itemTable->select();
        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_PACKAGE);
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
        $selectObj->order('frs_warehouse_item.id ASC');
        $this->view->packageItems = $selectObj->query()->fetchAll();
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/package-add-post                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC商品パッケージ管理 - 新規登録(Ajax)                      |
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

                if (!empty($errorMessage['product_code']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「商品コード」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['package_type']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「パッケージ種別」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['is_subscription']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「単発/定期」を選択してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['package_name_domestic']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「パッケージ名(国内)」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['price_domestic']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「販売価格(国内)」を入力してください';
                    $this->sendJson($result);
                    return;
                }
                  
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageTable        = new Shared_Model_Data_ItemPackage();
				$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
				$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();
				
				$existData = $packageTable->getByProductCode($success['product_code']);
				
				if (!empty($existData)) {
                    $result['result'] = 'NG';
                    $result['message'] = 'その商品コードはすでに登録されています';
                    $this->sendJson($result);
                    return;
				}
				
				
				$packageTable->getAdapter()->beginTransaction();
            	  
	            try {		
					// 登録
					$data = array(
						'product_code'           => $success['product_code'],
						'package_type'           => $success['package_type'],
						'is_subscription'        => $success['is_subscription'],
						'store_own_domestic'     => 0,
						'store_own_overseas'     => 0,
						'store_own_rakuten'      => 0,
						'store_own_yahoo_co_jp'  => 0,
						'store_own_amazon_co_jp' => 0,
						'package_name_domestic' => $success['package_name_domestic'],
						'price_domestic'        => $success['price_domestic'],
						'package_name_overseas' => '',
						'price_overseas'        => '',
					);
					
					if (!empty($success['store_own_domestic'])) {
						$data['store_own_domestic'] = 1;
					}
					
					if (!empty($success['store_own_overseas'])) {
						$data['store_own_overseas'] = 1;
					}
						
					if (!empty($success['store_own_rakuten'])) {
						$data['store_own_rakuten'] = 1;
					}
					
					if (!empty($success['store_own_yahoo_co_jp'])) {
						$data['store_own_yahoo_co_jp'] = 1;
					}
					
					if (!empty($success['store_own_amazon_co_jp'])) {
						$data['store_own_amazon_co_jp'] = 1;
					}
					$packageTable->create($data);
					$packageId = $packageTable->getLastInsertedId('id');
					

		            if (!empty($success['package_product_id_list'])) {
		            	
		            	$packageProductIdList = explode(',', $success['package_product_id_list']);
		            	
			            foreach ($packageProductIdList as $eachPackageProductId) {
	
			                $params = array(
						        'status'                => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_ACTIVE,
								'item_package_id'       => $packageId,
								'product_item_id'       => $request->getParam($eachPackageProductId . '_product_item_id'),
								'product_item_amount'   => $request->getParam($eachPackageProductId . '_product_item_amount'),
								'created'               => new Zend_Db_Expr('now()'),
						        'updated'               => new Zend_Db_Expr('now()'),
			                );  
			            	$packageProductTable->create($params);
			            }
		            }
		            
		            if (!empty($success['package_bundle_id_list'])) {
		            
						$packageBundleIdList = explode(',', $success['package_bundle_id_list']);
						
			            foreach ($packageBundleIdList as $eachPackageBundleId) {
			                $params = array(
						        'status'               => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_ACTIVE,
						        'item_package_id'      => $packageId,
								'bundle_item_id'       => $request->getParam($eachPackageBundleId . '_bundle_item_id'),
								'bundle_item_amount'   => $request->getParam($eachPackageBundleId . '_bundle_item_amount'),
								'created'              => new Zend_Db_Expr('now()'),
						        'updated'              => new Zend_Db_Expr('now()'),
			                );  
			            	$packageBundleTable->create($params);
			            }
		            }
		            
	                // commit
	                $packageTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $packageTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/ec/package-add-post transaction faied: ' . $e);
	                
	            }

			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
	    
    }
    	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/package-detail                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC商品パッケージ管理 - 詳細                                |
    +----------------------------------------------------------------------------*/
    public function packageDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/ec/package-list';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$packageTable              = new Shared_Model_Data_ItemPackage();
		$packageProductTable       = new Shared_Model_Data_ItemPackageProduct();
		$packageBundleTable        = new Shared_Model_Data_ItemPackageBundle();
		$packageShippingpackTable  = new Shared_Model_Data_ItemPackageShippingpack();
		
		if (!empty($id)) {
	    	$this->view->data = $packageTable->getById($id);
	    	$this->view->packageProductItems = $packageProductTable->getProductItemsByPackageId($id);
	    	$this->view->packageBundleItems = $packageBundleTable->getBundleItemsByPackageId($id);
	    	$this->view->shippingpackItems = $packageShippingpackTable->getShippingpackItemsByPackageId($id);
    	}
    	
		$itemTable = new Shared_Model_Data_WarehouseItem();
		
		// 商品リスト(選択用)
        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
		
        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        $selectObj->order('frs_warehouse_item.id ASC');
        $this->view->productItems = $selectObj->query()->fetchAll();

        // 付属品リスト
        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_BUNDLE);
        $selectObj->order('frs_warehouse_item.id ASC');
        $this->view->bundleItems = $selectObj->query()->fetchAll();
        
        // 発送用資材
        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($itemTable->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($itemTable->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($itemTable->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
        $selectObj->where('frs_warehouse_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_warehouse_item.stock_type = ?', Shared_Model_Code::ITEM_TYPE_PACKAGE);
        $selectObj->order('frs_warehouse_item.id ASC');
        $this->view->packageItems = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/update-basic                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報 - 更新(Ajax)                                      |
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

                if (!empty($errorMessage['product_code']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「商品コード」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['package_type']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「パッケージ種別」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['is_subscription']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「単発/定期」を選択してください';
                    $this->sendJson($result);
                    return;
                }
                  
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageTable = new Shared_Model_Data_ItemPackage();
				
				$existData = $packageTable->getByProductCode($success['product_code'], $id);
				
				if (!empty($existData)) {
                    $result['result'] = 'NG';
                    $result['message'] = 'その商品コードはすでに登録されています';
                    $this->sendJson($result);
                    return;
				}
				
				
				// 更新
				$data = array(
					'product_code'           => $success['product_code'],
					'package_type'           => $success['package_type'],
					'is_subscription'        => $success['is_subscription'],
					'store_own_domestic'     => 0,
					'store_own_overseas'     => 0,
					'store_own_rakuten'      => 0,
					'store_own_yahoo_co_jp'  => 0,
					'store_own_amazon_co_jp' => 0,
				);
				
				if (!empty($success['store_own_domestic'])) {
					$data['store_own_domestic'] = 1;
				}
				
				if (!empty($success['store_own_overseas'])) {
					$data['store_own_overseas'] = 1;
				}
					
				if (!empty($success['store_own_rakuten'])) {
					$data['store_own_rakuten'] = 1;
				}
				
				if (!empty($success['store_own_yahoo_co_jp'])) {
					$data['store_own_yahoo_co_jp'] = 1;
				}
				
				if (!empty($success['store_own_amazon_co_jp'])) {
					$data['store_own_amazon_co_jp'] = 1;
				}
				$packageTable->updateById($id, $data);
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/update-domestic                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 国内向け設定(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateDomesticAction()
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

                if (!empty($errorMessage['package_name_domestic']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「パッケージ名(国内)」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['price_domestic']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「販売価格(国内)」を入力してください';
                    $this->sendJson($result);
                    return;
                }
                    
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageTable = new Shared_Model_Data_ItemPackage();
	
				// 更新
				$data = array(
					'package_name_domestic' => $success['package_name_domestic'],
					'price_domestic'        => $success['price_domestic'],
				);

				$packageTable->updateById($id, $data);
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/update-overseas                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 海外向け設定(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateOverseasAction()
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

                if (!empty($errorMessage['package_name_overseas']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「パッケージ名(海外)」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['price_overseas']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「販売価格(海外)」を入力してください';
                    $this->sendJson($result);
                    return;
                }
                    
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageTable = new Shared_Model_Data_ItemPackage();
	
				// 更新
				$data = array(
					'package_name_overseas' => $success['package_name_overseas'],
					'price_overseas'        => $success['price_overseas'],
				);

				$packageTable->updateById($id, $data);
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/update-product-list                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 構成商品 - 編集(Ajax)                                      |
    +----------------------------------------------------------------------------*/
    public function updateProductListAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$packageId = $request->getParam('id');
		
		$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageProductTable->getAdapter()->beginTransaction();
				
	            try {
	            
					$packageProductIdList = explode(',', $success['package_product_id_list']);
					
					$oldPackageProductList = $packageProductTable->getProductItemsByPackageId($packageId);
					
					foreach ($oldPackageProductList as $eachOldPackageProduct) {
						$isExist = false;
						
						foreach ($packageProductIdList as $eachPackageProductId) {
							if ($eachPackageProductId == $eachOldPackageProduct['id']) {
								$isExist = true;
							}
						}
						
						if ($isExist == false) {
							// 削除
							$params = array(
			                    'status' => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_REMOVE,
			                );
			            	$packageProductTable->updateById($eachOldPackageProduct['id'], $params);
						}
					}
	            	
		            $count = 1;
		            if (!empty($packageProductIdList)) {
			            foreach ($packageProductIdList as $eachPackageProductId) {
			            	// 登録されている場合
			            	if (is_numeric($eachPackageProductId) && $packageProductTable->isExist($eachPackageProductId)) {
				                $params = array(
									'product_item_id'       => $request->getParam($eachPackageProductId . '_product_item_id'),
									'product_item_amount'   => $request->getParam($eachPackageProductId . '_product_item_amount'),
				                );  
				            	$packageProductTable->updateById($eachPackageProductId, $params);
			            	} else {
				                $params = array(
							        'status'                => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_ACTIVE,
									'item_package_id'       => $packageId,
									'product_item_id'       => $request->getParam($eachPackageProductId . '_product_item_id'),
									'product_item_amount'   => $request->getParam($eachPackageProductId . '_product_item_amount'),
									'created'               => new Zend_Db_Expr('now()'),
							        'updated'               => new Zend_Db_Expr('now()'),
				                );  
				            	$packageProductTable->create($params);
			            	}
			            	
			            	$count++;
			            }
		            }
		            
	            
	                // commit
	                $packageProductTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $packageProductTable->getAdapter()->rollBack();
	                
	                throw new Zend_Exception('/ec/update-product-list transaction faied: ' . $e);   
	            }
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		
		$result = array('result' => 'NG');
	    $this->sendJson($result);
		
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/update-bundle-list                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 同梱品 - 編集(Ajax)                                        |
    +----------------------------------------------------------------------------*/
    public function updateBundleListAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$packageId = $request->getParam('id');
		
		$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageBundleTable->getAdapter()->beginTransaction();
				
	            try {
	            	if (!empty($success['package_bundle_id_list'])) {
		            	$packageBundleIdList = explode(',', $success['package_bundle_id_list']);
	            	}
					
					$oldBundleList = $packageBundleTable->getBundleItemsByPackageId($packageId);
					
					foreach ($oldBundleList as $eachOldBundle) {
						$isExist = false;
						
						foreach ($packageBundleIdList as $eachPackageBundleId) {
							if ($eachPackageBundleId == $eachOldBundle['id']) {
								$isExist = true;
							}
						}
						
						if ($isExist == false) {
							// 削除
			            	$packageBundleTable->updateById($eachOldBundle['id'], array(
			                    'status' => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_REMOVE,
			                ));
						}
					}
	            	
		            $count = 1;
		            if (!empty($packageBundleIdList)) {
			            foreach ($packageBundleIdList as $eachPackageBundleId) {
			            	// 登録されている場合
			            	if (is_numeric($eachPackageBundleId) && $packageBundleTable->isExist($eachPackageBundleId)) { 
				            	$packageBundleTable->updateById($eachPackageBundleId, array(
									'bundle_item_id'       => $request->getParam($eachPackageBundleId . '_bundle_item_id'),
									'bundle_item_amount'   => $request->getParam($eachPackageBundleId . '_bundle_item_amount'),
				                ));
			            	} else { 
				            	$packageBundleTable->create(array(
							        'status'               => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_ACTIVE,
							        'item_package_id'      => $packageId,
									'bundle_item_id'       => $request->getParam($eachPackageBundleId . '_bundle_item_id'),
									'bundle_item_amount'   => $request->getParam($eachPackageBundleId . '_bundle_item_amount'),
									'created'              => new Zend_Db_Expr('now()'),
							        'updated'              => new Zend_Db_Expr('now()'),
				                ));
			            	}
			            	
			            	$count++;
			            }
		            }
		            
	            
	                // commit
	                $packageBundleTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $packageBundleTable->getAdapter()->rollBack();
	                
	                throw new Zend_Exception('/ec/update-bundle-list transaction faied: ' . $e);   
	            }
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG'));
		
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /ec/update-shippingpack-list                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 標準梱包資材 - 編集(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function updateShippingpackListAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$packageId = $request->getParam('id');
		
		$packageShippingpackTable  = new Shared_Model_Data_ItemPackageShippingpack();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$packageShippingpackTable->getAdapter()->beginTransaction();
				
	            try {
	            
					$packageShippingpackIdList = explode(',', $success['package_shippingpack_id_list']);
					
					$oldShippingpackList = $packageShippingpackTable->getShippingpackItemsByPackageId($packageId);
					
					foreach ($oldShippingpackList as $eachOldShippingpack) {
						$isExist = false;
						
						foreach ($packageShippingpackIdList as $eachPackageShippingpackId) {
							if ($eachPackageShippingpackId == $eachOldShippingpack['id']) {
								$isExist = true;
							}
						}
						
						if ($isExist == false) {
							// 削除
							$params = array(
			                    'status' => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_REMOVE,
			                );  
			            	$packageShippingpackTable->updateById($eachOldShippingpack['id'], $params);
						}
					}
	            	
		            $count = 1;
		            if (!empty($packageShippingpackIdList)) {
			            foreach ($packageShippingpackIdList as $eachPackageShippingpackId) {
			            	// 登録されている場合
			            	if (is_numeric($eachPackageShippingpackId) && $packageShippingpackTable->isExist($eachPackageShippingpackId)) {
				                $params = array(
									'shippingpack_item_id'       => $request->getParam($eachPackageShippingpackId . '_shippingpack_item_id'),
									'shippingpack_item_amount'   => $request->getParam($eachPackageShippingpackId . '_shippingpack_item_amount'),
				                );  
				            	$packageShippingpackTable->updateById($eachPackageShippingpackId, $params);
			            	} else {
				                $params = array(
							        'status'               => Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_ACTIVE,
							        'item_package_id'      => $packageId,
									'shippingpack_item_id'       => $request->getParam($eachPackageShippingpackId . '_shippingpack_item_id'),
									'shippingpack_item_amount'   => $request->getParam($eachPackageShippingpackId . '_shippingpack_item_amount'),
									'created'              => new Zend_Db_Expr('now()'),
							        'updated'              => new Zend_Db_Expr('now()'),
				                );  
				            	$packageShippingpackTable->create($params);
			            	}
			            	
			            	$count++;
			            }
		            }
		            
	            
	                // commit
	                $packageShippingpackTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $packageShippingpackTable->getAdapter()->rollBack();
	                
	                throw new Zend_Exception('/ec/update-shippingpack-list transaction faied: ' . $e);   
	            }
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		
		$result = array('result' => 'NG');
	    $this->sendJson($result);
		
    }
}

