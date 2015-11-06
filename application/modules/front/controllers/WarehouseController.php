<?php
/**
 * class WarehouseController
 */
 
class WarehouseController extends Front_Model_Controller
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
		$this->view->bodyLayoutName = 'one_column.phtml';
		$this->view->mainCategoryName = 'システム設定';
		$this->view->menuCategory     = 'system';
		$this->view->menu = 'warehouse';
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /warehouse/list                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 倉庫一覧                                                   |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		
		$warehouseTable = new Shared_Model_Data_Warehouse();
		
		$dbAdapter = $warehouseTable->getAdapter();
        $selectObj = $warehouseTable->getActiveList($this->_adminProperty['management_group_id'], true);
        
        // 検索条件
        
        
		$selectObj->order('id ASC');
		
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
    |  action_URL    * /warehouse/list-select                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 倉庫一覧(ポップアップ選択用)                               |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
   		$this->_helper->layout->setLayout('blank');
   		
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		
		$conditions = array();
		$conditions['condition_name'] = $request->getParam('condition_name', '');
		$this->view->conditions       = $conditions;
		
		
		$warehouseTable = new Shared_Model_Data_Warehouse();
		
		$dbAdapter = $warehouseTable->getAdapter();
        $selectObj = $warehouseTable->getActiveList($this->_adminProperty['management_group_id'], true);
        
        // 検索条件
        
        
		$selectObj->order('id ASC');
		
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
    |  action_URL    * /warehouse/add                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 倉庫新規登録                                               |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();		
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /warehouse/add-post                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 倉庫新規登録(Ajax)                                         |
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

                if (!empty($errorMessage['item_name']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「アイテム名」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「ステータス」を選択してください';
                    $this->sendJson($result);
                    return;
                }
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$warehouseTable = new Shared_Model_Data_Warehouse();
				
				// 新規登録
				$data = array(
					'management_group_id' => $this->_adminProperty['management_group_id'],
					'status'              => Shared_Model_Code::WAREHOUSE_STATUS_ACTIVE,
			        
			        'name'                => $success['name'],
					'company_name'        => $success['company_name'],
					'zipcode'             => $success['zipcode'],
					'prefecture'          => $success['prefecture'],
					'address1'            => $success['address1'],
					'address2'            => $success['address2'],
					'tel'                 => $success['tel'],
					'fax'                 => $success['fax'],
					
					'person_in_charge'    => $success['person_in_charge'],
					'mail'                => $success['mail'],
					'mobile'              => $success['mobile'],
					'memo'                => $success['memo'],
					
	                'created'             => new Zend_Db_Expr('now()'),
	                'updated'             => new Zend_Db_Expr('now()'),
				);

				$warehouseTable->getAdapter()->beginTransaction();
            	  
	            try {
					$warehouseTable->create($data);
					
					
	                // commit
	                $warehouseTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $warehouseTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/warehouse/add-post transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /warehouse/detail                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 倉庫詳細                                                   |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
        $this->view->backUrl = '/warehouse/list';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$warehouseTable     = new Shared_Model_Data_Warehouse();
		$this->view->data = $data = $warehouseTable->getById($this->_adminProperty['management_group_id'], $id);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /warehouse/update                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 倉庫情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$id = $request->getParam('id');
		
		if (empty($id)) {
			throw new Zend_Exception('/warehouse/update - ID is empty');
		}
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['name']['isEmpty'])) {;
                    $this->sendJson(array('result' => 'NG', 'message' => '「倉庫名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$warehouseTable = new Shared_Model_Data_Warehouse();

				// 更新
				$data = array(
			        'name'              => $success['name'],
					'company_name'      => $success['company_name'],
					'zipcode'           => $success['zipcode'],
					'prefecture'        => $success['prefecture'],
					'address1'          => $success['address1'],
					'address2'          => $success['address2'],
					'tel'               => $success['tel'],
					'fax'               => $success['fax'],
					
					'person_in_charge'  => $success['person_in_charge'],
					'mail'              => $success['mail'],
					'mobile'            => $success['mobile'],
					'memo'              => $success['memo'],
				);
				
				$warehouseTable->updateById($this->_adminProperty['management_group_id'], $id, $data);

			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

        
}

