<?php
/**
 * class ConnectionController
 */
 
class ConnectionController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '取引先・営業管理';
		$this->view->menuCategory     = 'connection';
	
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/progress-index                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗                                                   |
    +----------------------------------------------------------------------------*/
    public function progressIndexAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'list-progress';
		$page    = $request->getParam('page', '1');
		$this->view->keepFooter = true;
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/progress-main                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗                                                   |
    +----------------------------------------------------------------------------*/
    public function progressMainAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'list-progress';
		$page    = $request->getParam('page', '1');
		$this->view->keepFooter = true;
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/progress-add                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function progressAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/list-history                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 更新履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function listHistoryAction()
    {
    	$this->view->menu = 'list-history';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$page    = $request->getParam('page', '1');

		// 検索条件
		$conditions = array();
		$conditions['excutor']        = $request->getParam('excutor', '');
		$conditions['connection']     = $request->getParam('connection', '');
		$conditions['type']           = $request->getParam('type', '');
		$this->view->conditions       = $conditions;
		
		$logTable = new Shared_Model_Data_ConnectionLog();
		
		$dbAdapter = $logTable->getAdapter();

        $selectObj = $logTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_connection_log.connection_id = frs_connection.id', array($logTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_connection_log.excutor_user_id = frs_user.id', array('display_id', $logTable->aesdecrypt('user_name', false) . 'AS user_name'));

		// グループID
		//$selectObj->where('frs_connection_log.management_group_id = ?', $this->_adminProperty['management_group_id']);

        if ($conditions['excutor'] != '') {
        	$likeString = $dbAdapter->quoteInto($logTable->aesdecrypt('frs_user.user_name', false) . ' LIKE ?', '%' . $conditions['excutor'] .'%');
        	
        	$selectObj->where('frs_user.display_id = ? OR ' . $likeString, $conditions['excutor']);
        }
           
        if ($conditions['connection'] != '') {
        	$selectObj->where('frs_connection.display_id = ? OR ' . $logTable->aesdecrypt('company_name', false) . ' = ?', $conditions['connection']);
        }
        
        if ($conditions['type'] != '') {
        	$selectObj->where('frs_connection_log.type = ?', $conditions['type']);
        }

		$selectObj->order('frs_connection_log.id DESC');
		
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
    |  action_URL    * /connection/list                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先一覧                                                 |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$this->view->menu = 'list';

		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$session = new Zend_Session_Namespace('connection_list');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']          = '';
			$session->conditions['type']            = '';
			$session->conditions['relation_type']   = '';
			$session->conditions['sales_relation']  = '';
			$session->conditions['industry_type']   = '';
			$session->conditions['gs']              = '';
			$session->conditions['gb']              = '';
			$session->conditions['keyword']         = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['status']          = $request->getParam('status', '');
			$session->conditions['type']            = $request->getParam('type', '');
			$session->conditions['relation_type']   = $request->getParam('relation_type', '');
			$session->conditions['sales_relation']  = $request->getParam('sales_relation', '');
			$session->conditions['industry_type']   = $request->getParam('industry_type', '');
			$session->conditions['gs']              = $request->getParam('gs', '');
			$session->conditions['gb']              = $request->getParam('gb', '');
			$session->conditions['keyword']         = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;

		
		$connectionTable = new Shared_Model_Data_Connection();
		$dbAdapter = $connectionTable->getAdapter();
        $selectObj = $connectionTable->select(array('id', 'status', 'updated', 'display_id', 'company_name', 'relation_types', 'gs_supplier_id', 'gs_buyer_id', 'gsc_supplier_id', 'type'));
        $selectObj->joinLeft('frs_connection_staff', 'frs_connection.id = frs_connection_staff.connection_id', NULL);
		
		// グループID
		//$selectObj->where('frs_connection.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if ($session->conditions['status'] != '') {
        	$selectObj->where('frs_connection.status = ?', $session->conditions['status']);
        }
        
        if ($session->conditions['type'] != '') {
        	$selectObj->where('frs_connection.type = ?', $session->conditions['type']);
        }

        if ($session->conditions['relation_type'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`relation_types`  LIKE ?', '%"' . $session->conditions['relation_type'] .'"%');
            $selectObj->where($relationTypeString);
        }

        if ($session->conditions['sales_relation'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`sales_relations`  LIKE ?', '%"' . $session->conditions['sales_relation'] .'"%');
            $selectObj->where($relationTypeString);
        }
        
        if ($session->conditions['industry_type'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`industry_types`  LIKE ?', '%"' . $session->conditions['industry_type'] .'"%');
            $selectObj->where($relationTypeString);
        }

        if ($session->conditions['gs'] != '') {
            $selectObj->where('gs_supplier_id != 0');
        }
        
        if ($session->conditions['gb'] != '') {
            $selectObj->where('gs_buyer_id != 0');
        }
    
        if ($session->conditions['country'] != '') {
        	$selectObj->where('frs_connection.country = ?', $session->conditions['country']);
        }
        
        if (!empty($session->conditions['keyword'])) {
        	$keywordString = '';
        	$columns = array(
        		'display_id', 'company_name', 'company_name_kana', 'description', 'head_office_postal_code', 'head_office_prefecture', 'head_office_city',
        		'head_office_city', 'head_office_address', 'head_office_building', 'staff_name',
        	);
        	
        	foreach ($columns as $each) {
        		if ($keywordString !== '') {
        			$keywordString .= ' OR ';
        		}

        		if ($connectionTable->isCryptField($each) || $each == 'staff_name') {   
        			$keyword = $dbAdapter->quote('%' . $session->conditions['keyword'] . '%');
        			$keywordString .= $connectionTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordString .= $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $session->conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where($keywordString);
        }
        
        $selectObj->group('frs_connection.id');
		$selectObj->order('frs_connection.updated DESC');
		//var_dump($selectObj->__toString());exit;
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();
				
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/list-select                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先リスト(ポップアップ用)                               |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$this->view->menu = 'list';
		$page    = $request->getParam('page', '1');
		
		$conditions = array();
		// 検索条件
		$conditions['keyword']  = $request->getParam('keyword', '');
		$this->view->conditions = $conditions;

		$connectionTable = new Shared_Model_Data_Connection();
		$dbAdapter = $connectionTable->getAdapter();
        $selectObj = $connectionTable->select();
		$selectObj->where('frs_connection.status != ?', Shared_Model_Code::CONNECTION_STATUS_REMOVE);
		
		// グループID
		//$selectObj->where('frs_connection.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if (!empty($conditions['keyword'])) {
        	$keywordString = '';
        	$columns = array(
        		'company_name', 'company_name_kana', 'description', 'web_url', 'head_office_postal_code', 'head_office_prefecture', 'head_office_city',
        		'head_office_city', 'head_office_address', 'head_office_building', 'tel', 'fax',
        	);
        	
        	foreach ($columns as $each) {
        		if ($keywordString !== '') {
        			$keywordString .= ' OR ';
        		}

        		if ($connectionTable->isCryptField($each)) {   
        			$keyword = $dbAdapter->quote('%' . $conditions['keyword'] . '%');     			
        			$keywordString .= $connectionTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordString .= $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where($keywordString);
        }
        
        /*
        if (!empty($conditions['user_id_in_charge'])) {
        	$selectObj->where('fbc_item.user_id_in_charge = ?', $conditions['user_id_in_charge']);	
        }
        */
        
		$selectObj->order('frs_connection.updated DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator, 'javascript:pageConnection($page);');
    }


	   
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/list-progress                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗                                                   |
    +----------------------------------------------------------------------------*/
    public function listProgressAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'list-progress';
		$page    = $request->getParam('page', '1');

	}
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/list-record                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録一覧                                                 |
    +----------------------------------------------------------------------------*/
    public function listRecordAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'list-record';
		$page    = $request->getParam('page', '1');

	}	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/list-contract                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 契約書一覧                                                 |
    +----------------------------------------------------------------------------*/
    public function listContractAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'list-contract';
		$page    = $request->getParam('page', '1');

	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/list-application                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 決議申請                                                   |
    +----------------------------------------------------------------------------*/
    public function listApplicationAction()
    {
		$request = $this->getRequest();
		$this->view->menu = 'list-application';
		$page    = $request->getParam('page', '1');

	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/add                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先新規登録                                             |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		// 国
		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();

		// 当社事業区分
		$ourBusinessTable = new Shared_Model_Data_OurBusiness();
		$this->view->ourBusinessList = $ourBusinessTable->getList();
		
		// 業種		
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/add-post                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先新規登録(Ajax)                                       |
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「企業・機関名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['company_name_kana']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「企業・機関名カナ」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (!empty($errorMessage['relation_types']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「当社との取引関係」を選択してください'));
                    return;
                } else if (!empty($errorMessage['sales_relations']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「当社に対する先方役割」を選択してください'));
                    return;
                } else if (!empty($errorMessage['industry_types']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「業種」を選択してください'));
                    return;
                     
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「事業内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['country']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「国」を選択してください'));
                    return;
                } else if (!empty($errorMessage['head_office_postal_code']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「本社・郵便番号」を選択してください'));
                    return;       
                } else if (!empty($errorMessage['head_office_prefecture']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「本社・都道府県」を選択してください'));
                    return; 
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$connectionTable = new Shared_Model_Data_Connection();
				$logTable        = new Shared_Model_Data_ConnectionLog();
				
				// 新規登録
				$financialClosingIdList = explode(',', $success['financial_closing_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($financialClosingIdList)) {
		            foreach ($financialClosingIdList as $eachId) {
		                $itemList[] = array(
							'id'                       => $count,
							'financial_closing_year'   => $request->getParam($eachId . '_financial_closing_year'),
							'financial_closing_sales'  => $request->getParam($eachId . '_financial_closing_sales'),
							'financial_closing_profit' => $request->getParam($eachId . '_financial_closing_profit'),
		                );
		            	$count++;
		            }
	            }
	            
	            $connectionTable->getAdapter()->beginTransaction();
            	$connectionTable->getAdapter()->query("LOCK TABLES frs_connection WRITE, frs_connection_department WRITE, frs_connection_staff WRITE, frs_connection_log WRITE")->execute();
            	
	            try {
	            
	            	$displayId = $connectionTable->getNextDisplayId();
	            
					$data = array(
				        'management_group_id'               => $this->_adminProperty['management_group_id'],
				        'display_id'                        => $displayId,
						'status'                            => Shared_Model_Code::CONNECTION_STATUS_ACTIVE,
						
						'company_name'                      => $success['company_name'],
						'company_name_kana'                 => $success['company_name_kana'],
						
						'type'                              => !empty($success['type']) ? $success['type'] : 0, // 種別
						'types_of_our_business'             => serialize($success['types_of_our_business']),    // 関連当社事業区分
						
						'relation_types'                    => serialize($success['relation_types']),           // 当社取引関係
						'relation_type_other_text'          => $success['relation_type_other_text'],            // 当社取引関係 その他 テキスト
						'sales_relations'                   => serialize($success['sales_relations']),          // 主な商談ポジション 
						'industry_types'                    => serialize($success['industry_types']),           // 業種
						
						'description'                       => $success['description'],                         // 事業内容
						'corporate_number'                  => $success['corporate_number'],                    // 法人番号
						'country'                           => $success['country'],                             // 国
						'head_office_postal_code'           => $success['head_office_postal_code'],             // 本社所在地郵便番号
						'head_office_prefecture'            => $success['head_office_prefecture'],              // 本社・都道府県
						'head_office_city'                  => $success['head_office_city'],                    // 本社・市区町村
						'head_office_address'               => $success['head_office_address'],                 // 本社・丁番地
						'head_office_building'              => $success['head_office_building'],                // 本社・建物名・階／号室
				  
						'representative_name'               => $success['representative_name'],                 // 代表者名
						'representative_name_kana'          => $success['representative_name_kana'],            // 代表者名カナ
						
						'tel'                               => $success['tel'],                                 // 代表電話番号
						'fax'                               => $success['fax'],                                 // FAX番号
						'web_url'                           => $success['web_url'],                             // 企業URL
						'duty'                              => !empty($success['duty']) ? $success['duty'] : 0,     // 課税・免税
						
						'memo'                              => $success['memo'],                                // 取引先情報メモ
						
						'foundation_date'                   => $success['foundation_date'],                     // 会社設立年月日
						'company_form'                      => !empty($success['company_form']) ? $success['company_form'] : 0, // 会社形態
						'capital'                           => $success['capital'],                             // 資本金
						'employees'                         => $success['employees'],                           // 従業員数
						'branch_offices'                    => $success['branch_offices'],                      // 営業店舗数
				
						'main_stockholder'                  => $success['main_stockholder'],                    // 主な株主
						'main_bank'                         => $success['main_bank'],                           // 主要取引銀行
						'main_connection'                   => $success['main_connection'],                     // 主要取引先企業
						
						'detective_season'                  => $success['detective_season'],                    // 興信所・調査時期
						'detective_name'                    => $success['detective_name'],                      // 興信所・調査機関名
						'detective_result'                  => $success['detective_result'],                    // 興信所・信用格付結果
						'detective_own'                     => $success['detective_own'],                       // 当社信用格付
						'detective_memo'                    => $success['detective_memo'],                      // 他信用特記メモ
						
						'financial_closing'                 => json_encode($itemList),
						'created_user_id'                   => $this->_adminProperty['id'],
						'last_update_user_id'               => $this->_adminProperty['id'],                     // 最終更新者ユーザーID
	
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$connectionTable->create($data);
					$id = $connectionTable->getLastInsertedId('id');
					
					
					// ログ
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $id,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_CREATE,
						
						'import_key'       => NULL,
						'result'           => 1,
						'message'          => '',
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
    		
	                // commit
	                $connectionTable->getAdapter()->commit();
	                $connectionTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	            } catch (Exception $e) {
	                $connectionTable->getAdapter()->rollBack();
	                $connectionTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                throw new Zend_Exception('/connection/add-post transaction faied: ' . $e);
	                
	            }
			
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/import                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先取込画面                                             |
    +----------------------------------------------------------------------------*/
    public function importAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection/list';
        
		$request    = $this->getRequest();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/import-csv                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先 CSV取込                                             |
    +----------------------------------------------------------------------------*/
    public function importCsvAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        ini_set('display_errors', 1);
        
		$request = $this->getRequest();
		
		if (empty($_FILES['csv']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
        
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        $csvData = file_get_contents($_FILES['csv']['tmp_name']);
        $csvData = preg_replace("/\r\n|\r|\n/", "\n", $csvData);
        $dataEncoded = mb_convert_encoding($csvData, 'UTF-8', 'SJIS-win');

		$key = uniqid();
        $savePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($key . '.csv');
        
        $handle = fopen($savePath, "w+");
        
		// 一旦文字コードを変換したCSVを保存
        fwrite($handle, $dataEncoded);
        rewind($handle);

        $csvFilePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($key . '.csv');;
		
        if (file_exists($csvFilePath)) {  
            $handle = fopen($csvFilePath, "r");
            
            // 説明行
            $csvRow = fgetcsv($handle, 0, ","); // 0
            $csvRow = fgetcsv($handle, 0, ","); // 1
            $csvRow = fgetcsv($handle, 0, ","); // 2
            $csvRow = fgetcsv($handle, 0, ","); // 3
            $csvRow = fgetcsv($handle, 0, ","); // 4
            $csvRow = fgetcsv($handle, 0, ","); // 5
            $csvRow = fgetcsv($handle, 0, ","); // 6
            $csvRow = fgetcsv($handle, 0, ","); // 7
            
            // 注文データの登録
            $rowCount = 1;
            
            while (($csvRow = fgetcsv($handle, 0, ",")) !== FALSE) {
            	$result = $this->importConnection($rowCount, $key, $csvRow);
            	$rowCount++;
            }

        } else {
	        $this->sendJson(array('result' => 'NG'));
	        return;
        }

    	$this->sendJson(array('result' => 'OK', 'key' => $key, 'count' => $rowCount));
    	return;
    }

/*
0 会社名
1 部署名
2 役職
3 氏名
4 e-mail
5 郵便番号
6 住所
7 TEL会社
8 TEL部門・・・ほぼデータなし
9 TEL直通・・・ほぼデータなし
10 FAX
11 携帯電話
12 URL
13 名刺交換日
14 オプション()
*/  	
	/*
	 * 取引先データ1件取込
	*/
    private function importConnection($rowCount, $importKey, $csvRow)
    {
    
    	$connectionTable      = new Shared_Model_Data_Connection();
    	$departmentTable      = new Shared_Model_Data_ConnectionDepartment();
    	$connectionStaffTable = new Shared_Model_Data_ConnectionStaff();
    	$logTable             = new Shared_Model_Data_ConnectionLog();
		
		$connectionType = Shared_Model_Code::CONNECTION_TYPE_COMPANY;
		
		if (empty($csvRow[0])) {
			// 会社名がない場合は個人事業とする
			$csvRow[0] = $csvRow[3];
			$connectionType = Shared_Model_Code::CONNECTION_TYPE_PERSONAL_BUSINESS;
			
		} else {
			if (strpos($csvRow[0], '株式会社') !== false) {
				$connectionType = Shared_Model_Code::CONNECTION_TYPE_PERSONAL_BUSINESS;
			}
			
			if (empty($csvRow[1])) {
				$csvRow[1] = '部門名なし';
			}
	
			if (empty($csvRow[3])) {
				$csvRow[3] = '担当者名なし';
			}
		}
		
			
        $connectionTable->getAdapter()->beginTransaction();
    	$connectionTable->getAdapter()->query("LOCK TABLES frs_connection WRITE, frs_connection_department WRITE, frs_connection_staff WRITE, frs_connection_log WRITE")->execute();

		try {	    
			$targetConnectionData = $connectionTable->findByCompanyName($csvRow[0]);
	    	
	    	if (empty($targetConnectionData)) {
	    		$nextConnectionId = $connectionTable->getNextDisplayId();
	    		
				// 新規登録
				$data = array(
			        'management_group_id'               => $this->_adminProperty['management_group_id'],
			        'display_id'                        => $nextConnectionId,
					'status'                            => Shared_Model_Code::CONNECTION_STATUS_IMPORTED,
					
					'company_name'                      => $csvRow[0],
					'company_name_kana'                 => '',
					
					'type'                              => $connectionType,           // 種別
					'types_of_our_business'             => serialize(NULL),           // 関連当社事業区分
					'relation_types'                    => serialize(NULL),           // 当社取引関係
					'relation_type_other_text'          => '',                        // 当社取引関係 その他 テキスト
					'sales_relations'                   => serialize(NULL),           // 主な商談ポジション 
					'industry_types'                    => serialize(NULL),           // 業種
					
					'description'                       => '',                        // 事業内容
					'corporate_number'                  => '',                        // 法人番号
					'country'                           => 0,                         // 国
					'head_office_postal_code'           => '',                        // 本社所在地郵便番号
					'head_office_prefecture'            => '',                        // 本社・都道府県
					'head_office_city'                  => '',                        // 本社・市区町村
					'head_office_address'               => '',                        // 本社・丁番地
					'head_office_building'              => '',                        // 本社・建物名・階／号室
			  
					'representative_name'               => '',                        // 代表者名
					'representative_name_kana'          => '',                        // 代表者名カナ
					'tel'                               => '',                        // 電話番号
					'fax'                               => '',                        // FAX番号
					'web_url'                           => $csvRow[12],               // 企業URL
					'duty'                              => 0,                         // 課税・免税
					
					'memo'                              => '',                        // 取引先情報メモ
					
					'foundation_date'                   => '',                        // 会社設立年月日
					'company_form'                      => 0,                         // 会社形態
					'capital'                           => '',                        // 資本金
					'employees'                         => '',                        // 従業員数
					'branch_offices'                    => '',                        // 営業店舗数
			
					'main_stockholder'                  => '',                        // 主な株主
					'main_bank'                         => '',                        // 主要取引銀行
					'main_connection'                   => '',                        // 主要取引先企業
					
					'detective_season'                  => '',                        // 興信所・調査時期
					'detective_name'                    => '',                        // 興信所・調査機関名
					'detective_result'                  => '',                        // 興信所・信用格付結果
					'detective_own'                     => '',                        // 当社信用格付
					'detective_memo'                    => '',                        // 他信用特記メモ
					
					'financial_closing'                 => json_encode(array()),
					
					'created_user_id'                   => $this->_adminProperty['id'],
					'last_update_user_id'               => $this->_adminProperty['id'],            // 登録更新申請者
	
	                'created'                           => new Zend_Db_Expr('now()'),
	                'updated'                           => new Zend_Db_Expr('now()'),
				);
	
				$connectionTable->create($data);
				$connectionId = $connectionTable->getLastInsertedId('id');
	    		
	    		// ログ
	    		$logTable->create(array(
			        'excutor_user_id'  => $this->_adminProperty['id'],
			        'connection_id'    => $connectionId,
					'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT,
					
					'import_key'       => $importKey,
					'result'           => 1,
					'message'          => '',
	                'created'          => new Zend_Db_Expr('now()'),
	                'updated'          => new Zend_Db_Expr('now()'),
	    		));	

	    	} else {
	    		$connectionId = $targetConnectionData['id'];
	    	}
	    	
	    	$targetDepartmentData = $departmentTable->findByDepartmentName($connectionId, $csvRow[1]);

	    	if (empty($targetDepartmentData)) {
	    		$nextDepartmentId    = $departmentTable->getNextDisplayId($connectionId);
	    		$nextDepartmentOrder = $departmentTable->getNextOrderNo($connectionId);
	    		
	    		$dataDepartment = array(
			        'management_group_id'     => $this->_adminProperty['management_group_id'],
			        'connection_id'           => $connectionId,
			        
			        'display_id'              => $nextDepartmentId,
					'status'                  => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
					'department_name'         => $csvRow[1],
					
					'order_no'                => $nextDepartmentOrder,
					
	                'created'                 => new Zend_Db_Expr('now()'),
	                'updated'                 => new Zend_Db_Expr('now()'),
		    	);

		    	$departmentTable->create($dataDepartment);
		    	$departmentId = $departmentTable->getLastInsertedId('id');
		    	
	    		// ログ
	    		$logTable->create(array(
			        'excutor_user_id'  => $this->_adminProperty['id'],
			        'connection_id'    => $connectionId,
					'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_DEPARTMENT,
					
					'import_key'       => $importKey,
					'result'           => 1,
					'message'          => '拠点・部門名：' . $csvRow[1],
	                'created'          => new Zend_Db_Expr('now()'),
	                'updated'          => new Zend_Db_Expr('now()'),
	    		));
	    		
	    		$targetDepartmentData = $departmentTable->getById($this->_adminProperty['management_group_id'], $departmentId);
	    	} else {
	    		$departmentId = $targetDepartmentData['id'];
	    	}
	    	
	    	
	    	// 所属＋担当者情報(同姓同名区別しない)
	    	//$staffData = $connectionStaffTable->findByDepartmentStaffName($connectionId, $departmentId, $csvRow[3]);
			$staffData = $connectionStaffTable->findByStaffName($connectionId, $csvRow[3]);
			
	    	if (empty($staffData)) {
	    		// 新規登録 ------------------------------------------------------------------------
	    			
	    		$nextOrderNo  = $connectionStaffTable->getNextOrderNo($connectionId, $departmentId);
	    		
		        $params = array(
			        'management_group_id'      => $this->_adminProperty['management_group_id'],
			        'connection_id'            => $connectionId,
			        'connection_department_id' => $departmentId,
					'status'                   => Shared_Model_Code::CONNECTION_STAFF_STATUS_ACTIVE,
					'mail_flag'                  => 1,
					'staff_name'               => $csvRow[3],
					'staff_name_kana'          => '',
					'staff_department'         => $csvRow[1],
					'staff_position'           => $csvRow[2],
					'staff_tel'                => $csvRow[7],
					'staff_fax'                => $csvRow[10],
					'staff_mobile'             => $csvRow[11],
					'staff_mail'               => $csvRow[4],
					'staff_postal_code'        => $csvRow[5],
					'staff_address'            => $csvRow[6],
					'staff_memo'               => '',
					'order_no'                 => $nextOrderNo,
					'created'                  => new Zend_Db_Expr('now()'),
			        'updated'                  => new Zend_Db_Expr('now()'),
		        );
		        
		    	$connectionStaffTable->create($params);

	    		// ログ
	    		$logTable->create(array(
			        'excutor_user_id'  => $this->_adminProperty['id'],
			        'connection_id'    => $connectionId,
					'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
					
					'import_key'       => $importKey,
					'result'           => 1,
					'message'          => '担当者名：' . $csvRow[3],
	                'created'          => new Zend_Db_Expr('now()'),
	                'updated'          => new Zend_Db_Expr('now()'),
	    		));
	    		
			} else {
				// 更新 ----------------------------------------------------------------------------
		        $params = array(
		        	'connection_department_id' => $departmentId,
					'staff_name'               => $csvRow[3],
					'staff_position'           => $csvRow[2],
					'staff_tel'                => $csvRow[7],
					'staff_fax'                => $csvRow[10],
					'staff_mobile'             => $csvRow[11],
					'staff_mail'               => $csvRow[4],
					'staff_postal_code'        => $csvRow[5],
					'staff_address'            => $csvRow[6],
		        );
		        
		        
		        $staffData['department_name'] = $targetDepartmentData['department_name'];

		        $newData = $params;
		        $newData['department_name'] = $csvRow[1];
		        
		        $message = $logTable->createDifferenceMessageStaff($staffData, $newData);
				
				if ($message != '') {
			    	$connectionStaffTable->updateById($staffData['id'], $params);
			    	
					// ログ
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF_UPDATE,
						
						'import_key'       => NULL,
						'result'           => 1,
						'message'          => $message,
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    		}
			}
			
		    // commit
		    $connectionTable->getAdapter()->commit();
		    $connectionTable->getAdapter()->query("UNLOCK TABLES")->execute();
		    
		} catch (Exception $e) {
		    $connectionTable->getAdapter()->rollBack();
		    $connectionTable->getAdapter()->query("UNLOCK TABLES")->execute();
		    
		    throw new Zend_Exception($e);
		    //var_dump($e);exit;
		}
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/history                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 更新履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function historyAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$page    = $request->getParam('page', '1');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

		$logTable = new Shared_Model_Data_ConnectionLog();
		
		$conditions = array();
		// 検索条件
		$conditions['type'] = $request->getParam('type', '');
		$this->view->conditions = $conditions;
		
		
		$logTable = new Shared_Model_Data_ConnectionLog();
		
		$dbAdapter = $logTable->getAdapter();

        $selectObj = $logTable->select();
        $selectObj->where('connection_id = ?', $id); 
        $selectObj->joinLeft('frs_user', 'frs_connection_log.excutor_user_id = frs_user.id', array('display_id', $logTable->aesdecrypt('user_name', false) . 'AS user_name'));
		
		// グループID
		//$selectObj->where('frs_connection_log.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if ($conditions['type'] != '') {
        	$selectObj->where('frs_connection_log.type = ?', $conditions['type']);
        }

		$selectObj->order('frs_connection_log.id DESC');
		
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
    |  action_URL    * /connection/basic                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報                                                   |
    +----------------------------------------------------------------------------*/
    public function basicAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$connectionTable = new Shared_Model_Data_Connection();
		$userTable       = new Shared_Model_Data_User();
		
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		// 国
		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();

		// 当社事業区分
		$ourBusinessTable = new Shared_Model_Data_OurBusiness();
		$this->view->ourBusinessList = $ourBusinessTable->getList();
		
		// 業種
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/relational                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 連携情報                                                   |
    +----------------------------------------------------------------------------*/
    public function relationalAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$connectionTable = new Shared_Model_Data_Connection();
		$userTable       = new Shared_Model_Data_User();
		
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$this->view->createdUser     = $userTable->getById($data['created_user_id']);
		$this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionBankTable = new Shared_Model_Data_ConnectionBank();
		$this->view->bankList = $connectionBankTable->getListByConnectionId($id);

    	// 振込先確認者
    	if (!empty($data['gs_bank_confirmed_user_id'])) {
    		$this->view->confirmedUser = $userTable->getById($data['gs_bank_confirmed_user_id']);
    	}
			
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/bank-list-select                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先口座リスト(ポップアップ用)                           |
    +----------------------------------------------------------------------------*/
    public function bankListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$this->view->connectionId = $connectionId = $request->getParam('connection_id');
		
		$connectionBankTable = new Shared_Model_Data_ConnectionBank();
		$this->view->bankList = $connectionBankTable->getListByConnectionId($connectionId);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/relational-list                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 旧金融機関データ移行                                       |
    +----------------------------------------------------------------------------*/
    public function relationalListAction()
    {
    	/*
		$this->view->menu = 'list';

		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');

		$connectionTable = new Shared_Model_Data_Connection();
		$dbAdapter = $connectionTable->getAdapter();
        $selectObj = $connectionTable->select();
        $selectObj->where('gs_basic_bank_select != 0');
		$selectObj->order('frs_connection.updated DESC');
        $this->view->items = $items = $selectObj->query()->fetchAll();
    	
    	$connectionBankTable = new Shared_Model_Data_ConnectionBank();

    	foreach ($items as $each) {
			$basicbankList       = Shared_Model_Code::codes('basic_bank');
			$bankCodeList        = Shared_Model_Code::codes('basic_bank_code'); 
		
		    if ($each['gs_basic_bank_select'] !== (string)Shared_Model_Code::BASIC_BANK_OTHER) {
			    $bankCode = $bankCodeList[$each['gs_basic_bank_select']];
			    $bankName = $basicbankList[$each['gs_basic_bank_select']];
		    } else {
			    $bankCode = $each['gs_bank_code'];
			    $bankName = $each['gs_other_bank_name'];
		    }
    		
			$data = array(
			    'connection_id'                     => $each['id'],                                  // 取引先ID
			    'status'                            => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
			    
			    'bank_registered_type'              => Shared_Model_Code::BANK_REGISTERED_TYPE_GOOSA_SP, // 登録種別
			    
			    'target_id'                         => $each['gs_supplier_id'],                      // 対象supplier/buyer id
			    'target_display_id'                 => $each['gs_supplier_display_id'],              // 対象supplier/buyer 表示id
			    
			    'bank_code'                         => $bankCode,                        // 金融機関コード
			    'bank_name'                         => $bankName,                        // 金融機関名
			    
			    'branch_code'                       => $each['gs_bank_branch_id'],                      // 支店コード
			    'branch_name'                       => $each['gs_bank_branch_name'],                      // 支店名
			    
			    'account_type'                      => $each['gs_bank_account_type'],                     // 口座種別
			    'account_no'                        => $each['gs_bank_account_no'],                       // 口座番号
			    
			    'account_name'                      => $each['gs_bank_account_name'],                     // 口座名義
				'account_name_kana'                 => $each['gs_bank_account_name_kana'],

				'memo'                              => '',
				
                'created'                           => new Zend_Db_Expr('now()'),
                'updated'                           => new Zend_Db_Expr('now()'),
			);
			
			$connectionBankTable->create($data);
    	}
    	*/
		
    }
    


    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/bank-add                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先口座 - 新規登録                                      |
    +----------------------------------------------------------------------------*/
    public function bankAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->connectionId = $connectionId = $request->getParam('connection_id');
		
		$connectionTable = new Shared_Model_Data_Connection();
		
		$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $connectionId);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/bank-add-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先口座 - 新規登録(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function bankAddPostAction()
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
                } else {
                	$message = '予期せぬエラーが発生しました';
                }
                
                $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				// 口座名義(カナ) 
				$success['account_name_kana'] = str_replace('ァ', 'ア', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ィ', 'イ', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ゥ', 'ウ', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ェ', 'エ', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ォ', 'オ', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ャ', 'ヤ', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ュ', 'ユ', $success['account_name_kana']);
				$success['account_name_kana'] = str_replace('ョ', 'ヨ', $success['account_name_kana']);
				
                $success['account_name_kana'] = str_replace('（', '(', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace('）', ')', $success['account_name_kana']);
                $success['account_name_kana'] = str_replace(array('-', 'ー', '―', '‐'), '-', $success['account_name_kana']);
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
				
				$connectionBankTable = new Shared_Model_Data_ConnectionBank();

	            $connectionBankTable->getAdapter()->beginTransaction();
	
	            try {
					$data = array(
					    'connection_id'                     => $success['connection_id'],                    // 取引先ID
					    'status'                            => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
					    
					    'bank_registered_type'              => Shared_Model_Code::BANK_REGISTERED_TYPE_FASS, // 登録種別
					    
					    'target_id'                         => 0,                                            // 対象supplier/buyer id
					    'target_display_id'                 => 0,                                            // 対象supplier/buyer 表示id
					    
					    'bank_code'                         => $success['bank_code'],                        // 金融機関コード
					    'bank_name'                         => $success['bank_name'],                        // 金融機関名
					    
					    'branch_code'                       => $success['branch_code'],                      // 支店コード
					    'branch_name'                       => $success['branch_name'],                      // 支店名
					    
					    'account_type'                      => $success['account_type'],                     // 口座種別
					    'account_no'                        => $success['account_no'],                       // 口座番号
					    
					    'account_name'                      => $success['account_name'],                     // 口座名義
        				'account_name_kana'                 => $success['account_name_kana'],
	
						'memo'                              => $success['memo'],
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$connectionBankTable->create($data);
					
	                // commit
	                $connectionBankTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $connectionBankTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection/bank-add-post transaction failed: ' . $e);
	            }
			
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/bank-detail                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先口座 - 詳細                                          |
    +----------------------------------------------------------------------------*/
    public function bankDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
		$this->view->saveUrl = 'javascript:void(0);';
		$this->view->saveButtonName = '保存';
		
		$request = $this->getRequest();
		$this->view->connectionId = $connectionId = $request->getParam('connection_id');
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->connectionData = $connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $connectionId);
		
		$connectionBankTable = new Shared_Model_Data_ConnectionBank();
		$this->view->data = $connectionBankTable->getById($id);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/bank-update                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先口座 - 更新(Ajax)                                    |
    +----------------------------------------------------------------------------*/
    public function bankUpdateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         	
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (isset($errorMessage['bank_code'])) {
                    if (!empty($errorMessage['bank_code']['isEmpty'])) {
                    	$message = '「金融機関コード」を入力してください';
                    } else if (!empty($errorMessage['bank_code']['notDigits'])) {
                    	$message = "「金融機関コード」は4桁の半角数字で入力してください\n(先頭がゼロの場合も入力してください)";
                    } else if (!empty($errorMessage['bank_code']['stringLengthTooShort'])) {
                    	$message = "「金融機関コード」は4桁の半角数字で入力してください\n(先頭がゼロの場合も入力してください)";
                    } else if (!empty($errorMessage['bank_code']['stringLengthTooLong'])) {
                    	$message = "「金融機関コード」は4桁の半角数字で入力してください\n(先頭がゼロの場合も入力してください)";
                    }
                    
                } else if (isset($errorMessage['bank_name'])) {
                    $message = '「金融機関名」を入力してください';
                    
                } else if (isset($errorMessage['branch_code'])) {
                    if (!empty($errorMessage['branch_code']['isEmpty'])) {
                    	$message = '「支店コード」を入力してください';
                    } else if (!empty($errorMessage['branch_code']['notDigits'])) {
                    	$message = "「支店コード」は3桁の半角数字で入力してください\n(先頭がゼロの場合も入力してください)";
                    } else if (!empty($errorMessage['branch_code']['stringLengthTooShort'])) {
                    	$message = "「支店コード」は3桁の半角数字で入力してください\n(先頭がゼロの場合も入力してください)";
                    } else if (!empty($errorMessage['branch_code']['stringLengthTooLong'])) {
                    	$message = "「支店コード」は3桁の半角数字で入力してください\n(先頭がゼロの場合も入力してください)";
                    }	
                    
                } else if (isset($errorMessage['branch_name'])) {
                    $message = '「支店名」を入力してください';
                    
                } else if (isset($errorMessage['account_type'])) {
                    $message = '「口座種別」を選択してください';
                    
                } else if (isset($errorMessage['account_no'])) {
					if (!empty($errorMessage['account_no']['isEmpty'])) {
                    	$message = '「口座番号」を入力してください';
                    } else if (!empty($errorMessage['account_no']['notDigits'])) {
                    	$message = "「口座番号」は13桁以内の半角数字で入力してください";
                    } else if (!empty($errorMessage['account_no']['stringLengthTooLong'])) {
                    	$message = "「口座番号」は13桁以内の半角数字で入力してください";
                    }
                    
                } else if (isset($errorMessage['account_name'])) {
                    $message = '「口座名義」を入力してください';
                } else if (isset($errorMessage['account_name_kana'])) {
                    $message = '「口座名義(カナ)」を入力してください';
                } else {
                	$message = '予期せぬエラーが発生しました';
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
				
				
				
				$connectionBankTable = new Shared_Model_Data_ConnectionBank();

	            $connectionBankTable->getAdapter()->beginTransaction();
	
	            try {
					$data = array(
					    'is_confirmed'                      => 0,
					    'bank_registered_type'              => Shared_Model_Code::BANK_REGISTERED_TYPE_FASS, // 登録種別
					    
					    'target_id'                         => 0,                                            // 対象supplier/buyer id
					    'target_display_id'                 => 0,                                            // 対象supplier/buyer 表示id
					    
					    'bank_code'                         => $success['bank_code'],                        // 金融機関コード
					    'bank_name'                         => $success['bank_name'],                        // 金融機関名
					    
					    'branch_code'                       => $success['branch_code'],                      // 支店コード
					    'branch_name'                       => $success['branch_name'],                      // 支店名
					    
					    'account_type'                      => $success['account_type'],                     // 口座種別
					    'account_no'                        => $success['account_no'],                       // 口座番号
					    
					    'account_name'                      => $success['account_name'],                     // 口座名義
        				'account_name_kana'                 => $success['account_name_kana'],
	
						'memo'                              => $success['memo'],
						
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					$connectionBankTable->updateById($id, $data);
					
	                // commit
	                $connectionBankTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $connectionBankTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection/bank-update transaction failed: ' . $e);
	            }
			
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/industry-select                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業種選択（ポップアップ）                                   |
    +----------------------------------------------------------------------------*/
    public function industrySelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$this->view->menu = 'list';
		
		// 業種
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;		
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-basic                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateBasicAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「企業・機関名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['company_name_kana']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「企業・機関名カナ」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (!empty($errorMessage['relation_types']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「当社との取引関係」を選択してください'));
                    return;
                } else if (!empty($errorMessage['sales_relations']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「当社に対する先方役割」を選択してください'));
                    return;
                } else if (!empty($errorMessage['industry_types']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「業種」を選択してください'));
                    return;
                    
                } else if (!empty($errorMessage['description']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「事業内容」を入力してください'));
                    return;
                } else if (!empty($errorMessage['country']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「国」を選択してください'));
                    return;
                } else if (!empty($errorMessage['head_office_postal_code']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「本社・郵便番号」を選択してください'));
                    return;       
                } else if (!empty($errorMessage['head_office_prefecture']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「本社・都道府県」を選択してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$connectionTable = new Shared_Model_Data_Connection();
				$logTable        = new Shared_Model_Data_ConnectionLog();
				
				$oldData = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
				
				// 更新
				$data = array(
					'status'                              => $success['status'],
					'company_name'                        => $success['company_name'],
					'company_name_kana'                   => $success['company_name_kana'],
					'type'                                => !empty($success['type']) ? $success['type'] : 0,
					'types_of_our_business'               => serialize($success['types_of_our_business']),    // 関連当社事業区分
					'relation_types'                      => serialize($success['relation_types']),           // 当社取引関係
					'relation_type_other_text'            => $success['relation_type_other_text'],            // 当社取引関係 その他 テキスト
					'sales_relations'                     => serialize($success['sales_relations']),          // 主な商談ポジション 
					'industry_types'                      => serialize($success['industry_types']),           // 業種
					
					'description'                         => $success['description'],
					'corporate_number'                    => $success['corporate_number'],
					'country'                             => $success['country'],
					'head_office_postal_code'             => $success['head_office_postal_code'],
					'head_office_prefecture'              => $success['head_office_prefecture'],
					'head_office_city'                    => $success['head_office_city'],
					'head_office_address'                 => $success['head_office_address'],
					'head_office_building'                => $success['head_office_building'],

					'representative_name'                 => $success['representative_name'],
					'representative_name_kana'            => $success['representative_name_kana'],
					'tel'                                 => $success['tel'],
					'fax'                                 => $success['fax'],
					'web_url'                             => $success['web_url'],
					'duty'                                => !empty($success['duty']) ? $success['duty'] : 0,
					
					'memo'                                => $success['memo'],
					
					'last_update_user_id'                 => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				);
		            
				$connectionTable->updateById($id, $data);
				
				$message = $logTable->createDifferenceMessageBasic($oldData, $data);
				
				// ログ
	    		$logTable->create(array(
			        'excutor_user_id'  => $this->_adminProperty['id'],
			        'connection_id'    => $id,
					'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_UPDATE_BASIC,
					
					'import_key'       => NULL,
					'result'           => 1,
					'message'          => $message,
	                'created'          => new Zend_Db_Expr('now()'),
	                'updated'          => new Zend_Db_Expr('now()'),
	    		));
			}

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/delete                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(管理権限あり)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		// POST送信時
		if ($request->isPost()) {
			$connectionTable = new Shared_Model_Data_Connection();

			try {
				$connectionTable->getAdapter()->beginTransaction();
				
				$connectionTable->updateById($id, array(
					'status' => Shared_Model_Code::CONNECTION_STATUS_REMOVE,
				));
			
                // commit
                $connectionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $connectionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/connection/delete transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
 
 
	/*----------------------------------------------------------------------------+
    |  action_URL    * /connection/account-confirmed                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(管理権限あり)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function accountConfirmedAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');

		// POST送信時
		if ($request->isPost()) {
			$connectionTable = new Shared_Model_Data_Connection();

			try {
				$connectionTable->getAdapter()->beginTransaction();
				
				$connectionTable->updateById($id, array(
					'gs_bank_confirmed_date_time' => new Zend_Db_Expr('now()'),
					'gs_bank_confirmed'           => Shared_Model_Code::BANK_CONFIRM_STATUS_CONFIRMED,
					'gs_bank_confirmed_user_id'   => $this->_adminProperty['id'],
				));
			
                // commit
                $connectionTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $connectionTable->getAdapter()->rollBack();
                throw new Zend_Exception('/connection/account-confirmed transaction failed: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
         
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-base                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引拠点情報更新(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    /*
    public function updateBaseAction()
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
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'base_postal_code'        => $success['base_postal_code'],
					'base_prefecture'         => $success['base_prefecture'],
					'base_city'               => $success['base_city'],
					'base_address'            => $success['base_address'],
					'base_building'           => $success['base_building'],
					'base_tel'                => $success['base_tel'],
					'base_fax'                => $success['base_fax'],
					'memo'                    => $success['memo'],
					
					'last_update_user_id'     => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				);

				$connectionTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    */
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-credit                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 信用情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateCreditAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「アイテム名」を入力してください';
                    $this->sendJson($result);
                    return;
                }
                    
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'foundation_date'         => $success['foundation_date'],
					'company_form'            => !empty($success['company_form']) ? $success['company_form'] : 0,
					'capital'                 => $success['capital'],
					'employees'               => $success['employees'],
					'branch_offices'          => $success['branch_offices'],
			
					'main_stockholder'        => $success['main_stockholder'],
					'main_bank'               => $success['main_bank'],
					'main_connection'         => $success['main_connection'],
					
					'detective_season'        => $success['detective_season'],
					'detective_name'          => $success['detective_name'],
					'detective_result'        => $success['detective_result'],
					'detective_own'           => $success['detective_own'],
					'detective_memo'          => $success['detective_memo'],
					
					'last_update_user_id'     => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				);

				$connectionTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-financial-closing                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 決算情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateFinancialClosingAction()
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
                
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$financialClosingIdList = explode(',', $success['financial_closing_list']);
				$itemList = array();
				$count = 1;
	            if (!empty($financialClosingIdList)) {
		            foreach ($financialClosingIdList as $eachId) {
		                $itemList[] = array(
							'id'                       => $count,
							'financial_closing_year'   => $request->getParam($eachId . '_financial_closing_year'),
							'financial_closing_sales'  => $request->getParam($eachId . '_financial_closing_sales'),
							'financial_closing_profit' => $request->getParam($eachId . '_financial_closing_profit'),
		                );
		            	$count++;
		            }
	            }

		        // 更新   
				$connectionTable = new Shared_Model_Data_Connection();
				$connectionTable->updateById($id, array(
					'financial_closing'   => json_encode($itemList),
					'last_update_user_id' => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				));
			}

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/base                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点情報                                                   |
    +----------------------------------------------------------------------------*/
    public function baseAction()
    {
        $this->_helper->layout->setLayout('back_menu');

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}

		$connectionTable      = new Shared_Model_Data_Connection();
		$connectionBaseTable  = new Shared_Model_Data_ConnectionBase();
	
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

    	$this->view->items = $connectionBaseTable->getListByConnectionId($this->_adminProperty['management_group_id'], $id);	
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/base-select                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点 ポップアップ選択用                                    |
    +----------------------------------------------------------------------------*/
    public function baseSelectAction()
    {
        $this->_helper->layout->setLayout('blank');

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');

		$connectionTable      = new Shared_Model_Data_Connection();
		$connectionBaseTable  = new Shared_Model_Data_ConnectionBase();
	
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

    	$this->view->items = $connectionBaseTable->getListByConnectionId($this->_adminProperty['management_group_id'], $id);	
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/base-edit                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点 新規登録/編集                                         |
    +----------------------------------------------------------------------------*/
    public function baseEditAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->baseId = $baseId = $request->getParam('base_id');
		
		$connectionTable      = new Shared_Model_Data_Connection();
		$connectionBaseTable  = new Shared_Model_Data_ConnectionBase();
		
		$data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		$this->view->data = $data;
		
		if (empty($baseId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';
			
			$this->view->baseData = array(
		        'management_group_id' => '',
		        'connection_id'       => $id,
				'status'              => 0,
				
				'company_name'        => '',
				'base_name'           => '',
				'zipcode'             => '',
				'prefecture'          => '',
				'address1'            => '',
				'address2'            => '',
				'tel'                 => '',
				'fax'                 => '',
				'person_in_charge'    => '',
				'person_in_charge_kana' => '',
				'mail'                => '',
				'mobile'              => '',
				'memo'                => '',
			);
			
			
		} else {
			$this->view->saveButtonName = '保存';
			
			$this->view->baseData = $connectionBaseTable->getById($this->_adminProperty['management_group_id'], $baseId);
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-base                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点 新規登録/編集(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function updateBaseAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->baseId = $baseId = $request->getParam('base_id');
		
		$connectionBaseTable  = new Shared_Model_Data_ConnectionBase();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection/update-base failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['base_name'])) {
                    $message = '「拠点名」を入力してください';
                } else if (isset($errorMessage['zipcode'])) {
                    $message = '「郵便番号」を入力してください';
                } else if (isset($errorMessage['prefecture'])) {
                    $message = '「都道府県」を入力してください';
                } else if (isset($errorMessage['address1'])) {
                    $message = '「住所」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
			
				if (empty($baseId)) {
					//$nextDepartmentId    = $departmentTable->getNextDisplayId($id);
	    			//$nextDepartmentOrder = $departmentTable->getNextOrderNo($id);
	    		
					// 新規登録
					$data = array(
				        'management_group_id' => $this->_adminProperty['management_group_id'],
				        'connection_id'       => $id,
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						'company_name'        => $success['company_name'],
						'base_name'           => $success['base_name'],
						'zipcode'             => $success['zipcode'],
						'prefecture'          => $success['prefecture'],
						'address1'            => $success['address1'],
						'address2'            => $success['address2'],
						'tel'                 => $success['tel'],
						'fax'                 => $success['fax'],
						'person_in_charge'    => $success['person_in_charge'],
						'person_in_charge_kana' => $success['person_in_charge_kana'],
						'mail'                => $success['mail'],
						'mobile'              => $success['mobile'],
						'memo'                => $success['memo'],

		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);
					
					$connectionBaseTable->create($data);
			    	$baseId = $connectionBaseTable->getLastInsertedId('id');
		    	
		    		// ログ
		    		/*
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
						
						'import_key'       => $importKey,
						'result'           => 1,
						'message'          => '拠点・部門名：' . $csvRow[1],
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    			*/
	    			
				} else {
					// 編集
					$data = array(
						'company_name'        => $success['company_name'],
						'base_name'           => $success['base_name'],
						'zipcode'             => $success['zipcode'],
						'prefecture'          => $success['prefecture'],
						'address1'            => $success['address1'],
						'address2'            => $success['address2'],
						'tel'                 => $success['tel'],
						'fax'                 => $success['fax'],
						'person_in_charge'    => $success['person_in_charge'],
						'person_in_charge_kana' => $success['person_in_charge_kana'],
						'mail'                => $success['mail'],
						'mobile'              => $success['mobile'],
						'memo'                => $success['memo'],
					);

					$connectionBaseTable->updateById($baseId, $data);
		    		// ログ
		    		/*
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
						
						'import_key'       => $importKey,
						'result'           => 1,
						'message'          => '拠点・部門名：' . $csvRow[1],
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    			*/
					
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/department                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 部門・担当者                                               |
    +----------------------------------------------------------------------------*/
    public function departmentAction()
    {
        $this->_helper->layout->setLayout('back_menu');

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}

		$connectionTable      = new Shared_Model_Data_Connection();
		$departmentTable      = new Shared_Model_Data_ConnectionDepartment();
		$connectionStaffTable = new Shared_Model_Data_ConnectionStaff();
	
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

    	$departmentList = $departmentTable->getListByConnectionId($id);
    	
    	foreach ($departmentList as &$each) {
    		$each['items'] = $connectionStaffTable->getListByConnectionIdAndDepartmentId($id, $each['id']);
    	}
    	$this->view->departmentList = $departmentList;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/staff-select                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 担当者選択(ポップアップ用)                                 |
    +----------------------------------------------------------------------------*/
    public function staffSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('connection_id');
 
 		$connectionTable      = new Shared_Model_Data_Connection();
		$departmentTable      = new Shared_Model_Data_ConnectionDepartment();
		$connectionStaffTable = new Shared_Model_Data_ConnectionStaff();
		
		$this->view->data = $data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		
    	$departmentList = $departmentTable->getListByConnectionId($id);
    	
    	foreach ($departmentList as &$each) {
    		$each['items'] = $connectionStaffTable->getListByConnectionIdAndDepartmentId($id, $each['id']);
    	}
    	$this->view->departmentList = $departmentList;	
    	
    }
    	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/department-edit                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点・部門 新規登録/編集                                   |
    +----------------------------------------------------------------------------*/
    public function departmentEditAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->deaprtmentId = $deaprtmentId = $request->getParam('department_id');
		
		$connectionTable      = new Shared_Model_Data_Connection();
		$departmentTable      = new Shared_Model_Data_ConnectionDepartment();
		
		$data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		$this->view->data = $data;
		
		if (empty($deaprtmentId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';
			
			$this->view->departmentData = array(
		        'management_group_id' => '',
		        'connection_id'       => $id,
		        
		        'display_id'          => '',
				'status'              => 0,
				'department_name'     => '',
			);
			
			
		} else {
			$this->view->saveButtonName = '保存';
			
			$this->view->departmentData = $departmentTable->getById($this->_adminProperty['management_group_id'], $deaprtmentId);
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-department                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点・部門 新規登録/編集(Ajax)                             |
    +----------------------------------------------------------------------------*/
    public function updateDepartmentAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$id           = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		
		$departmentTable = new Shared_Model_Data_ConnectionDepartment();
		$logTable        = new Shared_Model_Data_ConnectionLog();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection/update-department failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['department_name'])) {
                    $message = '「拠点・部門名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
			
				if (empty($departmentId)) {
					$nextDepartmentId    = $departmentTable->getNextDisplayId($id);
	    			$nextDepartmentOrder = $departmentTable->getNextOrderNo($id);
	    		
					// 新規登録
					$data = array(
				        'management_group_id' => $this->_adminProperty['management_group_id'],
				        'connection_id'       => $id,
				        
				        'display_id'          => $nextDepartmentId,
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						'department_name'     => $success['department_name'],
						
						'order_no'            => $nextDepartmentOrder,

		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);
					
					$departmentTable->create($data);
			    	$departmentId = $departmentTable->getLastInsertedId('id');
		    	
		    		// ログ
		    		/*
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
						
						'import_key'       => $importKey,
						'result'           => 1,
						'message'          => '拠点・部門名：' . $csvRow[1],
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    			*/
	    			
				} else {
					// 編集
					$data = array(
				        'department_name'     => $success['department_name'],
					);

					$departmentTable->updateById($departmentId, $data);
		    		// ログ
		    		/*
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
						
						'import_key'       => $importKey,
						'result'           => 1,
						'message'          => '拠点・部門名：' . $csvRow[1],
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    			*/
					
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/department-staff-edit                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点・部門 担当者 新規登録/編集                            |
    +----------------------------------------------------------------------------*/
    public function departmentStaffEditAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->staffId = $staffId = $request->getParam('staff_id');		

		$connectionTable      = new Shared_Model_Data_Connection();
		$departmentTable      = new Shared_Model_Data_ConnectionDepartment();
		$connectionStaffTable = new Shared_Model_Data_ConnectionStaff();
		
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

		if (empty($staffId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';
			
			$this->view->staffData = array(
		        'connection_department_id'   => '',
				'status'                     => Shared_Model_Code::CONNECTION_STAFF_STATUS_ACTIVE,
				
				'card_exchange_date'         => '',
				'mail_flag'                  => 1,
				
				'staff_name'                 => '',
				'staff_name_kana'            => '',
				'staff_position'             => '',
				'staff_tel'                  => '',
				'staff_fax'                  => '',
				'staff_mobile'               => '',
				'staff_mail'                 => '',
				'staff_postal_code'          => '',
				'staff_address'              => '',
				'staff_memo'                 => '',
			);	
			
		} else {
			$this->view->saveButtonName = '保存';
			
			$this->view->staffData = $connectionStaffTable->getById($this->_adminProperty['management_group_id'], $staffId);
		}
		
		// 拠点・部門 選択肢
    	$this->view->departmentList = $departmentTable->getListByConnectionId($id);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-department-staff                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 拠点・部門 担当者 新規登録/編集(Ajax)                      |
    +----------------------------------------------------------------------------*/
    public function updateDepartmentStaffAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request      = $this->getRequest();
		$id           = $request->getParam('id');
		$staffId      = $request->getParam('staff_id');
		
		$connectionStaffTable = new Shared_Model_Data_ConnectionStaff();
		$logTable             = new Shared_Model_Data_ConnectionLog();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection/update-department failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['connection_department_id'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「所属」を選択してください'));
                    return;
                    
                } else if (isset($errorMessage['staff_name'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「担当者 氏名」を入力してください'));
                    return;
                    
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
			
				if (empty($staffId)) {
					$nextOrderNo = $connectionStaffTable->getNextOrderNo($id, $success['connection_department_id']);
	    		
					// 新規登録
					$data = array(
				        'management_group_id'        => $this->_adminProperty['management_group_id'],
				        'connection_id'              => $id,
				        'connection_department_id'   => $success['connection_department_id'],
						'status'                     => Shared_Model_Code::CONNECTION_STAFF_STATUS_ACTIVE,
						
						'card_exchange_date'         => $success['card_exchange_date'],
						'mail_flag'                  => 1,
						
						'staff_name'                 => $success['staff_name'],
						'staff_name_kana'            => $success['staff_name_kana'],
						'staff_position'             => $success['staff_position'],
						'staff_tel'                  => $success['staff_tel'],
						'staff_fax'                  => $success['staff_fax'],
						'staff_mobile'               => $success['staff_mobile'],
						'staff_mail'                 => $success['staff_mail'],
						'staff_postal_code'          => $success['staff_postal_code'],
						'staff_address'              => $success['staff_address'],
						'staff_memo'                 => $success['staff_memo'],
						
						'order_no'                   => $nextOrderNo,

		                'created'                    => new Zend_Db_Expr('now()'),
		                'updated'                    => new Zend_Db_Expr('now()'),
					);

					if (!empty($success['mail_flag'])) {
						$data['mail_flag'] = 1;
					}
					
					$connectionStaffTable->create($data);
		    	
		    		// ログ
		    		/*
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
						
						'import_key'       => $importKey,
						'result'           => 1,
						'message'          => '拠点・部門名：' . $csvRow[1],
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    			*/
	    			
				} else {
					$oldStaffData = $connectionStaffTable->getById($this->_adminProperty['management_group_id'], $staffId);
				
					// 編集
					$data = array(
						'connection_department_id'   => $success['connection_department_id'],
						'status'                     => $success['status'],
						
						'card_exchange_date'         => $success['card_exchange_date'],
						'mail_flag'                  => 0,
						
						'staff_name'                 => $success['staff_name'],
						'staff_name_kana'            => $success['staff_name_kana'],
						'staff_position'             => $success['staff_position'],
						'staff_tel'                  => $success['staff_tel'],
						'staff_fax'                  => $success['staff_fax'],
						'staff_mobile'               => $success['staff_mobile'],
						'staff_mail'                 => $success['staff_mail'],
						'staff_postal_code'          => $success['staff_postal_code'],
						'staff_address'              => $success['staff_address'],
						'staff_memo'                 => $success['staff_memo'],
					);

					if (!empty($success['mail_flag'])) {
						$data['mail_flag'] = 1;
					}
					
					$connectionStaffTable->updateById($staffId, $data);
					
		    		// ログ
		    		/*
		    		$logTable->create(array(
				        'excutor_user_id'  => $this->_adminProperty['id'],
				        'connection_id'    => $connectionId,
						'type'             => Shared_Model_Code::CONNECTION_LOG_TYPE_IMPORT_STAFF,
						
						'import_key'       => $importKey,
						'result'           => 1,
						'message'          => '拠点・部門名：' . $csvRow[1],
		                'created'          => new Zend_Db_Expr('now()'),
		                'updated'          => new Zend_Db_Expr('now()'),
		    		));
	    			*/
					
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-staff-list                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先担当者 - 編集(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function updateStaffListAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$connectionId = $request->getParam('id');
		$departmentId = $request->getParam('department_id');
		
		$connectionTable      = new Shared_Model_Data_Connection();
		$departmentTable      = new Shared_Model_Data_ConnectionDepartment();
		$connectionStaffTable = new Shared_Model_Data_ConnectionStaff();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$connectionStaffTable->getAdapter()->beginTransaction();
				
	            try {
	            
					$staffIdList = explode(',', $success['staff_list']);

		            $count = 1;
		            if (!empty($staffIdList)) {
			            foreach ($staffIdList as $eachStaffId) {
			                $params = array(
								'order_no'            => $count,
			                );  
			            	$connectionStaffTable->updateById($eachStaffId, $params);
			            	
			            	$count++;
			            }
		            }
		            
	                // commit
	                $connectionStaffTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $connectionStaffTable->getAdapter()->rollBack();
	                
	                throw new Zend_Exception('/connection/update-staff-list transaction failed: ' . $e);   
	            }
			}

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/sales-info                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 販売・役務提供情報                                         |
    +----------------------------------------------------------------------------*/
    public function salesInfoAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

		if (empty($data['sales_payment_conditions'])) {
			$itemList[] = array(
				'id'                        => '1',
				'payment_condition'         => '',
				'payment_condition_close'   => '',
				'payment_condition_month'   => '',
				'payment_condition_pay'     => '',
				'payment_condition_other'   => '',
	        );
	        $data['sales_payment_conditions'] = $itemList;
        }
        
		$this->view->data = $data;                
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/update-sales-info                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 販売・役務提供情報更新(Ajax)                               |
    +----------------------------------------------------------------------------*/
    public function updateSalesInfoAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {

				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'sales_ec'                        => !empty($success['sales_ec']) ? $success['sales_ec'] : 0, // 種別
					'sales_real'                      => !empty($success['sales_real']) ? $success['sales_real'] : 0, // 種別
					'sales_overseas'                  => !empty($success['sales_overseas']) ? $success['sales_overseas'] : 0, // 種別
					'sales_memo'                      => $success['sales_memo'],
					
					'sales_payment_method'            => serialize($success['sales_payment_method']),
					'sales_payment_method_other_text' => $success['sales_payment_method_other_text'],

					'last_update_user_id'             => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				);

				$paymentConditionList = explode(',', $success['sales_payment_coonditions']);

				$itemList = array();
				$count = 1;
	            if (!empty($paymentConditionList)) {
		            foreach ($paymentConditionList as $eachId) {
		            	$paymentCondition      = $request->getParam($eachId . '_payment_condition');
		            	$paymentConditionClose = $request->getParam($eachId . '_payment_condition_close');
		            	$paymentConditionMonth = $request->getParam($eachId . '_payment_condition_month');
		            	$paymentConditionPay   = $request->getParam($eachId . '_payment_condition_pay');
		            	
						if ($paymentCondition == Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY
		                || $paymentCondition == Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_CLAIM) {
		                	if (empty($paymentConditionClose)) {
							    $this->sendJson(array('result' => 'NG', 'message' => '通常入金条件' . $count . ' - 締日を選択してください'));
					    		return;
		                	} else if (empty($paymentConditionMonth)) {
							    $this->sendJson(array('result' => 'NG', 'message' => '通常入金条件' . $count . ' - 支払月を選択してください'));
					    		return;
		                	} else if (empty($paymentConditionPay)) {
							    $this->sendJson(array('result' => 'NG', 'message' => '通常入金条件' . $count . ' - 支払日を選択してください'));
					    		return;
		                	}
		                }
                
		                $itemList[] = array(
							'id'                        => $count,
							'payment_condition'         => $paymentCondition,
							'payment_condition_close'   => $paymentConditionClose,
							'payment_condition_month'   => $paymentConditionMonth,
							'payment_condition_pay'     => $paymentConditionPay,
							'payment_condition_other'   => $request->getParam($eachId . '_payment_condition_other'),
		                );
		            	$count++;
		            }
		            
		            $data['sales_payment_conditions']   = json_encode($itemList);
	            }
	            
				$connectionTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/provide-info                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 仕入・製造加工委託・役務委託情報                           |
    +----------------------------------------------------------------------------*/
    public function provideInfoAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

		if (empty($data['supply_payment_conditions'])) {
			$itemList[] = array(
				'id'                        => '1',
				'payment_condition'         => '',
				'payment_condition_close'   => '',
				'payment_condition_month'   => '',
				'payment_condition_pay'     => '',
				'payment_condition_other'   => '',
	        );
	        $data['supply_payment_conditions'] = $itemList;
        }
        
		$this->view->data = $data;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-provide-info                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 仕入・製造加工委託・役務委託情報更新(Ajax)                 |
    +----------------------------------------------------------------------------*/
    public function updateProvideInfoAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'supply_content'                   => $success['supply_content'],
					
					'supply_payment_method'            => serialize($success['supply_payment_method']),
					'supply_payment_method_other_text' => $success['supply_payment_method_other_text'],
					
					'last_update_user_id'              => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				);

				$paymentConditionList = explode(',', $success['supply_payment_conditions']);


				$itemList = array();
				$count = 1;
	            if (!empty($paymentConditionList)) {
		            foreach ($paymentConditionList as $eachId) {
		            	$paymentCondition      = $request->getParam($eachId . '_payment_condition');
		            	$paymentConditionClose = $request->getParam($eachId . '_payment_condition_close');
		            	$paymentConditionMonth = $request->getParam($eachId . '_payment_condition_month');
		            	$paymentConditionPay   = $request->getParam($eachId . '_payment_condition_pay');

						if ($paymentCondition == Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_DELIVERY
		                || $paymentCondition == Shared_Model_Code::CONNECTION_PAYMENT_CONDITION_BASED_CLAIM) {
		                	if (empty($paymentConditionClose)) {
							    $this->sendJson(array('result' => 'NG', 'message' => '通常支払条件' . $count . ' - 締日を選択してください'));
					    		return;
		                	} else if (empty($paymentConditionMonth)) {
							    $this->sendJson(array('result' => 'NG', 'message' => '通常支払条件' . $count . ' - 支払月を選択してください'));
					    		return;
		                	} else if (empty($paymentConditionPay)) {
							    $this->sendJson(array('result' => 'NG', 'message' => '通常支払条件' . $count . ' - 支払日を選択してください'));
					    		return;
		                	}
		                }
                
		                $itemList[] = array(
							'id'                        => $count,
							'payment_condition'         => $paymentCondition,
							'payment_condition_close'   => $paymentConditionClose,
							'payment_condition_month'   => $paymentConditionMonth,
							'payment_condition_pay'     => $paymentConditionPay,
							'payment_condition_other'   => $request->getParam($eachId . '_payment_condition_other'),
		                );
		                
		            	$count++;
		            }
		            
		            $data['supply_payment_conditions']   = json_encode($itemList);
	            }
	            
				$connectionTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-sales-payment-coonditions               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 販売・役務提供情報 入金条件 更新(Ajax)                     |
    +----------------------------------------------------------------------------*/
    /*
    public function updateSalesPaymentCoonditionsAction()
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
                
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$paymentConditionList = explode(',', $success['sales_payment_coonditions']);
				$itemList = array();
				$count = 1;
	            if (!empty($paymentConditionList)) {
		            foreach ($paymentConditionList as $eachId) {
		                $itemList[] = array(
							'id'                       => $count,
							'financial_closing_year'   => $request->getParam($eachId . '_financial_closing_year'),
							'financial_closing_sales'  => $request->getParam($eachId . '_financial_closing_sales'),
							'financial_closing_profit' => $request->getParam($eachId . '_financial_closing_profit'),
		                );
		            	$count++;
		            }
	            }

		        // 更新   
				$connectionTable = new Shared_Model_Data_Connection();
				$connectionTable->updateById($id, array(
					'sales_payment_conditions'   => json_encode($itemList),
					'last_update_user_id'        => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				));
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }
    */
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/update-provide-account                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 仕入・製造加工委託・役務委託情報 指定振込先更新(Ajax)      |
    +----------------------------------------------------------------------------*/
    public function updateProvideAccountAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {                
				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'supply_account_bank'           => $success['supply_account_bank'],
					'supply_account_type'           => $success['supply_account_type'],
					'supply_account_no'             => $success['supply_account_no'],
					'supply_account_name'           => $success['supply_account_name'],
					'supply_account_name_kana'      => $success['supply_account_name_kana'],
				);

				$connectionTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/investment-info                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 投融資情報                                                 |
    +----------------------------------------------------------------------------*/
    public function investmentInfoAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-investment-info                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 投融資情報更新(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function updateInvestmentInfoAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {                
				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'inv_fin_relation'                 => serialize($success['inv_fin_relation']),
					'inv_fin_memo'                     => $success['inv_fin_memo'],
				);

				$connectionTable->updateById($id, $data);
			}

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/update-investment-account                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 投融資情報 指定振込先更新(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function updateInvestmentAccountAction()
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

                if (!empty($errorMessage['company_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {                
				$connectionTable = new Shared_Model_Data_Connection();
	
				// 更新
				$data = array(
					'inv_fin_account_bank'             => $success['inv_fin_account_bank'],
					'inv_fin_account_type'             => $success['inv_fin_account_type'],
					'inv_fin_account_no'               => $success['inv_fin_account_no'],
					'inv_fin_account_name'             => $success['inv_fin_account_name'],
					'inv_fin_account_name_kana'        => $success['inv_fin_account_name_kana'],
					
					'last_update_user_id'              => $this->_adminProperty['id'],            // 最終更新者ユーザーID
				);

				$connectionTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/progress                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗                                                   |
    +----------------------------------------------------------------------------*/
    public function progressAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$itemTable = new Shared_Model_Data_ConnectionProgressItem();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
    	$selectObj->joinLeft('frs_connection', 'frs_connection_progress_item.connection_id = frs_connection.id', array($itemTable->aesdecrypt('company_name', false) . 'AS company_name', 'industry_types'));
    	$selectObj->joinLeft('frs_connection_progress_start_tag', 'frs_connection_progress_item.start_tag_id = frs_connection_progress_start_tag.id', array('tag_name'));
		$selectObj->joinLeft('frs_connection_area', 'frs_connection_progress_item.area_id = frs_connection_area.id', array($itemTable->aesdecrypt('area_name', false) . 'AS area_name'));
        $selectObj->where('connection_id = ?', $id);
        $selectObj->order('id DESC');
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);

		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
        
		// 業種
    	$industryTypeTable = new Shared_Model_Data_IndustryType();
    	$industryTypeItems = $industryTypeTable->getAllList();
    	$industryTypeList = array();
    	foreach ($industryTypeItems as $each) {
	    	$industryTypeList[$each['id']] = $each;
    	}
    	$this->view->industryList = $industryTypeList;
    	
        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);


        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/record                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録                                                     |
    +----------------------------------------------------------------------------*/
    public function recordAction()
    {
        $this->_helper->layout->setLayout('back_menu');

		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$session = new Zend_Session_Namespace('connection_detail_record');
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
		

		// 議事録
		$recordTable = new Shared_Model_Data_ConnectionRecord();
		
		$dbAdapter = $recordTable->getAdapter();

        $selectObj = $recordTable->select();
        $selectObj->joinLeft('frs_user', 'frs_connection_record.created_user_id = frs_user.id', array($recordTable->aesdecrypt('user_name', false) . 'AS user_name'));
        $selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array($recordTable->aesdecrypt('department_name', false) . 'AS department_name'));
        $selectObj->joinLeft('frs_connection_progress_item', 'frs_connection_record.progress_item_id = frs_connection_progress_item.id', array('frs_connection_progress_item.display_id AS progress_item_display_id'));
        $selectObj->where('frs_connection_record.management_group_id = ?', $this->_adminProperty['management_group_id']); // グループID
        
        $selectObj->where('frs_connection_record.connection_id = ?', $id);
        $selectObj->order('frs_connection_record.id DESC');
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(10);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/sample                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷                                               |
    +----------------------------------------------------------------------------*/
    public function sampleAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/transaction                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引実績（発注受注）                                       |
    +----------------------------------------------------------------------------*/
    public function transactionAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/estimate                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積提出実績                                               |
    +----------------------------------------------------------------------------*/
    public function estimateAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection/contract                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 契約書                                                     |
    +----------------------------------------------------------------------------*/
    public function contractAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/connection/list';
		}
		
		$connectionTable = new Shared_Model_Data_Connection();
		$this->view->data = $connectionTable->getById($this->_adminProperty['management_group_id'], $id);

    }
    
}

