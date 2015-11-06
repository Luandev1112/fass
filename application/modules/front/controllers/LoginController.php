<?php
/**
 * class LoginController
 */
 
class LoginController extends Front_Model_Controller
{
    /**
     * preDispatch
     *
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /login                                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ログイン                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
        $this->_helper->layout->setLayout('default_flex');
		$this->view->bodyLayoutName = 'one_column.phtml';
        
		$request = $this->getRequest();
		$this->view->redirect = $redirect = $request->getParam('redirect', '');
		
        // セッションオブジェクトの取得
		$loginPageSession = new Zend_Session_Namespace('login');

        // ボタン押下時
        if ($request->isPost()) {
						
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
			
			$validationResult = $validate->execute($request->getPost());
			$this->view->data = $data = $validate->getFiltered();
			
			if ($validationResult == false) {
        	    // バリデーションエラー時
                
            } else if (empty($data['csrf_block']) || $data['csrf_block'] != $loginPageSession->csrfBlock) {
                	// csrf 不一致
                	
            } else {
                $userTable  = new Shared_Model_Data_User();
				$loginTable = new Shared_Model_Data_UserLogin();
				$groupTable = new Shared_Model_Data_ManagementGroup();
				
                // ログイン
                $authResult = $userTable->authorizeAdmin($data['mail'], $data['password']);
                //var_dump($authResult);
                //exit;
                
                $loginTable->addLog($authResult['login_result'], $authResult['id'], $data['mail'], $data['password'], $request->getServer('REMOTE_ADDR'));
                
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
                    
                    if (!empty($redirect)) {
	                    $this->_redirect($redirect);
                    } else {
						$this->_redirect('/');
					}
                }   
            }
            
        } else {
        	if (!empty($loginPageSession->message)) {
        		$this->view->message = $loginPageSession->message;
        		$loginPageSession->message = NULL;
        	}
        }
		
		// CSRFコード発行
		$loginPageSession->csrfBlock = md5(uniqid(rand(), true));
    	$this->view->csrf = $loginPageSession->csrfBlock;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /login/logout                                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ログアウト                                                 |
    +----------------------------------------------------------------------------*/
    public function logoutAction()
    {
        Zend_Session::nameSpaceUnset('management_login');
        $this->_redirect('/login');
    }
}

