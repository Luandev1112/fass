<?php
/**
 * class OauthController
 */
 
class OauthController extends Front_Model_Controller
{
    const PER_PAGE = 100;
    const AUTH_HOST     = GMO_AOZORA_API_DOMAIN . '/ganb/api/auth/v1';
    const HOST          = GMO_AOZORA_API_DOMAIN . '/ganb/api/corporation/v1';
    
    const REDIRECT_URI  = HTTPS_PROTOCOL . APPLICATION_DOMAIN . "/oauth/callback";
    const AUTH_METHOD   = "POST"; // Your Auth method BASIC or POST
    
    
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
		$this->view->mainCategoryName = 'GMO連動';
		$this->view->menuCategory     = 'gmo';
		$this->view->menu             = 'GMO';
	
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth                                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO認可                                                    |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
		$id = $request->getParam('id');
        
        $adminLoginSession = new Zend_Session_Namespace('management_login');
        $sessionId = Zend_Session::getId();
        
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$adminLoginSession->gmoLoginAccount = $gmoTable->getById($id);
        //var_dump($adminLoginSession->gmoLoginAccount);exit;
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

        // Authorization
        $ganb = new Ganb\Auth($adminLoginSession->gmoLoginAccount['app_client_id'], $adminLoginSession->gmoLoginAccount['app_client_secret'], self::AUTH_METHOD);

        //$redirectUrl = $ganb->oauthAuthorization($sessionId, "corp:account corp:transfer", $callbackUrl);
        $this->view->redirectUrl = $redirectUrl = $ganb->openIDAuthorization($sessionId, 'openid offline_access private:account private:transfer private:bulk-transfer', self::REDIRECT_URI);
        
        //var_dump($this->view->redirectUrl);exit;

        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/callback                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMOコールバック                                            |
    +----------------------------------------------------------------------------*/
    public function callbackAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $request = $this->getRequest();
		$params = $request->getParams();

        $adminLoginSession = new Zend_Session_Namespace('management_login');

        $ganb = new Ganb\Auth($adminLoginSession->gmoLoginAccount['app_client_id'], $adminLoginSession->gmoLoginAccount['app_client_secret'], self::AUTH_METHOD);
        
