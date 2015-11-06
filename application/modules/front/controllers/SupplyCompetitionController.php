<?php
/**
 * class SupplyCompetitionController
 */
 
class SupplyCompetitionController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '調達管理';
		$this->view->menuCategory     = 'supply';
		$this->view->menu = 'competition';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ                                                     |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('supply_competition');
		
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
			$session->conditions['user_id']        = $request->getParam('user_id', '');
			$session->conditions['user_name']      = $request->getParam('user_name', '');
			$session->conditions['keyword']        = $request->getParam('keyword', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']         = '';
			$session->conditions['user_id']        = '';
			$session->conditions['user_name']      = '';
			$session->conditions['keyword']        = '';
			
		}
		
		$this->view->conditions = $conditions = $session->conditions;

			
		$competitionTable = new Shared_Model_Data_SupplyCompetition();
		
		$dbAdapter = $competitionTable->getAdapter();

        $selectObj = $competitionTable->select();
		$selectObj->joinLeft('frs_user', 'frs_supply_competition.management_user_id = frs_user.id',array($competitionTable->aesdecrypt('user_name', false) . 'AS management_user_name'));
        //$selectObj->joinLeft('frs_user', 'frs_supply_competition.last_update_user_id = frs_user.id',array($competitionTable->aesdecrypt('user_name', false) . 'AS updated_user_name'));
        // グループID
        $selectObj->where('frs_supply_competition.management_group_id = ?', $this->_adminProperty['management_group_id']);  

		if (!empty($conditions['status'])) {
			$selectObj->where('frs_supply_competition.status = ?', $conditions['status']);
		} else {
			$selectObj->where('frs_supply_competition.status != ?', Shared_Model_Code::COMPETITION_STATUS_DELETED);
		}

        if (!empty($session->conditions['keyword'])) {
	        $likeString = array();
	        $likeString[] = $dbAdapter->quoteInto($competitionTable->aesdecrypt('frs_supply_competition.title', false) . ' LIKE ?', '%' . $session->conditions['keyword'] .'%');
	        $likeString[] = $dbAdapter->quoteInto($competitionTable->aesdecrypt('frs_supply_competition.description', false) . ' LIKE ?', '%' . $session->conditions['keyword'] .'%');
        	
        	$selectObj->where(implode(' OR ', $likeString));
        }
        
		$selectObj->order('frs_supply_competition.id DESC');
		
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
    |  action_URL    * /supply-competition/delete                                 |
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
			$competitionTable = new Shared_Model_Data_SupplyCompetition();

			try {
				$competitionTable->getAdapter()->beginTransaction();
				
				$competitionTable->updateById($id, array(
					'status' => Shared_Model_Code::COMPETITION_STATUS_DELETED,
				));
			
                // commit
                $competitionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $competitionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-competition/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/add                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ - 新規登録                                          |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/add-post                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ - 新規登録(Ajax)                                    |
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「コンペ・企画件名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['competiion_started_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「コンペ開始日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['management_user_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「本件管理担当者」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「本件概要」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
				
				// 新規登録	            
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
	            
	            	$displayId = $competitionTable->getNextDisplayId();
	            	
	            	$defaultConditionList = array(
	            		array('id' => '1', 'label' => '依頼概要', 'value' => ''),
	            		array('id' => '2', 'label' => '内容量', 'value' => ''),
	            		array('id' => '3', 'label' => '内容・配合', 'value' => ''),
	            		array('id' => '4', 'label' => '包装', 'value' => ''),
	            		array('id' => '5', 'label' => 'ロット数', 'value' => ''),
	            		array('id' => '6', 'label' => '支給品', 'value' => ''),
	            	);
	            	
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'display_id'                        => $displayId,
						'status'                            => Shared_Model_Code::COMPETITION_STATUS_PROGRESS,

						'title'                             => $success['title'],                      // コンペ・企画件名
						'description'                       => $success['description'],                // 本件概要
		
						'competiion_started_date'           => $success['competiion_started_date'],    // コンペ開始日
						
						'management_user_id'                => $success['management_user_id'],         // 本件管理担当者
						'result_item_id'                    => NULL,                                   // 最終商品
						
						'material_list'                     => json_encode(array()),                   // 主な使用前提原料・仕入品
						'condition_list'                    => json_encode($defaultConditionList),     // コンペ依頼内容
						'competition_list'                  => json_encode(array()),                   // コンペ・企画比較結果・進捗表
						'file_list'                         => json_encode(array()),                   // 資料アップロード／資料名と資料概略
						
						'result_comment'                    => '',                                     // コンペ・企画の比較の総評と結論／商品化の方向
						'knowledge_comment'                 => '',                                     // 本案件で得た商品／業界の知見・一般情報・注意点
						'other_memo'                        => '',                                     // その他メモ
						'apploval_comment'                  => '',                                     // 承認者コメント

						'created_user_id'                   => $this->_adminProperty['id'],            // 作成者ユーザーID
						'last_update_user_id'               => $this->_adminProperty['id'],            // 最終更新者ユーザーID
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$competitionTable->create($data);
					$id = $competitionTable->getLastInsertedId('id');
					
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/add-post transaction faied: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
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
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/detail                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ詳細編集                                             |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
        $this->_helper->layout->setLayout('back_menu_competition');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		
		$competitionTable = new Shared_Model_Data_SupplyCompetition();
		$userTable        = new Shared_Model_Data_User();
		$this->view->data = $data = $competitionTable->getById($this->_adminProperty['management_group_id'], $id);

		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 本件管理担当者	
		if (!empty($data['management_user_id'])) {
			$this->view->managementUser = $userTable->getById($data['management_user_id']);
		}
		
		if ($data['status'] == Shared_Model_Code::COMPETITION_STATUS_PROGRESS) {
			$this->view->saveUrl        = 'javascript:void(0);';
		} else {
			$this->view->saveUrl        = NULL;
		}
		
		$this->view->backUrl = '/supply-competition';
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/detail-test                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ詳細編集                                             |
    +----------------------------------------------------------------------------*/
    public function detailTestAction()
    {
        $this->_helper->layout->setLayout('back_menu_competition_test');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		
		$competitionTable = new Shared_Model_Data_SupplyCompetition();
		$userTable        = new Shared_Model_Data_User();
		$this->view->data = $data = $competitionTable->getById($this->_adminProperty['management_group_id'], $id);

		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 本件管理担当者	
		if (!empty($data['management_user_id'])) {
			$this->view->managementUser = $userTable->getById($data['management_user_id']);
		}
		
		if ($data['status'] == Shared_Model_Code::COMPETITION_STATUS_PROGRESS) {
			$this->view->saveUrl        = 'javascript:void(0);';
		} else {
			$this->view->saveUrl        = NULL;
		}
		
		$this->view->backUrl = '/supply-competition';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/select-type                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 種別選択(ポップアップ用)                                   |
    +----------------------------------------------------------------------------*/
    public function selectTypeAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-basic                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ基本情報更新(Ajax)                                   |
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

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「コンペ・企画件名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「コンペ開始日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['base_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「本件管理担当者」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['uses']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「本件概要」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'title'                             => $success['title'],                      // コンペ・企画件名
						'description'                       => $success['description'],                // 本件概要
		
						'competiion_started_date'           => $success['competiion_started_date'],    // コンペ開始日
						'management_user_id'                => $success['management_user_id'],         // 本件管理担当
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-basic transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-status                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ステータス変更(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function updateStatusAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$targetStatus = $request->getParam('target_status');
		
		// POST送信時
		if ($request->isPost()) {
			$competitionTable = new Shared_Model_Data_SupplyCompetition();

			try {
				$competitionTable->getAdapter()->beginTransaction();
				
				$competitionTable->updateById($id, array(
					'status' => $targetStatus,
				));
			
                // commit
                $competitionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $competitionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-competition/update-status transaction faied: ' . $e);
                
            }
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-material-list                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 2.主な使用前提原料・仕入品更新(Ajax)                       |
    +----------------------------------------------------------------------------*/
    public function updateMaterialListAction()
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
				$materialList = array();

	            if (!empty($success['material_list'])) {
	            	$materialIdList = explode(',', $success['material_list']);
	            	
		            foreach ($materialIdList as $eachId) {
		                $materialList[] = array(
							'id'      => $eachId,
							'item_id' => $request->getParam($eachId . '_item_id'),
		                );
		            }
	            }
	            
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'material_list'         => json_encode($materialList),
						
						'last_update_user_id'   => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-material-list transaction failed: ' . $e);
	                
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-condition-list                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 3.コンペ依頼内容更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function updateConditionListAction()
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
	            	
		            foreach ($conditionIdList as $eachId) {
		                $conditionList[] = array(
							'id'      => $eachId,
							'label'   => $request->getParam($eachId . '_label'),
							'value'   => $request->getParam($eachId . '_value'),
		                );
		            }
	            }
	            
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'condition_list'         => json_encode($conditionList),
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-condition-list transaction failed: ' . $e);
	                
	            }
				
				$this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-competition-list                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 4.コンペ・企画比較結果・進捗表更新(Ajax)                   |
    +----------------------------------------------------------------------------*/
    public function updateCompetitionListAction()
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
				$competitionList = array();
				
	            if (!empty($success['competition_list'])) {
	            	$competitionIdList = explode(',', $success['competition_list']);
	            	$count = 1;
		            foreach ($competitionIdList as $eachId) {
		                $competitionList[] = array(
							'id'                       => $count,
							'connection_id'            => $request->getParam($eachId . '_connection_id'),
							'staff_id'                 => $request->getParam($eachId . '_staff_id'),
							'rating'                   => $request->getParam($eachId . '_rating'),
							'progress1'                => $request->getParam($eachId . '_progress1'),
							'progress2'                => $request->getParam($eachId . '_progress2'),
							'estimate_info'            => $request->getParam($eachId . '_estimate_info'),
							'competition_description'  => $request->getParam($eachId . '_competition_description'),
							'reference_type'           => $request->getParam($eachId . '_reference_type'),
							'reference_target_id'      => $request->getParam($eachId . '_reference_target_id'),
		                );
		                $count++;
		            }
	            }
	            
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'competition_list'         => json_encode($competitionList),
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-competition-list transaction failed: ' . $e);
	                
	            }
				
				$this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-result-comment                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 5.コンペ・企画の比較の総評と結論／商品化の方向更新(Ajax)   |
    +----------------------------------------------------------------------------*/
    public function updateResultCommentAction()
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
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'result_comment'         => $success['result_comment'],						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-result-comment transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-knowledge-comment               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 6.本案件で得た商品／業界の知見・一般情報・注意点(Ajax)     |
    +----------------------------------------------------------------------------*/
    public function updateKnowledgeCommentAction()
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
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'knowledge_comment'      => $success['knowledge_comment'],						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-knowledge-comment transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-other-memo                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 7.その他メモ(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateOtherMemoAction()
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
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'other_memo'             => $success['other_memo'],						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-other-memo transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/update-file-list                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 8.資料アップロード更新(Ajax)                               |
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
  
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$competitionTable = new Shared_Model_Data_SupplyCompetition();
	            $competitionTable->getAdapter()->beginTransaction();
 
				$fileList = array();
				
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
						$tempFileName = $request->getParam($eachId . '_temp_file_name');
	            		$fileName     = $request->getParam($eachId . '_file_name');

						if (!empty($tempFileName)) {
		            		// 正式保存
		            		$result = Shared_Model_Resource_SupplyCompetition::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
		            		
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

					$competitionTable->updateById($id, $data);
						
	                // commit
	                $competitionTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $competitionTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/supply-competition/update-file-list transaction failed: ' . $e);
	            }
				
				$this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));

    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/apply-apploval                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ 承認申請                                            |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$competitionTable = new Shared_Model_Data_SupplyCompetition();
			$approvalTable    = new Shared_Model_Data_Approval();
			$userTable        = new Shared_Model_Data_User();
			
			$data = $competitionTable->getById($this->_adminProperty['management_group_id'], $id);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        
	        if (empty($data)) {
				throw new Zend_Exception('/supply-competition/apply-apploval filed to fetch user data');
			}
				
			try {
				$competitionTable->getAdapter()->beginTransaction();
				
				$competitionTable->updateById($id, array(
					'status' => Shared_Model_Code::COMPETITION_STATUS_APPROVAL_PENDDING,
				));
				
				$approvalData = array(
					'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_SUPPLY_COMPETITION,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'], // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $data['title'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);
				
				// メール送信 -------------------------------------------------------
				$content = "コンペ・企画件名：\n" . $data['title'];
				
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
                $competitionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $competitionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-competition/apply-apploval transaction faied: ' . $e);
                
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/confirm                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ 承認確認                                            |
    +----------------------------------------------------------------------------*/
    public function confirmAction()
    {
        $this->_helper->layout->setLayout('back_menu_approval');
        $this->view->backUrl        = '/approval/list';
        $this->view->previewUrl     = '';
        $this->view->saveUrl        = 'javascript:void(0);';
        $this->view->saveButtonName = '保存';
        
		$request = $this->getRequest();
		$this->view->approvalId = $approvalId = $request->getParam('approval_id');
		$this->view->id  = $id  = $request->getParam('id');
		
		$competitionTable = new Shared_Model_Data_SupplyCompetition();
		$userTable        = new Shared_Model_Data_User();
		
    	$this->view->data = $data = $competitionTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		
		// 本件管理担当者	
		if (!empty($data['management_user_id'])) {
			$this->view->managementUser = $userTable->getById($data['management_user_id']);
		}
		
    	//$connectionTable = new Shared_Model_Data_Connection();
    	//$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/mod-request                            |
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
		$approvalComment = $request->getParam('apploval_comment');

		// POST送信時
		if ($request->isPost()) {
			$competitionTable = new Shared_Model_Data_SupplyCompetition();
			$approvalTable    = new Shared_Model_Data_Approval();
			$userTable        = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
	        $competitionData = $competitionTable->getById($this->_adminProperty['management_group_id'], $id);
	        
			try {
				$competitionTable->getAdapter()->beginTransaction();
				
				$competitionTable->updateById($id, array(
					'status' => Shared_Model_Code::COMPETITION_STATUS_PROGRESS,
					'apploval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
			
				// メール送信 -------------------------------------------------------
				$content = "コンペ・企画件名：\n" . $competitionData['title'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/supply-competition/detail?id=' . $id;
	        
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
                $competitionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $competitionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-competition/mod-request transaction faied: ' . $e);
                
            }
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-competition/approve                                |
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
		$approvalComment = $request->getParam('apploval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$competitionTable = new Shared_Model_Data_SupplyCompetition();
			$approvalTable    = new Shared_Model_Data_Approval();
			$userTable        = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);
			
			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
	        $competitionData = $competitionTable->getById($this->_adminProperty['management_group_id'], $id);
	        
			try {
				$competitionTable->getAdapter()->beginTransaction();
				
				$competitionTable->updateById($id, array(
					'status' => Shared_Model_Code::COMPETITION_STATUS_APPROVED,
					'apploval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status' => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
				$content = "コンペ・企画件名：\n" . $competitionData['title'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/supply-competition/detail?id=' . $id;
	        
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
                $competitionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $competitionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/supply-competition/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
}

