<?php
/**
 * class TransactionPayableController
 */
 
class TransactionPayableController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '取引処理';
		$this->view->menuCategory     = 'transaction';
		$this->view->menu = 'payable';  

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-debug                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * デバッグ更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateDebugAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = 935;
	
		$payableTable = new Shared_Model_Data_AccountPayable();
		$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
		//var_dump($oldData);
		//exit;
		
        $payableTable->getAdapter()->beginTransaction();
    	
        try {
			$data = array(
				//'tax'             => '137',
				'total_amount'    => '6134',      // 支払先
				
			);
			
			$payableTable->updateById($id, $data);

            // commit
            $payableTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $payableTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-payable/update-summary transaction failed: ' . $e);  
        }
		
	    $this->sendJson(array('result' => 'OK'));
    	return;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-template                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払いデータ修正(Develop)                              |
    +----------------------------------------------------------------------------*/
    public function updateTemplateAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = 12;
	
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$oldData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
		//var_dump($oldData);
		//exit;
		
        $payableTemplateTable->getAdapter()->beginTransaction();
    	
        try {
			$data = array(
				'tax'             => '48',
				'total_amount'    => '524',      // 支払先
			);
			
			$payableTemplateTable->updateById($id, $data);

            // commit
            $payableTemplateTable->getAdapter()->commit();
            
        } catch (Exception $e) {
            $payableTemplateTable->getAdapter()->rollBack();
            throw new Zend_Exception('/transaction-payable/update-template transaction failed: ' . $e);  
        }
		
	    $this->sendJson(array('result' => 'OK'));
    	return;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/invoice-dump-list                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請(デバッグリスト)                               |
    +----------------------------------------------------------------------------*/
    public function invoiceDumpListAction()
    {
		$payableTable = new Shared_Model_Data_AccountPayable();
		
		$dbAdapter = $payableTable->getAdapter();

        $selectObj = $payableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_INVOICE);
		$selectObj->where('frs_account_payable.status != ?', Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
		$selectObj->order('frs_account_payable.paying_plan_date DESC');
        
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
    |  action_URL    * /transaction-payable/update-to-draft                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 下書きに戻す(管理権限あり)(Ajax)                           |
    +----------------------------------------------------------------------------*/
    public function updateToDraftAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable = new Shared_Model_Data_AccountPayable();

			try {
				$payableTable->getAdapter()->beginTransaction();
				
				$payableTable->updateById($id, array(
					'status' => Shared_Model_Code::PAYABLE_STATUS_DRAFT,
				));
			
                // commit
                $payableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/update-to-draft transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/delete                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(管理権限あり)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable = new Shared_Model_Data_AccountPayable();

			try {
				$payableTable->getAdapter()->beginTransaction();
				
				$payableTable->updateById($id, array(
					'status' => Shared_Model_Code::PAYABLE_STATUS_DELETED,
				));
			
                // commit
                $payableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
       

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/invoice-list                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請                                               |
    +----------------------------------------------------------------------------*/
    public function invoiceListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_payable_invoice_list');
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['status']              = $request->getParam('status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']              = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
		}
		
		$this->view->conditions = $conditions = $session->conditions;
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		
		$dbAdapter = $payableTable->getAdapter();

        $selectObj = $payableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
		$selectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_INVOICE);
		$selectObj->where('frs_account_payable.status != ?', Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
		

        if (!empty($session->conditions['status'])) {
        	$selectObj->where('frs_account_payable.status = ?', $session->conditions['status']);
        } else {
        	$selectObj->where('frs_account_payable.status != ?', Shared_Model_Code::ORDER_FORM_STATUS_DELETED);
        }

        if (!empty($session->conditions['currency_id'])) {
        	$selectObj->where('frs_account_payable.currency_id = ?', $session->conditions['currency_id']);
        }

        if (!empty($session->conditions['account_title_id'])) {
        	$selectObj->where('frs_account_payable.account_title_id = ?', $session->conditions['account_title_id']);
        }
        
        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('frs_account_payable.target_connection_id = ?', $session->conditions['connection_id']);
        }

        if ($session->conditions['applicant_user_id'] !== '') {
        	$selectObj->where('frs_account_payable.created_user_id = ?', $session->conditions['applicant_user_id']);
        }  
   
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        
        var_dump($selectObj->__toString());
        
        $selectObj->order('frs_account_payable.paying_plan_date DESC');
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        }
		$this->view->currencyList = $currencyList;	 	
    }

    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/select-type                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 種別選択(ポップアップ用)                                   |
    +----------------------------------------------------------------------------*/
    public function selectTypeAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/invoice-add                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 登録                                        |
    +----------------------------------------------------------------------------*/
    public function invoiceAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/add-post                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 登録(Ajax)                                  |
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
				
				if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払先取引先」を入力してください'));
                    return;
                } else if (!empty($errorMessage['paying_method']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                    return;
                } else if (!empty($errorMessage['purchased_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payingPlanDate = NULL;
				$payingBankId = 0;
				$payingCardId = 0;
	            if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK
	                || $success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	                
	                $payingCardId = $success['paying_card_id'];
	                
	                // 支払日自動計算
	                $cardTable = new Shared_Model_Data_AccountCreditCard();	
					$cardData  = $cardTable->getById($payingCardId);
					
					$zPurchaedDate = new Zend_Date($success['purchased_date'], NULL, 'ja_JP');
					$purchasedYear   = $zPurchaedDate->get(Zend_Date::YEAR);
					$purchasedMonth  = $zPurchaedDate->get(Zend_Date::MONTH);
					$purchasedDay    = $zPurchaedDate->get(Zend_Date::DAY);
					
					$zClosingDate = NULL;
					$zPaymentDate = NULL;
					if ($cardData['closing_day'] === '99') {
						$monthEndDay = Nutex_Date::getMonthEndDay($purchasedYear, $purchasedMonth);
						$zClosingDate = new Zend_Date($purchasedYear . '-' . $purchasedMonth . '-' . $monthEndDay, NULL, 'ja_JP');
					} else {
						$zClosingDate = new Zend_Date($purchasedYear . '-' . $purchasedMonth . '-' . $cardData['closing_day'], NULL, 'ja_JP');
					}
					$zPaymentDate = new Zend_Date($purchasedYear . '-' . $purchasedMonth . '-' . $cardData['payment_day'], NULL, 'ja_JP');
					//echo $zPurchaedDate->get('yyyy-MM-dd');
	                //echo $zClosingDate->get('yyyy-MM-dd');
	                //exit;
					
					if ($zPurchaedDate->isEarlier($zClosingDate) || $zPurchaedDate->equals($zClosingDate)) {
						$zPaymentDate->add('1', Zend_Date::MONTH);
					} else {
						$zPaymentDate->add('2', Zend_Date::MONTH);
					}
					
					$payingPlanDate = $zPaymentDate->get('YYYY-MM-dd');
	                //echo $payingPlanDate;
	                //exit;
	                
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	                
	            }
			
				$payableTable    = new Shared_Model_Data_AccountPayable();
				$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
				
				$orderFormIds      = array();
				//$referenceItemList = array();
	            if (!empty($success['order_form_list'])) {
	            	$orderFormList = explode(',', $success['order_form_list']);
	            	
		            foreach ($orderFormList as $eachId) {
		            	$referenceTargetId = $request->getParam($eachId . '_order_form_id');
		            	if (!empty($referenceTargetId)) {
		            		$orderFormIds[] = $referenceTargetId;  
		            		
			                $referenceItemList[] = array(
				                'order_form_id' => $request->getParam($eachId . '_order_form_id'),
			                );
		            	}
		            }
	            }
	            

	            
				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
                
            	//$displayId = $payableTable->getNextDisplayId();
            	
				$data = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'status'                  => Shared_Model_Code::PAYABLE_STATUS_DRAFT, // 下書き
			        
			        'order_form_ids'          => serialize($orderFormIds),              // 発注IDリスト
			        
					'account_title_id'        => $success['account_title_id'],          // 会計科目ID
					'target_connection_id'    => $success['target_connection_id'],      // 支払先
					
					'paying_type'             => $success['paying_type'],               // 支払種別(請求支払/カード支払/自動振替)

					'file_list'               => json_encode($fileList),                // 請求書ファイルアップロード
					
					'paid_user_id'            => 0,                                     // 支払処理担当者
					'paid_date'               => NULL,                                  // 支払完了日
					
					'paying_method'           => $success['paying_method'],             // 支払方法
					'paying_bank_id'          => $payingBankId,                         // 支払元銀行口座
					'paying_card_id'          => $payingCardId,                         // 支払元クレジットカード
					'paying_method_memo'      => $success['paying_method_memo'],        // 支払方法メモ
					
					
					'purchased_date'          => $success['purchased_date'],            // 発生日
					
					'transfer_to_connection_bank_id' => 0,                                                 // 振込先 取引先金融機関ID
					'bank_registered_type'           => Shared_Model_Code::BANK_REGISTERED_TYPE_FASS,      // 連携元 登録種別
					'target_id'                      => 0,                                                 // 連携元 サプライヤーID/BuyerID
					
					
					'created_user_id'         => $this->_adminProperty['id'],           // 支払申請者
					'approval_user_id'        => 0,                                     // 承認者
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);

				if (!empty($payingPlanDate)) {
					$data['paying_plan_date'] = $payingPlanDate; // 支払予定日
				}
				
				// 新規登録	            
	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$payableTable->create($data);
					$id = $payableTable->getLastInsertedId('id');

					if (!empty($orderFormIds)) {
						foreach ($orderFormIds as $eachId) {
							$orderData =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $eachId);
							
							if (!empty($orderData['payable_ids'])) {
								if (!in_array($id, $orderData['payable_ids'])) {
									$orderData['payable_ids'][] = $id;
									
									$orderFormTable->updateById($orderData['id'], array(
										'payable_ids' => serialize($orderData['payable_ids']),
									));
								}
							} else {
								$orderFormTable->updateById($orderData['id'], array(
									'payable_ids' => serialize(array($id))
								));
							}
						}
					}

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Payable::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/invoice-add-post transaction failed: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/invoice-detail                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function invoiceDetailAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->direct      = $direct     = $request->getParam('direct', 0);
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			if (empty($direct)) {
				$this->view->backUrl = '/transaction-payable/invoice-list';
			}
			$this->_helper->layout->setLayout('back_menu');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        	$this->view->saveButtonName = '承認申請';
		        }
	        }
		}
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		// 銀行口座
		if (!empty($data['paying_bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
		}
		
		// クレジット
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}

        // ネット購入委託
        if (!empty($data['online_purchase_id'])) {
	        $onlinePurchaseTable = new Shared_Model_Data_OnlinePurchase();
	        $this->view->onlinePurchaseData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $data['online_purchase_id']);
	        
        }
        
        if (!empty($data['transfer_to_connection_bank_id'])) {
        	$connectionBankTable = new Shared_Model_Data_ConnectionBank();
        	$this->view->connectionBankData = $connectionBankTable->getById($data['transfer_to_connection_bank_id']);
        }
        
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-basic                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 基本情報更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function updateBasicAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
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
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				if (empty($success['account_totaling_group_id'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                }

				$payableTable = new Shared_Model_Data_AccountPayable();
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'account_title_id'          => $success['account_title_id'],          // 会計科目ID
						'memo'                      => $success['memo'],                      // 摘要
						'account_totaling_group_id' => $success['account_totaling_group_id'], // 採算コードID
					);
					
					$payableTable->updateById($id, $data);

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/update-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-summary                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 概要更新(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function updateSummaryAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払先取引先」を入力してください'));
                    return;  
                } else if (!empty($errorMessage['total_amount']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払総額(税込) 」を入力してください'));
                    return;
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['tax_division']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「税区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['purchased_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                    return; 
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable = new Shared_Model_Data_AccountPayable();
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'target_connection_id'    => $success['target_connection_id'],      // 支払先
						'total_amount'            => $success['total_amount'],              // 支払額
						'currency_id'             => $success['currency_id'],               // 通貨単位
						'tax_division'            => $success['tax_division'],              // 税区分
						'tax'                     => $success['tax'],                       // 消費税
						
						'purchased_date'          => $success['purchased_date'],            // 発生日
					);
					
					$payableTable->updateById($id, $data);

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/update-summary transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-order-list                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 対象発注更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function updateOrderListAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable    = new Shared_Model_Data_AccountPayable();
				$orderFormTable  = new Shared_Model_Data_DirectOrderForm();
				
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
				
				$orderFormIds      = array();
				//$referenceItemList = array();
	            if (!empty($success['order_form_list'])) {
	            	$orderFormList = explode(',', $success['order_form_list']);
	            	
		            foreach ($orderFormList as $eachId) {
		                $orderFormIds[] = $request->getParam($eachId . '_order_form_id');
		                
		                $referenceItemList[] = array(
			                'order_form_id' => $request->getParam($eachId . '_order_form_id'),
		                );
		            }
	            }
				
	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'order_form_ids'      => serialize($orderFormIds),
						//'reference_item_list' => json_encode($referenceItemList),
					);

					$payableTable->updateById($id, $data);
					
					if (!empty($oldData['order_form_ids'])) {
						foreach ($oldData['order_form_ids'] as $eachOldOrderFormId) {
							if (!in_array($eachOldOrderFormId, $orderFormIds)) {
								$orderData =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $eachOldOrderFormId);
								$newIds = array();
								if (!empty($orderData['payable_ids'])) {
									foreach ($orderData['payable_ids'] as $payableId) {
										if ($id != $payableId) {
											$newIds[] = $payableId;
										}
									}
								}
								$orderFormTable->updateById($eachOldOrderFormId, array(
									'payable_ids' => serialize($newIds),
								));
							}
						}
					}
					
					if (!empty($orderFormIds)) {
						foreach ($orderFormIds as $eachId) {
							$orderData =  $orderFormTable->getById($this->_adminProperty['management_group_id'], $eachId);
							
							if (!empty($orderData['payable_ids'])) {
								if (!in_array($id, $orderData['payable_ids'])) {
									$orderData['payable_ids'][] = $id;
									
									$orderFormTable->updateById($orderData['id'], array(
										'payable_ids' => serialize($orderData['payable_ids']),
									));
								}
							} else {
								$orderFormTable->updateById($orderData['id'], array(
									'payable_ids' => serialize(array($id))
								));
							}
						}
					}

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/update-order-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-paying                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 支払方法更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function updatePayingAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['paying_plan_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を入力してください'));
                    return;
                } else if (!empty($errorMessage['paying_method']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                    return; 
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable = new Shared_Model_Data_AccountPayable();
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
				
				$payingBankId = 0;
				$payingCardId = 0;
	            if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK) {
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	            	$payingCardId = $success['paying_card_id'];
	                
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	                
	            }
	            
	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'paying_plan_date'        => $success['paying_plan_date'],          // 支払予定日
						
						'paying_method'           => $success['paying_method'],             // 支払方法
						
						'paying_bank_id'          => $payingBankId,                         // 支払元銀行口座
						'paying_card_id'          => $payingCardId,                         // 支払元クレジットカード
						
						'paying_method_memo'      => $success['paying_method_memo'],        // 支払方法メモ
					);
					
					$payableTable->updateById($id, $data);

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/update-paying transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-file-list                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 請求書ファイルアップロード更新(Ajax)        |
    +----------------------------------------------------------------------------*/
    public function updateFileListAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable = new Shared_Model_Data_AccountPayable();
				
				$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'file_list' => json_encode($fileList), // 請求書ファイルアップロード
					);

					$payableTable->updateById($id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_Payable::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/update-file-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/update-transfer                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 振込先口座(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function updateTransferAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id      = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/transaction-payable/update-transfer failed to load config');
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
				$payableTable = new Shared_Model_Data_AccountPayable();
				
				$payableTable->getAdapter()->beginTransaction();

				try {
					$data = array();
					
					if (!empty($success['transfer_to_connection_bank_id'])) {
						$data['transfer_to_connection_bank_id'] = $success['transfer_to_connection_bank_id'];     // 振込先 取引先金融機関ID
						
					}
					
					if (!empty($success['transfer_to_bank_code'])) {
						
			            if (empty($success['transfer_to_bank_code'])) {
			                $this->sendJson(array('result' => 'NG', 'message' => '「金融機関コード」を入力してください'));
			                return;
			            } else if (empty($success['transfer_to_bank_name'])) {
			                $this->sendJson(array('result' => 'NG', 'message' => '「金融機関名」を入力してください'));
							return;
			            } else if (empty($success['transfer_to_branch_code'])) {
			                $this->sendJson(array('result' => 'NG', 'message' => '「支店コード」を入力してください'));
							return;
			            } else if (empty($success['transfer_to_branch_name'])) {
				            $this->sendJson(array('result' => 'NG', 'message' => '「支店コード」を入力してください'));
							return;
			
			            } else if (empty($success['transfer_to_account_type'])) {
			                $this->sendJson(array('result' => 'NG', 'message' => '「口座種別」を入力してください'));
							return;
			
			            } else if (empty($success['transfer_to_account_no'])) {
			                $this->sendJson(array('result' => 'NG', 'message' => '「口座番号」を入力してください'));
							return;
			
			            } else if (empty($success['transfer_to_account_name'])) {
				            $this->sendJson(array('result' => 'NG', 'message' => '「口座名義(カタカナ)」を入力してください'));
							return;
			            }
			            
					    // 口座名義(カナ) 
		                $success['transfer_to_account_name'] = str_replace('（', '(', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = str_replace('）', ')', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = str_replace('ー', '-', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = str_replace('／', '/', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = str_replace('．', '.', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = str_replace('，', ',', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = str_replace('　', ' ', $success['transfer_to_account_name']);
		                $success['transfer_to_account_name'] = strtoupper($success['transfer_to_account_name']);             // 大文字に変換
						$success['transfer_to_account_name'] = mb_convert_kana($success['transfer_to_account_name'], 'krn'); // 全角英字を半角・全角数字を半角・全角カナを半角カナ
						
						$valid = Shared_Model_Utility_Text::bankStringValid($success['transfer_to_account_name']);
						
						if (!$valid) {
							$this->sendJson(array('result' => 'NG', 'message' => '「口座名義(カナ) 」に利用できない文字が含まれています'));
			            	return;
						}
				
			            
						$data['transfer_to_bank_code']        = $success['transfer_to_bank_code'];
				        $data['transfer_to_bank_name']        = $success['transfer_to_bank_name'];
						$data['transfer_to_branch_code']      = $success['transfer_to_branch_code'];
						$data['transfer_to_branch_name']      = $success['transfer_to_branch_name'];
						$data['transfer_to_account_type']     = $success['transfer_to_account_type'];
						$data['transfer_to_account_no']       = $success['transfer_to_account_no'];
						$data['transfer_to_account_name']     = $success['transfer_to_account_name'];
					}
					
					$payableTable->updateById($id, $data);
					
					// commit
					$payableTable->getAdapter()->commit();
	           
				} catch (Exception $e) {
	            	$payableTable->getAdapter()->rollBack();
					throw new Zend_Exception('/transaction-payable/update-transfer transaction failed: ' . $e);
					
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
		    }
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	  
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/apply-apploval                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 承認申請                                      |
    +----------------------------------------------------------------------------*/
    public function applyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$payableTable  = new Shared_Model_Data_AccountPayable();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        	
			$oldData = $payableTable->getById($this->_adminProperty['management_group_id'], $id);


			if (empty($oldData['account_title_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return; 
            } else if (empty($oldData['account_totaling_group_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                return; 
			} else if (empty($oldData['total_amount'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払総額(税込)」を入力してください'));
                return; 
			} else if (empty($oldData['tax_division'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「税区分」を選択してください'));
                return;
			} else if (empty($oldData['paying_plan_date'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を入力してください'));
                return;
			} else if (empty($oldData['paying_method'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                return; 
			} else if (empty($oldData['purchased_date'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「発生日」を入力してください'));
                return; 
            }

			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $oldData['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $oldData['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $oldData['currency_id']);

            $templateData = NULL;
            
			try {
				$payableTable->getAdapter()->beginTransaction();
				
				$payableStatus = Shared_Model_Code::PAYABLE_STATUS_PENDING;
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_PAYABLE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $connectionData['company_name'] . "\n支払総額：" .  number_format($oldData['total_amount']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
		
				if ($oldData['paying_type'] === (string)Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD) {
					$approvalData['type'] = Shared_Model_Code::APPROVAL_TYPE_PAYABLE_CARD;
					
				} else if ($oldData['paying_type'] === (string)Shared_Model_Code::PAYABLE_PAYING_TYPE_MONTHLY) {
					$approvalData['type'] = Shared_Model_Code::APPROVAL_TYPE_PAYABLE_MONTHLY;
					
					$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
					$templateData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $oldData['template_id']);
					
					if ($templateData['template_type'] == (string)Shared_Model_Code::PAYABLE_TEMPLATE_TYPE_FIXED) {
						// 毎月支払 固定費用の場合は自動承認
						$payableStatus = Shared_Model_Code::PAYABLE_STATUS_APPROVED;
					}
				}

				$payableTable->updateById($id, array(
					'status' => $payableStatus,
				));
				
				
				if ($payableStatus != Shared_Model_Code::PAYABLE_STATUS_APPROVED) {
					// 毎月支払 固定費用の場合は承認申請しない
					$approvalTable->create($approvalData);
					
					// メール送信 -------------------------------------------------------
					$content = "支払先取引先：\n" . $connectionData['company_name'] . "\n\n"
							 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
					         . "支払総額：\n" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'];
					
					$groupTable  = new Shared_Model_Data_ManagementGroup();
					$groupData = $groupTable->getById($userData['management_group_id']);
					
					// 承認者
					$selectObj = $userTable->select();
			    	$selectObj->where('id = ?', $userData['approver_c1_user_id']);
			        $authorizerUserData = $selectObj->query()->fetch();
		        
					$approvalTypeList   = Shared_Model_Code::codes('approval_type');
					$mailInput = array(
						'managment_group_name' => $groupData['organization_name'],
						'to'       => $authorizerUserData['mail'],
						'cc'       => array(),
						'type'     => $approvalTypeList[$approvalData['type']],
						'content'  => $content,
						'name'     => $userData['user_name'],
						'user_id'  => $userData['display_id'],
					);		
	
					$mailer = new Shared_Model_Mail_Approval();
					$mailer->sendMail($mailInput);
					// -------------------------------------------------------------------
				}
				
                // commit
                $payableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/apply-apploval transaction faied: ' . $e);
                
            }
			
			if (!empty($templateData)) {
				if ($templateData['template_type'] == (string)Shared_Model_Code::PAYABLE_TEMPLATE_TYPE_FIXED) {
				    $this->sendJson(array('result' => 'OK', 'message' => '固定費用のため、自動承認されました'));
			    	return;
				}
			}
			
		    $this->sendJson(array('result' => 'OK', 'message' => '承認申請しました'));
	    	return;

		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/mod-request                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 修正依頼(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function modRequestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable  = new Shared_Model_Data_AccountPayable();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();

			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
			
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);

			try {
				$payableTable->getAdapter()->beginTransaction();
				
				$payableTable->updateById($id, array(
					'status'           => Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));
				
				$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-payable/invoice-detail?id=' . $id;
				
				if ($data['paying_type'] === (string)Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD) {
					$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-payable/card-detail?id=' . $id;
				}
					
				// メール送信 -------------------------------------------------------
				$content = "支払先取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . $url;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $payableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/approve                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 承認(Ajax)                                                 |
    +----------------------------------------------------------------------------*/
    public function approveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTable  = new Shared_Model_Data_AccountPayable();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();

			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
			
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable    = new Shared_Model_Data_Currency();
			$currencyData = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
			try {
				$payableTable->getAdapter()->beginTransaction();
				
				$payableTable->updateById($id, array(
					'status'           => Shared_Model_Code::PAYABLE_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-payable/invoice-detail?id=' . $id;
				
				if ($data['paying_type'] === (string)Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD) {
					$url = HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-payable/card-detail?id=' . $id;
				}
				
				// メール送信 -------------------------------------------------------
				$content = "支払先取引先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . $url;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $payableTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/card-list                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード支払申請                                             |
    +----------------------------------------------------------------------------*/
    public function cardListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_payable_card_list');
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$conditions = array();
			$session->conditions['status']              = $request->getParam('status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['card_name']           = $request->getParam('card_name', '');
			$session->conditions['card_id']             = $request->getParam('card_id', '');

		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']              = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['card_name']           = '';
			$session->conditions['card_id']             = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$dbAdapter = $payableTable->getAdapter();

        $selectObj = $payableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
        
        // グループID
        $selectObj->where('frs_account_payable.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        $selectObj->where('frs_account_payable.paying_type = ?', Shared_Model_Code::PAYABLE_PAYING_TYPE_CREDIT_CARD);
		$selectObj->where('frs_account_payable.status != ?', Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);
		
		
        if (!empty($session->conditions['status'])) {
        	$selectObj->where('frs_account_payable.status = ?', $session->conditions['status']);
        } else {
        	$selectObj->where('frs_account_payable.status != ?', Shared_Model_Code::ORDER_FORM_STATUS_DELETED);
        }

        if (!empty($session->conditions['currency_id'])) {
        	$selectObj->where('frs_account_payable.currency_id = ?', $session->conditions['currency_id']);
        }

        if (!empty($session->conditions['account_title_id'])) {
        	$selectObj->where('frs_account_payable.account_title_id = ?', $session->conditions['account_title_id']);
        }
        
        if (!empty($session->conditions['connection_id'])) {
        	$selectObj->where('frs_account_payable.target_connection_id = ?', $session->conditions['connection_id']);
        }

        if ($session->conditions['applicant_user_id'] !== '') {
        	$selectObj->where('frs_account_payable.created_user_id = ?', $session->conditions['applicant_user_id']);
        }  
   
        if (!empty($session->conditions['keyword'])) {
        	// TODO
        }
        
		
		$selectObj->order('frs_account_payable.paying_plan_date DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        }
		$this->view->currencyList = $currencyList; 	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/card-add                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カード支払申請 - 登録                                      |
    +----------------------------------------------------------------------------*/
    public function cardAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/card-detail                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function cardDetailAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);

		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/transaction-payable/card-list';
			$this->_helper->layout->setLayout('back_menu_competition');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        }
	        }
		}
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
        // ネット購入委託
        if (!empty($data['online_purchase_id'])) {
	        $onlinePurchaseTable = new Shared_Model_Data_OnlinePurchase();
	        $this->view->onlinePurchaseData = $onlinePurchaseTable->getById($this->_adminProperty['management_group_id'], $data['online_purchase_id']);
	        
        }
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-list                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理                                               |
    +----------------------------------------------------------------------------*/
    public function templateListAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('transaction_payable_template_list_1');
		$this->view->posTop      = $request->getParam('pos');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['status']              = $request->getParam('status', '');
			$session->conditions['currency_id']         = $request->getParam('currency_id', '');
			$session->conditions['account_title_name']  = $request->getParam('account_title_name', '');
			$session->conditions['account_title_id']    = $request->getParam('account_title_id', '');
			$session->conditions['template_type']       = $request->getParam('template_type', '');
			$session->conditions['applicant_user_name'] = $request->getParam('applicant_user_name', '');
			$session->conditions['applicant_user_id']   = $request->getParam('applicant_user_id', '');
			
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			$session->conditions['description']         = $request->getParam('description', '');
			
		} else if (empty($session->conditions) || !array_key_exists('status', $session->conditions)) {
			$session->conditions['status']              = '';
			$session->conditions['currency_id']         = '';
			$session->conditions['account_title_name']  = '';
			$session->conditions['account_title_id']    = '';
			$session->conditions['template_type']       = '';
			$session->conditions['applicant_user_name'] = '';
			$session->conditions['applicant_user_id']   = '';
			
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			$session->conditions['description']             = '';
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		
		$dbAdapter = $payableTemplateTable->getAdapter();
		
		// アクティブ
        $selectObj = $payableTemplateTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable_template.target_connection_id = frs_connection.id', array($payableTemplateTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_payable_template.created_user_id = frs_user.id',array($payableTemplateTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		
        $selectObj->where('frs_account_payable_template.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
        
		if ($session->conditions['status'] !== '') {
        	if ($session->conditions['status'] === (string)Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_NOT_APPROVED) {
	        	$selectObj->where('frs_account_payable_template.status != ' . Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_APPROVED
	        	           . ' AND frs_account_payable_template.status != ' . Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_FINISHED
	        	           . ' AND frs_account_payable_template.status != ' . Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_DELETED);          
        	} else {
        		$selectObj->where('frs_account_payable_template.status = ?', $session->conditions['status']);
        	}
		} else {
			$selectObj->where('frs_account_payable_template.status != ?', Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_DELETED);
		}
		
		if ($session->conditions['currency_id'] !== '') {
			$selectObj->where('frs_account_payable_template.currency_id = ?', $session->conditions['currency_id']);
		}
		
		if ($session->conditions['account_title_id'] !== '') {
			$selectObj->where('frs_account_payable_template.account_title_id = ?', $session->conditions['account_title_id']);
		}
		
		if ($session->conditions['applicant_user_id'] !== '') {
			$selectObj->where('frs_account_payable_template.created_user_id = ?', $session->conditions['applicant_user_id']);
		}

		if ($session->conditions['connection_id'] !== '') {
			$selectObj->where('frs_account_payable_template.target_connection_id = ?', $session->conditions['connection_id']);
		}

		if ($session->conditions['description'] !== '') {
			$likeString = $dbAdapter->quoteInto($payableTemplateTable->aesdecrypt('frs_account_payable_template.description', false) . ' LIKE ?', '%' . $session->conditions['description'] .'%');
			$selectObj->where($likeString);
		}
		$selectObj->where('frs_account_payable_template.status NOT IN (?)', array(Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_DELETED, Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_FINISHED));
		$selectObj->order('frs_account_payable_template.account_title_id ASC');
		$selectObj->order('frs_account_payable_template.id ASC');

        $this->view->items = $selectObj->query()->fetchAll();
		
		// 毎月支払い終了
		$selectObjF = $payableTemplateTable->select();
		$selectObjF->where('frs_account_payable_template.management_group_id = ?', $this->_adminProperty['management_group_id']);// グループID
		$selectObjF->where('frs_account_payable_template.status = ?', Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_FINISHED);
		$this->view->itemsFinished = $selectObjF->query()->fetchAll();
		
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        }
		$this->view->currencyList = $currencyList;	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-update-to-draft              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 下書きに戻す(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function templateUpdateToDraftAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();

			try {
				$payableTemplateTable->getAdapter()->beginTransaction();
				
				$payableTemplateTable->updateById($id, array(
					'status' => Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_DRAFT,
				));
			
                // commit
                $payableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/update-to-draft transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-finished                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払終了(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function templateFinishedAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();

			try {
				$payableTemplateTable->getAdapter()->beginTransaction();
				
				$payableTemplateTable->updateById($id, array(
					'status' => Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_FINISHED,
				));
			
                // commit
                $payableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/template-finished transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-add                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 登録                                        |
    +----------------------------------------------------------------------------*/
    public function templateAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-add-post                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 登録(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function templateAddPostAction()
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
				
				if (!empty($errorMessage['template_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払種別」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払先取引先」を入力してください'));
                    return;
                } else if (!empty($errorMessage['paying_method']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                    return; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payingPlanDate = NULL;
				$payingBankId = 0;
				$payingCardId = 0;
	            if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK
	                || $success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	                
	                $payingCardId = $success['paying_card_id'];
	                
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	                
	            }
			
				$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
	            
				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }

				$data = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'status'                  => Shared_Model_Code::PAYABLE_TEMPLATE_STATUS_DRAFT,
			        
			        'template_type'           => $success['template_type'],             // テンプレート種別
			        
					'account_title_id'        => $success['account_title_id'],          // 会計科目ID
					'account_totaling_group_id'=> $success['account_totaling_group_id'],  // 採算コード
					'target_connection_id'    => $success['target_connection_id'],      // 支払先
					
					'paying_plan_monthly_day' => $success['paying_plan_monthly_day'],   // 毎月支払時期
					
					'description'             => $success['description'],               // 内容
					'other_memo'              => $success['other_memo'],                // 備考
					
					'file_list'               => json_encode($fileList),                // 請求書ファイルアップロード
					
					'paying_method'           => $success['paying_method'],             // 支払方法
					'paying_method_memo'      => $success['paying_method_memo'],        // 支払方法メモ
					
					'paying_bank_id'          => $payingBankId,                         // 支払元銀行口座
					'paying_card_id'          => $payingCardId,                         // 支払元クレジットカード
					
					'created_user_id'         => $this->_adminProperty['id'],           // 支払申請者
					'approval_user_id'        => 0,                                     // 承認者
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);
				
				// 新規登録	            
	            $payableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$payableTemplateTable->create($data);
					$id = $payableTemplateTable->getLastInsertedId('id');
					
		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_PayableTemplate::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }

	                // commit
	                $payableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/template-add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-detail                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function templateDetailAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$this->view->data = $data = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/transaction-payable/template-list';
			$this->_helper->layout->setLayout('back_menu_competition');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        }
	        }
		}  
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

		// 銀行口座
		if (!empty($data['paying_bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
		}
		
		// クレジットカード
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-update-basic                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 基本情報更新(Ajax)                          |
    +----------------------------------------------------------------------------*/
    public function templateUpdateBasicAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['template_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払種別」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_title_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                    return;
				} else if (!empty($errorMessage['account_totaling_group_id']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['target_connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払先取引先」を入力してください'));
                    return;
                } if (!empty($errorMessage['total_amount']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払総額(税込) 」を入力してください'));
                    return;
                } else if (!empty($errorMessage['currency_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を選択してください'));
                    return; 
                } else if (!empty($errorMessage['tax_division']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「税区分」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				if (empty($success['account_totaling_group_id'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                    return;
                }
                
				$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
				$oldData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

	            $payableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'template_type'           => $success['template_type'],             // 支払種別
						'description'             => $success['description'],               // 内容
						'other_memo'              => $success['other_memo'],                // 備考
						
						'account_title_id'        => $success['account_title_id'],          // 会計科目ID
						'account_totaling_group_id'=> $success['account_totaling_group_id'],  // 採算コード
						'target_connection_id'    => $success['target_connection_id'],      // 支払先
						'total_amount'            => $success['total_amount'],              // 支払額
						'currency_id'             => $success['currency_id'],               // 通貨単位
						'tax_division'            => $success['tax_division'],              // 税区分
						'tax'                     => $success['tax'],                       // 消費税
						
					);
					
					$payableTemplateTable->updateById($id, $data);

	                // commit
	                $payableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/update-template-basic transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-update-paying                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 -支払方法更新(Ajax)                           |
    +----------------------------------------------------------------------------*/
    public function templateUpdatePayingAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['paying_method']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                    return; 
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();

				$payingBankId = 0;
				$payingCardId = 0;
	            if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK
	              || $success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 銀行振込
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	            	
	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
	            	// クレジットカード
	            	if (empty($success['paying_card_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
			    		return;
	            	}
	            	$payingCardId = $success['paying_card_id'];

	            } else if ($success['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
	            	// 自動振替
	            	if (empty($success['paying_bank_id'])) {
					    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
			    		return;
	            	}
	            	$payingBankId = $success['paying_bank_id'];
	                
	            }
	            
				$oldData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
				
	            $payableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'paying_plan_monthly_day' => $success['paying_plan_monthly_day'],   // 毎月支払時期	
						'paying_method'           => $success['paying_method'],             // 支払方法
						
						'paying_bank_id'          => $payingBankId,                         // 支払元銀行口座
						'paying_card_id'          => $payingCardId,                         // 支払元クレジットカード
						
						'paying_method_memo'      => $success['paying_method_memo'],        // 支払方法メモ
					);
					
					$payableTemplateTable->updateById($id, $data);

	                // commit
	                $payableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/template-update-paying transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-update-file-list             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 参考資料ファイルアップロード更新(Ajax)      |
    +----------------------------------------------------------------------------*/
    public function templateUpdateFileListAction()
    {
       	$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
				
				$oldData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

				$fileList = array();	
	            if (!empty($success['file_list'])) {
	            	$fileIdList = explode(',', $success['file_list']);
	            	
		            foreach ($fileIdList as $eachId) {
		                $fileList[] = array(
							'id'               => $eachId,
							'file_name_text'   => $request->getParam($eachId . '_file_name_text'),
							'file_name'        => $request->getParam($eachId . '_file_name'),
							'summary'          => $request->getParam($eachId . '_summary'),
		                );
		            }
	            }
	            
	            $payableTemplateTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'file_list' => json_encode($fileList), // 請求書ファイルアップロード
					);

					$payableTemplateTable->updateById($id, $data);

		            if (!empty($success['file_list'])) {
		            	$fileIdList = explode(',', $success['file_list']);
		            	
			            foreach ($fileIdList as $eachId) {
							$tempFileName = $request->getParam($eachId . '_temp_file_name');
		            		$fileName     = $request->getParam($eachId . '_file_name');
	
							if (!empty($tempFileName)) {
			            		// 正式保存
			            		$result = Shared_Model_Resource_PayableTemplate::makeResource($id, $eachId, $fileName, Shared_Model_Resource_TemporaryPrivate::getBinary($tempFileName));
			            		
				            	// tempファイルを削除
								Shared_Model_Resource_TemporaryPrivate::removeResource($tempFileName);								
			                }
			            }
		            }
		            
	                // commit
	                $payableTemplateTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTemplateTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/template-update-file-list transaction failed: ' . $e);  
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-apply-apploval               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 承認申請                                      |
    +----------------------------------------------------------------------------*/
    public function templateApplyApplovalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$payableTemplateTable  = new Shared_Model_Data_AccountPayableTemplate();
			$approvalTable = new Shared_Model_Data_Approval();
			$userTable     = new Shared_Model_Data_User();
			
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $this->_adminProperty['id']);
	        $userData = $selectObj->query()->fetch();
	        	
			$oldData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
			

			if (empty($oldData['template_type'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払種別」を選択してください'));
                return; 
			} else if (empty($oldData['account_title_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「会計科目」を選択してください'));
                return; 
			} else if (empty($oldData['account_totaling_group_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「採算コード」を選択してください'));
                return;
			} else if (empty($oldData['description'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「内容」を入力してください'));
                return;
			} else if (empty($oldData['target_connection_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払先取引先」を入力してください'));
                return;
			} else if (empty($oldData['total_amount'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「支払総額(税込) 」を入力してください'));
                return;
			} else if (empty($oldData['currency_id'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「通貨単位」を入力してください'));
                return; 
			} else if (empty($oldData['tax_division'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '「税区分」を入力してください'));
                return; 
			}

            if (empty($oldData['paying_method'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「支払方法」を選択してください'));
                return; 
            }
                
            if ($oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_BANK
                || $oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
            	// 銀行振込
            	if (empty($oldData['paying_bank_id'])) {
				    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
		    		return;
            	}
            	
            } else if ($oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_CREDIT) {
            	// クレジットカード
            	if (empty($oldData['paying_card_id'])) {
				    $this->sendJson(array('result' => 'NG', 'message' => '「支払用クレジットカード」を選択してください'));
		    		return;
            	}
                
            } else if ($oldData['paying_method'] === (string)Shared_Model_Code::PAYABLE_PAYING_METHOD_AUTO) {
            	// 自動振替
            	if (empty($oldData['paying_bank_id'])) {
				    $this->sendJson(array('result' => 'NG', 'message' => '「支払元銀行口座」を選択してください'));
		    		return;
            	}
                
            }
            
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $oldData['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData  = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $oldData['account_title_id']);
			
			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $oldData['currency_id']);
			
			try {
				$payableTemplateTable->getAdapter()->beginTransaction();
				
				$payableTemplateTable->updateById($id, array(
					'status' => Shared_Model_Code::PAYABLE_STATUS_PENDING,
				));
				
				$approvalData = array(
			        'management_group_id'   => $this->_adminProperty['management_group_id'],
			        'status'                => Shared_Model_Code::APPROVAL_STATUS_PENDDING,
					'type'                  => Shared_Model_Code::APPROVAL_TYPE_PAYABLE_TEMPLATE,
					
					'authorizer_user_id'    => $userData['approver_c1_user_id'], // 承認者ユーザーID
					'applicant_user_id'     => $this->_adminProperty['id'],      // 申請者ユーザーID
					
					'target_id'             => $id,
					
					'title'                 => $connectionData['company_name'] . "\n" . "支払総額：" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'],
					
	                'created'               => new Zend_Db_Expr('now()'),
	                'updated'               => new Zend_Db_Expr('now()'),
				);
				
				$approvalTable->create($approvalData);

				// メール送信 -------------------------------------------------------
				$content = "支払先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($oldData['total_amount']) . ' ' . $currencyData['name'];
				
				$groupTable  = new Shared_Model_Data_ManagementGroup();
				$groupData = $groupTable->getById($userData['management_group_id']);

				// 承認者
				$selectObj = $userTable->select();
		    	$selectObj->where('id = ?', $userData['approver_c1_user_id']);
		        $authorizerUserData = $selectObj->query()->fetch();
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'managment_group_name' => $groupData['organization_name'],
					'to'       => $authorizerUserData['mail'],
					'cc'       => array(),
					'type'     => $approvalTypeList[$approvalData['type']],
					'content'  => $content,
					'name'     => $userData['user_name'],
					'user_id'  => $userData['display_id'],
				);		

				$mailer = new Shared_Model_Mail_Approval();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
					
                // commit
                $payableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/template-apply-apploval transaction faied: ' . $e);    
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-mod-request                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 修正依頼(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function templateModRequestAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTemplateTable  = new Shared_Model_Data_AccountPayableTemplate();
			$approvalTable         = new Shared_Model_Data_Approval();
			$userTable             = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
			
            
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData  = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
			try {
				$payableTemplateTable->getAdapter()->beginTransaction();
				
				$payableTemplateTable->updateById($id, array(
					'status'           => Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
				));

				// メール送信 -------------------------------------------------------
				$content = "支払先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-payable/template-detail?id=' . $id;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $payableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/template-mod-request transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-approve                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 承認(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function templateApproveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$approvalId = $request->getParam('approval_id');
		$id         = $request->getParam('id');
		$approvalComment = $request->getParam('approval_comment');
		
		// POST送信時
		if ($request->isPost()) {
			$payableTemplateTable  = new Shared_Model_Data_AccountPayableTemplate();
			$approvalTable         = new Shared_Model_Data_Approval();
			$userTable             = new Shared_Model_Data_User();
			
			$approvalData = $approvalTable->getById($this->_adminProperty['management_group_id'], $approvalId);

			// 申請者
			$selectObj = $userTable->select();
	    	$selectObj->where('id = ?', $approvalData['applicant_user_id']);
	        $applicantUserData = $selectObj->query()->fetch();
	        
			$data = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);
			
            
			// 支払先取引先
			$connectionTable  = new Shared_Model_Data_Connection();
			$connectionData   = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

			$accountTitleTable = new Shared_Model_Data_AccountTitle();
			$accountTitleData  = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
			
			$currencyTable = new Shared_Model_Data_Currency();
			$currencyData  = $currencyTable->getById($this->_adminProperty['management_group_id'], $data['currency_id']);
			
			
			try {
				$payableTemplateTable->getAdapter()->beginTransaction();
				
				$payableTemplateTable->updateById($id, array(
					'status'           => Shared_Model_Code::PAYABLE_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
					'approval_user_id' => $this->_adminProperty['id'],
				));

				$approvalTable->updateById($approvalId, array(
					'status'    => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
				));

				// メール送信 -------------------------------------------------------
				$content = "支払先：\n" . $connectionData['company_name'] . "\n\n"
						 . "会計科目：\n" . $accountTitleData['title'] . "\n\n"
				         . "支払総額：\n" . number_format($data['total_amount']) . ' ' . $currencyData['name'] . "\n\n"
				         . "対象ページURL：\n" . HTTPS_PROTOCOL . APPLICATION_DOMAIN . '/transaction-payable/template-detail?id=' . $id;
	        
				$approvalTypeList   = Shared_Model_Code::codes('approval_type');
				$mailInput = array(
					'to'               => $applicantUserData['mail'], // 申請者メールアドレス
					'cc'               => array(),
					'type'             => $approvalTypeList[$approvalData['type']],
					'content'          => $content,
					'approval_status'  => Shared_Model_Code::APPROVAL_STATUS_APPROVED,
					'approval_comment' => $approvalComment,
				);		

				$mailer = new Shared_Model_Mail_ApprovalResult();
				$mailer->sendMail($mailInput);
				// -------------------------------------------------------------------
				
                // commit
                $payableTemplateTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $payableTemplateTable->getAdapter()->rollBack();
                throw new Zend_Exception('/transaction-payable/template-approve transaction faied: ' . $e);
                
            }

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/template-history                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 支払履歴                                    |
    +----------------------------------------------------------------------------*/
    public function templateHistoryAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id = $request->getParam('id');
		$this->view->posTop      = $request->getParam('pos');
		$page = $request->getParam('page');
		
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$this->view->data = $data = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $id);

		$this->view->backUrl = '/transaction-payable/template-list';
		$this->_helper->layout->setLayout('back_menu');

		$payableTable = new Shared_Model_Data_AccountPayable();
		
		$dbAdapter = $payableTable->getAdapter();

        $selectObj = $payableTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($payableTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($payableTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->where('frs_account_payable.template_id = ?', $id);
		$selectObj->where('frs_account_payable.status != ?', Shared_Model_Code::PAYABLE_STATUS_DELETED);
		$selectObj->order('frs_account_payable.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        }
		$this->view->currencyList = $currencyList;	
    
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/history-add                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 新規支払予定登録                            |
    +----------------------------------------------------------------------------*/
    public function historyAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '続ける';
		
		$request = $this->getRequest();
		$this->view->templateId = $templateId = $request->getParam('template_id');
		$this->view->posTop     = $request->getParam('pos');
		
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$this->view->data = $data = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $templateId);

        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);

		// 銀行口座
		if (!empty($data['paying_bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
		}
		
		// クレジットカード
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/history-add-post                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 新規支払予定登録(Ajax)                      |
    +----------------------------------------------------------------------------*/
    public function historyAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$templateId = $request->getParam('template_id');

		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$templateData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $templateId);
		
		// POST送信時
		if ($request->isPost()) {
		
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

				if (!empty($errorMessage['paying_plan_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「支払予定日」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$payableTable    = new Shared_Model_Data_AccountPayable();
                
            	//$displayId = $payableTable->getNextDisplayId();
            	
				$data = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'status'                  => Shared_Model_Code::PAYABLE_STATUS_DRAFT,    // 下書き
			        
			        'template_id'             => $templateData['id'],                        // 毎月支払テンプレートID
			        
			        'order_form_ids'          => serialize(array()),                         // 発注IDリスト
			        
					'account_title_id'        => $templateData['account_title_id'],          // 会計科目ID
					'account_totaling_group_id'=> $templateData['account_totaling_group_id'],  // 採算コード
					
					'target_connection_id'    => $templateData['target_connection_id'],      // 支払先
					
					'paying_type'             => Shared_Model_Code::PAYABLE_PAYING_TYPE_MONTHLY, // 支払種別(請求支払/カード支払/自動振替)

					'file_list'               => json_encode(array()),                       // 請求書ファイルアップロード
					
					'paid_user_id'            => 0,                                          // 支払処理担当者
					'paid_date'               => NULL,                                       // 支払完了日

					'memo'                    => $templateData['description'],               // 摘要
					
					'paying_plan_date'        => $success['paying_plan_date'],               // 支払予定日
					
					'paying_method'           => $templateData['paying_method'],             // 支払方法
					'paying_bank_id'          => $templateData['paying_bank_id'],            // 支払元銀行口座
					'paying_card_id'          => $templateData['paying_card_id'],            // 支払元クレジットカード
					'paying_method_memo'      => '',                                         // 支払方法メモ
					
					'created_user_id'         => $this->_adminProperty['id'],                // 支払申請者
					'approval_user_id'        => 0,                                          // 承認者
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
				);
				
				$message = '';
				
				if ($templateData['template_type'] == (string)Shared_Model_Code::PAYABLE_TEMPLATE_TYPE_FIXED) {
					// 固定費用
					$data['total_amount']  = $templateData['total_amount'];         // 支払額
					$data['currency_id']   = $templateData['currency_id'];          // 通貨単位
					$data['tax_division']  = $templateData['tax_division'];         // 税区分
					$data['tax']           = $templateData['tax'];                  // 消費税
				} else {
					// 毎月変動
					$data['total_amount']  = $success['total_amount'];              // 支払額
					$data['currency_id']   = $success['currency_id'];               // 通貨単位
					$data['tax_division']  = $success['tax_division'];              // 税区分
					$data['tax']           = $success['tax'];                       // 消費税
				}
				
				if (!empty($success['purchased_date'])) {
					$data['purchased_date'] = $success['purchased_date']; // カード利用日(購入日)
				}
				
				// 新規登録	            
	            $payableTable->getAdapter()->beginTransaction();
            	
	            try {
					$payableTable->create($data);
					$id = $payableTable->getLastInsertedId('id');

	                // commit
	                $payableTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $payableTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/transaction-payable/history-add-post transaction faied: ' . $e);
	            }

			    $this->sendJson(array('result' => 'OK', 'id' => $id));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/history-detail                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 - 支払予定詳細                                |
    +----------------------------------------------------------------------------*/
    public function historyDetailAction()
    {
		$request = $this->getRequest();
		$this->view->id          = $id         = $request->getParam('id');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->posTop      = $request->getParam('pos');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$payableTable = new Shared_Model_Data_AccountPayable();
		$this->view->data = $data = $payableTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$payableTemplateTable = new Shared_Model_Data_AccountPayableTemplate();
		$this->view->templateData = $templateData = $payableTemplateTable->getById($this->_adminProperty['management_group_id'], $data['template_id']);
	
		if (!empty($approvalId)) {
			$this->view->backUrl = '/approval/list';
			$this->_helper->layout->setLayout('back_menu_approval');
	        $this->view->saveUrl = 'javascript:void(0);';
	        $this->view->showRejectButton = false;
		} else {
			$this->view->backUrl = '/transaction-payable/template-history?id=' . $data['template_id'];
			$this->_helper->layout->setLayout('back_menu_competition');
	        
	        if ($this->view->allowEditing === true) {
		        if ($data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_DRAFT
		        || $data['status'] === (string)Shared_Model_Code::PAYABLE_STATUS_MOD_REQUEST) {
		        	$this->view->saveUrl = 'javascript:void(0);';
		        }
	        }
		}  
		
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
		$this->view->accountTitleData = $accountTitleTable->getById($this->_adminProperty['management_group_id'], $data['account_title_id']);
		
		// 支払先取引先
		$connectionTable  = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		
		// 登録者
		$userTable                = new Shared_Model_Data_User();
    	$this->view->createdUser  = $userTable->getById($data['created_user_id']);
    	
		// 銀行口座
		if (!empty($data['paying_bank_id'])) {
			$bankTable = new Shared_Model_Data_AccountBank();
			$this->view->bankData = $bankTable->getById($data['paying_bank_id']);
		}
		
		// クレジット
		if (!empty($data['paying_card_id'])) {
			$cardTable = new Shared_Model_Data_AccountCreditCard();	
			$this->view->cardData = $cardTable->getById($data['paying_card_id']);
		}
		
    }
   

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-payable/upload                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書ファイルアップロード(Ajax)                           |
    +----------------------------------------------------------------------------*/
    public function uploadAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
        
		if (empty($_FILES['file']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		$fileName = $_FILES['file']['name'];
		$tempFileName = uniqid();
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($tempFileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName, 'temp_file_name' => $tempFileName));
        return;
	}

}

