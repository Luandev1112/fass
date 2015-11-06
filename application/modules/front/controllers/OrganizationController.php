<?php
/**
 * class OrganizationController
 */
 
class OrganizationController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '利用者管理';
		$this->view->menuCategory     = 'user';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization                                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 組織一覧                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		$this->view->menu = 'organization';
		
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$groupTable = new Shared_Model_Data_ManagementGroup();
		$selectObj = $groupTable->select();
		$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
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
    |  action_URL    * /organization/add                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 組織 新規登録                                              |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();

		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization/add-post                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 組織 新規登録(Ajax)                                        |
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
				
				if (!empty($errorMessage['display_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「組織ID」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['organization_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「組織名」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$groupTable = new Shared_Model_Data_ManagementGroup();
				
				if ($groupTable->isUsedDisplayId($success['display_id'], false)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「組織ID」は既に使われています'));
		    		return;
				}
				
				// 新規登録
				$data = array(
			        'display_id'                        => $success['display_id'],                // 組織ID
					'status'                            => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
					
					'organization_name'                 => $success['organization_name'],         // 組織名
					'group_header_color'                => $success['group_header_color'],         // ヘッダーカラー
					
					'country'                           => $success['country'],                   // 国
					'postal_code'                       => $success['postal_code'],               // 郵便番号
					'prefecture'                        => $success['prefecture'],                // 都道府県
					'city'                              => $success['city'],                      // 市区町村
					'address'                           => $success['address'],                   // 丁番地
					'building'                          => $success['building'],                  // 建物名・階／号室
					'representative_name'               => $success['representative_name'],       // 代表者名
					'representative_name_kana'          => $success['representative_name_kana'],  // 代表者名カナ
					'tel'                               => $success['tel'],                       // TEL
					'fax'                               => $success['fax'],                       // FAX
					'web_url'                           => $success['web_url'],                   // WEB URL
					'memo'                              => $success['memo'],                      // メモ
					
	                'created'                           => new Zend_Db_Expr('now()'),
	                'updated'                           => new Zend_Db_Expr('now()'),
				);
				
				$groupTable->getAdapter()->beginTransaction();
            	  
	            try {
					$groupTable->create($data);
					$id = $groupTable->getLastInsertedId('id');
					
	                // commit
	                $groupTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $groupTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/organization/add-post transaction failed: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

	

    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization/detail                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 組織編集                                                   |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/organization';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$groupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->data = $data =  $groupTable->getById($id);

		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();
		
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$this->view->gmoAccountList = $gmoTable->getList();

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization/update                                       |
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
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				var_dump($errorMessage);exit;
				if (!empty($errorMessage['display_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「組織ID」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['organization_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「組織名」を入力してください'));
                    return;
                }
                
                $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
                return;
	    		
			} else {
				$groupTable = new Shared_Model_Data_ManagementGroup();

				if ($groupTable->isUsedDisplayId($success['display_id'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「組織ID」は既に使われています'));
		    		return;
				}
				
				// 更新
				$data = array(
					'status'                            => $success['status'],                     // ステータス
					
					'display_id'                        => $success['display_id'],                 // 組織ID
					'organization_name'                 => $success['organization_name'],          // 組織名
					'group_header_color'                => $success['group_header_color'],         // ヘッダーカラー
					
					'country'                           => $success['country'],                    // 国
					'postal_code'                       => $success['postal_code'],                // 郵便番号
					'prefecture'                        => $success['prefecture'],                 // 都道府県
					'city'                              => $success['city'],                       // 市区町村
					'address'                           => $success['address'],                    // 丁番地
					'building'                          => $success['building'],                   // 建物名・階／号室
					'representative_name'               => $success['representative_name'],        // 代表者名
					'representative_name_kana'          => $success['representative_name_kana'],   // 代表者名カナ
					'tel'                               => $success['tel'],                        // TEL
					'fax'                               => $success['fax'],                        // FAX
					'web_url'                           => $success['web_url'],                    // WEB URL
					'memo'                              => $success['memo'],                       // メモ
					'gmo_account_id'                    => $success['gmo_account_id'],                       // メモ
				);
				
				$groupTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }




    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization/gmo-set-up                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMOアカウント初期設定                                      |
    +----------------------------------------------------------------------------*/
    public function gmoSetUpAction()
    {
    	$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
    	
		// 新規登録
		$data = array(
			'status'                    => Shared_Model_Code::CONTENT_STATUS_ACTIVE, // ステータス
			
			'name'                      => 'フレスコ株式会社',                       // アカウント名
			
			'app_client_id'             => 'YxZYj8NaHnT376p4',
			'app_client_secret'         => 'uV1XLHzKWB3gI3XhMfAAuzjhHHo6WMZZBO7AhtrYNQ9cKjbLQH',

            'created'                   => new Zend_Db_Expr('now()'),
            'updated'                   => new Zend_Db_Expr('now()'),
		);
		
		$gmoTable->create($data);
		exit;
    }			
				
    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization/gmo                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMOアカウント一覧                                          |
    +----------------------------------------------------------------------------*/
    public function gmoAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		$this->view->menu = 'organization';
		
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$selectObj = $gmoTable->select();
		$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
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
    |  action_URL    * /organization/gmo-detail                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMOアカウント編集                                          |
    +----------------------------------------------------------------------------*/
    public function gmoDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/organization/gmo';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$this->view->data = $data = $gmoTable->getById($id);

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /organization/gmo-update                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMOアカウント更新(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function gmoUpdateAction()
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
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				var_dump($errorMessage);exit;
				if (!empty($errorMessage['name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「名義」を入力してください'));
                    return;
                } else if (!empty($errorMessage['app_client_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「クライアントID」を入力してください'));
                    return;
                } else if (!empty($errorMessage['app_client_secret']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「クライアントSecret"」を入力してください'));
                    return;
                }
                
                $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
                return;
	    		
			} else {
				$gmoTable = new Shared_Model_Data_ManagementGmoAccount();

				// 更新
				$data = array(
					'status'                            => $success['status'],                     // ステータス
					
					'name'                              => $success['name'],                 // 組織ID
					'app_client_id'                     => $success['app_client_id'],          // 組織名
					'app_client_secret'                 => $success['app_client_secret'],         // ヘッダーカラー
				);
		        
				$gmoTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
}

