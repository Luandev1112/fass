<?php
/**
 * class SupplyProductionController
 */
class SupplyProductionController extends Front_Model_Controller
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
		$this->view->menu = 'production';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/copied                                  |
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
			$projectTable = new Shared_Model_Data_SupplyProductionProject();

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
    |  action_URL    * /supply-production                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託                                               |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('supply_production_2');

		$this->view->posTop = $request->getParam('pos');

		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['status']         = $request->getParam('status', '');
			$session->conditions['purpose']        = $request->getParam('purpose', '');
			$session->conditions['keyword']        = $request->getParam('keyword', '');

			$session->conditions['connection_name']  = $request->getParam('connection_name', '');
			$session->conditions['connection_id']    = $request->getParam('connection_id', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']         = '';
			$session->conditions['purpose']        = '';
			$session->conditions['keyword']        = '';

			$session->conditions['connection_name']  = '';
			$session->conditions['connection_id']    = '';
		}
		
		$this->view->conditions = $conditions = $session->conditions;

		
		$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
		
		$dbAdapter = $productionProjectTable->getAdapter();

        $selectObj = $productionProjectTable->select();
		$selectObj->joinLeft('frs_supply_production', 'frs_supply_production_project.id = frs_supply_production.project_id', array('target_connection_id'));
		        
		// グループID
        $selectObj->where('frs_supply_production_project.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_production_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_production_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}

        if ($conditions['purpose'] != '') {
            $purposeString = $dbAdapter->quote('%"' . $conditions['purpose'] .'"%');
			$selectObj->where('purposes LIKE ' . $purposeString);
        }

        if (!empty($session->conditions['keyword'])) {
	        $likeString = array();
	        $likeString[] = $dbAdapter->quoteInto($productionProjectTable->aesdecrypt('frs_supply_production_project.title', false) . ' LIKE ?', '%' . $session->conditions['keyword'] .'%');
	        $likeString[] = $dbAdapter->quoteInto($productionProjectTable->aesdecrypt('frs_supply_production_project.description', false) . ' LIKE ?', '%' . $session->conditions['keyword'] .'%');
        	
        	$selectObj->where(implode(' OR ', $likeString));
        }

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_production.target_connection_id = ?', $conditions['connection_id']);
		}
		
        $selectObj->group('frs_supply_production_project.id');
		$selectObj->order('frs_supply_production_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		// 目的
		$purposeTable = new Shared_Model_Data_SupplyProductionPurpose();
		$this->view->purposeList = $purposeTable->getList();
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/list-select                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 選択(ポップアップ用)                          |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');

		// 検索条件
		$conditions = array();
		$conditions['status']        = $request->getParam('status', '');
		$conditions['purpose']       = $request->getParam('purpose', '');
		$conditions['keyword']       = $request->getParam('keyword', '');
		$conditions['connection_id'] = $request->getParam('connection_id', '');
		$this->view->conditions      = $conditions;
		
		$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
		
		$dbAdapter = $productionProjectTable->getAdapter();

        $selectObj = $productionProjectTable->select();
        $selectObj->joinLeft('frs_supply_production', 'frs_supply_production_project.id = frs_supply_production.project_id', array('target_connection_id'));
        
        // グループID
        $selectObj->where('frs_supply_production_project.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		//$selectObj->joinLeft('frs_connection', 'frs_supply_production.target_connection_id = frs_connection.id', array($productionTable->aesdecrypt('company_name', false) . 'AS company_name'));
        //$selectObj->joinLeft('frs_user', 'frs_supply_production.created_user_id = frs_user.id',array($directOrderTable->aesdecrypt('user_name', false) . 'AS user_name'));
        
		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_production_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_production_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_production.target_connection_id = ?', $conditions['connection_id']);
		}
		
		$selectObj->group('frs_supply_production_project.id');
		$selectObj->order('frs_supply_production_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;

        $url = 'javascript:pageSupplyProduction($page);';
        $this->view->pager($paginator, $url);
        
		// 目的
		$purposeTable = new Shared_Model_Data_SupplyProductionPurpose();
		$this->view->purposeList = $purposeTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/delete                                  |
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
			$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();

			try {
				$productionProjectTable->getAdapter()->beginTransaction();
				
				$productionProjectTable->updateById($id, array(
					'status' => Shared_Model_Code::SUPPLY_STATUS_DELETED,
				));
			
                // commit
                $productionProjectTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $productionProjectTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-production/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/add                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 新規登録                                    |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		// 目的
		$purposeTable = new Shared_Model_Data_SupplyProductionPurpose();
		$this->view->puposeList = $purposeTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/add-post                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 新規登録(Ajax)                              |
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

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「製造加工委託名」を入力してください'));
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
				$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
				
				// 新規登録
	            $productionProjectTable->getAdapter()->beginTransaction();
            	
	            try {

	            	$displayId = $productionProjectTable->getNextDisplayId();
	            
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'display_id'                        => $displayId,
						'status'                            => $success['status'],
						
						'title'                             => $success['title'],                // 製造加工委託名
						'description'                       => $success['description'],          // 製造加工委託内容

						'purposes'                          => serialize($success['purposes']),  // 目的
						'purpose_memo'                      => $success['purpose_memo'],         // 目的メモ
						
						'other_memo'                        => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'item_ids'                          => serialize(array()),               // 対象商品ID
						
						'created_user_id'                   => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$productionProjectTable->create($data);
					$id = $productionProjectTable->getLastInsertedId('id');

	                // commit
	                $productionProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/add-post transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/detail                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 プロジェクト詳細                              |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/supply-production';
		}
		
		$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
		$productionTable        = new Shared_Model_Data_SupplyProduction();
		
		$this->view->data = $data = $productionProjectTable->getById($this->_adminProperty['management_group_id'], $id);
        $this->view->supplierList = $productionTable->getListByProjectId($this->_adminProperty['management_group_id'], $id);
		
		$userTable = new Shared_Model_Data_User();
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 目的
		$purposeTable = new Shared_Model_Data_SupplyProductionPurpose();
		$this->view->purposeList = $purposeTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/update-overview                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - プロジェクト概要更新(Ajax)                  |
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

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「製造加工委託名」を入力してください'));
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
				$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
	            $productionProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'title'                 => $success['title'],                // 製造加工委託名
						'description'           => $success['description'],          // 製造加工委託内容
						'status'                => $success['status'],
						
						'purposes'              => serialize($success['purposes']),   // 目的
						'purpose_memo'          => $success['purpose_memo'],         // 目的メモ
						
						'other_memo'            => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'last_update_user_id'   => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					);
					
					$productionProjectTable->updateById($id, $data);
						
	                // commit
	                $productionProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/update-overview transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/update-item-list                        |
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

				$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
	            $productionProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'item_ids'              => serialize($itemList),
						
						'last_update_user_id'   => $this->_adminProperty['id'],
					);

					$productionProjectTable->updateById($id, $data);
						
	                // commit
	                $productionProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/update-item-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/supplier-add                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 調達先- 新規登録                              |
    +----------------------------------------------------------------------------*/
    public function supplierAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->projectId = $projectId = $request->getParam('project_id');
		
		// 委託方法
		$methodTable = new Shared_Model_Data_SupplyProductionMethod();
		$this->view->methodList = $methodTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/supplier-add-post                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 調達先 新規登録(Ajax)                       |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「製造加工委託名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください。'));
                    return;
                } else if (!empty($errorMessage['base_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引拠点名」を入力してください'));
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
				$productionTable = new Shared_Model_Data_SupplyProduction();
				$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
				
				// 新規登録
				
				$conditionList = array();
	            if (!empty($success['condition_list'])) {
	            	$conditionIdList = explode(',', $success['condition_list']);
	            	
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
	            
	            $productionTable->getAdapter()->beginTransaction();
            	
	            try {
	            
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'project_id'                        => $projectId,
						'status'                            => $success['status'],
						
						'title'                             => '',      // 製造加工委託名
						'description'                       => '',      // 製造加工委託内容
						
						'target_connection_id'              => $success['target_connection_id'], // 取引先ID
						
						'base_name'                         => $success['base_name'],            // 取引拠点名
						
						'purposes'                          => serialize(array()),   // 目的
						'purpose_memo'                      => '',                   // 目的メモ
						
						'methods'                           => serialize($success['methods']),   // 委託方法
						'method_memo'                       => $success['method_memo'],          // 委託方法メモ
						'supplying_memo'                    => $success['supplying_memo'],       // 当社支給品
						
						'condition_list'                    => json_encode($conditionList),      // 委託条件
						
						'condition_price'                   => '',      // 委託金額
						'condition_memo'                    => '',      // 条件メモ
						
						'file_list'                         => json_encode(array()),           // 入手見積書
				
						'created_user_id'                   => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$productionTable->create($data);
					$id = $productionTable->getLastInsertedId('id');


					$productionProjectTable->updateById($projectId, array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/add-post transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/supplier-detail                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 調達先 詳細                                 |
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
			$this->view->backUrl = '/supply-production';
		}
		
		$productionTable        = new Shared_Model_Data_SupplyProduction();
		$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
		$connectionTable        = new Shared_Model_Data_Connection();
		
		$this->view->data = $data = $productionTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}
		
		$this->view->projectData = $projectData= $productionProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $productionTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);
        

		$userTable = new Shared_Model_Data_User();
		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 委託方法
		$methodTable = new Shared_Model_Data_SupplyProductionMethod();
		$this->view->methodList = $methodTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		
		$materialTable = new Shared_Model_Data_Material();
		$selectObj = $materialTable->select();
		$this->view->estimateItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCTION, $id, Shared_Model_Code::MATERIAL_TYPE_ESTIMATE, NULL);
		$this->view->documentItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCTION, $id, Shared_Model_Code::MATERIAL_TYPE_DOCUMENT, $materialKind);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/update-supplier                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 仕入先更新(Ajax)                            |
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
				$productionTable = new Shared_Model_Data_SupplyProduction();
				$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
				
				$oldData = $productionTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $productionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_connection_id'            => $success['target_connection_id'],
						'base_name'                       => $success['base_name'],
						'status'                          => $success['status'],
						'history_memo'                    => $success['history_memo'],
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$productionTable->updateById($id, $data);

					$productionProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/update-supplyer transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/update-basic                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 基本情報更新(Ajax)                          |
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
				$productionTable = new Shared_Model_Data_SupplyProduction();
				$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
				
				$oldData = $productionTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $productionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(						
						'methods'                => serialize($success['methods']),   // 委託方法
						'method_memo'            => $success['method_memo'],          // 委託方法メモ
						
						'supplying_memo'         => $success['supplying_memo'],       // 当社支給品
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$productionTable->updateById($id, $data);

					$productionProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/update-basic transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/update-condition                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 委託条件 更新(Ajax)                         |
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
	            
				$productionTable = new Shared_Model_Data_SupplyProduction();
	            $productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
	            
	            $oldData = $productionTable->getById($this->_adminProperty['management_group_id'], $id);
	            
	            $productionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'condition_list'                  => json_encode($conditionList),
						
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$productionTable->updateById($id, $data);

					$productionProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/update-condition transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/update-file-list                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 - 入手見積書・補足資料 更新(Ajax)             |
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
				$productionTable = new Shared_Model_Data_SupplyProduction();
	            $productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
	            
	            $oldData = $productionTable->getById($this->_adminProperty['management_group_id'], $id);
	            
	            $productionTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');

						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_SupplyProduction::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
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

					$productionTable->updateById($id, $data);

					$productionProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $productionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $productionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-production/update-condition transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-production/upload                                  |
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
		$tempFileName = uniqid();
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($tempFileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName, 'temp_file_name' => $tempFileName));
        return;
	}
}

