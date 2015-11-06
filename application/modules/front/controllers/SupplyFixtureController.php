<?php
/**
 * class SupplyFixtureController
 */
class SupplyFixtureController extends Front_Model_Controller
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
		$this->view->menu = 'fixture';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/copied                                     |
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
			$projectTable = new Shared_Model_Data_SupplyFixtureProject();

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
    |  action_URL    * /supply-fixture                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('supply_fixture_2');
		
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
			$session->conditions['use']            = $request->getParam('use', '');
			$session->conditions['tag_name']       = $request->getParam('tag_name', '');
			$session->conditions['tag_id']         = $request->getParam('tag_id', '');

			$session->conditions['connection_name']  = $request->getParam('connection_name', '');
			$session->conditions['connection_id']    = $request->getParam('connection_id', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']         = '';
			$session->conditions['use']            = '';
			$session->conditions['tag_name']       = '';
			$session->conditions['tag_id']         = '';

			$session->conditions['connection_name']  = '';
			$session->conditions['connection_id']    = '';
		}
		
		$this->view->conditions = $conditions = $session->conditions;
		
		$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
		
		$dbAdapter = $fixtureProjectTable->getAdapter();

        $selectObj = $fixtureProjectTable->select();
		$selectObj ->joinLeft('frs_supply_fixture_tag', 'frs_supply_fixture_project.tag_id = frs_supply_fixture_tag.id', array('tag_name'));
		$selectObj->joinLeft('frs_supply_fixture', 'frs_supply_fixture_project.id = frs_supply_fixture.project_id', array('target_connection_id'));
		
        // グループID
        $selectObj->where('frs_supply_fixture_project.management_group_id = ?', $this->_adminProperty['management_group_id']);

		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_fixture_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_fixture_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}
		
        if ($conditions['use'] != '') {
            $useString = $dbAdapter->quote('%"' . $conditions['use'] .'"%');
			$selectObj->where($fixtureProjectTable->aesdecrypt('uses', false) . ' LIKE ' . $useString);
        }

		if (!empty($conditions['tag_id'])) {
			$selectObj->where('tag_id = ?', $conditions['tag_id']);
		}

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_fixture.target_connection_id = ?', $conditions['connection_id']);
		}
		
		$selectObj->group('frs_supply_fixture_project.id');
		$selectObj->order('frs_supply_fixture_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
		
		
		// 用途
		$useTable = new Shared_Model_Data_SupplyFixtureUse();
		$this->view->useList = $useTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/list-select                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 選択(ポップアップ用)                              |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');

		$conditions = array();
		$conditions['status']         = $request->getParam('status', '');
		$conditions['use']            = $request->getParam('use', '');
		$conditions['tag_name']       = $request->getParam('tag_name', '');
		$conditions['tag_id']         = $request->getParam('tag_id', '');
		$conditions['connection_id']  = $request->getParam('connection_id', '');
		$this->view->conditions       = $conditions;
		
		$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
		
		$dbAdapter = $fixtureProjectTable->getAdapter();

        $selectObj = $fixtureProjectTable->select();
		$selectObj ->joinLeft('frs_supply_fixture_tag', 'frs_supply_fixture_project.tag_id = frs_supply_fixture_tag.id', array('tag_name'));
		$selectObj->joinLeft('frs_supply_fixture', 'frs_supply_fixture_project.id = frs_supply_fixture.project_id', array('target_connection_id'));  
        

        // グループID
        $selectObj->where('frs_supply_fixture_project.management_group_id = ?', $this->_adminProperty['management_group_id']);  

		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_fixture_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_fixture_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_fixture.target_connection_id = ?', $conditions['connection_id']);
		}
		
		$selectObj->group('frs_supply_fixture_project.id');
		$selectObj->order('frs_supply_fixture_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        
        $url = 'javascript:pageSupplyFixture($page);';
        $this->view->pager($paginator, $url);
		
		
		// 用途
		$useTable = new Shared_Model_Data_SupplyFixtureUse();
		$this->view->useList = $useTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/delete                                     |
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
			$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();

			try {
				$fixtureProjectTable->getAdapter()->beginTransaction();
				
				$fixtureProjectTable->updateById($id, array(
					'status' => Shared_Model_Code::SUPPLY_STATUS_DELETED,
				));
			
                // commit
                $fixtureProjectTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $fixtureProjectTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-production/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/add                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		// 用途
		$useTable = new Shared_Model_Data_SupplyFixtureUse();
		$this->view->useList = $useTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/add-post                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 新規登録(Ajax)                                  |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「備品資材名」を入力してください'));
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
				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
				
				// 新規登録
	            $fixtureProjectTable->getAdapter()->beginTransaction();
            	
	            try {

	            	$displayId = $fixtureProjectTable->getNextDisplayId();
	            
					$data = array(
				        'management_group_id'       => $this->_adminProperty['management_group_id'],
				        'display_id'                => $displayId,
						'status'                    => $success['status'],
						
						'tag_id'                    => $success['tag_id'],                      // 一般名称タグ
						
						'title'                     => $success['title'],                // 製造加工委託名
						'description'               => $success['description'],          // 製造加工委託内容
						
						'uses'                      => serialize($success['uses']),      // 用途
						'use_memo'                  => $success['use_memo'],             // 用途メモ
						
						'other_memo'                => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'item_ids'                  => serialize(array()),               // 対象商品ID
						
						'created_user_id'           => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'       => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                   => new Zend_Db_Expr('now()'),
		                'updated'                   => new Zend_Db_Expr('now()'),
					);
					
					$fixtureProjectTable->create($data);
					$id = $fixtureProjectTable->getLastInsertedId('id');

	                // commit
	                $fixtureProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/add-post transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/detail                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 プロジェクト詳細                                  |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/supply-fixture';
		}
		
		$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
		$fixtureTable        = new Shared_Model_Data_SupplyFixture();
		
		$this->view->data = $data = $fixtureProjectTable->getById($this->_adminProperty['management_group_id'], $id);
        $this->view->supplierList = $fixtureTable->getListByProjectId($this->_adminProperty['management_group_id'], $id);
		
		$userTable = new Shared_Model_Data_User();
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 用途
		$useTable = new Shared_Model_Data_SupplyFixtureUse();
		$this->view->useList = $useTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/update-overview                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - プロジェクト概要更新(Ajax)                      |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「備品資材名」を入力してください'));
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
				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
				$fixtureProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'tag_id'                    => $success['tag_id'],               // 一般名称タグ
						
						'title'                     => $success['title'],                // 備品資材名
						'description'               => $success['description'],          // 備品資材内容
						'status'                    => $success['status'],
						
						'uses'                      => serialize($success['uses']),  // 用途
						'use_memo'                  => $success['use_memo'],         // 用途メモ
						
						'other_memo'                => $success['other_memo'],       // 調達方法・注意点等メモ
						
						'last_update_user_id'   => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					);
					
					$fixtureProjectTable->updateById($id, $data);
						
	                // commit
	                $fixtureProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/update-overview transaction failed: ' . $e);    
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/update-item-list                           |
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

				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();

	            $fixtureProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'item_ids'              => serialize($itemList),
						
						'last_update_user_id'   => $this->_adminProperty['id'],
					);

					$fixtureProjectTable->updateById($id, $data);
						
	                // commit
	                $fixtureProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/update-item-list transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/supplier-add                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 仕入先新規登録                                  |
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
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/supplier-add-post                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 仕入先新規登録(Ajax)                            |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「備品資材名」を入力してください'));
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
				$fixtureTable = new Shared_Model_Data_SupplyFixture();
				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
				
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
	            
	            $fixtureTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'project_id'                        => $projectId,
						'status'                            => $success['status'],
						
						'title'                             => '',      // 備品資材名
						'description'                       => '',      // 備品資材内容

						'target_connection_id'              => $success['target_connection_id'], // 取引先ID
						
						'base_name'                         => $success['base_name'],            // 取引拠点名

						'uses'                              => serialize(array()),               // 用途
						'use_memo'                          => '',                               // 用途メモ
						
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
					
					$fixtureTable->create($data);
					$id = $fixtureTable->getLastInsertedId('id');
					
					
					$fixtureProjectTable->updateById($projectId, array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileList as $each) {
							$tempFileName = $request->getParam($each['id'] . '_temp_file_name');
		            		$fileName     = $request->getParam($each['id'] . '_file_name');
		            		
			            	if (!empty($tempFileName)) {
			            		// 正式保存
			            		Shared_Model_Resource_SupplyFixture::makeResource($id, $each['id'], $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								
							}
						}
					}
						
	                // commit
	                $fixtureTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/add-post transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/supplier-detail                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 仕入先詳細                                      |
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
			$this->view->backUrl = '/supply-fixture';
		}
		
		$fixtureTable        = new Shared_Model_Data_SupplyFixture();
		$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
		$connectionTable     = new Shared_Model_Data_Connection();
		
		$this->view->data = $data = $fixtureTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}

		$this->view->projectData = $projectData = $fixtureProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $fixtureTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);
        		
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
		$this->view->estimateItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_FIXTURE, $id, Shared_Model_Code::MATERIAL_TYPE_ESTIMATE, NULL);
		$this->view->documentItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_FIXTURE, $id, Shared_Model_Code::MATERIAL_TYPE_DOCUMENT, $materialKind);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/update-supplier                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 仕入先更新(Ajax)                                |
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
				$fixtureTable = new Shared_Model_Data_SupplyFixture();
				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
				
				$oldData = $fixtureTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $fixtureTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_connection_id'            => $success['target_connection_id'],
						'base_name'                       => $success['base_name'],
						'status'                          => $success['status'],
						'history_memo'                    => $success['history_memo'],
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$fixtureTable->updateById($id, $data);
					
					$fixtureProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $fixtureTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/update-supplier transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/update-basic                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 基本情報更新(Ajax)                              |
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
				$fixtureTable = new Shared_Model_Data_SupplyFixture();
	            $fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
	            
	            $oldData = $fixtureTable->getById($this->_adminProperty['management_group_id'], $id);
	            
	            $fixtureTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'individual_name'        => $success['individual_name'],      // 仕入先毎呼称
						'methods'                => serialize($success['methods']),   // 調達方法
						'method_memo'            => $success['method_memo'],          // 調達方法メモ
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$fixtureTable->updateById($id, $data);

					$fixtureProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $fixtureTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/update-basic transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/update-condition                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 仕入先更新(Ajax)                                |
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
	            
				$fixtureTable = new Shared_Model_Data_SupplyFixture();
				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
				
				$oldData = $fixtureTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $fixtureTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'condition_list'          => json_encode($conditionList),
						
						'last_update_user_id'     => $this->_adminProperty['id'],
					);

					$fixtureTable->updateById($id, $data);

					$fixtureProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $fixtureTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/update-condition transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/update-file-list                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - 入手見積書・補足資料 更新(Ajax)                 |
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
				$fixtureTable    = new Shared_Model_Data_SupplyFixture();
				$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
				
				$oldData = $fixtureTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $fixtureTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');

						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_SupplyFixture::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
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

					$fixtureTable->updateById($id, $data);

					$fixtureProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $fixtureTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $fixtureTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-fixture/update-condition transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/upload                                     |
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
	

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/tag-list                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - タグ一覧                                        |
    +----------------------------------------------------------------------------*/
    public function tagListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$tagTable = new Shared_Model_Data_SupplyFixtureTag();
		
		$dbAdapter = $tagTable->getAdapter();

        $selectObj = $tagTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
        
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
    |  action_URL    * /supply-fixture/tag-list-select                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - タグ一覧(ポップアップ用)                        |
    +----------------------------------------------------------------------------*/
    public function tagListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$tagTable = new Shared_Model_Data_SupplyFixtureTag();
		
		$dbAdapter = $tagTable->getAdapter();

        $selectObj = $tagTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
        
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
        
        $url = 'javascript:pageTag($page);';
        $this->view->pager($paginator, $url);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/tag-detail                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 - タグ・編集                                      |
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
		
		$tagTable = new Shared_Model_Data_SupplyFixtureTag();
		
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
				throw new Zend_Exception('/supply-fixture/tag-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-fixture/tag-update                                 |
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
		
		$tagTable = new Shared_Model_Data_SupplyFixtureTag();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/supply-fixture/tag-update failed to load config');
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

