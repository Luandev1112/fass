<?php
/**
 * class SystemController
 */
 
class SystemController extends Front_Model_Controller
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
		$this->view->mainCategoryName = 'システム設定';
		$this->view->menuCategory     = 'system';
	
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/define-list                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 定義リスト                                                 |
    +----------------------------------------------------------------------------*/
    public function defineListAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();
		$key     = $request->getParam('key', '1');
		
		if ($key == Shared__Model_Code::DEFINATION_COUNTRY) {
			$countryTable = new Shared_Model_Data_Country();
	        $this->view->items = $countryTable->getList();
        }
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/country-list                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 国定義リスト                                               |
    +----------------------------------------------------------------------------*/
    public function countryListAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();

		// 検索条件
		$conditions = array();
		$this->view->conditions = $conditions;

		$countryTable = new Shared_Model_Data_Country();
        $this->view->items = $countryTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/industry-category-list                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種カテゴリ一覧                                           |
    +----------------------------------------------------------------------------*/
    public function industryCategoryListAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();


		$categoryTable = new Shared_Model_Data_IndustryCategory();
        $this->view->items = $categoryTable->getList();
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/industry-category-update-order                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種カテゴリ並び替え(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function industryCategoryUpdateOrderAction()
    {
	    /*
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		*/
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/system/industry-category-update-order failed to load config');
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
						$industryCategoryTable->updateById($eachId, array(
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
    |  action_URL    * /system/industry-category-edit                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種カテゴリ 編集                                          |
    +----------------------------------------------------------------------------*/
    public function industryCategoryEditAction()
    {
    	//if (empty($this->_adminProperty['allow_connection_progress_master'])) {
		//	throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		//}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'name'            => '', // 業種カテゴリ名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $industryCategoryTable->getById($id);

	        if (empty($data)) {
				throw new Zend_Exception('/system/industry-category-edit filed to fetch account title data');
			}

        	$this->view->data = $data;
        }

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/industry-category-update                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種カテゴリ 編集(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function industryCategoryUpdateAction()
    {
    	//if (empty($this->_adminProperty['allow_connection_progress_master'])) {
		//	throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		//}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/system/industry-category-update failed to load config');
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

				if ($industryCategoryTable->isExistName($success['name'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「カテゴリ名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $industryCategoryTable->getNextContentOrder();
					
					$data = array(
						'name'               => $success['name'],             // カテゴリ名
						'content_order'      => $contentOrder,                // 並び順
					);

					$industryCategoryTable->create($data);
				} else {
					// 編集
					$data = array(
						'name'               => $success['name'],             // カテゴリ名
					);

					$industryCategoryTable->updateById($id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/industry-category-detail                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種カテゴリ詳細                                           |
    +----------------------------------------------------------------------------*/
    public function industryCategoryDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/system/industry-category-list';
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId  = $request->getParam('category_id');
		
		$categoryTable = new Shared_Model_Data_IndustryCategory();
        $this->view->data = $categoryTable->getById($categoryId);
		
		
		$industryTypeTable = new Shared_Model_Data_IndustryType();
        $this->view->items = $industryTypeTable->getListByCategoryId($categoryId);
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/industry-type-update-order                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種並び替え(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function industryTypeUpdateOrderAction()
    {
	    /*
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		*/
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$industryTypeTable = new Shared_Model_Data_IndustryType();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/system/industry-type-update-order failed to load config');
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
						$industryTypeTable->updateById($eachId, array(
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
    |  action_URL    * /system/industry-type-edit                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種 編集                                                  |
    +----------------------------------------------------------------------------*/
    public function industryTypeEditAction()
    {
    	//if (empty($this->_adminProperty['allow_connection_progress_master'])) {
		//	throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		//}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId = $request->getParam('category_id');
		$this->view->id = $id = $request->getParam('id');
		
		$industryTypeTable = new Shared_Model_Data_IndustryType();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'name'            => '',                    // 業種名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $industryTypeTable->getById($id);

	        if (empty($data)) {
				throw new Zend_Exception('/system/industry-type-edit filed to fetch account title data');
			}

        	$this->view->data = $data;
        }

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/industry-type-update                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種 編集(Ajax)                                            |
    +----------------------------------------------------------------------------*/
    public function industryTypeUpdateAction()
    {
    	//if (empty($this->_adminProperty['allow_connection_progress_master'])) {
		//	throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		//}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$categoryId = $request->getParam('category_id');
		$id = $request->getParam('id');
		
		$industryTypeTable = new Shared_Model_Data_IndustryType();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/system/industry-type-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {

				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                $message = '';
                if (isset($errorMessage['name'])) {
                    $message = '「業種名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($industryTypeTable->isExistName($success['name'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「業種名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $industryTypeTable->getNextContentOrder($categoryId);
					
					$data = array(
						'industry_category_id'  => $categoryId,                  // カテゴリID
						'name'                  => $success['name'],             // 業種名
						'content_order'         => $contentOrder,                // 並び順
					);

					$industryTypeTable->create($data);
				} else {
					// 編集
					$data = array(
						'name'                 => $success['name'],              // 業種名
					);

					$industryTypeTable->updateById($id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    

    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/business-list                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 当社事業リスト                                             |
    +----------------------------------------------------------------------------*/
    public function businessListAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();

		// 検索条件
		$conditions = array();
		$this->view->conditions = $conditions;

		$businessTable = new Shared_Model_Data_OurBusiness();
        $this->view->items = $businessTable->getList();
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/product-class-list                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 調達製造区分リスト                                         |
    +----------------------------------------------------------------------------*/
    public function productClassListAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();

		// 検索条件
		$conditions = array();
		$this->view->conditions = $conditions;

		$classTable = new Shared_Model_Data_ItemProductClass();
        $this->view->items = $classTable->getList();
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/update-product-class                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 調達製造区分更新                                          |
    +----------------------------------------------------------------------------*/
    public function updateProductClassAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();

		/*
		$id   = '6';
		$name = '自社製自社原料';
		
		$classTable = new Shared_Model_Data_ItemProductClass();
        $classTable->updateById($id, array('name' => $name));
        
        echo 'OK';
        exit;
        */
        
        $classTable = new Shared_Model_Data_ItemProductClass();
        $classTable->create(array(
		    'name'          => '他社製他社名原料',    // 名称
			'content_order' => 99,         // 並び順
            'created'       => new Zend_Db_Expr('now()'),
            'updated'       => new Zend_Db_Expr('now()'), 
        ));
        
        echo 'OK';
        exit;
    }
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/supply-production-method                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 委託方法リスト                                |
    +----------------------------------------------------------------------------*/
    public function supplyProductionMethodAction()
    {
    	$this->view->menu = 'define';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		// 検索条件
		$conditions = array();
		$this->view->conditions = $conditions;

		$table = new Shared_Model_Data_SupplyProductionMethod();
        $this->view->items = $table->getList();
    }   



    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/material-kind-list                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料種別定義                                               |
    +----------------------------------------------------------------------------*/
    public function materialKindListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/system/list';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$kindTable = new Shared_Model_Data_MaterialKind();
		
		$dbAdapter = $kindTable->getAdapter();

        $selectObj = $kindTable->select();
        //$selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/material-kind-update-order                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料種別並び順更新(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function materialKindUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$kindTable = new Shared_Model_Data_MaterialKind();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/system/material-kind-update-order failed to load config');
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
						$kindTable->updateById($eachId, array(
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
    |  action_URL    * /system/material-kind-detail                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料種別・編集                                             |
    +----------------------------------------------------------------------------*/
    public function materialKindDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$kindTable = new Shared_Model_Data_MaterialKind();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'name'            => '',                    // 科目名
				'status'           => 0,
			);
			
		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $kindTable->getById($id);

	        if (empty($data)) {
				throw new Zend_Exception('/system/material-kind-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /system/material-kind-update                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料種別・編集(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function materialKindUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$kindTable = new Shared_Model_Data_MaterialKind();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/system/material-kind-update failed to load config');
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
				
				if (empty($id)) {
					// 新規登録
					$contentOrder = $kindTable->getNextContentOrder();
					
					$data = array(
						'name'                => $success['name'],             // 項目名
						'status'              => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
						'content_order'       => $contentOrder,                 // 並び順
					);
					
					if (!empty($success['status'])) {
						$data['status'] = Shared_Model_Code::CONTENT_STATUS_ACTIVE;
					}

					$kindTable->create($data);
				} else {
					// 編集
					$data = array(
						'name'               => $success['name'],             // 項目名
						'status'             => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
					);
					
					if (!empty($success['status'])) {
						$data['status'] = Shared_Model_Code::CONTENT_STATUS_ACTIVE;
					}

					$kindTable->updateById($id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
}

