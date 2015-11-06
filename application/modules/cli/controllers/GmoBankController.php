<?php
/**
 * class Cli_GmoBankController
 *
 */
class Cli_GmoBankController extends Cli_Model_Controller
{
    /**
     * init
     *
     * @param void
     * @return void
     * @see Front_Model_Controller::init()
     */
    public function init()
    {
        parent::init();
    }

    /**
     * cmd - php cli.php -p /gmo-bank/account
     * 口座一覧照会
     */
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#accountsUsingGET
    public function accountAction()
    {
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

		$logTable = new Shared_Model_Data_SystemLog();
		$logTable->addLog(
			'/cli/gmo-bank/account',
			'start',
			''
		);
		
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$gmoAccountList = $gmoTable->getList();
		
        foreach ($gmoAccountList as $each) {
	        $token = Shared_Model_Utility_GmoBank::getToken($each['id']); 

	        if (!empty($token)) {
	            $apiInstance = new Ganb\Corporate\Client\Api\AccountApi(
	                new GuzzleHttp\Client()
	            );
	    
	            try {
	                $accountData = $apiInstance->accountsUsingGET($token);
	                
	                foreach ($accountData['accounts'] as $eachAccount) {
	                	$this->_loadTransaction($eachAccount['account_id'], $eachAccount['account_number']);
	                }
	                
	                
	            } catch (Exception $e) {
	                throw new Zend_Exception('Exception when calling AccountApi->accountsUsingGET: ' . $e->getMessage());
	            }
	        } else {
	            echo 'GMO bank' . $each['id'] . ': no token' . "\n";
	        }
        }
    }

