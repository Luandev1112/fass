<?php
/**
 * class FinancialController
 */
 
class FinancialController extends Front_Model_Controller
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
		$this->view->mainCategoryName = 'システム設定';
		$this->view->menuCategory     = 'system';
		$this->view->menu     = 'financial';
	
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/currency-list                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 通貨一覧                                                   |
    +----------------------------------------------------------------------------*/
    public function currencyListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$currencyTable = new Shared_Model_Data_Currency();
		
		$dbAdapter = $currencyTable->getAdapter();

        $selectObj = $currencyTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id'], $managementGroupId);
		$selectObj->order('content_order ASC');
		
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
    |  action_URL    * /financial/currency-update-order                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 通貨一覧更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function currencyUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$currencyTable = new Shared_Model_Data_Currency();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/currency-update-order failed to load config');
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
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$currencyTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/currency-detail                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 通貨・編集                                                 |
    +----------------------------------------------------------------------------*/
    public function currencyDetailAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$currencyTable = new Shared_Model_Data_Currency();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'name'                => '',       // ISO 4217 通貨コード
		        'symbol'              => '',       // 記号
		        'general_name'        => '',       // 日本語一般名称
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $currencyTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/financial/currency-detail failed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/currency-update                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 通貨・編集(Ajax)                                           |
    +----------------------------------------------------------------------------*/
    public function currencyUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$currencyTable = new Shared_Model_Data_Currency();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/currency-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['name'])) {
                    $message = '「通貨コード」を入力してください';
                    
                } else if (isset($errorMessage['symbol'])) {
                    $message = '「記号」を入力してください';
                    
                } else if (isset($errorMessage['general_name'])) {
                    $message = '「日本語一般名称」を入力してください';
                    
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				if (empty($id)) {
					// 新規登録
					$contentOrder = $currencyTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						
				        'name'                => $success['name'],         // ISO 4217 通貨コード
				        'symbol'              => $success['symbol'],       // 記号
						'general_name'        => $success['general_name'], // 日本語一般名称
						
						'content_order'       => $contentOrder,
						
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$currencyTable->create($data);
					
				} else {
					// 編集
					$data = array(
				        'name'                => $success['name'],         // ISO 4217 通貨コード
				        'symbol'              => $success['symbol'],       // 記号
				        'general_name'        => $success['general_name'], // 日本語一般名称
					);

					$currencyTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/bank-list                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座一覧                                               |
    +----------------------------------------------------------------------------*/
    public function bankListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$bankTable = new Shared_Model_Data_AccountBank();
		
		$dbAdapter = $bankTable->getAdapter();

        $selectObj = $bankTable->select();
		$selectObj->order('content_order ASC');
		
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
    |  action_URL    * /financial/bank-list-select                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座一覧(ポップアップ用)                               |
    +----------------------------------------------------------------------------*/
    public function bankListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();

		$bankTable = new Shared_Model_Data_AccountBank();

        $selectObj = $bankTable->select();
		$selectObj->order('content_order ASC');
        $this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/bank-update-order                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function bankUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$bankTable = new Shared_Model_Data_AccountBank();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/bank-update-order failed to load config');
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
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$bankTable->updateById($eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/bank-detail                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座・編集                                             |
    +----------------------------------------------------------------------------*/
    public function bankDetailAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$bankTable = new Shared_Model_Data_AccountBank();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'bank_code'           => '',        // 金融機関コード
		        'bank_name'           => '',        // 金融機関名
		        
		        'branch_code'         => '',        // 支店コード
		        'branch_name'         => '',        // 支店名
		        
		        'account_type'        => '',        // 口座種別
		        'account_no'          => '',        // 口座番号
		        
		        'account_name'        => '',        // 口座名義
		        'account_name_kana'   => '',        // 口座名義(カナ)
		        'short_name'          => '',        // 略名
		        'gmo_account_id'      => '',        // GMOアカウントID
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $bankTable->getById($id);

	        if (empty($data)) {
				throw new Zend_Exception('/financial/bank-detail failed to fetch account title data');
			}

        	$this->view->data = $data;
        }
        
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$this->view->gmoAccountList = $gmoTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/bank-update                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 銀行口座・編集(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function bankUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$bankTable = new Shared_Model_Data_AccountBank();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/bank-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['bank_code'])) {
                    $message = '「金融機関コード」を入力してください';
                } else if (isset($errorMessage['bank_name'])) {
                    $message = '「金融機関名」を入力してください';
                } else if (isset($errorMessage['branch_code'])) {
                    $message = '「支店コード」を入力してください'; 
                } else if (isset($errorMessage['branch_name'])) {
                    $message = '「支店名」を入力してください'; 
                } else if (isset($errorMessage['account_type'])) {
                    $message = '「口座種別」を選択してください';
                } else if (isset($errorMessage['account_no'])) {
                    $message = '「口座番号」を入力してください';
                } else if (isset($errorMessage['account_name'])) {
                    $message = '「口座名義」を入力してください';
                } else if (isset($errorMessage['account_name_kana'])) {
                    $message = '「口座名義(カナ)」を入力してください';
                    
                } else if (!empty($errorMessage['short_name']['stringLengthTooLong'])) {
                	$message = '「略名」は5文字以下で入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				// 口座名義(カナ) 
                $success['account_name_kana'] = str_replace('（', '(', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('）', ')', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('ー', '-', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('／', '/', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('．', '.', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('，', ',', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('　', ' ', $success['account_name_kana']);
                $success['account_name_kana'] = strtoupper($success['account_name_kana']);             // 大文字に変換
				$success['account_name_kana'] = mb_convert_kana($success['account_name_kana'], 'krn'); // 全角英字を半角・全角数字を半角・全角カナを半角カナ
				
				$valid = Shared_Model_Utility_Text::bankStringValid($success['account_name_kana']);
				
				if (!$valid) {
					$this->sendJson(array('result' => 'NG', 'message' => '「口座名義(カナ) 」に利用できない文字が含まれています'));
	            	return;
				}
				
				
				
				if (empty($id)) {
					// 新規登録
					$contentOrder = $bankTable->getNextContentOrder();
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス
						
				        'bank_code'           => $success['bank_code'],         // 金融機関コード
				        'bank_name'           => $success['bank_name'],         // 金融機関名
				        
				        'branch_code'         => $success['branch_code'],       // 支店コード
				        'branch_name'         => $success['branch_name'],       // 支店名
				        
				        'account_type'        => $success['account_type'],      // 口座種別
				        'account_no'          => $success['account_no'],        // 口座番号
				        
				        'account_name'        => $success['account_name'],      // 口座名義
				        'account_name_kana'   => $success['account_name_kana'], // 口座名義(カナ)
				        'short_name'          => $success['short_name'],        // 略名
						        
						'content_order'       => $contentOrder,                 // 並び順  
						
						'gmo_account_id'      => $success['gmo_account_id'],
						
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$bankTable->create($data);
					
				} else {
					// 編集
					$data = array(
				        'bank_code'           => $success['bank_code'],         // 金融機関番号
				        'bank_name'           => $success['bank_name'],         // 金融機関名
				        
				        'branch_code'         => $success['branch_code'],       // 支店コード
				        'branch_name'         => $success['branch_name'],       // 支店名
				        
				        'account_type'        => $success['account_type'],      // 口座種別
				        'account_no'          => $success['account_no'],        // 口座番号
				        
				        'account_name'        => $success['account_name'],      // 口座名義
				        'account_name_kana'   => $success['account_name_kana'], // 口座名義(カナ)
				        'short_name'          => $success['short_name'],        // 略名
				        
				        'gmo_account_id'      => $success['gmo_account_id'],
				        
					);

					$bankTable->updateById($id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/card-list                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード一覧                                       |
    +----------------------------------------------------------------------------*/
    public function cardListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$cardTable = new Shared_Model_Data_AccountCreditCard();
		
		$dbAdapter = $cardTable->getAdapter();

        $selectObj = $cardTable->select();
		$selectObj->order('content_order ASC');
		
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
    |  action_URL    * /financial/card-list-select                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード一覧(ポップアップ用)                       |
    +----------------------------------------------------------------------------*/
    public function cardListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();

		$cardTable = new Shared_Model_Data_AccountCreditCard();		
        $selectObj = $cardTable->select();
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/card-update-order                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function cardUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$cardTable = new Shared_Model_Data_AccountCreditCard();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/card-update-order failed to load config');
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
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$cardTable->updateById($eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/card-detail                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード・編集                                     |
    +----------------------------------------------------------------------------*/
    public function cardDetailAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$cardTable = new Shared_Model_Data_AccountCreditCard();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
			    'card_name'      => '',         // カード名
			    'card_company'   => '',         // カード会社名
			    'card_no_last4'  => '',         // カード番号下4桁
			    'closing_day'    => '',         // 締め日
			    'payment_day'    => '',         // 支払日
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $cardTable->getById($id);

	        if (empty($data)) {
				throw new Zend_Exception('/financial/card-detail failed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /financial/card-update                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * クレジットカード・編集(Ajax)                               |
    +----------------------------------------------------------------------------*/
    public function cardUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_editing_accounting_title'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$cardTable = new Shared_Model_Data_AccountCreditCard();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/financial/card-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['card_name'])) {
                    $message = '「カード名」を入力してください';
                } else if (isset($errorMessage['card_company'])) {
                    $message = '「カード会社名」を入力してください';
                } else if (isset($errorMessage['card_no_last4'])) {
                    $message = '「カード番号下4桁」を入力してください';
                } else if (isset($errorMessage['closing_day'])) {
                    $message = '「締め日」を選択してください'; 
                } else if (isset($errorMessage['payment_day'])) {
                    $message = '「支払日」を選択してください'; 
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				if (empty($id)) {
					// 新規登録
					$contentOrder = $cardTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス

					    'card_name'           => $success['card_name'],         // カード名
					    'card_company'        => $success['card_company'],      // カード会社名
					    'card_no_last4'       => $success['card_no_last4'],     // カード番号下4桁
					    'closing_day'         => $success['closing_day'],       // 締め日
					    'payment_day'         => $success['payment_day'],       // 支払日
			    
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$cardTable->create($data);
					
				} else {
					// 編集
					$data = array(
					    'card_name'           => $success['card_name'],         // カード名
					    'card_company'        => $success['card_company'],      // カード会社名
					    'card_no_last4'       => $success['card_no_last4'],     // カード番号下4桁
					    'closing_day'         => $success['closing_day'],       // 締め日
					    'payment_day'         => $success['payment_day'],       // 支払日
					);

					$cardTable->updateById($id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
}

