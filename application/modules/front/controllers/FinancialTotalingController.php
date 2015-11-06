<?php
/**
 * class FinancialTotalingController
 */
 
class FinancialTotalingController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '社内処理';
		$this->view->menuCategory     = 'transaction';
		$this->view->menu             = 'account-totaling';
	
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/copy                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループ単位 コピー                                    |
    +----------------------------------------------------------------------------*/
    public function copyAction()
    {
		$request = $this->getRequest();
		
		$categoryId    = '9';
		$newCategoryId = '10';
		$oldManagementGroupId = '2';
		$newManagementGroupId = '3';
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
		$layoutTable   = new Shared_Model_Data_AccountTotalingGroupLayout();
		$itemTable      = new Shared_Model_Data_AccountTotalingGroup();
		
		// カテゴリデータ
		$categoryData = $categoryTable->getById($oldManagementGroupId, $categoryId);
		
		
		// カテゴリレイアウト(引用はコピーできないので、コピー後に設定する)
		$layoutItems = $layoutTable->getListByCategoryId($oldManagementGroupId, $categoryId, $categoryData['layout_version_id']);
		
		foreach ($layoutItems as $eachLayout) {
			unset($eachLayout['id']);
			unset($eachLayout['created']);
			unset($eachLayout['updated']);
			
			$eachLayout['management_group_id'] = $newManagementGroupId;  // 管理グループID
        
			$eachLayout['category_id']         = $newCategoryId;
			$eachLayout['version_id']          = '1';
			$layoutTable->create($eachLayout);
		}
		
		// 採算コード
        $items = $itemTable->getListByCategoryId($oldManagementGroupId, $categoryId);

		foreach ($items as $each) {
			unset($each['id']);
			unset($each['created']);
			unset($each['updated']);
			$each['management_group_id'] = $newManagementGroupId;  // 管理グループID
			$each['category_id']         = $newCategoryId;
			$itemTable->create($each);
		}
		
		echo 'OK';
		exit;
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/category-list                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループリスト                                         |
    +----------------------------------------------------------------------------*/
    public function categoryListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
		
		$dbAdapter = $categoryTable->getAdapter();

        $selectObj = $categoryTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id'], $managementGroupId);
		$selectObj->order('content_order ASC');
		
        $this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/category-update-order                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループ並び順更新(Ajax)                               |
    +----------------------------------------------------------------------------*/
    public function categoryUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/currency-update-order failed to load config');
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
    |  action_URL    * /financial-totaling/category-layout                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループ - レイアウト                                  |
    +----------------------------------------------------------------------------*/
    public function categoryLayoutAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '保存';
        
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId  = $request->getParam('category_id');
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
        $this->view->data = $data = $categoryTable->getById($this->_adminProperty['management_group_id'], $categoryId);
		
		
		$layoutTable = new Shared_Model_Data_AccountTotalingGroupLayout();
        $this->view->items = $layoutTable->getListByCategoryId($this->_adminProperty['management_group_id'], $categoryId, $data['layout_version_id']);

    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/get-unique-id                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ユニークID発行                                             |
    +----------------------------------------------------------------------------*/
    public function getUniqueIdAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $this->sendJson(array('result' => 'OK', 'unique_id' => uniqid()));
        
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/save-layout                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * レイアウト保存(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function saveLayoutAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$categoryId = $request->getParam('category_id');
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial-totaling/category-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				$layoutTable = new Shared_Model_Data_AccountTotalingGroupLayout();
				$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();


	            $layoutTable->getAdapter()->beginTransaction();
            	//$layoutTable->getAdapter()->query("LOCK TABLES frs_account_totaling_group_category WRITE, frs_account_totaling_group_layout WRITE")->execute();
            	
	            try {
	            
					$itemList = explode(',', $success['item_list']);

					$count = 1;
					$nextVersion = $categoryTable->getNextVersion($this->_adminProperty['management_group_id'], $categoryId);
					
					if (!empty($itemList)) {
						foreach ($itemList as $eachId) {
							$uniqueId     = $request->getParam($eachId . '_unique_id');
							$rowType      = $request->getParam($eachId . '_row_type');
							
							$content  = '';
							$calcText = '';
							
							if ($rowType === (string)Shared_Model_Code::ACCOUNT_TOTALING_ROW_TYPE_REFERENCE) {
								$content  = $request->getParam($eachId . '_reference_id');
								
								if (empty($content)) {
									$categoryTable->getAdapter()->rollBack();
									$categoryTable->getAdapter()->query("UNLOCK TABLES")->execute();
								    $this->sendJson(array('result' => 'NG', 'message' => $count . '行目：採算コードを選択してください'));
						    		return;
								}
								
							} else {
								$content  = $request->getParam($eachId . '_content_text');
								
								// 項目名
								if (empty($content)) {
									$categoryTable->getAdapter()->rollBack();
									$categoryTable->getAdapter()->query("UNLOCK TABLES")->execute();
									
							    	$this->sendJson(array('result' => 'NG', 'message' => $count . '行目：項目名を入力してください'));
									return;
								}
								
								// 合計
								if ($rowType === (string)Shared_Model_Code::ACCOUNT_TOTALING_ROW_TYPE_TOTAL) {
									$calcText = $request->getParam($eachId . '_calc_text');
									
									// 項目名
									if (empty($content)) {
										$categoryTable->getAdapter()->rollBack();
										$categoryTable->getAdapter()->query("UNLOCK TABLES")->execute();
										
								    	$this->sendJson(array('result' => 'NG', 'message' => $count . '行目：計算式を入力してください'));
										return;
									}
								}
							}
							
							// 新規登録
							$data = array(
								'management_group_id' => $this->_adminProperty['management_group_id'],
								'category_id'         => $categoryId,                 // カテゴリID
								'version_id'          => $nextVersion,                // バージョンID
								'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
								
								'unique_id'           => $uniqueId,                   // キー
								'row_type'            => $rowType,                    // コンテンツ種別
								'content'             => $content,                    // 内容
								'calc_text'           => $calcText,                   // 計算式
								'content_order'       => $count,                      // 並び順
								
				                'created'             => new Zend_Db_Expr('now()'),
				                'updated'             => new Zend_Db_Expr('now()'),
							);
			
							$layoutTable->create($data);
							
							$count++;
						}
					}
					
					$categoryTable->updateById($this->_adminProperty['management_group_id'], $categoryId, array(
						'layout_version_id' => $nextVersion,
					));
				
	                // commit
	                $layoutTable->getAdapter()->commit();
	                //$layoutTable->getAdapter()->query("UNLOCK TABLES")->execute();

	            } catch (Exception $e) {
	                $layoutTable->getAdapter()->rollBack();
	                //$layoutTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                throw new Zend_Exception('/financial-totaling/save-layout faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/category-edit                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループ・編集                                         |
    +----------------------------------------------------------------------------*/
    public function categoryEditAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'category_name' => '',       // カテゴリ名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $categoryTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/financial-totaling/category-edit filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/category-update                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループ・編集(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function categoryUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial-totaling/category-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['name'])) {
                    $message = '「カテゴリ名」を入力してください';                    
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
				
				if (empty($id)) {
					// 新規登録
					$contentOrder = $categoryTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						
				        'category_name'       => $success['category_name'],    // カテゴリ名
						
						'content_order'       => $contentOrder,
						
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$categoryTable->create($data);
					
				} else {
					// 編集
					$data = array(
				        'category_name'       => $success['category_name'],    // カテゴリ名
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
    |  action_URL    * /financial-totaling/category-detail                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算グループ詳細                                           |
    +----------------------------------------------------------------------------*/
    public function categoryDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/financial-totaling/category-list';
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId  = $request->getParam('category_id');
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
        $this->view->data = $categoryTable->getById($this->_adminProperty['management_group_id'], $categoryId);
		
		
		$itemTable = new Shared_Model_Data_AccountTotalingGroup();
        $this->view->items = $itemTable->getListByCategoryId($this->_adminProperty['management_group_id'], $categoryId);
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/item-select                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード選択                                             |
    +----------------------------------------------------------------------------*/
    public function itemSelectAction()
    {
        $this->_helper->layout->setLayout('blank');
        
		$request = $this->getRequest();
		$categoryId  = $request->getParam('category_id');
		$this->view->lock  = $request->getParam('lock');
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
        $this->view->categoryList = $categoryList = $categoryTable->getList($this->_adminProperty['management_group_id']);
		
		if (empty($categoryId)) {
			$categoryId = $categoryList[0]['id'];
		}
		
		$this->view->categoryId = $categoryId;
		
		if (!empty($categoryId)) {
			$itemTable = new Shared_Model_Data_AccountTotalingGroup();
			$this->view->items = $itemTable->getListByCategoryId($this->_adminProperty['management_group_id'], $categoryId);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/item-update-order                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード - 並び替え(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function itemUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}

	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$categoryId = $request->getParam('category_id');
			
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial-totaling/item-update-order failed to load config');
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
				$itemTable = new Shared_Model_Data_AccountTotalingGroup();

				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$itemTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
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
    |  action_URL    * /financial-totaling/item-edit                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード - 編集                                          |
    +----------------------------------------------------------------------------*/
    public function itemEditAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId = $request->getParam('category_id');
		$this->view->id = $id = $request->getParam('id');
		
		$itemTable = new Shared_Model_Data_AccountTotalingGroup();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'title'            => '',     // 項目名
				'memo'             => '',     // 説明
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/financial-totaling/item-edit filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial-totaling/item-update                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード - 編集(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function itemUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$categoryId = $request->getParam('category_id');
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial-totaling/item-update failed to load config');
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
				$itemTable = new Shared_Model_Data_AccountTotalingGroup();
				
				if ($itemTable->isExistTitle($this->_adminProperty['management_group_id'], $categoryId, $success['title'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「項目名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $itemTable->getNextContentOrder($this->_adminProperty['management_group_id'], $categoryId);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						
						'category_id'         => $categoryId,                   // カテゴリID
				        'title'               => $success['title'],             // 項目名
						'memo'                => $success['memo'],              // 説明
						
						'content_order'       => $contentOrder,                 // 並び順
						
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$itemTable->create($data);
				} else {
					// 編集
					$data = array(
						'title'               => $success['title'],             // 項目名
						'memo'                => $success['memo'],              // 説明
					);

					$itemTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
       
}

