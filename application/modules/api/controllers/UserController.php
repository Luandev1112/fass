<?php
/**
 * class Api_UserController
 */

class Api_UserController extends Api_Model_Controller
{
    /**
     * listAction
     * ユーザーリスト
     * /api/user/list
     */
    public function listAction()
    {
    	$request = $this->getRequest();
    	$groupId = $request->getParam('group_id');
    	
    	if (empty($groupId)) {
	    	throw new Zend_Exception('/api/user/list - group_id required');
    	}
    	
		$userTable        = new Shared_Model_Data_User();

		$selectObj = $userTable->getListByUserType(Shared_Model_Code::USER_TYPE_ADMIN, true);

		$selectObj->joinLeft('frs_user_department', 'frs_user_department.id = frs_user.user_department_id', array(
			$userTable->aesdecrypt('department_name', false) . 'AS department_name',
			$userTable->aesdecrypt('department_name_en', false) . 'AS department_name_en')
		);

        $dbAdapter = $userTable->getAdapter();
        
        $selectObj->where('app_passcode IS NOT NULL');
        $whereString1 = $userTable->aesdecrypt('app_passcode', false) . " != ''";
        $selectObj->where($whereString1);
        
		$selectObj->where('frs_user.management_group_id = ?', $groupId);
		$selectObj->where('frs_user_department.is_accountants_office = 0');
		$selectObj->where('frs_user.status = ?', Shared_Model_Code::USER_STATUS_ACTIVE);
		$selectObj->order('frs_user.display_id ASC');


		$userList = $selectObj->query()->fetchAll();
		
		$items = array();
		
		foreach ($userList as $each) {
			$items[] = array(
				'id'         => $each['id'],
				'user_name'  => $each['user_name'],
			);
			
		}
		
		
        $params = array (
            'result'      => true,
            'user_list'   => $items,
        );
               
		return $this->sendJson($params);  
    }
 
 
    /**
     * loginPasscodeAction
     * アプリパスコードログイン
     */
    public function loginPasscodeAction()
    {
        $request    = $this->getRequest();

        $userTable    = new Shared_Model_Data_User();
  
        $config = $this->getActionConfig();
        $validate = new Nutex_Parameters_Validate($config);
		$validate->execute($request->getPost());
		$data = $validate->getFiltered();
	

        $userTable  = new Shared_Model_Data_User();
		$loginTable = new Shared_Model_Data_UserLogin();
		
        // ログイン
        $authResult = $userTable->authorizePassCode($data['id'], $data['passcode']);
        
        //$loginTable->addLog($authResult['login_result'], $authResult['id'], $authResult['mail'], $authResult['password'], $request->getServer('REMOTE_ADDR'));
        
        // ログインエラー
        if ($authResult['login_result'] === false) { 
        	$this->sendJson(array('result' => false));
        	return;
        }
       	
		$userData = $userTable->getById($authResult['id']);
       	
        // 認証成功
        $this->_loginSetup($userData);
        
        // セッションID発行
        Nutex_Session::regenerateId();

        $params = array (
            'result'      => true,
            'sessionId'   => Nutex_Session::getId(),
        );
            
		return $this->sendJson($params);
    }
    
       
    /**
     * loginAction
     * アプリログイン
     */
    public function loginAction()
    {
        $request    = $this->getRequest();

        $userTable    = new Shared_Model_Data_User();
  
        $config = $this->getActionConfig();
        $validate = new Nutex_Parameters_Validate($config);
		$validate->execute($request->getPost());
		$data = $validate->getFiltered();
	
        try {
            $userTable  = new Shared_Model_Data_User();
			$loginTable = new Shared_Model_Data_UserLogin();
			
            // ログイン
            $authResult = $userTable->authorizeAdmin($data['mail'], $data['password']);
            
            $loginTable->addLog($authResult['login_result'], $authResult['id'], $data['mail'], $data['password'], $request->getServer('REMOTE_ADDR'));
            
            // ログインエラー
            if ($authResult['login_result'] === false) { 
            	$this->sendJson(array('result' => false));
            	return;
            }
           	
           	$userData = $userTable->getById($authResult['id']);
           	
            // 認証成功
            $this->_loginSetup($userData);
            
            // セッションID発行
            Nutex_Session::regenerateId();

        } catch (Exception $e) {
            throw new Zend_Exception('login failed.' . $e);
        }

        $params = array (
            'result'      => true,
            'sessionId'   => Nutex_Session::getId(),
        );
            
		return $this->sendJson($params);
    }

    
    /**
     * ログアウト
     */
    public function logoutAction()
    {
    	// UUID-user_idの設定をデフォルトにする
    	$request       = $this->getRequest();
        $appId         = $request->getParam('app_id');
    	$uuid          = $request->getParam('uuid');
		$deviceToken   = $request->getParam('device_token');
		
    	$userTable = new Shared_Model_Data_User();
    	$uuidTable = new Shared_Model_Data_UUIDUser();
    	
    	$uuidData       = $uuidTable->getByUUID($appId, $uuid);
		$defaultAccount = $userTable->getByDefaultUUIDWithAppId($appId, $uuid);
		
		if (empty($defaultAccount)) {
			// UUIDアカウントがなければ登録
            $defaultAccount = $userTable->registWithUUID($appId, $uuid);
		}
		
    	// UUIDを保存
	    $uuidTable->updateUserId($appId, $uuid, $defaultAccount['id'], NULL, NULL, NULL);
	    
	    // マスターログインをOFF(アカウント切り替え機能)
	    if ((int)$uuidData['is_master'] == 1) {
    		$uuidTable->updateMasterLogin($appId, $uuid, false, 0);
    	}
    	
    	if (!empty($deviceToken)) {
    		$snsManager = new Shared_Model_Aws_SNSManager();
			$snsManager->registToken($appId, $defaultAccount['id'], $deviceToken);  
    	}
    	
    	$this->sendJson(array('result' => true));
    }
    

    


    


}