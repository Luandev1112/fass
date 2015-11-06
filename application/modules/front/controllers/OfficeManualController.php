<?php
/**
 * class OfficeManualController
 */
 
class OfficeManualController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '社内処理';
		$this->view->menuCategory     = 'office';

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/index                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル一覧                                             |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('office_manual');
		$session->from = 'index';
		
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'manual';
		

		$manualTable = new Shared_Model_Data_Manual();
		
		$dbAdapter = $manualTable->getAdapter();

        $selectObj = $manualTable->select();

		// グループID
		$selectObj->where('frs_manual.management_group_id = ?', $this->_adminProperty['management_group_id']);
		 
        $selectObj->joinLeft('frs_user', 'frs_manual.manager_user_id = frs_user.id', array($manualTable->aesdecrypt('user_name', false) . 'AS user_name'));
        //$selectObj->where('status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
		$selectObj->order('frs_manual.content_order ASC');

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
 		$categoryTable = new Shared_Model_Data_ManualCategory();
 		$categoryList = array();
 		$categoryItems = $categoryTable->getList($this->_adminProperty['management_group_id']);
    	
    	foreach ($categoryItems as $each) {
    		$categoryList[$each['id']] = $each;
    	}
		$this->view->categoryList = $categoryList;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/manual-update-order                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル区分 並び順更新(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function manualUpdateOrderAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$manualTable = new Shared_Model_Data_Manual();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office-manual/manual-update-order failed to load config');
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
						$manualTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
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
    |  action_URL    * /office-manual/search                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル検索                                             |
    +----------------------------------------------------------------------------*/
    public function searchAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('office_manual');
		$session->from = 'search';
		$this->view->menu = 'manual';

		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['user_id']    = $request->getParam('user_id', '');
			$session->conditions['user_name']  = $request->getParam('user_name', '');
			$session->conditions['keyword']    = $request->getParam('keyword', '');
			
		} else if (empty($session->conditions) || !array_key_exists('user_id', $session->conditions)) {
			$session->conditions['user_id']    = '';
			$session->conditions['user_name']  = '';
			$session->conditions['keyword']    = '';
			
		}
		
		$this->view->conditions = $conditions = $session->conditions;
			
			
	    $itemTable = new Shared_Model_Data_ManualItem();
	    
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->joinLeft('frs_manual', 'frs_manual_item.manual_id = frs_manual.id', array($itemTable->aesdecrypt('frs_manual.title', false) . 'AS manual_title'));
        $selectObj->joinLeft('frs_manual_chapter', 'frs_manual_item.chapter_id = frs_manual_chapter.id', array($itemTable->aesdecrypt('chapter_name', false) . 'AS chapter_name'));
        $selectObj->joinLeft('frs_user', 'frs_manual.manager_user_id = frs_user.id', array($itemTable->aesdecrypt('user_name', false) . 'AS user_name'));

		// グループID
		$selectObj->where('frs_manual.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        $selectObj->where('frs_manual_item.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
        
        if (!empty($session->conditions['user_id'])) {
        	$selectObj->where('frs_manual_item.last_update_user_id = ?', $session->conditions['user_id']);
        }
		
        if (!empty($session->conditions['keyword'])) {
        	$keywords = Shared_Model_Utility_Text::extractKeywords($session->conditions['keyword']);

 			$where = array();
			foreach ($keywords as $eachKeyword) {
				$whereEach = array();
		        //$likeString[] = $dbAdapter->quoteInto($itemTable->aesdecrypt('frs_manual.title', false) . ' LIKE ?', '%' . $eachKeyword .'%');
		        $whereEach[] = $dbAdapter->quoteInto($itemTable->aesdecrypt('frs_manual_chapter.chapter_name', false) . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto($itemTable->aesdecrypt('frs_manual_item.content', false) . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto($itemTable->aesdecrypt('frs_manual_item.content', false) . ' LIKE ?', '%' . str_replace('\\', '\\\\', str_replace('"', '', json_encode($eachKeyword))) .'%');
	        	
	        	$whereEach[] = $dbAdapter->quoteInto($itemTable->aesdecrypt('frs_manual_item.title', false) . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_item.keyword_1' . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_item.keyword_2' . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_item.keyword_3' . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_item.keyword_4' . ' LIKE ?', '%' . $eachKeyword.'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_item.keyword_5' . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_item.keyword_6' . ' LIKE ?', '%' . $eachKeyword .'%');
	        	$whereEach[] = $dbAdapter->quoteInto('frs_manual_chapter.chapter_name' . ' LIKE ?', '%' . $eachKeyword .'%');
				
				$where[] = '(' . implode($whereEach, ' OR ') . ')';
			}

			//var_dump($where);
        	$selectObj->where(implode(' OR ', $where));
        }
        
		$selectObj->order('frs_manual_item.id DESC');

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
        
 		$categoryTable = new Shared_Model_Data_ManualCategory();
 		$categoryList = array();
 		$categoryItems = $categoryTable->getList($this->_adminProperty['management_group_id']);
    	
    	foreach ($categoryItems as $each) {
    		$categoryList[$each['id']] = $each;
    	}
		$this->view->categoryList = $categoryList;
		
	}





    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/category-list                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル区分定義                                         |
    +----------------------------------------------------------------------------*/
    public function categoryListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/office-manual';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$categoryTable = new Shared_Model_Data_ManualCategory();
		
		$dbAdapter = $categoryTable->getAdapter();

        $selectObj = $categoryTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/category-update-order                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル区分 並び順更新(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function categoryUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$categoryTable = new Shared_Model_Data_ManualCategory();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office-manual/category-update-order failed to load config');
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
						$categoryTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
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
    |  action_URL    * /office-manual/category-detail                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル区分 編集                                        |
    +----------------------------------------------------------------------------*/
    public function categoryDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$categoryTable = new Shared_Model_Data_ManualCategory();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'category_name'            => '',  // マニュアル区分名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $categoryTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/office-manual/category-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/category-update                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル区分 編集(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function categoryUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$categoryTable = new Shared_Model_Data_ManualCategory();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office-manual/category-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {

				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                $message = '';
                if (isset($errorMessage['title'])) {
                    $message = '「区分名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($categoryTable->isExistCategoryName($this->_adminProperty['management_group_id'], $success['category_name'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「カテゴリ名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $categoryTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'category_name'       => $success['category_name'],     // カテゴリ名
						'content_order'       => $contentOrder,                 // 並び順
					);

					$categoryTable->create($data);
				} else {
					// 編集
					$data = array(
						'category_name'       => $success['category_name'],     // カテゴリ名
					);

					$categoryTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/add                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル - 新規登録                                      |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
		$session = new Zend_Session_Namespace('office_manual');
		$this->view->from = $session->from;
		
	    $this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$categoryTable = new Shared_Model_Data_ManualCategory();
		$this->view->categoryList = $categoryTable->getList($this->_adminProperty['management_group_id']);
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/add-post                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル - 新規登録(Ajax)                                |
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

				if (!empty($errorMessage['title']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「タイトル」を入力してください'));
                    return;  
                } else if (!empty($errorMessage['manual_category_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「マニュアル区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['confidentiality']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「機密度」を選択してください'));
                    return;
                } else if (!empty($errorMessage['confidentiality']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「管理責任者」を選択してください'));
                    return;
                }


			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$manualTable = new Shared_Model_Data_Manual();
			
				$data = array(
			        'management_group_id'    => $this->_adminProperty['management_group_id'], // 管理グループID
					'status'                 => 1,                               // ステータス
					
			        'title'                  => $success['title'],               // タイトル
					
					'confidentiality'        => $success['confidentiality'],     // 機密度
					'manual_category_id'     => $success['manual_category_id'],  // 区分
					
					'manager_user_id'        => $success['manager_user_id'],     // 責任者ユーザーID
					
					'memo'                   => $success['memo'],                // 備考
					
					'created_user_id'        => $this->_adminProperty['id'],
					'last_update_user_id'    => $this->_adminProperty['id'],     // 最終更新者ユーザーID
						
	                'created'                => new Zend_Db_Expr('now()'),
	                'updated'                => new Zend_Db_Expr('now()'),
				);

				try {
					$manualTable->getAdapter()->beginTransaction();
					
					$manualTable->create($data);
					
	                // commit
	                $manualTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $manualTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/office-manual/add-post transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/detail                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル情報編集                                         |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
	    $request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
	    
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '保存';
        
		$manualTable = new Shared_Model_Data_Manual();
		$this->view->data = $data = $manualTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$categoryTable = new Shared_Model_Data_ManualCategory();
		$this->view->categoryList = $categoryTable->getList($this->_adminProperty['management_group_id']);
		
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/update                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル情報編集(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function updateAction()
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
                	$this->sendJson(array('result' => 'NG', 'message' => '「タイトル」を入力してください'));
                    return;
                } else if (!empty($errorMessage['manual_category_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「マニュアル区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['confidentiality']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「機密度」を選択してください'));
                    return;
                } else if (!empty($errorMessage['confidentiality']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「管理責任者」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$manualTable = new Shared_Model_Data_Manual();

				$data = array(
					'title'                  => $success['title'],               // タイトル
					'confidentiality'        => $success['confidentiality'],     // 機密度
					'manual_category_id'     => $success['manual_category_id'],  // 区分
					
					'manager_user_id'        => $success['manager_user_id'],     // 責任者ユーザーID
					
					'memo'                   => $success['memo'],                // 備考
					
					'last_update_user_id'    => $this->_adminProperty['id'],     // 最終更新者ユーザーID
				);

				try {
					$manualTable->getAdapter()->beginTransaction();
					
					$manualTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
					
	                // commit
	                $manualTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $manualTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/office-manual/update transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/top                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアルトップ                                           |
    +----------------------------------------------------------------------------*/
    public function topAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id        = $id        = $request->getParam('id');
		$this->view->chapterId = $chapterId = $request->getParam('chapter_id', 0);
		
		$this->view->posTop = $request->getParam('pos');
		
		$session = new Zend_Session_Namespace('office_manual');
		if ($session->from === 'search') {
			$this->view->backUrl = '/office-manual/search';
		} else {
			$this->view->backUrl = '/office-manual';
		}
		

        $session = new Zend_Session_Namespace('manual_edit_mode');

		$this->view->mode = $session->mode[$id];
		
		
		$manualTable = new Shared_Model_Data_Manual();
		$this->view->manual = $manual = $manualTable->getById($this->_adminProperty['management_group_id'], $id);
		
		
		$chapterTable = new Shared_Model_Data_ManualChapter();
		$this->view->chapterList = $chapterList = $chapterTable->getListWithManualId($this->_adminProperty['management_group_id'], $id);

		if (empty($chapterId) && !empty($chapterList)) {
			$chapterId = $chapterList[0]['id'];
		}
		
		$this->view->chapterId = $chapterId;
		
		if (!empty($chapterId)) {
			$itemTable = new Shared_Model_Data_ManualItem();
			$this->view->items = $itemTable->getListWithChapterId($this->_adminProperty['management_group_id'], $chapterId);
		}
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/mode                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 編集モード切り替え                                         |
    +----------------------------------------------------------------------------*/
    public function modeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $request = $this->getRequest();
        $id = $request->getParam('id');
        $mode = $request->getParam('mode');

        $session = new Zend_Session_Namespace('manual_edit_mode');
		$session->mode[$id] = $mode;
		
		$this->sendJson(array('result' => 'OK'));
		return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/chapter-list                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * チャプター管理                                             |
    +----------------------------------------------------------------------------*/
    public function chapterListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->backUrl = '/office-manual/top?id=' . $id;
	
		$chapterTable = new Shared_Model_Data_ManualChapter();
		$this->view->chapterList = $chapterList = $chapterTable->getListWithManualId($this->_adminProperty['management_group_id'], $id);
	
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/chapter-update-order                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * チャプター並び順更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function chapterUpdateOrderAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$chapterTable = new Shared_Model_Data_ManualChapter();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office-manual/chapter-update-order failed to load config');
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
						$chapterTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
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
    |  action_URL    * /office-manual/chapter-detail                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * チャプター・編集                                           |
    +----------------------------------------------------------------------------*/
    public function chapterDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->chapterId = $chapterId = $request->getParam('chapter_id');
		
		$chapterTable = new Shared_Model_Data_ManualChapter();
		
		if (empty($chapterId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'chapter_name'              => '',      // チャプター名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $chapterTable->getById($this->_adminProperty['management_group_id'], $chapterId);

	        if (empty($data)) {
				throw new Zend_Exception('/office-manual/chapter-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/chapter-update                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * チャプター・編集(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function chapterUpdateAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$chapterId = $request->getParam('chapter_id');
		
		$chapterTable = new Shared_Model_Data_ManualChapter();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office-manual/chapter-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['chapter_name'])) {
                    $message = '「チャプター名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if (empty($chapterId)) {

					// 新規登録
					$contentOrder = $chapterTable->getNextContentOrder($this->_adminProperty['management_group_id'], $id);

					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'manual_id'           => $id,                                       // マニュアルID
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス
												
				        'chapter_name'        => $success['chapter_name'],                  // チャプター名
				        'content_order'       => $contentOrder,                             // 並び順

						'created_user_id'     => $this->_adminProperty['id'],
						'last_update_user_id' => $this->_adminProperty['id'],                     // 最終更新者ユーザーID
						  
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$chapterTable->create($data);
					
				} else {
					// 編集
					$data = array(
						'chapter_name'        => $success['chapter_name'],      // チャプター名
					);

					$chapterTable->updateById($this->_adminProperty['management_group_id'], $chapterId, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/item-detail                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アイテム・編集                                             |
    +----------------------------------------------------------------------------*/
    public function itemDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->chapterId = $chapterId = $request->getParam('chapter_id');
		$this->view->itemId    = $itemId    = $request->getParam('item_id');
		$this->view->pos       = $pos       = $request->getParam('pos');
		
		$chapterTable = new Shared_Model_Data_ManualChapter();
		$this->view->chapterData = $chapterTable->getById($this->_adminProperty['management_group_id'], $chapterId);
		
		$itemTable = new Shared_Model_Data_ManualItem();
		
		if (empty($itemId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'content_type' => (string)Shared_Model_Code::MANUAL_CONTENT_TYPE_TEXT,      // コンテンツ種別
		        'content'      => '',
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $itemTable->getById($this->_adminProperty['management_group_id'], $itemId);

	        if (empty($data)) {
				throw new Zend_Exception('/office-manual/item-detail filed to fetch account title data');
			}
			
        	$this->view->data = $data;
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/item-update                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アイテム・編集(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function itemUpdateAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$chapterId = $request->getParam('chapter_id');
		$itemId    = $request->getParam('item_id');
		$pos       = $request->getParam('pos');

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office-manual/item-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['chapter_name'])) {
                    $message = '「チャプター名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				$manualTable  = new Shared_Model_Data_Manual();
				$chapterTable = new Shared_Model_Data_ManualChapter();
				$itemTable    = new Shared_Model_Data_ManualItem();
				
				$chapterData = $chapterTable->getById($this->_adminProperty['management_group_id'], $chapterId);
				
				$itemTable->getAdapter()->beginTransaction();
				try {
					
					if (empty($itemId)) {
						// POSITION変更
						$itemTable->updatePositionFrom($this->_adminProperty['management_group_id'], $chapterId, $pos);
						
						
						// 新規登録
						$data = array(
							'management_group_id'  => $this->_adminProperty['management_group_id'], // 管理グループID
					        'manual_id'            => $chapterData['manual_id'],                // マニュアルID
					        'chapter_id'           => $chapterId,                               // チャプターID
					        
					        'title'                => $success['title'],
							'status'               => Shared_Model_Code::CONTENT_STATUS_ACTIVE, // ステータス
							
							'content_type'         => $success['content_type'],                 // コンテンツ種別
							'content'              => NULL,                                     // コンテンツ
							
							'keyword_1'            => $success['keyword_1'],
							'keyword_2'            => $success['keyword_2'],
							'keyword_3'            => $success['keyword_3'],
							'keyword_4'            => $success['keyword_4'],
							'keyword_5'            => $success['keyword_5'],
							'keyword_6'            => $success['keyword_6'],
							
							'content_order'        => (int)$pos + 1,                            // 並び順
	
							'created_user_id'      => $this->_adminProperty['id'],
							'last_update_user_id'  => $this->_adminProperty['id'],              // 最終更新者ユーザーID
							
							'content_updated'      => new Zend_Db_Expr('now()'),                // 内容最終更新日時
							
			                'created'              => new Zend_Db_Expr('now()'),
			                'updated'              => new Zend_Db_Expr('now()'),
						);
						
						if ($success['content_type'] == (string)Shared_Model_Code::MANUAL_CONTENT_TYPE_TEXT) {
							$data['content']      = $success['content_text'];
						}
						
						$itemTable->create($data);
						$itemId = $itemTable->getLastInsertedId('id');
						
					} else {
						// 編集
						$data = array(
							'title'                => $success['title'],
							'content_type'         => $success['content_type'],                 // コンテンツ種別
							'last_update_user_id'  => $this->_adminProperty['id'],              // 最終更新者ユーザーID

							'keyword_1'            => $success['keyword_1'],
							'keyword_2'            => $success['keyword_2'],
							'keyword_3'            => $success['keyword_3'],
							'keyword_4'            => $success['keyword_4'],
							'keyword_5'            => $success['keyword_5'],
							'keyword_6'            => $success['keyword_6'],
							
							//'content_order'        => $success['content_order'],
							
							'content_updated'      => new Zend_Db_Expr('now()'),                // 内容最終更新日時
						);
	
						if ($success['content_type'] == (string)Shared_Model_Code::MANUAL_CONTENT_TYPE_TEXT) {
							$data['content']      = $success['content_text'];
						}
						
						
						$itemTable->updateById($this->_adminProperty['management_group_id'], $itemId, $data);
			        }
			        
			        $manualTable->updateById($this->_adminProperty['management_group_id'], $chapterData['manual_id'], array(
			        	'last_update_user_id'  => $this->_adminProperty['id'],  // 最終更新者ユーザーID
			        ));
			        
	
					$fileList = array();
					
		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							//var_dump('manual_id: ' . $chapterData['manual_id'] . ' $itemId: ' .  $itemId . ' $fileName: ' . $fileName . ' $tempFileName: ' . $tempFileName);exit;
							
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Manual::makeResource($chapterData['manual_id'], $itemId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			                
			                $fileList[] = array(
								'id'               => $eachId,
								'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
								'file_name'        => $request->getParam($eachId . '_file_name'),
								'summary'          => $request->getParam($eachId . '_summary'),
			                );
			            }
			            
			            $itemTable->updateById($this->_adminProperty['management_group_id'], $itemId, array(
				        	'content' => json_encode($fileList),
			            ));
		            }
		            
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/office-manual/item-update transaction faied: ' . $e);
	            }
	            
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/upload                                      |
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
    |  action_URL    * /office-manual/update-position                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ブロック並び替え(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function updatePositionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		$direction  = $request->getParam('direction');
		
		// POST送信時
		if ($request->isPost()) {
			$itemTable = new Shared_Model_Data_ManualItem();
			
			$targetItem = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
			
			$itemTable->getAdapter()->beginTransaction();
			
			try {
				if ($direction === 'down') {
					$nextOrderItem = $itemTable->getNextOrderItem($this->_adminProperty['management_group_id'], $targetItem['chapter_id'], $targetItem['content_order']);
					
					if (empty($nextOrderItem)) {
						throw new Zend_Exception('/office-manual/update-position no item');
					}
					
					$itemTable->updateById($this->_adminProperty['management_group_id'], $targetItem['id'], array(
						'content_order' => $nextOrderItem['content_order'],
					));
					
					$itemTable->updateById($this->_adminProperty['management_group_id'], $nextOrderItem['id'], array(
						'content_order' => $targetItem['content_order'],
					));
				} else {
					$preOrderItem = $itemTable->getPreOrderItem($this->_adminProperty['management_group_id'], $targetItem['chapter_id'], $targetItem['content_order']);
					
					if (empty($preOrderItem)) {
						throw new Zend_Exception('/office-manual/update-position no item');
					}
					
					$itemTable->updateById($this->_adminProperty['management_group_id'], $targetItem['id'], array(
						'content_order' => $preOrderItem['content_order'],
					));
					
					$itemTable->updateById($this->_adminProperty['management_group_id'], $preOrderItem['id'], array(
						'content_order' => $targetItem['content_order'],
					));
				}
				
                // commit
                $itemTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $itemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/office-manual/update-position transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-manual/delete-item                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ブロック削除(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function deleteItemAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		// POST送信時
		if ($request->isPost()) {
			$itemTable = new Shared_Model_Data_ManualItem();
			
			$targetData = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
			
			try {
				$itemTable->getAdapter()->beginTransaction();
				
				$itemTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
				));
				
				$itemTable->updateDownPositionFrom($this->_adminProperty['management_group_id'], $targetData['chapter_id'], $targetData['content_order']);
			
                // commit
                $itemTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $itemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/office-manual/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
}