        try {
            //$token = $ganb->getOAuthToken(self::REDIRECT_URI, $params['code']);
            $token = $ganb->getOpenIDToken(self::REDIRECT_URI, $params['code']);

            $adminLoginSession->accessToken = $token->access_token;
            
            //$userTable = new Shared_Model_Data_User();
            //$userTable->updateByUserId($this->_adminProperty['id'], array('gmo_reflesh_token' => $token->refresh_token));
            //$adminLoginSession->adminProperty['gmo_reflesh_token'] = $token->refresh_token;

		    $zDate1 = new Zend_Date(NULL, NULL, 'ja_JP');
            $zDate1->add('2592000', Zend_Date::SECOND);
            $zDate1->sub('1', Zend_Date::DAY);
            $accessTokenExpireIn = $zDate1->get('yyyy-MM-dd HH:mm:ss');
            
		    $zDate2 = new Zend_Date(NULL, NULL, 'ja_JP');
            $zDate2->add('7776000', Zend_Date::SECOND);
            $zDate2->sub('1', Zend_Date::DAY);
            $refleshTokenExpireIn = $zDate2->get('yyyy-MM-dd HH:mm:ss');
            
            $gmoTable = new Shared_Model_Data_ManagementGmoAccount();
            
            // トークンを保存
			$data = array(
				'gmo_access_token'                        => $token->access_token,
				'gmo_access_token_expired_datetime'       => $accessTokenExpireIn,
				'gmo_reflesh_token'                       => $token->refresh_token,
				'gmo_reflesh_token_expired_datetime'      => $refleshTokenExpireIn,
			);
	        
			$gmoTable->updateById($adminLoginSession->gmoLoginAccount['id'], $data);
            
            $this->_redirect('/');
            
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/reflesh                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * トークン再発行                                             |
    +----------------------------------------------------------------------------*/
    public function refleshAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 

        $request = $this->getRequest();
		$id = $request->getParam('id');
		
        Shared_Model_Utility_GmoBank::reflesh($id);

	    $this->sendJson(array('result' => 'OK'));
        return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/account                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 口座一覧照会                                               |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#accountsUsingGET
    public function accountAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/';

        $request = $this->getRequest();
		$id = $request->getParam('id');
		
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $token = Shared_Model_Utility_GmoBank::getToken($id);
        
        //var_dump($token);exit;
        
        if (!empty($token)) {
            $apiInstance = new Ganb\Corporate\Client\Api\AccountApi(
                new GuzzleHttp\Client()
            );
    
            try {
                $this->view->accountList = $apiInstance->accountsUsingGET($token);
                
            } catch (Exception $e) {
                throw new Zend_Exception('Exception when calling AccountApi->accountsUsingGET: ' . $e->getMessage());
            }
        } else {
            $this->view->tokenError = true;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transaction-ajax                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入出金明細照会(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transactionAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $request = $this->getRequest();
		$accountId = $request->getParam('account_id');
		$accountNo = $request->getParam('account_no');

		$bankHistoryTable     = new Shared_Model_Data_AccountBankHistory();
		$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
		
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData = $bankTable->getGMOBank($accountNo);

		if (empty($bankData)) {
    	    $this->sendJson(array('result' => 'NG', 'message' => '口座未登録'));
        	return;
        	
		} else {
	        if (empty($bankData['gmo_bank_account_id'])) {
	            // アカウントIDを保存
	            $bankTable->updateById($bankData['id'], array('gmo_bank_account_id' => $accountId));
	            $bankData['gmo_bank_account_id'] = $accountId;
	        }
		    
		    $itemCount = 0;
		    $key = uniqid();

            $currencyTable = new Shared_Model_Data_Currency();
            $currencyData = $currencyTable->getBySymbol($this->_adminProperty['management_group_id'], '¥');
		
		    $latestHistoryItem = NULL;
		    
		    $latestHistory = $bankHistoryTable->latestHistoryOfBank($bankData['id']);
		    
		    if (!empty($latestHistory)) {
		        $latestHistoryItem = $bankHistoryItemTable->lastRowOfHistory($latestHistory['id']);
		    }
		        
		    $bankHistoryItemTable->getAdapter()->beginTransaction();
		    
		    try {
                // 履歴取込
        		$data = array(
        	        'management_group_id' => $this->_adminProperty['management_group_id'], // 管理グループID
        	        'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
        	        'import_key'          => $key,                                         // ファイル名
        	        'bank_id'             => $bankData['id'],                              // 銀行ID
        	        'created_user_id'     => $this->_adminProperty['id'],                  // 取込実施者
        
        			'term_form'           => '',                                           // 期間開始日
        			'term_to'             => '',                                           // 期間終了日
        
                    'created'             => new Zend_Db_Expr('now()'),
                    'updated'             => new Zend_Db_Expr('now()'),
        		);
        		
        		$bankHistoryTable->create($data);
                $importId = $bankHistoryTable->getLastInsertedId('id');

                while ($result = $this->_loadLatestTransaction($bankData, $latestHistoryItem)) {
                    foreach ($result['transactions'] as $each) {
                        
                        $isExist = $bankHistoryItemTable->itemKeyExist($each['item_key']);
                        //var_dump($each);
                        //var_dump($isExist);
                        //exit;
                        
                        if ($isExist === false) {
                    		$rowData = array(            
                    	        'management_group_id'   => $this->_adminProperty['management_group_id'],       // 管理グループID
                    	        'bank_history_id'       => $importId,                                          // 銀行取込CSVID
                    	        'status'                => Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_NONE,   // ステータス
                    	        'row_count'             => $itemCount + 1,             // 行番号
                    	        'target_date'           => $each['transaction_date'],  // 対象日
                    	        'name'                  => $each['remarks'],           // 項目名
                    	        'currency_id'           => $currencyData['id'],        // 通貨ID
                    	        'paid_amount'           => 0,                          // 出金額
                    	        'received_amount'       => 0,                          // 預かり額(入金額)
                    	        'balance_amount'        => $each['balance'],           // 残高
                    	        
                    	        'gmo_item_key'          => $each['item_key'],          // GMO明細キー
                    	        
                    	        'payable_id'            => 0,   // 買掛ID    
                    			'receivable_id'         => 0,   // 売掛ID
                    	
                                'created'               => new Zend_Db_Expr('now()'),
                                'updated'               => new Zend_Db_Expr('now()'),
                    		);
                    		
                    		if ($each['transaction_type'] === '1') {
                    		    $rowData['received_amount'] = $each['amount'];
                    		
                    		} else if ($each['transaction_type'] === '2') {
                    		    $rowData['paid_amount']     = $each['amount'];
                    		}
                    		$bankHistoryItemTable->create($rowData);
                    		
                    		$itemCount++;
                        }
                    }

            		if ($result['has_next'] === false) {
                        break;
                    }
                }
                
                if ($itemCount <= 0) {
                    // データが0件
                    $bankHistoryItemTable->getAdapter()->rollBack();
                } else {
        			$fromDate = $bankHistoryItemTable->getFirstDateByHistoryId($importId);
        			$toDate   = $bankHistoryItemTable->getLastDateByHistoryId($importId);
			
                    $bankHistoryTable->updateById($importId, array(
                        'term_form' => $fromDate,                           // 期間開始日
		                'term_to'   => $toDate,                             // 期間終了日
                    ));
                    
                    // commit
                    $bankHistoryItemTable->getAdapter()->commit();
                }
                
            } catch (Exception $e) {
                $bankHistoryItemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/oauth/transaction-ajax transaction failed: ' . $e);
            }
            
    	    $this->sendJson(array('result' => 'OK', 'count' => $itemCount));
        	return;
		}
    }
    
    private function _loadLatestTransaction($bankData, $latestHistoryItem) 
    {
        $apiInstance = new Ganb\Corporate\Client\Api\AccountApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        
        $token = Shared_Model_Utility_GmoBank::getToken($bankData['gmo_account_id']);
        
        if (!empty($latestHistoryItem) || !empty($latestHistoryItem['gmo_item_key'])) {
            $date_from     = $latestHistoryItem['target_date']; 
            $date_to       = ""; 
            $next_item_key = "";
        } else {
            $date_from     = "2021-07-08"; // API取込運用開始日
            $date_to       = ""; 
            $next_item_key = ""; 
        
        }
        
        //var_dump($date_from);
        //var_dump($date_to);
        //var_dump($next_item_key);exit;
        
        try {
            return $apiInstance->transactionsUsingGET($bankData['gmo_bank_account_id'], $token, $date_from, $date_to, $next_item_key);
            // string | 口座ID 半角英数字 口座を識別するID  科目コードが以下の場合のみ受け付けます ・01=普通預金（有利息） ・02=普通預金（決済用）  minLength: 12 maxLength: 29
            // string | 対象期間From 半角文字 YYYY-MM-DD形式  minLength: 10 maxLength: 10
            // string | 対象期間To 半角文字 YYYY-MM-DD形式 対象期間Fromと対象期間Toを指定する場合は、対象期間From≦対象期間Toとし、それ以外は「400 Bad Request」を返却  minLength: 10 maxLength: 10
            // string | 次明細キー 半角数字 初回要求時は未設定 初回応答で次明細キーが「true」の場合、返却された同項目を2回目以降に設定  minLength: 1 maxLength: 24
            
        } catch (Exception $e) {
            echo 'Exception when calling AccountApi->transactionsUsingGET: ', $e->getMessage(), PHP_EOL;
        }

    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/bank-select                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO支払元口座選択                                          |
    +----------------------------------------------------------------------------*/
    public function bankSelectAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '振込予約';

        $request = $this->getRequest();
        $this->view->payingType = $payingType = $request->getParam('paying_type');

    	if ($payingType === (string)Shared_Model_Code::PAYABLE_PAYING_TYPE_INVOICE) {
        	$this->view->backUrl = '/transaction-paid/invoice-list';
        } else {
        	$this->view->backUrl = '/transaction-paid/site-list';
        }
        
        
        if (!empty($this->_adminProperty['group_data']['gmo_account_id'])) {
            $bankTable = new Shared_Model_Data_AccountBank();
    		$dbAdapter = $bankTable->getAdapter();
            $selectObj = $bankTable->select();
            $selectObj->where($bankTable->aesdecrypt('bank_code', false) . ' = 0310'); // GMO口座
            $selectObj->where('gmo_account_id = ?', $this->_adminProperty['group_data']['gmo_account_id']);
    		$selectObj->order('content_order ASC');
            $this->view->bankList = $selectObj->query()->fetchAll();
        }
        
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/get-plan-items                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO支払候補                                                |
    +----------------------------------------------------------------------------*/
    public function getPlanItemsAction()
    {
        $this->_helper->layout->setLayout('blank');
        
        $request = $this->getRequest();
        $this->view->payingType = $payingType = $request->getParam('paying_type');
        $this->view->bankId     = $bankId     = $request->getParam('bank_id');
        $this->view->targetDate = $targetDate = $request->getParam('target_date');
        
        if (!empty($bankId)) {
            $payableTable = new Shared_Model_Data_AccountPayable();
    		$dbAdapter = $payableTable->getAdapter();
    		
    		$selectObj = $payableTable->select();
    		$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name', 'gs_bank_confirmed'));
    		$selectObj->joinLeft('frs_account_bank', 'frs_account_payable.paying_bank_id = frs_account_bank.id', array($payableTable->aesdecrypt('bank_name', false) . 'AS bank_name', $payableTable->aesdecrypt('branch_name', false) . 'AS branch_name', 'account_type', $payableTable->aesdecrypt('account_no', false) . 'AS account_no'));
    		$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id', array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
    		
    		// グループID
            $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
    		
    		$selectObj->where('frs_account_payable.paying_type = ?', $payingType);
    		
    		$selectObj->where('frs_account_payable.paying_method != ?', Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT);
    		
    		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
    		            . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
    	    $selectObj->where('frs_account_payable.payment_status = ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
			
			
			$selectObj->where('paying_bank_id = ?', $bankId);
			
			if (!empty($targetDate)) {
			    $selectObj->where('paying_plan_date = ?', $targetDate);
			}
			
			$selectObj->order('frs_account_payable.paying_plan_date DESC');
			$selectObj->order('frs_account_payable.id DESC');
			
            $this->view->items = $selectObj->query()->fetchAll();
            
            // 通貨リスト
    		$currencyTable = new Shared_Model_Data_Currency();
    		$currencyList  = array();
    		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
            foreach ($currencyItems as $each) {
            	$currencyList[$each['id']] = $each;
            	
            	$total[$each['id']] = $each;
            	$total[$each['id']]['item_count'] = 0;
            	$total[$each['id']]['total'] = 0;
            	
            	$unpaidTotal[$each['id']] = $each;
            	$unpaidTotal[$each['id']]['item_count'] = 0;
            	$unpaidTotal[$each['id']]['total'] = 0;
            }
    		$this->view->currencyList = $currencyList;
        }
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer-post                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO振込予約(一括振込形式)                                  |
    +----------------------------------------------------------------------------*/
    public function transferPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 

        $request = $this->getRequest();
		$params = $request->getParams();
		
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData  = $bankTable->getById($params['bank_select']);
        
        $payableTable    = new Shared_Model_Data_AccountPayable();
        $connectionTable = new Shared_Model_Data_Connection();
        $connectionBankTable = new Shared_Model_Data_ConnectionBank();
        $transferTable    = new Shared_Model_Data_AccountGmoTransfer();
        
        // 振込リスト
        $transfer     = array();
        $payableList  = array();
        
        $count = 0;
        $totalAmount = 0;

        if (empty($params['target_date'])) {
            $this->sendJson(array('result' => 'NG', 'message' => '振込予定日を選択してください'));
        	return;
        } else if (empty($params['target_date'])) {
            $this->sendJson(array('result' => 'NG', 'message' => '振込予定日を選択してください'));
        	return;
        }
        
        $payingPlanDate = '';
        
        foreach ($params['target'] as $eachTargetId) {
            $each = $payableTable->getById($this->_adminProperty['management_group_id'], $eachTargetId);
            $each['item_id'] = (string)($count + 1);
            
            $payingPlanDate = $each['paying_plan_date'];
            
            if ($payingPlanDate !== '' && $payingPlanDate !== $each['paying_plan_date']) {
                $this->sendJson(array('result' => 'NG', 'message' => '振込予定日が一致しません'));
            	return;
            }
            
            $payableList[] = $each;
        }
            

        foreach ($payableList as $each) {
            if (!empty($each['transfer_to_bank_code'])) { 
			    // 個別振込先
                $accountTypeCode = '';
                if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
                    $accountTypeCode = '1';
                } else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
                    $accountTypeCode = '2';
                } else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
                    $accountTypeCode = '3';
                }
                
                $transfer[] = new \Ganb\Corporate\Client\Model\Transfer([
                    'item_id'                 => (string)($count + 1),
                    'transfer_amount'         => $each['total_amount'],
                    'beneficiary_bank_code'   => $each['transfer_to_bank_code'],
                    'beneficiary_branch_code' => $each['transfer_to_branch_code'],
                    'account_type_code'       => $accountTypeCode,
                    'account_number'          => sprintf("%07d", $each['transfer_to_account_no']),
                    'beneficiary_name'        => str_replace('　', ' ', str_replace('（', '(', mb_convert_kana($each['transfer_to_account_name'], "k", "UTF-8"))),
                ]);
            } else {
                // 取引先登録口座
                $connectionBankData = $connectionBankTable->getById($each['transfer_to_connection_bank_id']);

                $accountTypeCode = '';
                if ($connectionBankData['account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
                    $accountTypeCode = '1';
                } else if ($connectionBankData['account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
                    $accountTypeCode = '2';
                } else if ($connectionBankData['account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
                    $accountTypeCode = '3';
                }
                
                $transfer[] = new \Ganb\Corporate\Client\Model\Transfer([
                    'item_id'                 => (string)($count + 1),
                    'transfer_amount'         => $each['total_amount'],
                    'beneficiary_bank_code'   => $connectionBankData['bank_code'],
                    'beneficiary_branch_code' => $connectionBankData['branch_code'],
                    'account_type_code'       => $accountTypeCode,    // 1：普通　2：当座　4：貯蓄  9：その他
                    'account_number'          => sprintf("%07d", $connectionBankData['account_no']),
                    'beneficiary_name'        => str_replace('　', ' ', str_replace('（', '(', mb_convert_kana($connectionBankData['account_name_kana'], "k", "UTF-8"))),
                ]);
            }
            
            $totalAmount += $each['total_amount'];
            $count++;
        }

        //var_dump($bankData['gmo_bank_account_id']);
        //var_dump($transfer);
        //var_dump(count($transfer));
        //var_dump($totalAmount);
        //exit;

        $token = Shared_Model_Utility_GmoBank::getToken($bankData['gmo_account_id']);

        $apiInstance = new Ganb\Corporate\Client\Api\TransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );

        $body = new \Ganb\Corporate\Client\Model\TransferRequest([
            'account_id'               => $bankData['gmo_bank_account_id'],
            'transfer_designated_date' => $payingPlanDate,
            'total_count'              => (string)$count,
            'total_amount'             => (string)$totalAmount,
            'transfers'                => $transfer
        ]); // \Ganb\Corporate\Client\Model\TransferRequest | HTTPリクエストボディ
        
        try {
            $result = $apiInstance->transferRequestUsingPOST($body, $token);
            //print_r($result);
            
            foreach ($payableList as $eachPayable) {
                $transferData = array(
                    'management_group_id'      => $this->_adminProperty['management_group_id'], // 管理グループID
                    'payable_id'               => $eachPayable['id'],
                    'status'                   => 1,                                   // ステータス
                    
                    'account_id'               => $bankData['gmo_bank_account_id'],    // 口座ID
    
                    'transfer_designated_date' => $each['paying_plan_date'],           // 振込指定日
                    'apply_no'                 => $result['apply_no'],                 // 受付番号（振込申請番号）
                    'item_id'                  => $eachPayable['item_id'],             // 
                    
                    'result_code'              => Shared_Model_Code::GMO_API_TRANSFER_RESULT_CODE_UNAPPLOVED, // 未承認
                    'apply_end_datetime'       => $result['apply_end_datetime'],       // 振込依頼完了日時
                    
                    'transfer_status'          => 0,                                   // 振込ステータス
                    'transfer_status_name'     => 0,                                   // 振込ステータス名
                    
                    'created'                  => new Zend_Db_Expr('now()'),
                    'updated'                  => new Zend_Db_Expr('now()'),
                );
                
                $transferTable->create($transferData);
            }

    	    $this->sendJson(array('result' => 'OK'));
        	return;
        	
        } catch (Exception $e) {
            echo 'Exception when calling TransferApi->transferRequestUsingPOST: ', $e->getMessage(), PHP_EOL;
        }

    }

    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer-bulk-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GMO振込予約(総合振込形式)                                  |
    +----------------------------------------------------------------------------*/
    public function transferBulkPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(); 

        $request = $this->getRequest();
		$params = $request->getParams();
		
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData = $bankTable->getById($params['bank_select']);
        
        $payableTable        = new Shared_Model_Data_AccountPayable();
        $connectionTable     = new Shared_Model_Data_Connection();
        $connectionBankTable = new Shared_Model_Data_ConnectionBank();
        $transferTable       = new Shared_Model_Data_AccountGmoTransfer();
        
        // 振込リスト
        $bulkTransfer = array();
        $payableList  = array();
        
        $count = 0;
        $totalAmount = 0;
        
        if (empty($params['target_date'])) {
            $this->sendJson(array('result' => 'NG', 'message' => '振込予定日を選択してください'));
        	return;
        } else if (empty($params['target_date'])) {
            $this->sendJson(array('result' => 'NG', 'message' => '振込予定日を選択してください'));
        	return;
        }
            
        $payingPlanDate = '';

        foreach ($params['target'] as $eachTargetId) {
            $each = $payableTable->getById($this->_adminProperty['management_group_id'], $eachTargetId);
            $each['item_id'] = (string)($count + 1);
            
            
            $payingPlanDate = $each['paying_plan_date'];
            
            if ($payingPlanDate !== '' && $payingPlanDate !== $each['paying_plan_date']) {
                $this->sendJson(array('result' => 'NG', 'message' => '振込予定日が一致しません'));
            	return;
            }
            
            $payableList[] = $each;
        }

        
        foreach ($payableList as $each) {
            if (!empty($each['transfer_to_bank_code'])) { 
			    // 個別振込先
                $accountTypeCode = '';
                if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
                    $accountTypeCode = '1';
                } else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
                    $accountTypeCode = '2';
                } else if ($each['transfer_to_account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
                    $accountTypeCode = '3';
                }
                
                $bulkTransfer[] = new \Ganb\Corporate\Client\Model\BulkTransfer([
                    'item_id'                 => (string)$count + 1,
                    'transfer_amount'         => $each['total_amount'],
                    'beneficiary_bank_code'   => $each['transfer_to_bank_code'],
                    'beneficiary_branch_code' => $each['transfer_to_branch_code'],
                    'account_type_code'       => $accountTypeCode,   // 1：普通　2：当座　4：貯蓄  9：その他
                    'account_number'          => sprintf("%07d", $each['transfer_to_account_no']),
                    'beneficiary_name'        => str_replace('　', ' ', str_replace('（', '(', mb_convert_kana($each['transfer_to_account_name'], "k", "UTF-8"))),
                ]);
		        //var_dump($each['transfer_to_branch_code']);
            } else {
                // 取引先登録口座
		    	$connectionBankData = $connectionBankTable->getById($each['transfer_to_connection_bank_id']);
                
                $accountTypeCode = '';
                if ($connectionBankData['account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_GENERAL) {
                    $accountTypeCode = '1';
                } else if ($connectionBankData['account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_CURRENT) {
                    $accountTypeCode = '2';
                } else if ($connectionBankData['account_type'] === (string)Shared_Model_Code::BANK_ACCOUNT_TYPE_SAVING_DEPOSIT) {
                    $accountTypeCode = '3';
                }

                $bulkTransfer[] = new \Ganb\Corporate\Client\Model\BulkTransfer([
                    'item_id'                 => (string)($count + 1),
                    'transfer_amount'         => $each['total_amount'],
                    'beneficiary_bank_code'   => $connectionBankData['bank_code'],
                    'beneficiary_branch_code' => $connectionBankData['branch_code'],
                    'account_type_code'       => $accountTypeCode,   // 1：普通　2：当座　4：貯蓄  9：その他
                    'account_number'          => sprintf("%07d", $connectionBankData['account_no']),
                    'beneficiary_name'        => str_replace('　', ' ', str_replace('（', '(', mb_convert_kana($connectionBankData['account_name_kana'], "k", "UTF-8"))),
                ]); 
                //var_dump($connectionBankData['branch_code']);
            }
            
            $totalAmount += $each['total_amount'];
            $count++;
        }
        
        //var_dump($payingPlanDate);
        //var_dump($bankData['gmo_bank_account_id']);
        //var_dump($bulkTransfer);
        //var_dump(count($bulkTransfer));
        //var_dump($totalAmount);
        //exit;
        
        $token = Shared_Model_Utility_GmoBank::getToken($bankData['gmo_account_id']);
        //var_dump($token);exit;
        
        $apiInstance = new Ganb\Corporate\Client\Api\BulkTransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );

        $body = new \Ganb\Corporate\Client\Model\BulkTransferRequest([
            'account_id'               => $bankData['gmo_bank_account_id'],
            'transfer_designated_date' => $payingPlanDate,
            'total_count'              => (string)$count,
            'total_amount'             => (string)$totalAmount,
            'bulk_transfers'           => $bulkTransfer
        ]); // \Ganb\Corporate\Client\Model\BulkTransferRequest | HTTPリクエストボディ
        
        try {
            //$result = $apiInstance->bulkTransferFeeUsingPOST($body, $token); // 事前確認
            $result = $apiInstance->bulkTransferRequestUsingPOST($body, $token);
            
            //print_r($result);
            //print_r($result['apply_no']);
            
            //exit;
            
            foreach ($payableList as $eachPayable) {
                
                $payableTable->updateById($eachPayable['id'], array(
                    'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED_NOT_APPROVED,
                ));
                
                $transferData = array(
                    'management_group_id'      => $this->_adminProperty['management_group_id'], // 管理グループID
                    'payable_id'               => $eachPayable['id'],
                    'status'                   => 1,                                   // ステータス
                    
                    'account_id'               => $bankData['gmo_bank_account_id'],    // 口座ID

                    'transfer_designated_date' => $each['paying_plan_date'],           // 振込指定日
                    'apply_no'                 => $result['apply_no'],                 // 受付番号（振込申請番号）
                    'item_id'                  => $eachPayable['item_id'],             // 明細番号
                    
                    'result_code'              => Shared_Model_Code::GMO_API_TRANSFER_RESULT_CODE_UNAPPLOVED, // 未承認
                    'apply_end_datetime'       => $result['apply_end_datetime'],       // 振込依頼完了日時
                    
                    'transfer_status'          => 0,                                   // 振込ステータス
                    
                    'created'                  => new Zend_Db_Expr('now()'),
                    'updated'                  => new Zend_Db_Expr('now()'),
                );
                
                $transferTable->create($transferData);
                            
            }
            
    	    $this->sendJson(array('result' => 'OK'));
        	return;
            
        } catch (Exception $e) {
            echo 'Exception when calling BulkTransferApi->bulkTransferFeeUsingPOST: ', $e->getMessage(), PHP_EOL;
        }

    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer-result                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 総合振込依頼結果照会                                       |
    +----------------------------------------------------------------------------*/
    public function transferResultAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/transaction-paid/gmo-transfer-develop-list';
        
        $request = $this->getRequest();
		$accountId = $request->getParam('account_id', '');
		$applyNo   = $request->getParam('apply_no', '');

        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData = $bankTable->getGMOBankBankAccountId($accountId);
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $token = Shared_Model_Utility_GmoBank::getToken($bankData['gmo_account_id']);

        
        $apiInstance = new Ganb\Corporate\Client\Api\BulkTransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        
        try {
            $result = $apiInstance->bulkTransferRequestResultUsingGET($accountId, $applyNo, $token);
            //print_r($result);
            //exit;
            
            if (!empty($result['result_code'])) {
                $transferTable = new Shared_Model_Data_AccountGmoTransfer();
                
                $transferTable->updateByApplyNo($applyNo, array(
                    'result_code' => $result['result_code']
                ));
                
            }
            
            $this->view->result = $result;
            
            
        } catch (Exception $e) {
            throw new Zend_Exception('Exception when calling BulkTransferApi->bulkTransferRequestResultUsingGET: ' . $e->getMessage());
        }   
    }
    

 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer-status                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 総合振込依頼結果照会                                       |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transferStatusAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/transaction-paid/gmo-transfer-develop-list';
        
        $request = $this->getRequest();
		$accountId = $request->getParam('account_id', '');
		$applyNo   = $request->getParam('apply_no', '');
        
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData = $bankTable->getGMOBankBankAccountId($accountId);
        //var_dump($bankData);exit;
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $token = Shared_Model_Utility_GmoBank::getToken($bankData['gmo_account_id']);
        
        
        $apiInstance = new Ganb\Corporate\Client\Api\BulkTransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        
        $query_key_class = "1"; // string | 照会対象キー区分 半角数字 照会対象のキー 1：振込申請照会対象指定、2：振込一括照会対象指定  minLength: 1 maxLength: 1

        /*
        $bulktransfer_item_key = NULL; // 
        
        $date_from = NULL; // string | 対象期間From 半角文字 YYYY-MM-DD形式 照会対象キー区分が、2のときは入力可 それ以外はNULLを設定（値が設定されている場合は、「400 Bad Request」を返却）              minLength: 10 maxLength: 10
        $date_to = NULL; // string | 対象期間To 半角文字 YYYY-MM-DD形式 照会対象キー区分が、2のときは入力可 それ以外はNULLを設定（値が設定されている場合は、「400 Bad Request」を返却） 対象期間Fromと対象期間Toを指定する場合は、対象期間From≦対象期間Toとし、それ以外は「400 Bad Request」を返却  minLength: 10 maxLength: 10
        $next_item_key = ""; // string | 次明細キー 半角数字 照会対象キー区分が、2のときは入力可 それ以外はNULLを設定（値が設定されている場合は、「400 Bad Request」を返却）              minLength: 1 maxLength: 24
        $request_transfer_status = array(); // string[] | 照会対象ステータス  半角数字  2:申請中、3:差戻、4:取下げ、5:期限切れ、8:承認取消/予約取消、  11:予約中、12:手続中、13:リトライ中、  20:手続済、30:不能・組戻あり、40:手続不成立   照会対象キー区分が、2のときは設定可  それ以外は設定しません（値が設定されている場合は、「400 Bad Request」を返却）  配列のため、複数設定した場合は対象のステータスをOR条件で検索します  省略した場合は全てを設定したものとみなします  minLength: 1 maxLength: 3
        $request_transfer_class = NULL; // string | 振込照会対象取得区分 半角数字 1：ALL、2：振込申請のみ、3：振込受付情報のみ NULLを設定 値が設定されている場合は、「400 Bad Request」を返却  minLength: 1 maxLength: 1
        $request_transfer_term = NULL; // string | 振込照会対象期間区分 半角数字 対象期間Fromと対象期間Toで指定する日付の区分 1：振込申請受付日　2：振込指定日 照会対象キー区分が2のときのみ入力可 それ以外はNULLを設定（値が設定されている場合は、「400 Bad Request」を返却） 照会対象キー区分が、2のときに指定しない場合は1と扱います  minLength: 1 maxLength: 1
        */
        
        try {
            $result = $apiInstance->bulkTransferStatusUsingGET(
                $accountId,
                $query_key_class,
                $token, 
                /*$detail_info_necessity*/ 'true', // bool | 明細情報取得フラグ 総合振込明細情報の取得要否 照会対象キー区分が、1のときは「True：取得する」を指定可 それ以外で「True：取得する」が設定されている場合は、「400 Bad Request」を返却 True：取得する、False:取得しない　省略/NULLは　false扱い
                1, // string | 総合振込明細情報取得対象キー 半角数字 明細情報取得フラグが、「True：取得する」のとき指定可 それ以外はNULLを設定（値が設定されている場合は、「400 Bad Request」を返却） 総合振込明細情報を取得するときに取得を開始する番号 明細情報取得フラグが、「True：取得する」のときの省略/NULLは1扱い 1500明細を取得する場合、は以下のように設定 1電文目：1 2電文目：501 3電文目：1001  minLength: 1 maxLength: 6
                /*$applyNo*/ $applyNo,
                /*$date_from*/ NULL,
                /*$date_to*/ NULL,
                /*$next_item_key*/ NULL,
                /*$request_transfer_status*/ NULL, 
                 /*$request_transfer_class*/ NULL,
                 /*$request_transfer_term*/ NULL
            );
            //print_r($result);

            $transferTable = new Shared_Model_Data_AccountGmoTransfer();
            
            /*
            var_dump($result['bulk_transfer_details'][0]['transfer_status']);
            exit;
                */
            $transferTable->updateByApplyNo($applyNo, array(
                'transfer_status' => $result['bulk_transfer_details'][0]['transfer_status'],
            ));
            
            foreach ($result['bulk_transfer_details'][0]['bulktransfer_responses'][0]['bulk_transfer_infos'] as $eachDetail) {
                //var_dump($eachDetail['item_id']);
                //var_dump($eachDetail['unable_detail_infos']);
                
                // 仮データ
                if ($applyNo === '2021062800000004' && $eachDetail['item_id'] === '1') {
                    $eachDetail['unable_detail_infos'] = array( 0 => array(
                        'transferDetailStatus' => '1',
                        'refundStatus'  => '1',
                        'isRepayment'   => 'true',
                        'repaymentDate' => '2021-07-05'
                    ));
                }
                
                if (!empty($eachDetail['unable_detail_infos'])) {
                    $transferTable->updateByApplyNoAndItemId($applyNo, $eachDetail['item_id'], array(
                        'unable_detail_info' => serialize($eachDetail['unable_detail_infos']),
                    ));
                }
            }
            
            $this->view->result = $result;
            
        } catch (Exception $e) {
            echo 'Exception when calling BulkTransferApi->bulkTransferStatusUsingGET: ', $e->getMessage(), PHP_EOL;
        }
        
    }
    




    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/cancel                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入出金明細照会                                             |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function cancelAction()
    {

        $request = $this->getRequest();
		$accountId = $request->getParam('account_id', '');
		$applyNo   = $request->getParam('apply_no', '');
		
		
		var_dump($accountId);
		var_dump($applyNo);
		
        $bankTable = new Shared_Model_Data_AccountBank();
        $bankData = $bankTable->getGMOBankBankAccountId($accountId);
		
		
		$token = Shared_Model_Utility_GmoBank::getToken($bankData['gmo_account_id']);
		
		//var_dump($token);exit;
		
        $apiInstance = new Ganb\Corporate\Client\Api\BulkTransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        $body = new \Ganb\Corporate\Client\Model\TransferCancelRequest([
            'account_id' => $accountId,
            'cancel_target_key_class' => '4', // 1:振込申請取消　2:振込受付取消　3:総合振込申請取消　4:総合振込受付取消
                                              //・2、4のみの指定可能
            'apply_no' => $applyNo]
        ); // \Ganb\Corporate\Client\Model\TransferCancelRequest | HTTPリクエストボディ

        try {
            $result = $apiInstance->bulkTransferCancelUsingPOST($body, $token);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling BulkTransferApi->bulkTransferCancelUsingPOST: ', $e->getMessage(), PHP_EOL;
        }
    
exit;


    }
















    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transaction                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入出金明細照会(develop)                                    |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transactionAction()
    {
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $adminLoginSession = new Zend_Session_Namespace('management_login');
        
        $apiInstance = new Ganb\Corporate\Client\Api\AccountApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        
        
        $account_id = "101011003270"; // string | 口座ID 半角英数字 口座を識別するID  科目コードが以下の場合のみ受け付けます ・01=普通預金（有利息） ・02=普通預金（決済用）  minLength: 12 maxLength: 29
        $date_from = "2021-01-13"; // string | 対象期間From 半角文字 YYYY-MM-DD形式  minLength: 10 maxLength: 10
        $date_to = "2021-06-05"; // string | 対象期間To 半角文字 YYYY-MM-DD形式 対象期間Fromと対象期間Toを指定する場合は、対象期間From≦対象期間Toとし、それ以外は「400 Bad Request」を返却  minLength: 10 maxLength: 10
        $next_item_key = ""; // string | 次明細キー 半角数字 初回要求時は未設定 初回応答で次明細キーが「true」の場合、返却された同項目を2回目以降に設定  minLength: 1 maxLength: 24
        
        try {
            $this->view->items = $apiInstance->transactionsUsingGET($account_id, $adminLoginSession->accessToken, $date_from, $date_to, $next_item_key);

        } catch (Exception $e) {
            echo 'Exception when calling AccountApi->transactionsUsingGET: ', $e->getMessage(), PHP_EOL;
        }
        
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer-fee                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 振込手数料事前照会(develop)                                |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transferFeeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $adminLoginSession = new Zend_Session_Namespace('management_login');
        
        $apiInstance = new Ganb\Corporate\Client\Api\TransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        
        $payableTable = new Shared_Model_Data_AccountPayable();
        
        $transferParam = [
            'transfer_amount'         => '1000',
            'beneficiary_bank_code'   => '0005',
            'beneficiary_branch_code' => '069',
            'account_type_code'       => '1',   // 1：普通　2：当座　4：貯蓄  9：その他
            'account_number'          => '0508573',
            'beneficiary_name'        => 'ﾌﾚｽｺ(ｶ',
        ];
        
        $account_id = "101011003270";
        
        $transfer = new \Ganb\Corporate\Client\Model\Transfer($transferParam);
        $body = new \Ganb\Corporate\Client\Model\TransferRequest([
            'account_id'               => $account_id,
            'transfer_designated_date' => '2021-06-30',
            'transfers'                => [$transfer]
        ]); // \Ganb\Corporate\Client\Model\TransferRequest | HTTPリクエストボディ
        
        try {
            $result = $apiInstance->transferFeeUsingPOST($body, $adminLoginSession->accessToken);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling TransferApi->transferFeeUsingPOST: ', $e->getMessage(), PHP_EOL;
        }

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 振込依頼(develop)                                          |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transferAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $adminLoginSession = new Zend_Session_Namespace('management_login');
        
        $apiInstance = new Ganb\Corporate\Client\Api\TransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        
        $transferParam = [
            'transfer_amount'         => '1000',
            'beneficiary_bank_code'   => '0005',
            'beneficiary_branch_code' => '069',
            'account_type_code'       => '1',   // 1：普通　2：当座　4：貯蓄  9：その他
            'account_number'          => '0508573',
            'beneficiary_name'        => 'ﾌﾚｽｺ(ｶ',
        ];
        
        
        $account_id = "101011003270";
        
        $transfer = new \Ganb\Corporate\Client\Model\Transfer($transferParam);
        $body = new \Ganb\Corporate\Client\Model\TransferRequest([
            'account_id'               => $account_id,
            'transfer_designated_date' => '2021-06-30',
            'transfers'                => [$transfer]
        ]); // \Ganb\Corporate\Client\Model\TransferRequest | HTTPリクエストボディ
        
        try {
            $result = $apiInstance->transferRequestUsingPOST($body, $adminLoginSession->accessToken);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling TransferApi->transferRequestUsingPOST: ', $e->getMessage(), PHP_EOL;
        } 
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /oauth/transfer-bluk                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 総合振込依頼(develop)                                      |
    +----------------------------------------------------------------------------*/
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transferBlukAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
        $adminLoginSession = new Zend_Session_Namespace('management_login');

        $apiInstance = new Ganb\Corporate\Client\Api\BulkTransferApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client()
        );
        $bulk_transfer = new \Ganb\Corporate\Client\Model\BulkTransfer([
            'item_id'                 => '1',
            'transfer_amount'         => '1000',
            'beneficiary_bank_code'   => '0005',
            'beneficiary_branch_code' => '069',
            'account_type_code'       => '1',   // 1：普通　2：当座　4：貯蓄  9：その他
            'account_number'          => '0508573',
            'beneficiary_name'        => 'ﾌﾚｽｺ(ｶ',
        ]);
        $body = new \Ganb\Corporate\Client\Model\BulkTransferRequest([
            'account_id'               => $account_id,
            'transfer_designated_date' => '2021-06-30',
            'total_count' => '1',
            'total_amount' => '1000',
            'bulk_transfers' => [$bulk_transfer]
        ]); // \Ganb\Corporate\Client\Model\BulkTransferRequest | HTTPリクエストボディ
        
        try {
            $result = $apiInstance->bulkTransferRequestUsingPOST($body, $adminLoginSession->accessToken);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling BulkTransferApi->bulkTransferRequestUsingPOST: ', $e->getMessage(), PHP_EOL;
        }
    }
}