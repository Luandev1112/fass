<?php
/**
 * class ConnectionRecordController
 * 議事録
 */
 
class ConnectionRecordController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '取引先・営業管理';
		$this->view->menuCategory     = 'connection';
		$this->view->menu             = 'list-record';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/set-up-target-date                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 対象日設定(develop)                                        |
    +----------------------------------------------------------------------------*/
    public function setUpTargetDateAction()
    {
	    $recordTable = new Shared_Model_Data_ConnectionRecord();
	    $selectObj = $recordTable->select();
	    $selectObj->order('id ASC');
	    $items = $selectObj->query()->fetchAll();
	    
	    foreach ($items as $each) {
		    $recordTable->updateById($this->_adminProperty['management_group_id'], $each['id'], array(
			    'target_date' => date('Y-m-d', strtotime($each['created'])),
		    ));
	    }
	    
	    echo 'OK';
	    exit;
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/list                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 一覧                                              |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
	    $request = $this->getRequest();
	    $this->view->posTop      = $request->getParam('pos');
	    
	    $session = new Zend_Session_Namespace('connection_record_1');

		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['record_type']         = $request->getParam('record_type', array());
			
		} else if (empty($session->conditions) || !array_key_exists('connection_id', $session->conditions)) {
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
			$session->conditions['record_type']         = array();
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$recordTable = new Shared_Model_Data_ConnectionRecord();
		$dbAdapter = $recordTable->getAdapter();

        $selectObj = $recordTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_connection_record.connection_id = frs_connection.id', array($recordTable->aesdecrypt('company_name', false) . 'AS company_name', 'industry_types'));
		$selectObj->joinLeft('frs_user', 'frs_connection_record.last_update_user_id = frs_user.id', array($recordTable->aesdecrypt('user_name', false) . 'AS user_name', 'user_department_id'));
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array($recordTable->aesdecrypt('department_name', false) . 'AS department_name'));

		$selectObj->where('frs_connection_record.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);     // ステータス
		
		// グループID
		$selectObj->where('frs_connection_record.management_group_id = ?', $this->_adminProperty['management_group_id']);

        // 実績
        $progressWhereString = '';
        if (!empty($session->conditions['record_type'])) {
	        foreach($session->conditions['record_type'] as $eachType) {
        		if ($progressWhereString !== '') {
        			$progressWhereString .= ' OR ';
        		}
				
        		$progressWhereString .= $dbAdapter->quoteInto('`record_type` = ?', $eachType);
	        }
	        
	        if ($progressWhereString !== '') {
	        	$selectObj->where($progressWhereString);
	        }
        }
        
		
        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('connection_id = ?', $session->conditions['connection_id']);
        }
		
		$selectObj->order('frs_connection_record.target_date DESC');

        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
        
        $typeTable = new Shared_Model_Data_ConnectionRecordType();
        $this->view->typeList = $typeTable->getActiveList($this->_adminProperty['management_group_id']);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/delete                                  |
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
			$recordTable = new Shared_Model_Data_ConnectionRecord();

			try {
				$recordTable->getAdapter()->beginTransaction();
				
				$recordTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
				));
			
                // commit
                $recordTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $recordTable->getAdapter()->rollBack();
                throw new Zend_Exception('/connection-record/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/type-list                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 種別定義                                                   |
    +----------------------------------------------------------------------------*/
    public function typeListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection-record/list';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$typeTable = new Shared_Model_Data_ConnectionRecordType();
		
		$dbAdapter = $typeTable->getAdapter();

        $selectObj = $typeTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/type-update-order                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 種別並び順更新(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function typeUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$typeTable = new Shared_Model_Data_ConnectionRecordType();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-record/type-update-order failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();

			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$typeTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/type-detail                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 種別・編集                                                 |
    +----------------------------------------------------------------------------*/
    public function typeDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$typeTable = new Shared_Model_Data_ConnectionRecordType();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'title'            => '',                    // 科目名
				'status'           => 0,
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $typeTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/connection-record/type-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }

        $typeTable = new Shared_Model_Data_ConnectionRecordType();
        $this->view->typeList = $typeTable->getActiveList($this->_adminProperty['management_group_id']);
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/type-update                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 種別・編集(Ajax)                                           |
    +----------------------------------------------------------------------------*/
    public function typeUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$typeTable = new Shared_Model_Data_ConnectionRecordType();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-record/type-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {

				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                $message = '';
                if (isset($errorMessage['title'])) {
                    $message = '「項目名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($typeTable->isExistTitle($this->_adminProperty['management_group_id'], $success['title'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「項目名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $typeTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'title'               => $success['title'],             // 項目名
						'status'              => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
						'content_order'       => $contentOrder,                 // 並び順
					);
					
					if (!empty($success['status'])) {
						$data['status'] = Shared_Model_Code::CONTENT_STATUS_ACTIVE;
					}

					$typeTable->create($data);
				} else {
					// 編集
					$data = array(
						'title'               => $success['title'],             // 項目名
						'status'              => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
					);
					
					if (!empty($success['status'])) {
						$data['status'] = Shared_Model_Code::CONTENT_STATUS_ACTIVE;
					}

					$typeTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/add-solo                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 新規登録                                          |
    +----------------------------------------------------------------------------*/
    public function addSoloAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
	    
		$request = $this->getRequest();
		$this->view->from             = $from             = $request->getParam('from');

		$this->view->data = array('target_date' => date('Y-m-d'));

        $typeTable = new Shared_Model_Data_ConnectionRecordType();
        $this->view->typeList = $typeTable->getActiveList($this->_adminProperty['management_group_id']);
    }
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/add                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 新規登録                                          |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
	    
		$request = $this->getRequest();
		$this->view->from             = $from             = $request->getParam('from');
		$this->view->connectionId     = $connectionId     = $request->getParam('connection_id');
		$this->view->progressItemId   = $progressItemId   = $request->getParam('progress_item_id', '');

		$this->view->data = array('target_date' => date('Y-m-d'));

        $typeTable = new Shared_Model_Data_ConnectionRecordType();
        $this->view->typeList = $typeTable->getActiveList($this->_adminProperty['management_group_id']);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/add-post                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 新規登録(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
   
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				
				if (!empty($errorMessage['target_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「対象日」を入力してください'));
                    return;   
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「タイトル」を入力してください'));
                    return;
                } else if (!empty($errorMessage['content']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                    return;   
                }  

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$recordTable = new Shared_Model_Data_ConnectionRecord();

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
				$data = array(
			        'management_group_id'    => $this->_adminProperty['management_group_id'], // 管理グループID
					'status'                 => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
					
					'connection_id'          => $success['connection_id'],    // 取引先ID
					'progress_item_id'       => 0,                            // 営業進捗ID
					'record_type'            => 0,
					
					'target_date'            => $success['target_date'],      // 対象日
					
			        'title'                  => $success['title'],            // タイトル
					'content'                => $success['content'],          // 内容
					
					'file_list'              => json_encode($fileList),      // ファイルアップロード
					
					'created_user_id'        => $this->_adminProperty['id'],  // 初期登録者ユーザーID
					'last_update_user_id'    => $this->_adminProperty['id'],  // 最終更新者ユーザーID

	                'created'                => new Zend_Db_Expr('now()'),
	                'updated'                => new Zend_Db_Expr('now()'),
				);
				
				if (!empty($success['progress_item_id'])) {
					$data['progress_item_id'] = $success['progress_item_id'];
				}
				
				if (!empty($success['record_type'])) {
					$data['record_type'] = $success['record_type'];
				}
				

				try {
					$recordTable->getAdapter()->beginTransaction();
					
					$data['display_id'] = $recordTable->getNextDisplayId();
					$recordTable->create($data);
					$id = $recordTable->getLastInsertedId('id');

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Record::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $recordTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $recordTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection-record/add-post transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/edit                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 編集                                              |
    +----------------------------------------------------------------------------*/
    public function editAction()
    {
	    $this->_helper->layout->setLayout('back_menu');

		$request = $this->getRequest();
		$this->view->from   = $from = $request->getParam('from');
		$this->view->id     = $id   = $request->getParam('id');
		
		$recordTable = new Shared_Model_Data_ConnectionRecord();
		$userTable   = new Shared_Model_Data_User();
		
		$this->view->data = $data = $recordTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);

    	if ($from === 'progress') {
			$this->view->backUrl = '/connection-progress/record?id=' . $data['progress_item_id'];
		} else if ($from === 'list') {	
			$this->view->backUrl = '/connection-record/list';
		} else {
			$this->view->backUrl = '/connection/record?id=' . $data['connection_id'];
		}
		
        $typeTable = new Shared_Model_Data_ConnectionRecordType();
        $this->view->typeList = $typeTable->getActiveList($this->_adminProperty['management_group_id']);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/update-basic                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 更新(Ajax)                                          |
    +----------------------------------------------------------------------------*/
    public function updateBasicAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
   
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['target_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「対象日」を入力してください'));
                    return;   
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「タイトル」を入力してください'));
                    return;
                } else if (!empty($errorMessage['content']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                    return;   
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$recordTable = new Shared_Model_Data_ConnectionRecord();

				$data = array(
					'target_date'            => $success['target_date'],      // 対象日
					'record_type'            => 0,
			        'title'                  => $success['title'],            // タイトル
					'content'                => $success['content'],          // 内容

					'last_update_user_id'    => $this->_adminProperty['id'],  // 最終更新者ユーザーID
				);

				if (!empty($success['record_type'])) {
					$data['record_type'] = $success['record_type'];
				}
				
				
				try {
					$recordTable->getAdapter()->beginTransaction();
					
					$recordTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
					
	                // commit
	                $recordTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $recordTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection-record/update-basic transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/update-file-list                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 添付ファイルアップロード更新(Ajax)                |
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
				$recordTable = new Shared_Model_Data_ConnectionRecord();
				
				$oldData = $recordTable->getById($this->_adminProperty['management_group_id'], $id);

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
	            $recordTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'file_list' => json_encode($fileList), // 請求書ファイルアップロード
					);

					$recordTable->updateById($this->_adminProperty['management_group_id'], $id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Record::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $recordTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $recordTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection-record/update-file-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-record/upload                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録 - 添付ファイルアップロード(Ajax)                    |
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

