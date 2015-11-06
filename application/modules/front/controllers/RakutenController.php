<?php
/**
 * class RakutenController
 */
 
class RakutenController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '楽天';
		$this->view->menuCategory     = 'rakuten';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rakuten                                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 一覧                                                       |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();

		
		$rakutenTable = new Shared_Model_Data_RakutenPage();
		
		$dbAdapter = $rakutenTable->getAdapter();

        $selectObj = $rakutenTable->select();
        $selectObj->where('status != ?', 0);
        
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
    |  action_URL    * /item/add                                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アイテム新規登録                                           |
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
    |  action_URL    * /item/add-post                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品新規登録(Ajax)                                         |
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

                if (!empty($errorMessage['customer_id']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「プロジェクト名」を選択してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「タイトル」を入力してください';
                    $this->sendJson($result);
                    return;
                }
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$rakutenTable     = new Shared_Model_Data_RakutenPage();

				$rakutenTable->getAdapter()->beginTransaction();
            	  
	            try {
					// 新規登録
					$data = array(
						'status'          => 1,
						
						'customer_id'     => $success['customer_id'],
						'title'           => $success['title'],
						'pc_product_text' => '',
						'sp_product_text' => '',
						'pc_sales_text'   => '',
		                
		                'created'         => new Zend_Db_Expr('now()'),
		                'updated'         => new Zend_Db_Expr('now()'),
					);

					$rakutenTable->create($data);
					$pageId = $rakutenTable->getLastInsertedId('id');
					
	                // commit
	                $rakutenTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $rakutenTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/rakuten/add-post transaction faied: ' . $e);
	                
	            }
				
				$result = array('result' => 'OK');
			    $this->sendJson($result);
		    	return;
			}
		}
		
		$result = array('result' => 'NG');
	    $this->sendJson($result);
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rakuten/detail                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報                                                   |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->type   = $type = $request->getParam('type');
		$this->view->posTop = $request->getParam('pos');
		
		$rakutenTable     = new Shared_Model_Data_RakutenPage();
		$this->view->data = $rakutenTable->getById($id);
		//header('Cache-Control: no-cache, no-store, must-revalidate');
		$this->view->backUrl = 'javascript:void(0);';
		
		$this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '保存';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/update                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$id      = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			
			$rakutenTable     = new Shared_Model_Data_RakutenPage();
			
			// 更新
			$data = array(
				$success['type'] => $success['text'],
			);
			
			$rakutenTable->updateById($id, $data);
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }

    
}

