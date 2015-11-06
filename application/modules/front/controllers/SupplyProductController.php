<?php
/**
 * class SupplyProductController
 */
class SupplyProductController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '仕入・調達管理';
		$this->view->menuCategory     = 'supply';
		$this->view->menu = 'product';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/copied                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コピー済み(Ajax)                                           |
    +----------------------------------------------------------------------------*/
    public function copiedAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$projectTable = new Shared_Model_Data_SupplyProductProject();

			try {
				$projectTable->getAdapter()->beginTransaction();
				
				$projectTable->updateById($id, array(
					'is_copied' => 1,
				));
			
                // commit
                $projectTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $projectTable->getAdapter()->rollBack();
                throw new Zend_Exception('transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('supply_product_2');
		
		$this->view->posTop = $request->getParam('pos');
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['status']           = $request->getParam('status', '');
			$session->conditions['use_sale']         = $request->getParam('use_sale', '');
			$session->conditions['use_not_sale']     = $request->getParam('use_not_sale', '');
			$session->conditions['tag_name']         = $request->getParam('tag_name', '');
			$session->conditions['tag_id']           = $request->getParam('tag_id', '');
			
			$session->conditions['connection_name']  = $request->getParam('connection_name', '');
			$session->conditions['connection_id']    = $request->getParam('connection_id', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']          = '';
			$session->conditions['use_sale']        = '';
			$session->conditions['use_not_sale']    = '';
			$session->conditions['tag_name']        = '';
			$session->conditions['tag_id']          = '';
			
			$session->conditions['connection_name']  = '';
			$session->conditions['connection_id']    = '';
		}

		$this->view->conditions = $conditions = $session->conditions;
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		
		$dbAdapter = $productProjectTable->getAdapter();

        $selectObj = $productProjectTable->select();
        $selectObj->joinLeft('frs_supply_product_tag', 'frs_supply_product_project.tag_id = frs_supply_product_tag.id', array('tag_name'));
        $selectObj->joinLeft('frs_supply_product', 'frs_supply_product_project.id = frs_supply_product.project_id', array('target_connection_id'));
        
        // グループID
        $selectObj->where('frs_supply_product_project.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_product_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_product_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}
		
        if ($conditions['use_sale'] != '') {
            $useSaleString = $dbAdapter->quote('%"' . $conditions['use_sale'] .'"%');
			$selectObj->where($productProjectTable->aesdecrypt('uses_sales', false) . ' LIKE ' . $useSaleString);
        }
		
        if ($conditions['use_not_sale'] != '') {
            $useNotSaleString = $dbAdapter->quoteInto('`uses_not_sales`  LIKE ?', '%"' . $conditions['uses_not_sale'] .'"%');
            $selectObj->where($useNotSaleString);
        }

		if (!empty($conditions['tag_id'])) {
			$selectObj->where('tag_id = ?', $conditions['tag_id']);
		}
		
		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_product.target_connection_id = ?', $conditions['connection_id']);
		}
		
		$selectObj->group('frs_supply_product_project.id');
		$selectObj->order('frs_supply_product_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);

		// 商品区分
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->usesSalesList = $categoryTable->getList();
    }
   
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/list-select                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 選択(ポップアップ用)                              |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');

		$conditions = array();
		$conditions['status']         = $request->getParam('status', '');
		$conditions['use_sale']       = $request->getParam('use_sale', '');
		$conditions['use_not_sale']   = $request->getParam('use_not_sale', '');
		$conditions['tag_name']       = $request->getParam('tag_name', '');
		$conditions['tag_id']         = $request->getParam('tag_id', '');
		$conditions['connection_id']  = $request->getParam('connection_id', '');
		$this->view->conditions       = $conditions;
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		
		$dbAdapter = $productProjectTable->getAdapter();

        $selectObj = $productProjectTable->select();
        $selectObj->joinLeft('frs_supply_product_tag', 'frs_supply_product_project.tag_id = frs_supply_product_tag.id', array('tag_name'));
        $selectObj->joinLeft('frs_supply_product', 'frs_supply_product_project.id = frs_supply_product.project_id', array('target_connection_id'));
        
		// グループID
        $selectObj->where('frs_supply_product_project.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_product_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_product_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_product.target_connection_id = ?', $conditions['connection_id']);
		}
		
		$selectObj->group('frs_supply_product_project.id');
		$selectObj->order('frs_supply_product_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        
        $url = 'javascript:pageSupplyProduct($page);';
        $this->view->pager($paginator, $url);

		// 商品区分
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->usesSalesList = $categoryTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/delete                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(管理権限あり)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$productProjectTable = new Shared_Model_Data_SupplyProductProject();

			try {
				$productProjectTable->getAdapter()->beginTransaction();
				
				$productProjectTable->updateById($id, array(
					'status' => Shared_Model_Code::SUPPLY_STATUS_DELETED,
				));
			
                // commit
                $productProjectTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $productProjectTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-product/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/add                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		// 商品区分
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->usesSalesList = $categoryTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/add-post                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 新規登録(Ajax)                                  |
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

                if (!empty($errorMessage['tag_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「一般名称タグ」を選択してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「原料製品名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['purposes']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「目的」を選択してください'));
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ステータス」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$productProjectTable = new Shared_Model_Data_SupplyProductProject();
				
				// 新規登録
	            $productProjectTable->getAdapter()->beginTransaction();
            	
	            try {

	            	$displayId = $productProjectTable->getNextDisplayId();
	            
					$data = array(
				        'management_group_id'       => $this->_adminProperty['management_group_id'],
				        'display_id'                => $displayId,
						'status'                    => $success['status'],
						
						'tag_id'                    => $success['tag_id'],                      // 一般名称タグ
						
						'title'                     => $success['title'],                       // 原料製品名
						'description'               => $success['description'],                 // 原料製品内容

						'uses_sales'                => serialize($success['uses_sales']),       // 販売用途
						'uses_sales_other_text'     => $success['uses_sales_other_text'],            // 販売用途メモ
				
						'uses_not_sales'            => serialize($success['uses_not_sales']),   // 非売用途
						'uses_not_sales_other_text' => $success['uses_not_sales_other_text'],   // 非売用途メモ
						
						'uses_memo'                 => $success['uses_memo'],                   // 商品区分メモ
						
						'other_memo'                => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'item_ids'                  => serialize(array()),               // 対象商品ID
						
						'created_user_id'           => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'       => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                   => new Zend_Db_Expr('now()'),
		                'updated'                   => new Zend_Db_Expr('now()'),
					);
					
					$productProjectTable->create($data);
					$id = $productProjectTable->getLastInsertedId('id');

	                // commit
	                $productProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/add-post transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/detail                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 プロジェクト詳細                                  |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/supply-product';
		}

		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$productTable        = new Shared_Model_Data_SupplyProduct();
		
		$this->view->data = $data = $productProjectTable->getById($this->_adminProperty['management_group_id'], $id);
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $id);
		
		$userTable = new Shared_Model_Data_User();
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 商品区分
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->usesSalesList = $categoryTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/price-list                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 プロジェクト詳細 価格表                           |
    +----------------------------------------------------------------------------*/
    public function priceListAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/supply-product';
		}

		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$productTable        = new Shared_Model_Data_SupplyProduct();
		
		$this->view->data = $data = $productProjectTable->getById($this->_adminProperty['management_group_id'], $id);
		
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $id);
		
		$userTable = new Shared_Model_Data_User();
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 商品区分
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->usesSalesList = $categoryTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/update-overview                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - プロジェクト概要更新(Ajax)                      |
    +----------------------------------------------------------------------------*/
    public function updateOverviewAction()
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

                if (!empty($errorMessage['tag_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「一般名称タグ」を選択してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「原料製品名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['uses']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「用途」を選択してください'));
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ステータス」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$productProjectTable = new Shared_Model_Data_SupplyProductProject();
				$productProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'tag_id'                    => $success['tag_id'],                      // 一般名称タグ
						
						'title'                     => $success['title'],                       // 原料製品名
						'description'               => $success['description'],                 // 原料製品内容
						'status'                    => $success['status'],
						
						'uses_sales'                => serialize($success['uses_sales']),       // 販売用途
						'uses_sales_other_text'     => $success['uses_sales_other_text'],       // 販売用途メモ
				
						'uses_not_sales'            => serialize($success['uses_not_sales']),   // 非売用途
						'uses_not_sales_other_text' => $success['uses_not_sales_other_text'],   // 非売用途メモ
						
						'uses_memo'                 => $success['uses_memo'],                   // 用途メモ
						
						'other_memo'                => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'last_update_user_id'   => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					);
					
					$productProjectTable->updateById($id, $data);
						
	                // commit
	                $productProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/update-overview transaction failed: ' . $e);    
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/update-item-list                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 対象商品更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateItemListAction()
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
				$itemList = array();

	            if (!empty($success['item_list'])) {
	            	$itemIdList = explode(',', $success['item_list']);
	            	
		            foreach ($itemIdList as $eachId) {
		                $itemList[] = $request->getParam($eachId . '_item_id');
		            }
	            }

				$productProjectTable = new Shared_Model_Data_SupplyProductProject();

	            $productProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'item_ids'              => serialize($itemList),
						
						'last_update_user_id'   => $this->_adminProperty['id'],
					);

					$productProjectTable->updateById($id, $data);
						
	                // commit
	                $productProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/update-item-list transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-add                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先新規登録                                  |
    +----------------------------------------------------------------------------*/
    public function supplierAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->projectId = $projectId = $request->getParam('project_id');

		// 調達方法
		$supplyMethodTable = new Shared_Model_Data_SupplyMethod();
		$this->view->supplyMethodList = $supplyMethodTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-add-post                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先新規登録(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function supplierAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$projectId = $request->getParam('project_id');
		
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「原料製品名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['base_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引拠点名」を入力してください'));
                    return; 
                } else if (!empty($errorMessage['uses']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「用途」を選択してください'));
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ステータス」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$productTable = new Shared_Model_Data_SupplyProduct();
				$productProjectTable = new Shared_Model_Data_SupplyProductProject();
				
				// 新規登録
				$conditionList = array();				
				$fileList      = array();
				
				
	            $productTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'project_id'                        => $projectId,
						'status'                            => $success['status'],
						
						'target_connection_id'              => $success['target_connection_id'], // 取引先ID
						
						'base_name'                         => $success['base_name'],            // 取引拠点名
						
						'individual_name'                   => $success['individual_name'],      // 仕入先毎呼称
						'methods'                           => serialize($success['methods']),   // 調達方法
						'method_memo'                       => $success['method_memo'],          // 調達方法メモ
						
						'condition_list'                    => json_encode($conditionList),      // 購入条件
						
						'file_list'                         => json_encode($fileList),           // 入手見積書
				
						'created_user_id'                   => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);

					
					$productTable->create($data);
					$id = $productTable->getLastInsertedId('id');
					
					$productProjectTable->updateById($projectId, array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);

			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');

			            	if (!empty($tempFileName)) {
			            		// 正式保存
			            		Shared_Model_Resource_SupplyProduct::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								
							}
						}
					}
					
						
	                // commit
	                $productTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/add-post transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-detail                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先詳細                                      |
    +----------------------------------------------------------------------------*/
    public function supplierDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->materialKind = $materialKind = $request->getParam('material_kind');
		$this->view->direct = $direct  = $request->getParam('direct');
		
		if (empty($direct)) {
			$this->view->backUrl = '/supply-product';
		}

		$productTable        = new Shared_Model_Data_SupplyProduct();
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$connectionTable     = new Shared_Model_Data_Connection();
		
		$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}
		
		$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);
        
		
		$userTable = new Shared_Model_Data_User();
		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 調達方法
		$supplyMethodTable = new Shared_Model_Data_SupplyMethod();
		$this->view->supplyMethodList = $supplyMethodTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		
		$materialTable = new Shared_Model_Data_Material();
		$selectObj = $materialTable->select();
		$this->view->estimateItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT, $id, Shared_Model_Code::MATERIAL_TYPE_ESTIMATE, NULL);
		$this->view->documentItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT, $id, Shared_Model_Code::MATERIAL_TYPE_DOCUMENT, $materialKind);

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/update-supplier                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先更新(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function updateSupplierAction()
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
				} else if (!empty($errorMessage['base_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引拠点名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ステータス」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$productTable = new Shared_Model_Data_SupplyProduct();
				$productProjectTable = new Shared_Model_Data_SupplyProductProject();
				
				$oldData = $productTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $productTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_connection_id'            => $success['target_connection_id'],
						'base_name'                       => $success['base_name'],
						'status'                          => $success['status'],
						'history_memo'                    => $success['history_memo'],
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$productTable->updateById($id, $data);

					$productProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/update-supplier transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/update-basic                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 基本情報更新(Ajax)                              |
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

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$productTable    = new Shared_Model_Data_SupplyProduct();
				$productProjectTable = new Shared_Model_Data_SupplyProductProject();
				
				$oldData = $productTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $productTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'individual_name'        => $success['individual_name'],      // 仕入先毎呼称
						'methods'                => serialize($success['methods']),   // 調達方法
						'method_memo'            => $success['method_memo'],          // 調達方法メモ
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$productTable->updateById($id, $data);

					$productProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/update-basic transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/update-condition                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先更新(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function updateConditionAction()
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
				$conditionList = array();
				
				if (!empty($success['condition_list'])) {
					$conditionIdList = explode(',', $success['condition_list']);
					
		            if (!empty($conditionIdList)) {
			            foreach ($conditionIdList as $eachId) {
			                $conditionList[] = array(
								'id'               => $eachId,
								'lot_amount'       => $request->getParam($eachId . '_lot_amount'),
								'lot_unit'         => $request->getParam($eachId . '_lot_unit'),
								'unit_price'       => $request->getParam($eachId . '_unit_price'),
								'currency'         => $request->getParam($eachId . '_currency'),
								'total_price'      => $request->getParam($eachId . '_total_price'),
								'delivery_cost'    => $request->getParam($eachId . '_delivery_cost'),
								'currency_delivery'=> $request->getParam($eachId . '_currency_delivery'),
								'condition_memo'   => $request->getParam($eachId . '_condition_memo'),
			                );
			            }
		            }
	            }
	            
				$productTable    = new Shared_Model_Data_SupplyProduct();
	            $productProjectTable = new Shared_Model_Data_SupplyProductProject();
	            
	            $oldData = $productTable->getById($this->_adminProperty['management_group_id'], $id);
	            
	            $productTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'condition_list'                  => json_encode($conditionList),
						
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$productTable->updateById($id, $data);

					$productProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $productTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/update-condition transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/update-file-list                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 入手見積書・補足資料 更新(Ajax)                 |
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
				$productTable    = new Shared_Model_Data_SupplyProduct();
				$productProjectTable = new Shared_Model_Data_SupplyProductProject();
				
				$oldData = $productTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $productTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');

						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_SupplyProduct::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
			            	// tempファイルを削除
							Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
		                }
		                
		                $fileList[] = array(
							'id'               => $eachId,
							'material_kind'    => $request->getParam($eachId . '_material_kind'),
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

					$productTable->updateById($id, $data);

					$productProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $productTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-product/update-file-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-material-add                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先詳細 - 入手資料                           |
    +----------------------------------------------------------------------------*/
    public function supplierMaterialAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->materialType = $materialType = $request->getParam('material_type');
		$this->view->posTop = $request->getParam('pos');

		$productTable    = new Shared_Model_Data_SupplyProduct();
		$connectionTable = new Shared_Model_Data_Connection();
		
		$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}
		
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);

		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-material-add-post                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品詳細 - 資料 新規登録(Ajax)                             |
    +----------------------------------------------------------------------------*/
    public function supplierMaterialAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id = $request->getParam('id');
		
		$productTable    = new Shared_Model_Data_SupplyProduct();
		$data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
		
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['kind']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「資料名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['explanation']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「説明および注意事項」を選択してください'));
                    return;
                } else if (!empty($errorMessage['file_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ファイル」をアップロードしてください'));
                    return;
                } else if (!empty($errorMessage['temp_file_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ファイル」をアップロードしてください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$materialTable = new Shared_Model_Data_Material();
		
				$materialTable->getAdapter()->beginTransaction();
					
				try {
					
					//$nextOrder = $materialTable->getNextOrder($id);
					
					$data = array(
				        'management_group_id'    => $this->_adminProperty['management_group_id'],       // 管理グループID
				        'type'                   => $success['material_type'],                          // 種別
				        'item_type'              => Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT,
				        
				        'product_id'             => 0,
				        'supply_id'              => $id,
						'status'                 => Shared_Model_Code::MATERIAL_STATUS_AVAILABLE,       // ステータス
				
						'kind'                   => $success['kind'],                                   // 種別
						
						'title'                  => $success['title'],                                  // 資料名
						'explanation'            => $success['explanation'],                            // 資料説明及び注意事項
						
						'not_for_shared'         => 0,        // 配布禁止
						
						'file_type'              => 0,                                                  // ファイル種類
						'file_name'              => $success['temp_file_name'],                         // 保存ファイル名
						'default_file_name'      => $success['file_name'],                              // 初期ファイル名						
						'display_order'          => 1,                                                  // 並び順
						
		                'created'                => new Zend_Db_Expr('now()'),
		                'updated'                => new Zend_Db_Expr('now()'),
					);
					
					//if (!empty($success['not_for_shared'])) {
					//	$data['not_for_shared'] = 1;
					//}
					
					
					$materialTable->create($data);
					$materialId = $materialTable->getLastInsertedId('id');

					if (!empty($success['file_name'])) {
				        $result = Shared_Model_Resource_Material::makeResource($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name'], Shared_Model_Resource_TemporaryPrivate::getBinary($success['temp_file_name']));
	
		            	// tempファイルを削除
						Shared_Model_Resource_TemporaryPrivate::removeResource($success['temp_file_name']);
						
						$materialTable->updateById($this->_adminProperty['management_group_id'], $materialId, array(
							'file_size' => Shared_Model_Resource_Material::getFileSize($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name']),
						));
						
						
						$historyTable = new Shared_Model_Data_MaterialHistory();
						
						$historyData = array(
					        'material_id'            => $materialId,                    // 資料ID
					        'version_id'             => $historyTable->getNextVersionId($materialId), // バージョンID

							'file_type'              => 0,                             // ファイル種類
							'file_size'              => 111,            // ファイルサイズ
							'file_name'              => $success['temp_file_name'],    // 保存ファイル名
							'default_file_name'      => $success['file_name'],         // 初期ファイル名
							
			                'created'                => new Zend_Db_Expr('now()'),
			                'updated'                => new Zend_Db_Expr('now()'),
						);
						
						$historyTable->create($historyData);
					}

					// commit
					$materialTable->getAdapter()->commit();
               
				} catch (Exception $e) {
                	$materialTable->getAdapter()->rollBack();
					throw new Zend_Exception('/supplier/item/material-add-post transaction faied: ' . $e);
					
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-material-detail                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先詳細 - 入手資料                           |
    +----------------------------------------------------------------------------*/
    public function supplierMaterialDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->materialId = $materialId = $request->getParam('material_id');
		$this->view->posTop = $request->getParam('pos');
		
		$this->view->backUrl = '/supply-product/supplier-detail?id=' . $id;

		$productTable    = new Shared_Model_Data_SupplyProduct();
		$connectionTable = new Shared_Model_Data_Connection();
		$materialTable   = new Shared_Model_Data_Material();
		
		$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
		
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}
		
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);

		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		$this->view->materialData = $materialTable->getById($this->_adminProperty['management_group_id'], $materialId);
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/material-update                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品詳細 - 資料 更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function materialUpdateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id = $request->getParam('id');
		$materialId = $request->getParam('material_id');
		
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['material_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「形式」を選択してください'));
                    return;
                } else if (!empty($errorMessage['kind']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「資料名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$materialTable = new Shared_Model_Data_Material();
		
				//$materialTable->getAdapter()->beginTransaction();
					
				try {

					$data = array(
						'kind'                   => $success['kind'],                               // 種別  
						
						'title'                  => $success['title'],                              // 資料名
						'explanation'            => $success['explanation'],                        // 資料説明及び注意事項
					);
								
					if (!empty($success['file_name'])) {
						$data['file_type']              = 0;                                         // ファイル種類
						$data['file_name']              = $success['temp_file_name'];                // 保存ファイル名
						$data['default_file_name']      = $success['file_name'];                     // 初期ファイル名
						
				        $result = Shared_Model_Resource_Material::makeResource($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name'], Shared_Model_Resource_TemporaryPrivate::getBinary($success['temp_file_name']));
						
						$data['file_size'] = Shared_Model_Resource_Material::getFileSize($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name']);
						
		            	// tempファイルを削除
						Shared_Model_Resource_TemporaryPrivate::removeResource($success['temp_file_name']);
						
						$historyTable = new Shared_Model_Data_MaterialHistory();
						
						$historyData = array(
					        'material_id'            => $materialId,                    // 資料ID
					        'version_id'             => $historyTable->getNextVersionId($materialId), // バージョンID

							'file_type'              => 0,                             // ファイル種類
							'file_size'              => $data['file_size'],            // ファイルサイズ
							'file_name'              => $success['temp_file_name'],    // 保存ファイル名
							'default_file_name'      => $success['file_name'],         // 初期ファイル名
							
			                'created'                => new Zend_Db_Expr('now()'),
			                'updated'                => new Zend_Db_Expr('now()'),
						);
						
						$historyTable->create($historyData);
					}

					$materialTable->updateById($this->_adminProperty['management_group_id'], $materialId, $data);
					
					// commit
					//$materialTable->getAdapter()->commit();
               
				} catch (Exception $e) {
                	//$materialTable->getAdapter()->rollBack();
					throw new Zend_Exception('/supply-product/material-update transaction faied: ' . $e);
					
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/supplier-material-version                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先詳細 - ファイル更新履歴                   |
    +----------------------------------------------------------------------------*/
    public function supplierMaterialVersionAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->materialId = $materialId = $request->getParam('material_id');
		$this->view->posTop = $request->getParam('pos');
		
		$this->view->backUrl = '/supply-product/supplier-detail?id=' . $id;

		$productTable    = new Shared_Model_Data_SupplyProduct();
		$connectionTable = new Shared_Model_Data_Connection();
		$materialTable   = new Shared_Model_Data_Material();
		$historyTable    = new Shared_Model_Data_MaterialHistory();
		
		$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
		
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}
		
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);

		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		$this->view->materialData = $materialTable->getById($this->_adminProperty['management_group_id'], $materialId);
		
		$this->view->historyList = $historyTable->getHitoryListByMaterialId($materialId);
		
    }
    
      
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/upload                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入手見積アップロード(Ajax)                                 |
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
		
		$exploded = explode('.', $fileName);
		$ext = end($exploded);
		
		$tempFileName = uniqid() . '.' . $ext;
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($tempFileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName, 'temp_file_name' => $tempFileName));
        return;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/tag-list                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - タグ一覧                                        |
    +----------------------------------------------------------------------------*/
    public function tagListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$tagTable = new Shared_Model_Data_SupplyProductTag();
		
		$dbAdapter = $tagTable->getAdapter();

        $selectObj = $tagTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']); // グループID
        
        if (!empty($conditions['keyword'])) {
        	$likeString1 = $dbAdapter->quoteInto('`tag_name` LIKE ?', '%' . $conditions['keyword'] .'%');
        	$likeString2 = $dbAdapter->quoteInto('`serach_words_list`  LIKE ?', '%"' . $conditions['keyword'] .'"%');
        	
        	$selectObj->where($likeString1 . 'OR ' . $likeString2);
		}
        
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
    |  action_URL    * /supply-product/tag-list-select                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - タグ一覧(ポップアップ用)                        |
    +----------------------------------------------------------------------------*/
    public function tagListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$tagTable = new Shared_Model_Data_SupplyProductTag();
		
		$dbAdapter = $tagTable->getAdapter();

        $selectObj = $tagTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']); // グループID
        
        if (!empty($conditions['keyword'])) {
        	$likeString1 = $dbAdapter->quoteInto('`tag_name` LIKE ?', '%' . $conditions['keyword'] .'%');
        	$likeString2 = $dbAdapter->quoteInto('`serach_words_list`  LIKE ?', '%"' . $conditions['keyword'] .'"%');
        	
        	$selectObj->where($likeString1 . 'OR ' . $likeString2);
		}
        
		$selectObj->order('id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        
        $url = 'javascript:pageTag($page);';
        $this->view->pager($paginator, $url);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/tag-detail                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - タグ・編集                                      |
    +----------------------------------------------------------------------------*/
    public function tagDetailAction()
    {
    	if (empty($this->_adminProperty['allow_editing_search_tag'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$tagTable = new Shared_Model_Data_SupplyProductTag();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'tag_name'                => '',      // タグ名称
		        'serach_words_list'       => '',      // 検索ワードリスト
		        'descripition'            => '',      // 詳細
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $tagTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/supply-product/tag-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-product/tag-update                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - タグ・編集(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function tagUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_editing_search_tag'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$tagTable = new Shared_Model_Data_SupplyProductTag();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/supply-product/tag-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['tag_name'])) {
                    $message = '「タグ名称」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				$itemList = array();
				
				if (!empty($success['item_list'])) {
					$itemIdList = explode(',', $success['item_list']);
	            	
		            foreach ($itemIdList as $eachId) {
						$title = $request->getParam($eachId . '_title');
		                
		                if (!empty($title)) {
			                $itemList[] = $title;
		                }
		            }
				}	
				
				if (empty($id)) {
					// 新規登録
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス
						
				        'tag_name'            => $success['tag_name'],      // タグ名称
				        'serach_words_list'   => serialize($itemList),      // 検索ワードリスト
				        'descripition'        => '',      // 詳細

		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$tagTable->create($data);
					
				} else {
					// 編集
					$data = array(
						'tag_name'            => $success['tag_name'],      // タグ名称
				        'serach_words_list'   => serialize($itemList),      // 検索ワードリスト
				        'descripition'        => '',      // 詳細
					);

					$tagTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
}

