<?php
/**
 * class PriceEstimateController
 */
class PriceEstimateController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '商品価格決定・提出見積';
		$this->view->menuCategory     = 'price';
		$this->view->menu             = 'estimate';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }		
				
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/list                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 提出見積管理                                               |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('price_estimate_2');

		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['status']              = $request->getParam('status', '');
			$session->conditions['type']                = $request->getParam('type', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['item_name']           = $request->getParam('item_name', '');
			$session->conditions['item_id']             = $request->getParam('item_id', '');
			$session->conditions['keyword']             = $request->getParam('keyword', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']              = '';
			$session->conditions['type']             = '';
			$session->conditions['connection_name']  = '';
			$session->conditions['connection_id']    = '';
			$session->conditions['item_name']        = '';
			$session->conditions['item_id']          = '';
			$session->conditions['keyword']          = '';
		}
		
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		// 検索条件
		/*
		$conditions = array();
		$conditions['status']      = $request->getParam('status', '');
		$conditions['connection']  = $request->getParam('connection', '');
		$conditions['type']        = $request->getParam('type', '');
		$conditions['keyword']     = $request->getParam('keyword', '');
		$this->view->conditions    = $conditions;
		*/
		
    	$estimateTable = new Shared_Model_Data_Estimate();
		
		$dbAdapter = $estimateTable->getAdapter();

        $selectObj = $estimateTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_estimate.target_connection_id = frs_connection.id', array($estimateTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_estimate.created_user_id = frs_user.id',array($estimateTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		
		$selectObj->joinLeft('frs_estimate_version', 'frs_estimate.id = frs_estimate_version.estimate_id', array($estimateTable->aesdecrypt('item_list', false) . 'AS item_list'));
		
		$selectObj->where('frs_estimate.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if (!empty($conditions['status'])) {
        	$selectObj->where('frs_estimate.status = ?', $conditions['status']);
        } else {
        	$selectObj->where('frs_estimate.status != ?', Shared_Model_Code::ESTIMATE_STATUS_DELETED);
        }
        
        if (!empty($conditions['connection_id'])) {	        
	        $selectObj->where('frs_estimate.target_connection_id = ?', $conditions['connection_id']);
        }
                
        if (!empty($conditions['item_id'])) {
	        $keyword = $dbAdapter->quote('%"item_id":"' . $conditions['item_id'] . '"%');
	        $keywordString = $estimateTable->aesdecrypt('item_list', false) . ' LIKE ' . $keyword;
	        $selectObj->where($keywordString);
        }
        
        
        $selectObj->group('frs_estimate.id');
        
		$selectObj->order('frs_estimate.updated DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator);
		
		$templateList = array();
    	$templateTable     = new Shared_Model_Data_EstimateTemplate();
    	$templateItems = $templateTable->getList();
    	foreach ($templateItems as $each) {
    		$templateList[$each['id']] = $each;
    	}
    	$this->view->templateList = $templateList;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/list-select                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * リスト選択用(ポップアップ画面)                             |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request      = $this->getRequest();
		$connectionId = $request->getParam('connection_id', '1');
		$page         = $request->getParam('page', '1');

		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $connectionId);
		
    	$estimateTable = new Shared_Model_Data_Estimate();
		
		$dbAdapter = $estimateTable->getAdapter();

        $selectObj = $estimateTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_estimate.target_connection_id = frs_connection.id', array($estimateTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_estimate.created_user_id = frs_user.id',array($estimateTable->aesdecrypt('user_name', false) . 'AS user_name'));
        
        $selectObj->where('frs_estimate.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        $selectObj->where('frs_estimate.target_connection_id = ?', $connectionId);
        $selectObj->where('frs_estimate.status = ?', Shared_Model_Code::ESTIMATE_STATUS_SUBMITTED);// 提出済み
		
		$selectObj->order('frs_estimate.updated DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$templateList = array();
    	$templateTable     = new Shared_Model_Data_EstimateTemplate();
    	$templateItems = $templateTable->getList();
    	foreach ($templateItems as $each) {
    		$templateList[$each['id']] = $each;
    	}
    	$this->view->templateList = $templateList;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/delete                                     |
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
			$estimateTable = new Shared_Model_Data_Estimate();

			try {
				$estimateTable->getAdapter()->beginTransaction();
				
				$estimateTable->updateById($id, array(
					'status' => Shared_Model_Code::ESTIMATE_STATUS_DELETED,
				));
			
                // commit
                $estimateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $estimateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-estimate/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/version-list                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積編集履歴                                               |
    +----------------------------------------------------------------------------*/
    public function versionListAction()
    {
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$estimateTable = new Shared_Model_Data_Estimate();
		$this->view->data = $estimateTable->getById($this->_adminProperty['management_group_id'], $id);

    	$versionTable = new Shared_Model_Data_EstimateVersion();
    	$this->view->items = $versionTable->getListByEstimateId($id);
    
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/price-estimate/list';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/copy                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積バージョンコピー                                       |
    +----------------------------------------------------------------------------*/
    public function copyAction()
    {
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$baseVersionId = $request->getParam('base_version_id');
		
		$estimateTable = new Shared_Model_Data_Estimate();
		$versionTable  = new Shared_Model_Data_EstimateVersion();
		
		$data = $estimateTable->getById($this->_adminProperty['management_group_id'], $id);
    	$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $baseVersionId);

		try {
			$estimateTable->getAdapter()->beginTransaction();
			
			$estimateTable->updateById($id, array('status' => Shared_Model_Code::ESTIMATE_STATUS_DRAFT));
			 
	    	$versionTable->updateById($baseVersionId, array('is_copied' => 1));
	    	
	        $versionTable->create(array(
		        'management_group_id'               => $this->_adminProperty['management_group_id'],
		        'estimate_id'                       => $id,
		        'version_id'                        => (int)$versionData['version_id'] + 1,
				'version_status'                    => Shared_Model_Code::ESTIMATE_VERSION_STATUS_MAKING,
				
				'target_connection_id'              => $versionData['target_connection_id'],
				'title'                             => $versionData['title'],
				
				'template_id'                       => $versionData['template_id'],
				'memo'                              => $versionData['memo'],
				'memo_private'                      => $versionData['memo_private'],
				
				'created_user_id'                   => $this->_adminProperty['id'],                     // 作成者ユーザーID
				'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID
	
	            'created'                           => new Zend_Db_Expr('now()'),
	            'updated'                           => new Zend_Db_Expr('now()'),
	        ));
	        $newVersionId = $versionTable->getLastInsertedId('id');

            // commit
            $estimateTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $estimateTable->getAdapter()->rollBack();
            throw new Zend_Exception('/price-estimate/copy transaction faied: ' . $e);
            
        }        

        $this->_redirect('/price-estimate/form?id=' . $id . '&version_id=' . $newVersionId);
    }
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/create                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積書新規作成                                             |
    +----------------------------------------------------------------------------*/
    public function createAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
		$request = $this->getRequest();	
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/add-post                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積書新規登録(Ajax)                                       |
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

                if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「取引先」を選択してください';
                    $this->sendJson($result);
                    return; 
                } else if (!empty($errorMessage['template_id']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「テンプレート」を選択してください';
                    $this->sendJson($result);
                    return;
                     
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「表題」を入力してください';
                    $this->sendJson($result);
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$estimateTable    = new Shared_Model_Data_Estimate();
				$versionTable     = new Shared_Model_Data_EstimateVersion();
				$templateTable    = new Shared_Model_Data_EstimateTemplate();
				$connectionTable  = new Shared_Model_Data_Connection();
				
				// 取引先が有効か
				$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $success['target_connection_id']);
				if (empty($connectionData)) {
					throw new Zend_Exception('/price-estimate/add-post connection data is empty');
				}
				
					
				$defaultItems = array(
					array(
					'id'                 => '1',
					'item_id'            => '',
					'item_name'          => '',
					'unit'               => '',
					'unit_price'         => '',
					'standard_price'     => '',
					'standard_price_tax' => '',
					'wholesale_rate'     => '',
					'price_per_month'    => '',
					)
				);
				
				$nextEstimateId = $estimateTable->getNextDisplayId();
				
				$templateData = $templateTable->getById($success['template_id']);
	            
				$data = array(
			        'management_group_id'               => $this->_adminProperty['management_group_id'],
			        'display_id'                        => $nextEstimateId,
					'status'                            => Shared_Model_Code::ESTIMATE_STATUS_DRAFT,
					'target_connection_id'              => $success['target_connection_id'],

					'title'                             => $success['title'],
					'template_id'                       => $success['template_id'],
					
					'created_user_id'                   => $this->_adminProperty['id'],                     // 作成者ユーザーID
					'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID

	                'created'                           => new Zend_Db_Expr('now()'),
	                'updated'                           => new Zend_Db_Expr('now()'),
				);

				$versiondData = array(
			        'management_group_id'               => $this->_adminProperty['management_group_id'],
			        'estimate_id'                       => NULL,
			        'version_id'                        => '1',
					'version_status'                    => Shared_Model_Code::ESTIMATE_VERSION_STATUS_MAKING,
					
					'target_connection_id'              => $success['target_connection_id'],
					'to_name'                           => $connectionData['company_name'] . ' 御中',
					
					'title'                             => $success['title'],
					
					'template_id'                       => $success['template_id'],
					'labels'                            => json_encode($templateData['default_labels']), // テーブル項目ラベル
					'item_list'                         => json_encode($defaultItems),
		
					'memo'                              => '',
					'memo_private'                      => '',
					
					'created_user_id'                   => $this->_adminProperty['id'],                     // 作成者ユーザーID
					'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID

	                'created'                           => new Zend_Db_Expr('now()'),
	                'updated'                           => new Zend_Db_Expr('now()'),
				);

				$estimateTable->getAdapter()->beginTransaction();
            	  
	            try {
					$estimateTable->create($data);
					$id = $estimateTable->getLastInsertedId('id');
					
					$versiondData['estimate_id'] = $id;
					$versionTable->create($versiondData);
					$versionId = $versionTable->getLastInsertedId('id');
					
	                // commit
	                $estimateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $estimateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-estimate/add-post transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $id, 'versionId' => $versionId));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/create-by-copy                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コピーから新規作成                                         |
    +----------------------------------------------------------------------------*/
    public function createByCopyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$baseVersionId = $request->getParam('base_version_id');

		$estimateTable    = new Shared_Model_Data_Estimate();
		$versionTable     = new Shared_Model_Data_EstimateVersion();
		$templateTable    = new Shared_Model_Data_EstimateTemplate();
		$connectionTable  = new Shared_Model_Data_Connection();
		
		// 取引先が有効か
		//$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $success['target_connection_id']);
		//if (empty($connectionData)) {
		//	throw new Zend_Exception('/price-estimate/create-by-copy connection data is empty');
		//}
		$baseVersionData = $versionTable->getById($this->_adminProperty['management_group_id'], $baseVersionId);		
		
			
		$nextEstimateId = $estimateTable->getNextDisplayId();
		
		$templateData = $templateTable->getById($baseVersionData['template_id']);
        
		$data = array(
	        'management_group_id'               => $this->_adminProperty['management_group_id'],
	        'display_id'                        => $nextEstimateId,
			'status'                            => Shared_Model_Code::ESTIMATE_STATUS_DRAFT,
			'target_connection_id'              => 0,

			'title'                             => $baseVersionData['title'],
			'template_id'                       => $baseVersionData['template_id'],
			
			'created_user_id'                   => $this->_adminProperty['id'],                     // 作成者ユーザーID
			'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID

            'created'                           => new Zend_Db_Expr('now()'),
            'updated'                           => new Zend_Db_Expr('now()'),
		);

		$versiondData = array(
	        'management_group_id'               => $this->_adminProperty['management_group_id'],
	        'estimate_id'                       => NULL,
	        'version_id'                        => '1',
			'version_status'                    => Shared_Model_Code::ESTIMATE_VERSION_STATUS_MAKING,
			
			'target_connection_id'              => 0,
			'to_name'                           => '',
			
			'title'                             => $baseVersionData['title'],
			
			'template_id'                       => $baseVersionData['template_id'],
			'labels'                            => json_encode($templateData['default_labels']), // テーブル項目ラベル
			'item_list'                         => json_encode($baseVersionData['item_list']),
			
			'file_name'                         => '',
			
			'memo'                              => '',
			'memo_private'                      => '',
			
			'created_user_id'                   => $this->_adminProperty['id'],                     // 作成者ユーザーID
			'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID

            'created'                           => new Zend_Db_Expr('now()'),
            'updated'                           => new Zend_Db_Expr('now()'),
		);

		$estimateTable->getAdapter()->beginTransaction();
    	  
        try {
			$estimateTable->create($data);
			$id = $estimateTable->getLastInsertedId('id');
			
			$versiondData['estimate_id'] = $id;
			$versionTable->create($versiondData);
			$versionId = $versionTable->getLastInsertedId('id');
			
            // commit
            $estimateTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $estimateTable->getAdapter()->rollBack();
            throw new Zend_Exception('/price-estimate/create-by-copy transaction faied: ' . $e);
            
        }
		
	    $this->sendJson(array('result' => 'OK', 'id' => $id, 'versionId' => $versionId));
    	return;

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/form                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積書フォーム                                             |
    +----------------------------------------------------------------------------*/
    public function formAction()
    {
        $this->_helper->layout->setLayout('back_menu_estimate');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->versionId = $versionId = $request->getParam('version_id');
		
		$estimateTable   = new Shared_Model_Data_Estimate();
		$versionTable    = new Shared_Model_Data_EstimateVersion();
		$templateTable   = new Shared_Model_Data_EstimateTemplate();
		$connectionTable = new Shared_Model_Data_Connection();
		$userTable       = new Shared_Model_Data_User();
		
		$this->view->data        = $data        = $estimateTable->getById($this->_adminProperty['management_group_id'], $id);
    	$this->view->versionData = $versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
    	
    	// フォーマット
    	$this->view->formatData     = $templateTable->getById($versionData['template_id']);
    	
    	// 提出先
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	// 見積作成者
    	if (!empty($versionData['created_user_id'])) {
    		$this->view->createdUser = $userTable->getById($versionData['created_user_id']);
    	}
    	
    	$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/template-list                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート一覧                                           |
    +----------------------------------------------------------------------------*/
    public function templateListAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
    	$templateTable     = new Shared_Model_Data_EstimateTemplate();
    	$this->view->items = $templateTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/change-template                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート切り替え                                       |
    +----------------------------------------------------------------------------*/
    public function changeTemplateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$versionId  = $request->getParam('version_id');
		$templateId = $request->getParam('template_id');
		
    	$templateTable = new Shared_Model_Data_EstimateTemplate();
    	$estimateTable = new Shared_Model_Data_Estimate();
    	$versionTable  = new Shared_Model_Data_EstimateVersion();
    	
    	// テンプレートデータ
    	$templateData  = $templateTable->getById($templateId);
    	//var_dump($templateData['default_labels']);exit;
    	if (empty($templateData)) {
    		throw new Zend_Exception('/price-estimate/change-template - no template data');
    	}
		
		try {
			$versionTable->getAdapter()->beginTransaction();
			
			$data = array(
			'template_id' => $templateData['id'],
			);
			$estimateTable->updateById($id, $data);
			
			
			$versionData = array(
				'template_id' => $templateData['id'],
				'labels'      => json_encode($templateData['default_labels']), // テーブル項目ラベル
			);
			
			$versionTable->updateById($versionId, $versionData);
			
            // commit
            $versionTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $versionTable->getAdapter()->rollBack();
            throw new Zend_Exception('/price-estimate/update transaction faied: ' . $e);
        }

	    $this->sendJson(array('result' => 'OK'));
    	return;
		    	
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/update                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積書フォーム 保存                                        |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$versionId  = $request->getParam('version_id');
		
		// POST送信時
		if ($request->isPost()) {
			$estimateTable = new Shared_Model_Data_Estimate();
			$versionTable  = new Shared_Model_Data_EstimateVersion();
			$templateTable = new Shared_Model_Data_EstimateTemplate();
			
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
            
            // 現在のバージョンデータ
            $oldVersionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
         	
         	// テンプレートデータ
         	$templateData = $templateTable->getById($oldVersionData['template_id']);
         	    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$estimateDate = NULL;
				if (!empty($success['estimate_date'])) {
					$year  = mb_substr($success['estimate_date'], 0, 4);
	            	$month = mb_substr($success['estimate_date'], 5, 2);
	            	$date  = mb_substr($success['estimate_date'], 8, 2);
	            	$estimateDate = $year . '-' . $month . '-' . $date;
            	}

				$data = array(
					'target_connection_id'       => $success['target_connection_id'], // 提出先取引ID
					'estimate_date'              => $estimateDate,                    // 見積書発行日
					'title'                      => $success['title'],                // タイトル
				);
				
				$versionData = array(
					'target_connection_id'       => $success['target_connection_id'], // 提出先取引ID
					'template_id'                => $success['template_id'],          // 選択済みテンプレートID
					'to_name'                    => $success['to_name'],              // 宛先
					
					'title'                      => $success['title'],                // タイトル
					
					'memo'                       => $success['memo'],                 // 備考
					'memo_private'               => $success['memo_private'],         // 社内メモ
					
					'created_user_id'            => $success['created_user_id'], // 見積作成者
				);

				// ファイルアップロード
				if (!empty($success['file_name'])) {
					$versionData['file_name'] = $success['file_name'];
				}
					
				// ラベル
				$labels = $templateData['default_labels'];
				
				if (!empty($labels)) {
					foreach ($labels as $key => &$val) {
						$val = $request->getParam($key);
					}
				}
				
				$versionData['labels'] = json_encode($labels);
				
				// テーブル中身
				if (!empty($success['estimate_item_list'])) {
					$estimateItemList = explode(',', $success['estimate_item_list']);
				
					$itemList = array();
					$count = 1;

		            foreach ($estimateItemList as $eachId) {
		            	$itemId           = $request->getParam($eachId . '_item_id', '');
		            	$itemName         = $request->getParam($eachId . '_item_name', '');
		            	$unit             = $request->getParam($eachId . '_unit', '');
		            	$standardPrice    = $request->getParam($eachId . '_standard_price', '');
						$standardPriceTax = $request->getParam($eachId . '_standard_price_tax', '');
						$wholeSaleRate    = $request->getParam($eachId . '_wholesale_rate', '');
						$unitPrice        = $request->getParam($eachId . '_unit_price', '');
						$pricePerMonth    = $request->getParam($eachId . '_price_per_month', '');
						$standardPriceImportCurrency = $request->getParam($eachId . '_standard_price_import_currency', '');
						$standardPriceImport    = $request->getParam($eachId . '_standard_price_import', '');
						$wholesaleRateOverseas  = $request->getParam($eachId . '_wholesale_rate_overseas', '');
						/*
	                	if (empty($itemName)) {
						    $this->sendJson(array('result' => 'NG', 'message' => 'No.' . $count . ' - 項目名が空欄です'));
				    		return;
	                	}
	                	*/
            
		                $itemList[] = array(
							'id'                  => $count,
							'item_id'             => $itemId,
							'item_name'           => $itemName,
							'unit'                => $unit,
							'standard_price'      => $standardPrice,
							'standard_price_tax'  => $standardPriceTax,
							'wholesale_rate'      => $wholeSaleRate,
							'unit_price'          => $unitPrice,
							'price_per_month'     => $pricePerMonth,
							'standard_price_import_currency' => $standardPriceImportCurrency,
							'standard_price_import'          => $standardPriceImport,
							'wholesale_rate_overseas'        => $wholesaleRateOverseas,
		                );
		                
		            	$count++;
		            }
		            
		            $versionData['item_list']   = json_encode($itemList);
	            } else {
	            	$versionData['item_list']   = json_encode(array());
	            }


				try {
					$versionTable->getAdapter()->beginTransaction();

					$estimateTable->updateById($id, $data);
					$versionTable->updateById($versionId, $versionData);

					
					if (!empty($success['file_name'])) {
						// 古いファイルを削除
						if ($oldVersionData['file_name'] != $success['file_name']) {
							if (Shared_Model_Resource_EstimateUpload::isExist($id, $versionId, $oldVersionData['file_name'])) {
								Shared_Model_Resource_EstimateUpload::removeResource($id, $versionId, $oldVersionData['file_name']);
							}
						}
						
						// ファイルを正式な場所に配置
						Shared_Model_Resource_EstimateUpload::makeResource($id, $versionId, $success['file_name'], Shared_Model_Resource_TemporaryPrivate::getBinary($success['file_name']));
		
						// 仮ファイルの削除
						Shared_Model_Resource_TemporaryPrivate::removeResource($success['file_name']);
					}
				
	                // commit
	                $versionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $versionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/price-estimate/update transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));				
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/upload-file                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 関連資料アップロード(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function uploadFileAction()
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
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($fileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName));
        return;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/preview                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積書フォーム PDFプレビュー                               |
    +----------------------------------------------------------------------------*/
    public function previewAction()
    {
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$versionId = $request->getParam('version_id');
		
		$estimateTable = new Shared_Model_Data_Estimate();
		$versionTable  = new Shared_Model_Data_EstimateVersion();
		$userTable     = new Shared_Model_Data_User();
		
		$data = $estimateTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (empty($data)) {
			throw new Zend_Exception('/price-estimate/preview - no target data');	
		}
		
    	$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);

		if (empty($versionData)) {
			throw new Zend_Exception('/price-estimate/preview - no target version data');	
		}
		
    	//$connectionTable = new Shared_Model_Data_Connection();
    	//$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $versionData['target_connection_id']);
    	//$versionData['company_name'] = $connectionData['company_name'];
    	
    	$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
    	
		$companyData = array(
			'company_name' => $groupData['organization_name'],
			'address'      => '〒' . $groupData['postal_code'] . ' ' . $groupData['prefecture'] . $groupData['city'] . $groupData['address'],
			'tel'          => $groupData['tel'],
			'fax'          => $groupData['fax'],
			'user_name'    => '',
		);
		
		
		$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
		
		
		// 見積作成者
    	if (!empty($versionData['created_user_id'])) {
    		$createdUser = $userTable->getById($versionData['created_user_id']);
    		
    		$companyData['user_name'] = $createdUser['department_name'] . '　' . $createdUser['user_name'];
    	}
    	
    	
		if ($versionData['template_id'] == '1') {
			Shared_Model_Pdf_EstimateType1::makeSingle($data, $versionData, $companyData);
		} else if ($versionData['template_id'] == '2') {
			Shared_Model_Pdf_EstimateType2::makeSingle($data, $versionData, $companyData);
		} else if ($versionData['template_id'] == '3') {
			Shared_Model_Pdf_EstimateType3::makeSingle($data, $versionData, $companyData);
		} else if ($versionData['template_id'] == '4') {
			Shared_Model_Pdf_EstimateType4::makeSingle($data, $versionData, $companyData);
		} else if ($versionData['template_id'] == '5') {
			Shared_Model_Pdf_EstimateType5::makeSingle($data, $versionData, $companyData);
		} else if ($versionData['template_id'] == '6') {
			Shared_Model_Pdf_EstimateType6::makeSingle($data, $versionData, $companyData);
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/apply-apploval                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認申請                                                   |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$versionId  = $request->getParam('version_id');

		// POST送信時
		if ($request->isPost()) {
			$estimateTable = new Shared_Model_Data_Estimate();
			$versionTable  = new Shared_Model_Data_EstimateVersion();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
			$data = $estimateTable->getById($this->_adminProperty['management_group_id'], $id);
			$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
			
            if (!empty($$data['target_connection_id'])) {
                $result['result'] = 'NG';
                $result['message'] = '「取引先」を選択してください';
                $this->sendJson($result);
                return; 
			} else if (empty($data['estimate_date'])) {
                $result['result'] = 'NG';
                $result['message'] = '「見積書日付」を入力してください';
                $this->sendJson($result);
                return; 
                	
            } else if (empty($versionData['to_name'])) {
                $result['result'] = 'NG';
                $result['message'] = '「宛先」を入力してください';
                $this->sendJson($result);
                return; 
			}
			
			if ($versionData['template_id'] == '9') {
				if (empty($versionData['file_name'])) {
	                $result['result'] = 'NG';
	                $result['message'] = '「見積書ファイルデータ」をアップロードしてください';
	                $this->sendJson($result);
	                return; 
				}
			}

	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
	    	
			try {
				$estimateTable->getAdapter()->beginTransaction();
				
				$estimateTable->updateById($id, array(
					'status' => Shared_Model_Code::ESTIMATE_STATUS_PENDING,
				));
				
				$versionTable->updateById($versionId, array(
					'version_status' => Shared_Model_Code::ESTIMATE_VERSION_STATUS_PENDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_ESTIMATE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $versionId,
					
					'title'                 => $data['title'] . "\n"
					                         . "提出先：" . $connectionData['company_name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);
			
				// メール送信 -------------------------------------------------------
				$content = "提出先：\n" . $connectionData['company_name'] . "\n\n"
				         . "表題：\n" . $data['title'] . "\n\n"
				         . "バージョン：\n" . $versionData['version_id'];
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);

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
                $estimateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $estimateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-estimate/apply-apploval transaction faied: ' . $e);  
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/submit                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 提出完了(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$versionId  = $request->getParam('version_id');
		
		// POST送信時
		if ($request->isPost()) {
			$estimateTable = new Shared_Model_Data_Estimate();
			$versionTable  = new Shared_Model_Data_EstimateVersion();
			
			$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
			
			try {
				$estimateTable->getAdapter()->beginTransaction();
				
				$estimateTable->updateById($id, array(
					'status' => Shared_Model_Code::ESTIMATE_STATUS_SUBMITTED,
				));
				
				$versionTable->updateById($versionData['id'], array(
					'version_status' => Shared_Model_Code::ESTIMATE_VERSION_STATUS_SUBMITTED,
				));
			
                // commit
                $estimateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $estimateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-estimate/submit transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/confirm                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認確認                                                   |
    +----------------------------------------------------------------------------*/
    public function confirmAction()
    {
		$request = $this->getRequest();
		$this->view->approvalId   = $approvalId   = $request->getParam('approval_id');
		$this->view->versionId    = $versionId    = $request->getParam('version_id');
		$this->view->selectedRow  = $selectedRow  = $request->getParam('selected_row', '');
		
		$estimateTable   = new Shared_Model_Data_Estimate();
		$versionTable    = new Shared_Model_Data_EstimateVersion();
		$templateTable   = new Shared_Model_Data_EstimateTemplate();
		$connectionTable = new Shared_Model_Data_Connection();
		$userTable       = new Shared_Model_Data_User();
		
    	$this->view->versionData = $versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
    	$this->view->data = $data = $estimateTable->getById($this->_adminProperty['management_group_id'], $versionData['estimate_id']);
		$this->view->id = $id = $data['id'];
		
		if ((string)$selectedRow !== '') {
			$this->_helper->layout->setLayout('back_menu');
			
		} else if (!empty($approvalId)) {
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->backUrl        = '/approval/list';
	        $this->view->previewUrl     = 'javascript:void(0);';
	        $this->view->saveUrl        = 'javascript:void(0);';
	        $this->view->saveButtonName = '保存';
	        
	        $this->view->showRejectButton = true; // 却下ボタン表示
		} else {
			$this->_helper->layout->setLayout('back_menu');
	        $this->view->backUrl        = '/price-estimate/version-list?id=' . $data['id'];
		}
		
    	// フォーマット
    	$this->view->formatData     = $templateTable->getById($versionData['template_id']);
    	
    	// 提出先
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	// 見積作成者
    	if (!empty($versionData['created_user_id'])) {
    		$this->view->createdUser = $userTable->getById($versionData['created_user_id']);
    	}
    	
    	$managementGroupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $managementGroupTable->getById($this->_adminProperty['management_group_id']);
		
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/detail-select                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積書選択(ポップアップ画面)                               |
    +----------------------------------------------------------------------------*/
    public function detailSelectAction()
    {
        $this->_helper->layout->setLayout('blank');;
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$estimateTable = new Shared_Model_Data_Estimate();
		$versionTable  = new Shared_Model_Data_EstimateVersion();
		$templateTable   = new Shared_Model_Data_EstimateTemplate();
		$connectionTable = new Shared_Model_Data_Connection();
		$userTable       = new Shared_Model_Data_User();
		
		$this->view->data = $data = $estimateTable->getById($this->_adminProperty['management_group_id'], $id);
    	
    	// 提出済みバージョンの取得
    	$this->view->versionData = $versionData = $versionTable->getSubmittedVersionByEstimateId($id);
    	
    	// フォーマット
    	$this->view->formatData     = $templateTable->getById($versionData['template_id']);
    	
    	// 提出先
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    	
    	// 見積作成者
    	if (!empty($versionData['created_user_id'])) {
    		$this->view->createdUser = $userTable->getById($versionData['created_user_id']);
    	}

    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/mod-request                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 修正依頼(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function modRequestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$versionId  = $request->getParam('version_id');
		$memoPrivate = $request->getParam('memo_private');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$estimateTable = new Shared_Model_Data_Estimate();
			$versionTable  = new Shared_Model_Data_EstimateVersion();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
			$data = $estimateTable->getById($this->_adminProperty['management_group_id'], $versionData['estimate_id']);

	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			try {
				$estimateTable->getAdapter()->beginTransaction();
				
				$estimateTable->updateById($versionData['estimate_id'], array(
					'status' => Shared_Model_Code::ESTIMATE_STATUS_MOD_REQUEST,
				));
				
				$versionTable->updateById($versionData['id'], array(
					'version_status'   => Shared_Model_Code::ESTIMATE_VERSION_MOD_REQUEST,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
				));
				
				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
				
				// メール送信 -------------------------------------------------------
				$content = "提出先：\n" . $connectionData['company_name'] . "\n\n"
				         . "表題：\n" . $data['title'] . "\n\n"
				         . "バージョン：\n" . $versionData['version_id'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/price-estimate/confirm?version_id=' . $versionId;
	        
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
                $estimateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $estimateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-estimate/mod-request transaction faied: ' . $e);
                
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));

    }
       
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/reject                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 却下(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function rejectAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$versionId  = $request->getParam('version_id');
		$memoPrivate = $request->getParam('memo_private');
		$approvalComment = $request->getParam('approval_comment');
			
		// POST送信時
		if ($request->isPost()) {
			$estimateTable = new Shared_Model_Data_Estimate();
			$versionTable  = new Shared_Model_Data_EstimateVersion();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
			$data = $estimateTable->getById($this->_adminProperty['management_group_id'], $versionData['estimate_id']);

	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);	
			
			try {
				$estimateTable->getAdapter()->beginTransaction();
				
				$estimateTable->updateById($versionData['estimate_id'], array(
					'status' => Shared_Model_Code::ESTIMATE_STATUS_REJECTED,
				));
				
				$versionTable->updateById($versionData['id'], array(
					'version_status' => Shared_Model_Code::ESTIMATE_VERSION_STATUS_REJECTED,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
				));
				
				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_REJECTED,
				));
				
				// メール送信 -------------------------------------------------------
				$content = "提出先：\n" . $connectionData['company_name'] . "\n\n"
				         . "表題：\n" . $data['title'] . "\n\n"
				         . "バージョン：\n" . $versionData['version_id'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/price-estimate/confirm?version_id=' . $versionId;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_REJECTED,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $estimateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $estimateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-estimate/reject transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/approve                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function approveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$versionId  = $request->getParam('version_id');
		$memoPrivate = $request->getParam('memo_private');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$estimateTable = new Shared_Model_Data_Estimate();
			$versionTable  = new Shared_Model_Data_EstimateVersion();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$versionData = $versionTable->getById($this->_adminProperty['management_group_id'], $versionId);
			$data = $estimateTable->getById($this->_adminProperty['management_group_id'], $versionData['estimate_id']);

	    	// 提出先
	    	$connectionTable = new Shared_Model_Data_Connection();
	    	$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);	
			
			try {
				$estimateTable->getAdapter()->beginTransaction();
				
				$estimateTable->updateById($versionData['estimate_id'], array(
					'status' => Shared_Model_Code::ESTIMATE_STATUS_APPROVED,
				));
				
				$versionTable->updateById($versionData['id'], array(
					'version_status'   => Shared_Model_Code::ESTIMATE_VERSION_STATUS_APPROVED,
					'memo_private'     => $memoPrivate,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));
				
				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));
				
				// メール送信 -------------------------------------------------------
				$content = "提出先：\n" . $connectionData['company_name'] . "\n\n"
				         . "表題：\n" . $data['title'] . "\n\n"
				         . "バージョン：\n" . $versionData['version_id'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/price-estimate/confirm?version_id=' . $versionId;
	        
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
                $estimateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $estimateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/price-estimate/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

        
    /*----------------------------------------------------------------------------+
    |  action_URL    * /price-estimate/product-list                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品選択                                                   |
    +----------------------------------------------------------------------------*/
    public function productListAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        
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
        
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        
        $url = 'javascript:pageProduct($page);';
        $this->view->pager($paginator, $url);
    }
    
}