    /*
    _loadTransaction
    入出金明細照会
    */
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function _loadTransaction($accountId, $accountNo)
    {
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
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
            $currencyData = $currencyTable->getBySymbol('1', '¥');
		
		    $latestHistoryItem = NULL;
		    
		    $latestHistory = $bankHistoryTable->latestHistoryOfBank($bankData['id']);
		    
		    if (!empty($latestHistory)) {
		        $latestHistoryItem = $bankHistoryItemTable->lastRowOfHistory($latestHistory['id']);
		    }
		        
		    $bankHistoryItemTable->getAdapter()->beginTransaction();
		    
		    try {
                // 履歴取込
        		$data = array(
        	        'management_group_id' => '0',  // 管理グループID
        	        'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
        	        'import_key'          => $key,                                         // ファイル名
        	        'bank_id'             => $bankData['id'],                              // 銀行ID
        	        'created_user_id'     => '0',  // 取込実施者
        
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
                    	        'management_group_id'   => '0',       // 管理グループID
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
                        }
                		
                		$itemCount++;
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
    
    
    /**
     * cmd - php cli.php -p /gmo-bank/transfer-result
     * 総合振込依頼結果照会 頻度よく回す
     */
    // SDK DOC: https://github.com/gmoaozora/gmo-aozora-api-php/blob/master/corporate/docs/Api/AccountApi.md#transactionsUsingGET
    public function transferResultAction()
    {
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
		$gmoTable      = new Shared_Model_Data_ManagementGmoAccount();
		$bankTable     = new Shared_Model_Data_AccountBank();
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		$payableTable  = new Shared_Model_Data_AccountPayable();
		
		$gmoAccountList = $gmoTable->getList();
		
        foreach ($gmoAccountList as $each) {
            $accountList = $bankTable->getGMOBankListWithGmoId($each['id']);
	        
	        // 口座リスト
	        foreach ($accountList as $eachAccount) {
	            if (empty($eachAccount['gmo_account_id']) || empty($eachAccount['gmo_bank_account_id']) ) {
	                continue;
	            }
	            
	            $token = Shared_Model_Utility_GmoBank::getToken($eachAccount['gmo_account_id']);
	            
	            if (empty($token)) {
	                continue;
	            }
	            
	            // 承認結果未確認データ(申請番号ごとにグループ)
	            $transferItems = $transferTable->getConfirmListByGmoBankAccountId($eachAccount['gmo_bank_account_id']);
	            
    	        if (!empty($transferItems)) {
    	            foreach ($transferItems as $eachTransfer) {
    	                $apiInstance = new Ganb\Corporate\Client\Api\BulkTransferApi(
    	                    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    	                    // This is optional, `GuzzleHttp\Client` will be used as default.
    	                    new GuzzleHttp\Client()
    	                );
    	                
    	                try {
                            // API実行
    	                    $result = $apiInstance->bulkTransferRequestResultUsingGET($eachAccount['gmo_bank_account_id'], $eachTransfer['apply_no'], $token);
    	                    //print_r($result);
    	                    //exit;
    	                    
    	                    // result_code　1：完了　2：未完了　8:期限切れ
    	                    
    	                    if (!empty($result['result_code'])) {
    	                        // ステータス更新(トランザクション)
    	                        $transferTable->getAdapter()->beginTransaction();
    	                        
    	                        try {
        	                        $transferTable->updateByApplyNo($eachTransfer['apply_no'], array(
        	                            'result_code' => $result['result_code']
        	                        ));
        	                        
        	                        if ($result['result_code'] === '8') { // 期限切れ
        	                            $applyItems = $transferTable->getListByApplyNo($eachTransfer['apply_no']);
        	                            //var_dump($applyItems);exit;
        	                            
        	                            foreach ($applyItems as $eachApply) {
        	                                $payableTable->updateById($eachApply['payable_id'], array(
        	                                    'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED_EXPIRED,
        	                                )); 
        	                            }
        	                            
        	                        } else if ($result['result_code'] === '2') { // 期限切れ
        	                            $applyItems = $transferTable->getListByApplyNo($eachTransfer['apply_no']);
        	                            //var_dump($applyItems);exit;
        	                            
        	                            foreach ($applyItems as $eachApply) {
        	                                $payableTable->updateById($eachApply['payable_id'], array(
        	                                    'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID,
        	                                )); 
        	                            }
        	                            
        	                        } elseif ($result['result_code'] === '1') { // 完了
        	                            $applyItems = $transferTable->getListByApplyNo($eachTransfer['apply_no']);
        	                            
        	                            foreach ($applyItems as $eachApply) {
        	                                $payableTable->updateById($eachApply['payable_id'], array(
        	                                    'payment_status' => Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED,
        	                                )); 
        	                            }
        	                        }

                        			// commit
                                    $transferTable->getAdapter()->commit();
            
    	                        } catch (Exception $e) {
    	                            $transferTable->getAdapter()->rollBack();
            	                    throw new Zend_Exception('applyNo: ' . $eachTransfer['apply_no'] . '/' . $e->getMessage());
            	                }   
    	                    }
    	                    
    	                } catch (Exception $e) {
    	                    
    	                    
    	                    
    	                }  
    	            }
    	        }
    	        
	            
	        }
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
        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
        
		$gmoTable      = new Shared_Model_Data_ManagementGmoAccount();
		$bankTable     = new Shared_Model_Data_AccountBank();
		$transferTable = new Shared_Model_Data_AccountGmoTransfer();
		$payableTable  = new Shared_Model_Data_payable();
		
		$gmoAccountList = $gmoTable->getList();
		

        foreach ($gmoAccountList as $each) {
            
            // 口座リスト
            $accountList = $bankTable->getGMOBankListWithGmoId($each['id']);
	        
	        foreach ($accountList as $eachAccount) {
	            if (empty($eachAccount['gmo_account_id']) || empty($eachAccount['gmo_bank_account_id']) ) {
	                continue;
	            }
	            
	            $token = Shared_Model_Utility_GmoBank::getToken($eachAccount['gmo_account_id']);    
	            

        		//$applyNo   = $request->getParam('apply_no', '');
                
                
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
        }
        
    }
    
    

}
