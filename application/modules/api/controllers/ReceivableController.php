<?php
/**
 * class Api_ReceivableController
 */

class Api_ReceivableController extends Api_Model_Controller
{
    /**
     * updateAction
     * 登録・更新
     */
    public function updateAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();
		
        $userTable    = new Shared_Model_Data_User();
  
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		
		$data = array(
	        'management_group_id'       => $params['management_group_id'],                          // 管理グループID
	        'status'                    => Shared_Model_Code::RECEIVABLE_STATUS_APPROVED,           // ステータス
	        'payment_status'            => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED, // 入金ステータス

	        'relational_id'             => $params['relational_id'],                                // 連携ID
	        'relational_display_id'     => $params['relational_display_id'],                        // 連携表示ID
         
	        'accrual_date'              => $params['accrual_date'],                                 // 発生日(請求日)
	        
			'type'                      => Shared_Model_Code::RECEIVABLE_TYPE_SITE_DATA,            // 売掛管理種別
			'invoice_id'                => 0,                                                       // 請求書ID
			
			'account_title_id'          => $params['account_title_id'],                             // 会計科目ID
			'account_totaling_group_id' => $params['account_totaling_group_id'],                    // 採算コード
			
			'target_connection_id'      => $params['target_connection_id'],       // 請求先取引先ID                  (修正必要)
			'currency_id'               => 1,                                     // 請求金額通貨ID                  (修正必要)
			'total_amount'              => $params['total_amount_with_tax'],      // 請求金額
			
			'bank_sender_name'          => $params['bank_sender_name'],           // 振込名義人
			
			
			'bank_id'                   => $params['bank_id'],                    // 入金予定口座
			'receive_plan_date'         => $params['receive_plan_date'],          // 入金予定日
			'received_date'             => NULL,                                  // 入金受取日
			
			'created_user_id'           => 22,                                    // 登録者ユーザーID
			
			'confirm_user_id'           => 0,                                     // 入金確認者ユーザーID
			'confirm_datetime'          => NULL,                                  // 入金確認日
			
			'memo'                      => $params['memo'],                       // メモ

            'created'                   => new Zend_Db_Expr('now()'),
            'updated'                   => new Zend_Db_Expr('now()'),
		);
		
		$receivableTable->create($data);
		$receivableId = $receivableTable->getLastInsertedId('id');
		
        $params = array (
            'result'        => true,
            'receivable_id' => $receivableId,
        );
            
		return $this->sendJson($params);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /api/receivable/attachment                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金割当情報照会(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function attachmentAction()
    {
		$request  = $this->getRequest();
		$params   = $request->getParams();
 
		$receivableTable  = new Shared_Model_Data_AccountReceivable();
		$data = $receivableTable->getByRelationalId($params['management_group_id'], $params['relational_id']);
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = $currencyTable->getList($params['management_group_id']);
		
		/*
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		$userTable                = new Shared_Model_Data_User();
    	$this->view->createdUser  = $userTable->getById($data['created_user_id']);
    	*/

		// 銀行口座
		if (!empty($data['bank_id'])) {
			//$bankTable = new Shared_Model_Data_AccountBank();
			//$this->view->bankData = $bankTable->getById($data['bank_id']);
			
			// 割当情報
			$bankHistoryItemTable = new Shared_Model_Data_AccountBankHistoryItem();
			$data['history_items'] = $bankHistoryItemTable->getListByReceivableId($data['id']);

		}
	    
	    return $this->sendJson(array('result' => true, 'data' => $data));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-invoice/add-receivable-post                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金予定登録(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    /*
    public function addReceivablePostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$invoiceId  = $request->getParam('invoice_id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
   
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['account_title_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
                } else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                } else if (!empty($errorMessage['bank_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「入金予定口座」を選択してください'));
                    return;
                } else if (!empty($errorMessage['receive_plan_date']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「入金予定日」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$receivableTable  = new Shared_Model_Data_AccountReceivable();
				$invoiceTable     = new Shared_Model_Data_Invoice();
				
				// 請求書情報
		    	$invoiceData = $invoiceTable->getById($this->_adminProperty['management_group_id'], $invoiceId);
		    	$invoiceData['currency_id'] = '1'; // 仮
	    	
				try {
					$invoiceTable->getAdapter()->beginTransaction();
					
					$data = array(
				        'management_group_id'    => $this->_adminProperty['management_group_id'],            // 管理グループID
				        'status'                 => Shared_Model_Code::RECEIVABLE_STATUS_APPROVED,           // ステータス
				        'payment_status'         => Shared_Model_Code::RECEIVABLE_PAYMENT_STATUS_UNRECEIVED, // 入金ステータス
				        
				        'accrual_date'           => $invoiceData['invoice_date'],                        // 発生日(請求日)
				        
						'type'                   => Shared_Model_Code::RECEIVABLE_TYPE_INVOICE,  // 売掛管理種別
						'invoice_id'             => $invoiceData['id'],                          // 請求書ID
						'account_title_id'       => $success['account_title_id'],                // 会計科目ID
						
						'account_totaling_group_id' => $success['account_totaling_group_id'],    // 採算コード
						
						'target_connection_id'   => $invoiceData['target_connection_id'],  // 請求先取引先ID
						'currency_id'            => $invoiceData['currency_id'],           // 請求金額通貨ID
						'total_amount'           => $invoiceData['total_with_tax'],        // 請求金額
						
						'bank_id'                => $success['bank_id'],                   // 入金予定口座
						'receive_plan_date'      => $success['receive_plan_date'],         // 入金予定日
						'received_date'          => NULL,                                  // 入金受取日
						
						'created_user_id'        => $this->_adminProperty['id'],           // 登録者ユーザーID
						
						'confirm_user_id'        => 0,                                     // 入金確認者ユーザーID
						'confirm_datetime'       => NULL,                                  // 入金確認日
						
						'memo'                   => $success['memo'],                      // メモ
	
		                'created'                 => new Zend_Db_Expr('now()'),
		                'updated'                 => new Zend_Db_Expr('now()'),
					);
					
					$receivableTable->create($data);
					
					$invoiceTable->updateById($invoiceId, array(
						'status' => Shared_Model_Code::INVOICE_STATUS_PAYABLED_ADDED,
					));
				
	                // commit
	                $invoiceTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $invoiceTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-invoice/submit transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    */  



}