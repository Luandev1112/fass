<?php
/**
 * class OfficeController
 */
 
class OfficeController extends Front_Model_Controller
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
    |  action_URL    * /office/payment                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 決裁申請                                                   |
    +----------------------------------------------------------------------------*/
    public function paymentAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'payment';
		
		/*
		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', $typeCode);
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		*/

        //$this->view->items = $items;
        //$this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/expense                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 決裁申請                                                   |
    +----------------------------------------------------------------------------*/
    public function expenseAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'management';
		
		/*
		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', $typeCode);
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		*/

        //$this->view->items = $items;
        //$this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 会計科目一覧                                               |
    +----------------------------------------------------------------------------*/
    public function accountingAction()
    {
	    $this->view->menuCategory     = 'transaction';
	    
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$this->view->menu = 'accounting';

		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		
        $selectObj = $accountTitleTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');

        $this->view->items = $selectObj->query()->fetchAll();

        // 区分
        $accountDivisionTable = new Shared_Model_Data_AccountDivision();
        
        $acountDivisionList = array();
        $accountDivisionItems = $accountDivisionTable->getList();
        
        foreach ($accountDivisionItems as $each) {
        	$acountDivisionList[$each['id']] = $each;
        }
        
        $this->view->accountDivision = $acountDivisionList;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-copy                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 会計科目一覧                                               |
    +----------------------------------------------------------------------------*/
    public function accountingCopyAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		
        $selectObj = $accountTitleTable->select();
        $selectObj->where('management_group_id = ?', '1');
		$selectObj->order('content_order ASC');

        $items = $selectObj->query()->fetchAll();
		
		foreach ($items as $each) {
			unset($each['id']);
			unset($each['created']);
			unset($each['updated']);
			
			$each['management_group_id'] = '2';
			
			$accountTitleTable->create($each);
		}
		
		echo 'OK';
		exit;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-select                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 会計科目一覧(ポップアップ用)                               |
    +----------------------------------------------------------------------------*/
    public function accountingSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();

		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $selectObj = $accountTitleTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
		

        $accountDivisionTable = new Shared_Model_Data_AccountDivision();
        
        $acountDivisionList = array();        
        $accountDivisionItems = $accountDivisionTable->getList();
        
        foreach ($accountDivisionItems as $each) {
        	$acountDivisionList[$each['id']] = $each;
        }
        
        $this->view->accountDivision = $acountDivisionList;
    }
      
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-update-order                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 会計科目並び順更新(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function accountingUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office/accounting-update failed to load config');
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
						$accountTitleTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
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
    |  action_URL    * /office/accounting-detail                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 会計科目・編集                                             |
    +----------------------------------------------------------------------------*/
    public function accountingDetailAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'account_division' => '',                    // 区分
				'title'            => '',                    // 科目名
				'content'          => '',                    // 内容
				'example'          => '',                    // 例
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/office/accounting-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
        
        $accountDivisionTable = new Shared_Model_Data_AccountDivision();
        $this->view->accountDivision = $accountDivisionTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-update                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 会計科目・編集(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function accountingUpdateAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/office/accounting-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['account_division'])) {
                    $message = '「区分」を選択してください';
                } else if (isset($errorMessage['title'])) {
                    $message = '「科目名」を入力してください';   
                } else if (isset($errorMessage['content'])) {
                    $message = '「内容」を入力してください';   
                } else if (isset($errorMessage['example'])) {
                    $message = '「例」を入力してください';   
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($accountTitleTable->isExistTitle($this->_adminProperty['management_group_id'], $success['title'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「科目名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $accountTitleTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'account_division'    => $success['account_division'],  // 区分
						'title'               => $success['title'],             // 科目名
						'content'             => $success['content'],           // 内容
						'example'             => $success['example'],           // 例
						'content_order'       => $contentOrder,                 // 並び順
					);

					$accountTitleTable->create($data);
				} else {
					// 編集
					$data = array(
						'account_division' => $success['account_division'],  // 区分
						'title'            => $success['title'],             // 科目名
						'content'          => $success['content'],           // 内容
						'example'          => $success['example'],           // 例
					);

					$accountTitleTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }




    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-group                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード一覧                                             |
    +----------------------------------------------------------------------------*/
    public function accountingGroupAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		$this->view->menu = 'accounting-group';

		$accountGroupTable = new Shared_Model_Data_AccountTotalingGroup();
		
		$dbAdapter = $accountGroupTable->getAdapter();

        $selectObj = $accountGroupTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		
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
    |  action_URL    * /office/accounting-group-select                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード一覧(ポップアップ用)                             |
    +----------------------------------------------------------------------------*/
    public function accountingGroupSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();

		$accountGroupTable = new Shared_Model_Data_AccountTotalingGroup();
        $selectObj = $accountGroupTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
      
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-group-update-order                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード並び順更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function accountingGroupUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$accountGroupTable = new Shared_Model_Data_AccountTotalingGroup();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/management/office/accounting-group-update-order failed to load config');
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
						$accountGroupTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
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
    |  action_URL    * /office/accounting-group-detail                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード・編集                                           |
    +----------------------------------------------------------------------------*/
    public function accountingGroupDetailAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$accountGroupTable = new Shared_Model_Data_AccountTotalingGroup();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'title'            => '',                    // 項目名
				'memo'             => '',                    // コメント
				'example'          => '',                    // 例
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $accountGroupTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/office/accounting-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/accounting-group-update                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 採算コード・編集(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function accountingGroupUpdateAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$accountGroupTable = new Shared_Model_Data_AccountTotalingGroup();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/management/office/accounting-group-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['user_name'])) {
                    $message = '「利用者ID」を入力してください';
                } else if (isset($errorMessage['password'])) {
                    $message = '「パスワード」を入力してください';   
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($accountTitleTable->isExistTitle($this->_adminProperty['management_group_id'], $success['title'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「科目名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $accountGroupTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'title'               => $success['title'],             // 項目名
						'memo'                => $success['memo'],              // コメント
						'content_order'       => $contentOrder,                 // 並び順
					);

					$accountGroupTable->create($data);
				} else {
					// 編集
					$data = array(
						'title'            => $success['title'],             // 科目名
						'content'          => $success['content'],           // 内容
						'example'          => $success['example'],           // 例
					);

					$accountGroupTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }


}

