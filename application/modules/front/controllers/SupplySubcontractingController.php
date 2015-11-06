<?php
/**
 * class SupplySubcontractingController
 */
class SupplySubcontractingController extends Front_Model_Controller
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
		$this->view->menu = 'subcontracting';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/copied                              |
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
			$projectTable = new Shared_Model_Data_SupplySubcontractingProject();

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
    |  action_URL    * /supply-subcontracting                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('supply_subcontracting_2');
		
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
			
		$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
		
		$dbAdapter = $subcontractingProjectTable->getAdapter();

        $selectObj = $subcontractingProjectTable->select();
        $selectObj->joinLeft('frs_supply_subcontracting', 'frs_supply_subcontracting_project.id = frs_supply_subcontracting.project_id', array('target_connection_id'));
        
        // グループID
        $selectObj->where('frs_supply_subcontracting_project.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_subcontracting_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_subcontracting_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}

        if ($conditions['purpose'] != '') {
            $purposeString = $dbAdapter->quote('%"' . $conditions['purpose'] .'"%');
			$selectObj->where('purposes LIKE ' . $purposeString);
        }
        
        if (!empty($session->conditions['keyword'])) {
	        $likeString = array();
	        $likeString[] = $dbAdapter->quoteInto($subcontractingProjectTable->aesdecrypt('frs_supply_subcontracting_project.title', false) . ' LIKE ?', '%' . $session->conditions['keyword'] .'%');
	        $likeString[] = $dbAdapter->quoteInto($subcontractingProjectTable->aesdecrypt('frs_supply_subcontracting_project.description', false) . ' LIKE ?', '%' . $session->conditions['keyword'] .'%');
        	
        	$selectObj->where(implode(' OR ', $likeString));
        }

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_subcontracting.target_connection_id = ?', $conditions['connection_id']);
		}
		
        $selectObj->group('frs_supply_subcontracting_project.id');
		$selectObj->order('frs_supply_subcontracting_project.id DESC');
		
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
		$purposeTable = new Shared_Model_Data_SupplySubcontractingPurpose();
		$this->view->purposeList = $purposeTable->getList();
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/list-select                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 選択(ポップアップ用)                              |
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
			
		$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
		
		$dbAdapter = $subcontractingProjectTable->getAdapter();

        $selectObj = $subcontractingProjectTable->select();
        $selectObj->joinLeft('frs_supply_subcontracting', 'frs_supply_subcontracting_project.id = frs_supply_subcontracting.project_id', array('target_connection_id'));
        
		//$selectObj->joinLeft('frs_connection', 'frs_supply_subcontracting.target_connection_id = frs_connection.id', array($subcontractingTable->aesdecrypt('company_name', false) . 'AS company_name'));
        //$selectObj->joinLeft('frs_user', 'frs_supply_subcontracting.created_user_id = frs_user.id',array($subcontractingTable->aesdecrypt('user_name', false) . 'AS user_name'));
        // グループID
        $selectObj->where('frs_supply_subcontracting_project.management_group_id = ?', $this->_adminProperty['management_group_id']);

		if (!empty($conditions['connection_id'])) {
			$selectObj->where('frs_supply_subcontracting.target_connection_id = ?', $conditions['connection_id']);
		}
		
		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_subcontracting_project.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_subcontracting_project.status != ?', Shared_Model_Code::SUPPLY_STATUS_DELETED);
		}
		
		$selectObj->group('frs_supply_subcontracting_project.id');
		$selectObj->order('frs_supply_subcontracting_project.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;

        $url = 'javascript:pageSupplySubcontracting($page);';
        $this->view->pager($paginator, $url);
        
		// 目的
		$purposeTable = new Shared_Model_Data_SupplySubcontractingPurpose();
		$this->view->purposeList = $purposeTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/delete                              |
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
			$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();

			try {
				$subcontractingProjectTable->getAdapter()->beginTransaction();
				
				$subcontractingProjectTable->updateById($id, array(
					'status' => Shared_Model_Code::SUPPLY_STATUS_DELETED,
				));
			
                // commit
                $subcontractingProjectTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $subcontractingProjectTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-production/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/add                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		// 目的
		$purposeTable = new Shared_Model_Data_SupplySubcontractingPurpose();
		$this->view->puposeList = $purposeTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/add-post                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 新規登録(Ajax)                                  |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「業務委託名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['purposes']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「目的」を選択してください'));
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ステータス」を選択してください'));
                    return;
                }
                
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
				
				// 新規登録
	            $subcontractingProjectTable->getAdapter()->beginTransaction();
            	
	            try {

	            	$displayId = $subcontractingProjectTable->getNextDisplayId();
	            
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'display_id'                        => $displayId,
						'status'                            => $success['status'],
						
						'title'                             => $success['title'],                // 業務委託名
						'description'                       => $success['description'],          // 業務委託内容

						'purposes'                          => serialize($success['purposes']),  // 目的
						'purpose_memo'                      => $success['purpose_memo'],         // 目的メモ
						
						'other_memo'                        => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'item_ids'                          => serialize(array()),               // 対象商品ID
						
						'created_user_id'                   => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$subcontractingProjectTable->create($data);
					$id = $subcontractingProjectTable->getLastInsertedId('id');

	                // commit
	                $subcontractingProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/add-post transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/detail                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 プロジェクト詳細                                  |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/supply-subcontracting';
		}		

		$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
		$subcontractingTable        = new Shared_Model_Data_SupplySubcontracting();
		
		$this->view->data  = $data = $subcontractingProjectTable->getById($this->_adminProperty['management_group_id'], $id);
        $this->view->supplierList = $subcontractingTable->getListByProjectId($this->_adminProperty['management_group_id'], $id);

		$userTable = new Shared_Model_Data_User();
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 目的
		$purposeTable = new Shared_Model_Data_SupplySubcontractingPurpose();
		$this->view->purposeList = $purposeTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/update-overview                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - プロジェクト概要更新(Ajax)                      |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「業務委託名」を入力してください'));
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
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
	            $subcontractingProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'title'                 => $success['title'],                // 業務委託名
						'description'           => $success['description'],          // 業務委託内容
						'status'                => $success['status'],
						
						'purposes'              => serialize($success['purposes']),  // 目的
						'purpose_memo'          => $success['purpose_memo'],         // 目的メモ
						
						'other_memo'            => $success['other_memo'],           // 調達方法・注意点等メモ
						
						'last_update_user_id'   => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					);
					
					$subcontractingProjectTable->updateById($id, $data);
						
	                // commit
	                $subcontractingProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/update-overview transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
	    $this->sendJson($result);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/update-item-list                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 対象商品更新(Ajax)                                |
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

				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
	            $subcontractingProjectTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'item_ids'              => serialize($itemList),
						
						'last_update_user_id'   => $this->_adminProperty['id'],
					);

					$subcontractingProjectTable->updateById($id, $data);
						
	                // commit
	                $subcontractingProjectTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingProjectTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/update-item-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/supplier-add                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 仕入先 新規登録                                 |
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
		$methodTable = new Shared_Model_Data_SupplySubcontractingMethod();
		$this->view->methodList = $methodTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/supplier-add-post                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 仕入先 新規登録(Ajax)                           |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「業務委託名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
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
				$subcontractingTable = new Shared_Model_Data_SupplySubcontracting();
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
				
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
	            if (!empty($fileIdList)) {
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
	            
	            $subcontractingTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'project_id'                        => $projectId,
						'status'                            => $success['status'],
						
						'title'                             => '',      // 業務委託名
						'description'                       => '',      // 業務委託内容
						
						'target_connection_id'              => $success['target_connection_id'], // 取引先ID
						
						'base_name'                         => $success['base_name'],            // 取引拠点名
						
						'purposes'                          => serialize(array()),   // 目的
						'purpose_memo'                      => '',                   // 目的メモ
						
						'methods'                           => serialize($success['methods']),   // 委託方法
						'method_memo'                       => $success['method_memo'],          // 委託方法メモ
						
						'condition_list'                    => json_encode($conditionList),      // 委託条件					
						'condition_price'                   => '',       // 委託金額
						'condition_memo'                    => '',       // 条件メモ
				
						'file_list'                         => json_encode($fileList),           // 入手見積書
				
						'created_user_id'                   => $this->_adminProperty['id'],      // 作成者ユーザーID
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$subcontractingTable->create($data);
					$id = $subcontractingTable->getLastInsertedId('id');

					$subcontractingProjectTable->updateById($projectId, array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
		            		
			            	if (!empty($tempFileName)) {
			            		// 正式保存
			            		Shared_Model_Resource_SupplySubcontracting::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);
								
							}
						}
					}
						
	                // commit
	                $subcontractingTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/add-post transaction faied: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/supplier-detail                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 仕入先 詳細                                     |
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
			$this->view->backUrl = '/supply-subcontracting';
		}
		
		$subcontractingTable        = new Shared_Model_Data_SupplySubcontracting();
		$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
		$connectionTable            = new Shared_Model_Data_Connection();
		
		$this->view->data = $data = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}

		$this->view->projectData = $projectData= $subcontractingProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $subcontractingTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);


		$userTable = new Shared_Model_Data_User();
		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 委託方法
		$methodTable = new Shared_Model_Data_SupplySubcontractingMethod();
		$this->view->methodList = $methodTable->getList();
		
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		
		$materialTable = new Shared_Model_Data_Material();
		$selectObj = $materialTable->select();
		$this->view->estimateItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_SUBCONTRACTING, $id, Shared_Model_Code::MATERIAL_TYPE_ESTIMATE, NULL);
		$this->view->documentItems = $materialTable->getList(Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_SUBCONTRACTING, $id, Shared_Model_Code::MATERIAL_TYPE_DOCUMENT, $materialKind);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/update-supplier                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 仕入先更新(Ajax)                                |
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
				$subcontractingTable  = new Shared_Model_Data_SupplySubcontracting();
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
				
				$oldData = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $subcontractingTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_connection_id'            => $success['target_connection_id'],
						'base_name'                       => $success['base_name'],
						'status'                          => $success['status'],
						'history_memo'                    => $success['history_memo'],
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$subcontractingTable->updateById($id, $data);

					$subcontractingProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $subcontractingTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/update-supplier transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/update-basic                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 基本情報更新(Ajax)                              |
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
				$subcontractingTable  = new Shared_Model_Data_SupplySubcontracting();
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
				
				$oldData = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $subcontractingTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						//'purposes'               => serialize($success['purposes']),   // 目的
						//'purpose_memo'           => $success['purpose_memo'],         // 目的メモ
						
						'methods'                => serialize($success['methods']),   // 委託方法
						'method_memo'            => $success['method_memo'],          // 委託方法メモ
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);
				
					$subcontractingTable->updateById($id, $data);

					$subcontractingProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
						
	                // commit
	                $subcontractingTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/update-basic transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/update-condition                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 委託条件 更新(Ajax)                             |
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

				$subcontractingTable  = new Shared_Model_Data_SupplySubcontracting();
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
				
				$oldData = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $subcontractingTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'condition_list'                  => json_encode($conditionList),
						
						'last_update_user_id'             => $this->_adminProperty['id'],
					);

					$subcontractingTable->updateById($id, $data);

					$subcontractingProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
							
	                // commit
	                $subcontractingTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/update-condition transaction failed: ' . $e); 
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/update-file-list                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 - 入手見積書・補足資料 更新(Ajax)                 |
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
				$subcontractingTable  = new Shared_Model_Data_SupplySubcontracting();
				$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
				
				$oldData = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $subcontractingTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');
						
						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_SupplySubcontracting::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
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

					$subcontractingTable->updateById($id, $data);

					$subcontractingProjectTable->updateById($oldData['project_id'], array(
						'last_update_user_id'               => $this->_adminProperty['id'],      // 最終更新者ユーザーID
					));
					
	                // commit
	                $subcontractingTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $subcontractingTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-subcontracting/update-condition transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-subcontracting/upload                              |
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

