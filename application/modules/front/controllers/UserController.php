<?php
/**
 * class UserController
 */
 
class UserController extends Front_Model_Controller
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
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/develop-index                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 利用者一覧(開発用)                                         |
    +----------------------------------------------------------------------------*/
    public function developIndexAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		$this->view->menu = 'user';
			
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$userTable  = new Shared_Model_Data_User();
		$groupTable = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
		
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_user_department', 'frs_user_department.id = frs_user.user_department_id', array($userTable->aesdecrypt('department_name', false) . 'AS department_name'));
		$selectObj->order('frs_user.user_department_id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$eachData = $eachItem;
        	$eachData['user_name']  = $eachData['user_name'];
            $items[] = $eachData;
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/develop-detail                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 管理者登録・編集(開発用)                                   |
    +----------------------------------------------------------------------------*/
    public function developDetailAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$userTable = new Shared_Model_Data_User();
		$groupTable = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);	

		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'id'                             => '0',
				'display_id'                     => '',
				'user_department_id'             => '',
				'user_name'                      => '',
				'user_name_en'                   => '',
				'mail'                           => '',
				'password'                       => '',
				'app_password'                   => '',
				
				'is_master'                      => 0,
				'access_member_data'             => 0,
				'allow_editing_accounting_title' => 0,
				'allow_cancel_finish_attach'     => 0,
				'allow_editing_search_tag'       => 0,
				'allow_delete_row_data'          => 0,
				'allow_connection_progress_master'  => 0,
				'allow_connection_progress_tag'     => 0,   
			);
			
		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $id);
	        $data = $selectObj->query()->fetch();
	        if (empty($data)) {
				throw new Zend_Exception('/management/user/detail failed to fetch user data');
			}

        	$this->view->data = $data;
        }

        $departmentTable = new Shared_Model_Data_UserDepartment();
    	$this->view->departmentList = $departmentTable->getList($this->_adminProperty['management_group_id']);
    } 

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/all                                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 全グループ利用者一覧                                       |
    +----------------------------------------------------------------------------*/
    public function allAction()
    {
		$this->view->menu = '';
			
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$userTable  = new Shared_Model_Data_User();
		$groupTable = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
		
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->order('frs_user.id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$eachData = $eachItem;
        	$eachData['user_name']  = $eachData['user_name'];
            $items[] = $eachData;
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user                                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 利用者一覧                                                 |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		$this->view->menu = 'user';
			
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$userTable  = new Shared_Model_Data_User();
		$groupTable = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
		
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_user_department', 'frs_user_department.id = frs_user.user_department_id', array($userTable->aesdecrypt('department_name', false) . 'AS department_name'));
		$selectObj->where('frs_user.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_user.status != ?', Shared_Model_Code::USER_STATUS_INACTIVE);
		$selectObj->order('frs_user.user_department_id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$eachData = $eachItem;
        	$eachData['user_name']  = $eachData['user_name'];
            $items[] = $eachData;
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/inactive                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 利用者一覧(退職済み)                                       |
    +----------------------------------------------------------------------------*/
    public function inactiveAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		$this->view->menu = 'user';
			
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$userTable  = new Shared_Model_Data_User();
		$groupTable = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
		
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_user_department', 'frs_user_department.id = frs_user.user_department_id', array($userTable->aesdecrypt('department_name', false) . 'AS department_name'));
		$selectObj->where('frs_user.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_user.status = ?', Shared_Model_Code::USER_STATUS_INACTIVE);
		$selectObj->order('frs_user.user_department_id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$eachData = $eachItem;
        	$eachData['user_name']  = $eachData['user_name'];
            $items[] = $eachData;
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /send-mail                                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アカウント切替(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function sendMailAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
	    
		$request = $this->getRequest();
		$id      = $request->getParam('target_id');
		
        // ボタン押下時
        if ($request->isPost()) {
            $userTable  = new Shared_Model_Data_User();
            
	    	$selectObj = $userTable->select();
	    	$selectObj->where('frs_user.id = ?', $id);
	        $data = $selectObj->query()->fetch();

			$mailer = new Shared_Model_Mail_LoginInfo();
			$mailer->sendMail($data);
			
			$mailer2 = new Shared_Model_Mail_LoginPassword();
			$mailer2->sendMail($data);
			
	        $this->sendJson(array('result' => 'OK'));
			return;
        }
        
        $this->sendJson(array('result' => 'NG'));
		return; 
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/list-switch                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アカウント切替                                             |
    +----------------------------------------------------------------------------*/
    public function listSwitchAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->backUrl = $request->getServer('HTTP_REFERER');
		
		$userTable        = new Shared_Model_Data_User();
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_management_group', 'frs_user.management_group_id = frs_management_group.id', array(
			$userTable->aesdecrypt('organization_name', false) . 'AS organization_name',
		));
		
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array(
			$userTable->aesdecrypt('department_name', false) . 'AS department_name',
			$userTable->aesdecrypt('department_name_en', false) . 'AS department_name_en',
		));
		$selectObj->where('frs_user.parent_user_id != 0');
		$selectObj->where('frs_user.id = ' . $this->_adminProperty['parent_user_id'] . ' OR frs_user.parent_user_id = ' . $this->_adminProperty['parent_user_id']);
		$selectObj->order('frs_user.id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$eachData = $eachItem;
        	$eachData['user_name']  = $eachData['user_name'];
            $items[] = $eachData;
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /switch                                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アカウント切替(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function switchAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
	    
		$request = $this->getRequest();
		$id      = $request->getParam('target_id');
		
        // ボタン押下時
        if ($request->isPost()) {
            $userTable  = new Shared_Model_Data_User();
			$loginTable = new Shared_Model_Data_UserLogin();
			$groupTable = new Shared_Model_Data_ManagementGroup();
			
			
			$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
			$selectObj->where('frs_user.id = ' . $this->_adminProperty['parent_user_id'] . ' OR frs_user.parent_user_id = ' . $this->_adminProperty['parent_user_id']);
			$selectObj->order('frs_user.display_id ASC');
			$accountList = $selectObj->query()->fetchAll();
			
			$authResult = NULL;

			foreach ($accountList as $eachItem) {
				if ($eachItem['id'] === $id) {
					$authResult = $eachItem;
					$authResult['login_result'] = true;
				}
			}
			
			if (empty($authResult)) {
		        $this->sendJson(array('result' => 'NG'));
				return;
			}
			    
            // ログイン
            $loginTable->addLog($authResult['login_result'], $authResult['id'], '', '', $request->getServer('REMOTE_ADDR'));
            
            // ログイン成功時
            if ($authResult['login_result'] === true) { 
            	// ログインログを保存
                
                $userData = $userTable->getById($authResult['id']);
				$userData['group_data'] = $groupTable->getById($userData['management_group_id']);
				
                // 最終ログインを更新
                $userTable->updateByUserId($authResult['id'], array('last_logined' => new Zend_Db_Expr('now()')));

                // ログイン情報保存 ---------------------------------------------------
                $adminLoginSession = new Zend_Session_Namespace('management_login');
        
                // セッションにログイン情報を保存
                $adminLoginSession->isSuccess = true;
                $adminLoginSession->adminProperty = $userData;
        
                // 有効時間は180分
                $adminLoginSession->setExpirationSeconds(60 * 180);
                
                // セッション情報を編集不可に
                $adminLoginSession->lock();
                // --------------------------------------------------------------------
                
                $this->sendJson(array('result' => 'OK'));
				return;
            }
        }
        
        $this->sendJson(array('result' => 'NG'));
		return; 
    }
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/list-select                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 利用者一覧(選択用)                                         |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$userTable        = new Shared_Model_Data_User();
		$departmentTable  = new Shared_Model_Data_UserDepartment();
		$groupTable       = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);
		$selectObj->joinLeft('frs_user_department', 'frs_user_department.id = frs_user.user_department_id', array(
			$userTable->aesdecrypt('department_name', false) . 'AS department_name',
			$userTable->aesdecrypt('department_name_en', false) . 'AS department_name_en')
		);
		$selectObj->where('frs_user.management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->where('frs_user_department.is_accountants_office = 0');
		$selectObj->where('frs_user.status = ?', Shared_Model_Code::USER_STATUS_ACTIVE);
		$selectObj->order('frs_user.display_id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$eachData = $eachItem;
        	$eachData['user_name']  = $eachData['user_name'];
            $items[] = $eachData;
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/detail                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 管理者登録・編集                                           |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$userTable = new Shared_Model_Data_User();
		$groupTable = new Shared_Model_Data_ManagementGroup();
		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);	

		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'id'                             => '0',
				'display_id'                     => '',
				'user_department_id'             => '',
				'user_name'                      => '',
				'user_name_en'                   => '',
				'mail'                           => '',
				'password'                       => '',
				'app_passcode'                   => '',
				
				'limit_shipping'                    => 0,
				'limit_production'                  => 0,
				
				'is_master'                         => 0,
				'access_member_data'                => 0,
				'allow_editing_accounting_title'    => 0,
				'allow_cancel_finish_attach'        => 0,
				'allow_editing_search_tag'          => 0,
				'allow_delete_row_data'             => 0,
				'allow_connection_progress_master'  => 0,
				'allow_connection_progress_tag'     => 0,   
			);
			
		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $id);
	        $data = $selectObj->query()->fetch();
	        if (empty($data)) {
				throw new Zend_Exception('/management/user/detail failed to fetch user data');
			}

        	$this->view->data = $data;
        }

        $departmentTable = new Shared_Model_Data_UserDepartment();
    	$this->view->departmentList = $departmentTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/update                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 管理者登録・編集(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$userTable = new Shared_Model_Data_User();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/management/user/update failed to load config');
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
                    
                } else if (isset($errorMessage['user_name'])) {
                    $message = '「氏名」を入力してください';
                } else if (isset($errorMessage['user_name'])) {
                    $message = '「氏名(英語・ローマ字)」を入力してください';
                         
                } else if (isset($errorMessage['mail'])) {
                    $message = '「メールアドレス」を入力してください';
                    
                } else if (isset($errorMessage['password'])) {
                    $message = '「パスワード」を入力してください';
                    
                } else if (isset($errorMessage['user_department_id'])) {
                    $message = '「所属」を選択してください';
                    
                } else {
	                $message = '予期せぬエラーが発生しました';
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				if ($userTable->isUsedDisplayId($this->_adminProperty['management_group_id'], $success['display_id'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「利用者ID」は既に使われています'));
		    		return;
				}
				
				if (!empty($success['app_passcode'])) {
					// パスコード数字5桁か
					if (!preg_match("/^\d{5}$/", $success['app_passcode'])) {
				    	$this->sendJson(array('result' => 'NG', 'message' => '「在庫管理アプリパスコード」は5桁の数字で入力してください'));
						return;
		    		}
				}
				
				if (empty($id)) {
					// 新規登録
					
					// パスコード重複禁止
					//if ($userTable->passcodeIsDuplicated($this->_adminProperty['management_group_id'], $success['app_passcode'], NULL)) {
					//	$this->sendJson(array('result' => 'NG', 'message' => 'その「在庫管理アプリパスコード」は既に使われています'));
					//	return;
					//}
					
					$data = array(
						'management_group_id'              => $this->_adminProperty['management_group_id'],
						'user_department_id'               => $success['user_department_id'],
				        'user_type'                        => Shared_Model_Code::USER_TYPE_ADMIN,
				        
				        'limit_shipping'                   => 0,      // ページ制限：出荷担当
				        'limit_production'                 => 0,      // ページ制限：製造担当
				        
						'is_master'                        => 0,
						'allow_editing_accounting_title'   => 0,
						'allow_editing_search_tag'         => 0,
						'allow_delete_row_data'            => 0,
						'allow_connection_progress_master' => 0,
						'allow_connection_progress_tag'    => 0,
						'allow_cancel_finish_attach'       => 0,
						
						'display_id'                       => $success['display_id'],
						
				        'mail'                             => $success['mail'],
				        'mail_hash'                        => $userTable->mailHash($success['mail']),
				        'password'                         => $success['password'],
				        
				        'app_passcode'                     => $success['app_passcode'],
				        
				        'status'                           => Shared_Model_Code::USER_STATUS_ACTIVE,		        

				        'user_name'                        => $success['user_name'],
				        'user_name_en'                     => $success['user_name_en'],
						
				        'last_logined'                     => new Zend_Db_Expr('now()'),
		                'created'                          => new Zend_Db_Expr('now()'),
		                'updated'                          => new Zend_Db_Expr('now()'),
					);
					
					// 仮
					$data['approver_c1_user_id'] = 3;
					
					if (!empty($success['limit_shipping'])) {
						$data['limit_shipping'] = 1;
					}
					
					if (!empty($success['limit_production'])) {
						$data['limit_production'] = 1;
					}
					
					
					if (!empty($success['is_master'])) {
						$data['is_master'] = 1;
					}

					if (!empty($success['allow_editing_accounting_title'])) {
						$data['allow_editing_accounting_title'] = 1;
					}

					if (!empty($success['allow_editing_search_tag'])) {
						$data['allow_editing_search_tag'] = 1;
					}

					if (!empty($success['allow_delete_row_data'])) {
						$data['allow_delete_row_data'] = 1;
					}

					if (!empty($success['allow_connection_progress_master'])) {
						$data['allow_connection_progress_master'] = 1;
					}
					
					if (!empty($success['allow_connection_progress_tag'])) {
						$data['allow_connection_progress_tag'] = 1;
					}
					
					if (!empty($success['allow_cancel_finish_attach'])) {
						$data['allow_cancel_finish_attach'] = 1;
					}
					
					$userTable->create($data);
					$id = $userTable->getLastInsertedId('id');
					
				} else {
					// 編集
					
					// パスコード重複禁止
					//if ($userTable->passcodeIsDuplicated($success['app_passcode'], $id)) {
					//	$this->sendJson(array('result' => 'NG', 'message' => 'その「在庫管理アプリパスコード」は既に使われています'));
					//	return;
					//}
					
					$data = array(
						'limit_shipping'                   => 0,      // ページ制限：出荷担当
				        'limit_production'                 => 0,      // ページ制限：製造担当
				        
						'is_master'                        => 0,
						'user_department_id'               => $success['user_department_id'],
						'allow_editing_accounting_title'   => 0,
						'allow_editing_search_tag'         => 0,
						'allow_delete_row_data'            => 0,
						'allow_connection_progress_master' => 0,
						'allow_connection_progress_tag'    => 0,
						'allow_cancel_finish_attach'       => 0,
						
						'display_id'                       => $success['display_id'],
						
				        'mail'                             => $success['mail'],
				        'mail_hash'                        => $userTable->mailHash($success['mail']),
				        'password'                         => $success['password'],
				        
				        'app_passcode'                     => $success['app_passcode'],
				        
				        'user_name'                        => $success['user_name'],
				        'user_name_en'                     => $success['user_name_en'],
				        
				        'status'                           => $success['status'],
					);

					if (!empty($success['limit_shipping'])) {
						$data['limit_shipping'] = 1;
					}
					
					if (!empty($success['limit_production'])) {
						$data['limit_production'] = 1;
					}

					if (!empty($success['is_master'])) {
						$data['is_master'] = 1;
					}

					if (!empty($success['allow_editing_accounting_title'])) {
						$data['allow_editing_accounting_title'] = 1;
					}

					if (!empty($success['allow_editing_search_tag'])) {
						$data['allow_editing_search_tag'] = 1;
					}

					if (!empty($success['allow_delete_row_data'])) {
						$data['allow_delete_row_data'] = 1;
					}
					
					if (!empty($success['allow_connection_progress_master'])) {
						$data['allow_connection_progress_master'] = 1;
					}
					
					if (!empty($success['allow_connection_progress_tag'])) {
						$data['allow_connection_progress_tag'] = 1;
					}

					if (!empty($success['allow_cancel_finish_attach'])) {
						$data['allow_cancel_finish_attach'] = 1;
					}
					
					$userTable->updateByUserId($id, $data);

				}

				if (!empty($success['temp_file_name'])) {
					// 正式保存
            		Shared_Model_Resource_Stamp::makeResource($id, Shared_Model_Resource_Temporary::getBinary($success['temp_file_name']));
            		
	            	// tempファイルを削除
					Shared_Model_Resource_Temporary::removeResource($success['temp_file_name']);
				}
				
				// セッションオブジェクトの取得
				$adminLoginSession = new Zend_Session_Namespace('management_login');
				
				// 自身の場合
        		if ($adminLoginSession->adminProperty['id'] == $id) {
        			// セッションを持っている場合
        			$adminLoginSession->adminProperty = $userTable->getByUserId($id);
        			$groupTable = new Shared_Model_Data_ManagementGroup();
        			$adminLoginSession->adminProperty['group_data'] = $groupTable->getById($adminLoginSession->adminProperty['management_group_id']);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /**
     * uploadImageAction
     *
     * @param void
     * @return void
     */
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/upload-image                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 印鑑画像アップロード                                       |
    +----------------------------------------------------------------------------*/
    public function uploadImageAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
        
		if (empty($_FILES['image']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		$key = uniqid();
		$fileName = $key . '.' . 'png';
		
		// PNGに変換して保存
		
		$filePath = Shared_Model_Utility_Image::makePngImageWithWidth($_FILES['image']['tmp_name'], 200, $fileName);
        
        if ($filePath === false) {
        	throw new Zend_Exception('/user/upload-image save failed');
        }
        
        $this->sendJson(array('result' => true, 'key' => $key, 'temp_file_name' => $fileName));
        return;

	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/department-list                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 部署一覧                                                   |
    +----------------------------------------------------------------------------*/
    public function departmentListAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
		$this->view->menu = 'department';
			
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$departmentTable  = new Shared_Model_Data_UserDepartment();
		$selectObj = $departmentTable->select();
		$selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
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
        
		$groupTable = new Shared_Model_Data_ManagementGroup();
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/department-add                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 部署 - 新規登録                                            |
    +----------------------------------------------------------------------------*/
    public function departmentAddAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$departmentTable = new Shared_Model_Data_UserDepartment();
 		$groupTable = new Shared_Model_Data_ManagementGroup();
 		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
		// 新規登録
		$this->view->saveButtonName = '登録';
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/department-add-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 部署 新規登録(Ajax)                                        |
    +----------------------------------------------------------------------------*/
    public function departmentAddPostAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$departmentTable = new Shared_Model_Data_UserDepartment();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/management/user/department-add-oost failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if ($errorMessage['department_name']) {
					$this->sendJson(array('result' => 'NG', 'message' => '「部署・事業部門名」を入力してください'));
	    			return;
                } else if ($errorMessage['department_name_en']) {
					$this->sendJson(array('result' => 'NG', 'message' => '「部署・事業部門名(英語)」を入力してください'));
	    			return;          
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				/*
				if ($userTable->isUsedDisplayId($this->_adminProperty['management_group_id'], $success['display_id'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「利用者ID」は既に使われています'));
		    		return;
				}
				*/
				
				$cotentOrder = $departmentTable->getNextContentOrder($this->_adminProperty['management_group_id']);

				// 新規登録
				$data = array(
			        'management_group_id'  => $this->_adminProperty['management_group_id'],   // 管理グループID(確認必要)
			        'status'               => Shared_Model_Code::CONTENT_STATUS_ACTIVE,

			        'department_name'         => $success['department_name'],       // 部署・事業部門名
			        'department_name_en'      => $success['department_name_en'],    // 部署・事業部門名 英語
			        'is_accountants_office'   => 0,                                 // 会計事務所
			        'mailing_list_address'    => $success['mailing_list_address'],  // 通知用メールアドレス
					
					'mail_cost'               => 0,         // 通知 原価計算
					'mail_estimate'           => 0,         // 通知 提出見積
					
					'mail_supply'             => 0,         // 通知 調達管理
					
					'mail_order'              => 0,         // 通知 受注管理
					'mail_order_form'         => 0,         // 通知 発注管理
					'mail_payable'            => 0,         // 通知 支払申請
					'mail_payable_monthly'    => 0,         // 通知 毎月支払申請
					
					'mail_invoice'            => 0,      // 通知 請求書発行
					'mail_receivable_monthly' => 0,      // 通知 毎月入金管理
		
					'content_order'        => $cotentOrder,   // 並び順
					
	                'created'              => new Zend_Db_Expr('now()'),
	                'updated'              => new Zend_Db_Expr('now()'),
				);

				if (!empty($success['is_accountants_office'])) {
					$data['is_accountants_office'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_cost'])) {
					$data['mail_cost'] = 1;
				}
				
				if (!empty($success['mail_estimate'])) {
					$data['mail_estimate'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_supply'])) {
					$data['mail_supply'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_order'])) {
					$data['mail_order'] = 1;
				}
				
				if (!empty($success['mail_order_form'])) {
					$data['mail_order_form'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_payable'])) {
					$data['mail_payable'] = 1;
				}
				
				if (!empty($success['mail_payable_monthly'])) {
					$data['mail_payable_monthly'] = 1;
				}	

				// ----------------------------------------------------
				if (!empty($success['mail_invoice'])) {
					$data['mail_invoice'] = 1;
				}
				
				if (!empty($success['mail_receivable_monthly'])) {
					$data['mail_receivable_monthly'] = 1;
				}
				
				// ----------------------------------------------------
				
				$departmentTable->create($data);

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/department-detail                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 部署登録・編集                                             |
    +----------------------------------------------------------------------------*/
    public function departmentDetailAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/user/department-list';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$departmentTable = new Shared_Model_Data_UserDepartment();
 		$groupTable      = new Shared_Model_Data_ManagementGroup();
 		
		$this->view->groupData = $groupTable->getById($this->_adminProperty['management_group_id']);
		
    	$this->view->data = $departmentTable->getById($this->_adminProperty['management_group_id'], $id);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/department-update                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 部署 登録・編集(Ajax)                                      |
    +----------------------------------------------------------------------------*/
    public function departmentUpdateAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');

		$departmentTable = new Shared_Model_Data_UserDepartment();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/management/user/department-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if ($errorMessage['department_name']) {
					$this->sendJson(array('result' => 'NG', 'message' => '「部署・事業部門名」を入力してください'));
	    			return;
                } else if ($errorMessage['department_name_en']) {
					$this->sendJson(array('result' => 'NG', 'message' => '「部署・事業部門名(英語)」を入力してください'));
	    			return;          
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				/*
				if ($userTable->isUsedDisplayId($this->_adminProperty['management_group_id'], $success['display_id'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「利用者ID」は既に使われています'));
		    		return;
				}
				*/
				
				$cotentOrder = $departmentTable->getNextContentOrder($this->_adminProperty['management_group_id']);
				
				// 編集
				$data = array(
					'status'                  => $success['status'],                // ステータス

			        'department_name'         => $success['department_name'],       // 部署・事業部門名
			        'department_name_en'      => $success['department_name_en'],    // 部署・事業部門名 英語
			        'is_accountants_office'   => 0,                                 // 会計事務所
			        'mailing_list_address'    => $success['mailing_list_address'],  // 通知用メールアドレス
			        
					'mail_cost'               => 0,      // 通知 原価計算
					'mail_estimate'           => 0,      // 通知 提出見積
					
					'mail_supply'             => 0,      // 通知 調達管理
					
					'mail_order'              => 0,      // 通知 受注管理
					'mail_order_form'         => 0,      // 通知 発注管理
					'mail_payable'            => 0,      // 通知 支払申請
					'mail_payable_monthly'    => 0,      // 通知 毎月支払申請
					
					'mail_invoice'            => 0,      // 通知 請求書発行
					'mail_receivable_monthly' => 0,      // 通知 毎月入金管理
				);

				if (!empty($success['is_accountants_office'])) {
					$data['is_accountants_office'] = 1;
				}

				// ----------------------------------------------------
				if (!empty($success['mail_cost'])) {
					$data['mail_cost'] = 1;
				}
				
				if (!empty($success['mail_estimate'])) {
					$data['mail_estimate'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_supply'])) {
					$data['mail_supply'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_order'])) {
					$data['mail_order'] = 1;
				}
				
				if (!empty($success['mail_order_form'])) {
					$data['mail_order_form'] = 1;
				}
				
				// ----------------------------------------------------
				if (!empty($success['mail_payable'])) {
					$data['mail_payable'] = 1;
				}
				
				if (!empty($success['mail_payable_monthly'])) {
					$data['mail_payable_monthly'] = 1;
				}	

				// ----------------------------------------------------
				if (!empty($success['mail_invoice'])) {
					$data['mail_invoice'] = 1;
				}
				
				if (!empty($success['mail_receivable_monthly'])) {
					$data['mail_receivable_monthly'] = 1;
				}
				
				// ----------------------------------------------------

				$departmentTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /user/admin-log                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 管理画面ログ                                               |
    +----------------------------------------------------------------------------*/
    public function adminLogAction()
    {
    	if (empty($this->_adminProperty['is_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$loginTable = new Shared_Model_Data_Login();

		$selectObj = $loginTable->select();
		$selectObj->joinLeft('fbc_user', 'fbc_login.user_id = fbc_user.id', array($loginTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->order('fbc_login.id DESC');
		
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
    

        
}

