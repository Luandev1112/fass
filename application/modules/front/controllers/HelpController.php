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
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		$this->view->menu = 'accounting';

		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		
		$dbAdapter = $accountTitleTable->getAdapter();

        $selectObj = $accountTitleTable->select();
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
        
        $accountDivisionTable = new Shared_Model_Data_AccountDivision();
        
        $acountDivisionList = array();
        
        $accountDivisionItems = $accountDivisionTable->getList();
        
        foreach ($accountDivisionItems as $each) {
        	$acountDivisionList[$each['id']] = $each;
        }
        
        $this->view->accountDivision = $acountDivisionList;
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
				throw new Zend_Exception('/management/office/accounting-update failed to load config');
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
    	if (empty($this->_adminProperty['is_master'])) {
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
				throw new Zend_Exception('/management/office/accounting-update failed to load config');
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
    |  action_URL    * /office/manual-index                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアル                                                 |
    +----------------------------------------------------------------------------*/
    public function manualIndexAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'manual';
		
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
    |  action_URL    * /office/manual-item                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアルアイテム                                         |
    +----------------------------------------------------------------------------*/
    public function manualItemAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->backUrl = '/office/manual-index';
		
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
}

