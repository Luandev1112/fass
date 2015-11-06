<?php
/**
 * class Api_ConnectionController
 */

class Api_ConnectionController extends Api_Model_Controller
{
    /**
     * findAction
     * 検索
     */
    public function findAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();
		
		if (empty($params['company_name'])) {
			return $this->sendJson(array('result' => false, 'message' => '会社名を指定してください'));
		}
		
		$params['company_name'] = str_replace(' ', '', $params['company_name']);
		$params['company_name'] = str_replace('　', '', $params['company_name']);

		$connectionTable  = new Shared_Model_Data_Connection();
		$selectObj = $connectionTable->select();
		$selectObj->where('status != ?', Shared_Model_Code::CONNECTION_STATUS_REMOVE);
    	$keywordString = $connectionTable->aesdecrypt('company_name', false) . ' LIKE "%' . $params['company_name'] . '%"';
    	$selectObj->where($keywordString);
		$items = $selectObj->query()->fetchAll();
		
		if (empty($items)) {
			return $this->sendJson(array('result' => false, 'message' => '該当する会社が見つかりません'));
		}
            
		return $this->sendJson(array('result' => true, 'items' => $items));
    }

    /**
     * getAction
     * 表示IDでデータ取得
     */
    public function getAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();
		
		if (empty($params['display_id'])) {
			return $this->sendJson(array('result' => false, 'message' => '取引先IDを指定してください'));
		}
		
		$connectionTable  = new Shared_Model_Data_Connection();
		$selectObj = $connectionTable->select();
    	$selectObj->where('display_id = ?', $params['display_id']);
		$items = $selectObj->query()->fetchAll();

		
		if (empty($items)) {
			return $this->sendJson(array('result' => false, 'message' => 'この取引先IDはFASSに存在しません'));
		} else if (count($items) > 1) {
			return $this->sendJson(array('result' => false, 'message' => '2件以上のデータが該当します'));
		}
            
		return $this->sendJson(array('result' => true, 'data' => $items[0]));
    }

    /**
     * addAction
     * 新規登録
     */
    public function addAction()
    {
        $request    = $this->getRequest();

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
                
                /*
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
                */
                }
                
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$connectionTable = new Shared_Model_Data_Connection();
				$logTable        = new Shared_Model_Data_ConnectionLog();
				
				// 新規登録
				/*
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
	            */
				// 業種
		    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
		    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
		    	$categoryList = $industryCategoryTable->getList();
		    	
		    	foreach ($categoryList as &$each) {
		    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
		    		
		    		
		    	}
		    	$industryCategoryList = $categoryList;
    	
				// 国
				$countryTable = new Shared_Model_Data_Country();
				$countryList = $countryTable->getList();
		
	            $connectionTable->getAdapter()->beginTransaction();
            	$connectionTable->getAdapter()->query("LOCK TABLES frs_connection WRITE, frs_connection_department WRITE, frs_connection_staff WRITE, frs_connection_log WRITE")->execute();
            	
	            try {
		            
		            
	            	$selectedIndustyType = array();

	            	foreach ($success['industry_types'] as $eachType) {
		            	foreach ($industryCategoryList as $eachCategory) {
			            	foreach ($eachCategory['items'] as $eachItem) {
				            	if ($eachType === $eachItem['name']) {
					            	$selectedIndustyType[] = $eachItem['id'];
				            	}
				            }
		            	}
	            	}
	            	
	            	$countryId = 0;
	            	foreach($countryList as $eachCountry) {
		            	if ($success['country'] === $eachCountry['name']) {
			            	$countryId = $eachCountry['id'];
		            	}
	            	}
	            	
	            	
	            
	            	$displayId = $connectionTable->getNextDisplayId();
	            
					$data = array(
				        'management_group_id'               => '1',
				        'display_id'                        => $displayId,
						'status'                            => Shared_Model_Code::CONNECTION_STATUS_ACTIVE,
						
						'gs_supplier_id'                    => 0,
						'gs_supplier_display_id'            => '',
						'gs_buyer_id'                       => 0,
						'gs_buyer_display_id'               => '',
								
						'company_name'                      => $success['company_name'],
						'company_name_kana'                 => $success['company_name_kana'],

						'type'                              => $success['type'],          // 種別
						'types_of_our_business'             => serialize(NULL),           // 関連当社事業区分
						'relation_types'                    => serialize(NULL),           // 当社取引関係
						'relation_type_other_text'          => '',                        // 当社取引関係 その他 テキスト
						'sales_relations'                   => serialize(NULL),           // 主な商談ポジション 
						'industry_types'                    => serialize($selectedIndustyType), // 業種
						
						/*
						'type'                              => !empty($success['type']) ? $success['type'] : 0, // 種別
						'types_of_our_business'             => serialize($success['types_of_our_business']),    // 関連当社事業区分
						'relation_types'                    => serialize($success['relation_types']),           // 当社取引関係
						'relation_type_other_text'          => $success['relation_type_other_text'],            // 当社取引関係 その他 テキスト
						'sales_relations'                   => serialize($success['sales_relations']),          // 主な商談ポジション 
						'industry_types'                    => serialize($success['industry_types']),           // 業種
						*/
						
						'description'                       => '',                                              // 事業内容
						'corporate_number'                  => $success['corporate_number'],                    // 法人番号
						'country'                           => $countryId,                                      // 国
						'head_office_postal_code'           => $success['head_office_postal_code'],             // 本社所在地郵便番号
						'head_office_prefecture'            => $success['head_office_prefecture'],              // 本社・都道府県
						'head_office_city'                  => $success['head_office_city'],                    // 本社・市区町村
						'head_office_address'               => $success['head_office_address'],                 // 本社・丁番地
						'head_office_building'              => $success['head_office_building'],                // 本社・建物名・階／号室
						/*
						'description'                       => $success['description'],                         // 事業内容
						'corporate_number'                  => $success['corporate_number'],                    // 法人番号
						'country'                           => $success['country'],                             // 国
						'head_office_postal_code'           => $success['head_office_postal_code'],             // 本社所在地郵便番号
						'head_office_prefecture'            => $success['head_office_prefecture'],              // 本社・都道府県
						'head_office_city'                  => $success['head_office_city'],                    // 本社・市区町村
						'head_office_address'               => $success['head_office_address'],                 // 本社・丁番地
						'head_office_building'              => $success['head_office_building'],                // 本社・建物名・階／号室
						*/

						'representative_name'               => $success['representative_name'],                 // 代表者名
						'representative_name_kana'          => $success['representative_name_kana'],            // 代表者名カナ
						'tel'                               => $success['tel'],                                 // 電話番号
						'fax'                               => $success['fax'],                                 // FAX番号
						'web_url'                           => $success['web_url'],                             // 企業URL
						'duty'                              => 0,                                               // 課税・免税
						/*
						'representative_name'               => $success['representative_name'],                 // 代表者名
						'representative_name_kana'          => $success['representative_name_kana'],            // 代表者名カナ
						
						'tel'                               => $success['tel'],                                 // 代表電話番号
						'fax'                               => $success['fax'],                                 // FAX番号
						'web_url'                           => $success['web_url'],                             // 企業URL
						'duty'                              => !empty($success['duty']) ? $success['duty'] : 0,     // 課税・免税
						*/
						
						'memo'                              => '',                        // 取引先情報メモ
						/*
						'memo'                              => $success['memo'],                                // 取引先情報メモ
						*/

						'foundation_date'                   => '',                        // 会社設立年月日
						'company_form'                      => 0,                         // 会社形態
						'capital'                           => '',                        // 資本金
						'employees'                         => '',                        // 従業員数
						'branch_offices'                    => '',                        // 営業店舗数
						/*
						'foundation_date'                   => $success['foundation_date'],                     // 会社設立年月日
						'company_form'                      => !empty($success['company_form']) ? $success['company_form'] : 0, // 会社形態
						'capital'                           => $success['capital'],                             // 資本金
						'employees'                         => $success['employees'],                           // 従業員数
						'branch_offices'                    => $success['branch_offices'],                      // 営業店舗数
						*/
						
						'main_stockholder'                  => '',                        // 主な株主
						'main_bank'                         => '',                        // 主要取引銀行
						'main_connection'                   => '',                        // 主要取引先企業
						/*
						'main_stockholder'                  => $success['main_stockholder'],                    // 主な株主
						'main_bank'                         => $success['main_bank'],                           // 主要取引銀行
						'main_connection'                   => $success['main_connection'],                     // 主要取引先企業
						*/


						'detective_season'                  => '',                        // 興信所・調査時期
						'detective_name'                    => '',                        // 興信所・調査機関名
						'detective_result'                  => '',                        // 興信所・信用格付結果
						'detective_own'                     => '',                        // 当社信用格付
						'detective_memo'                    => '',                        // 他信用特記メモ
						
						'financial_closing'                 => json_encode(array()),
						/*
						'detective_season'                  => $success['detective_season'],                    // 興信所・調査時期
						'detective_name'                    => $success['detective_name'],                      // 興信所・調査機関名
						'detective_result'                  => $success['detective_result'],                    // 興信所・信用格付結果
						'detective_own'                     => $success['detective_own'],                       // 当社信用格付
						'detective_memo'                    => $success['detective_memo'],                      // 他信用特記メモ
						
						'financial_closing'                 => json_encode($itemList),
						*/

						'created_user_id'                   => 0,
						'last_update_user_id'               => 0,                     // 最終更新者ユーザーID
	
		                'created'                           => new Zend_Db_Expr('now()'),
		                'updated'                           => new Zend_Db_Expr('now()'),
					);
					
					if (!empty($success['gs_supplier_id'])) {
						$data['gs_supplier_id']         = $success['gs_supplier_id'];
						$data['gs_supplier_display_id'] = $success['gs_supplier_display_id'];
					}
					
					if (!empty($success['gs_buyer_id'])) {
						$data['gs_buyer_id']         = $success['gs_buyer_id'];
						$data['gs_buyer_display_id'] = $success['gs_buyer_display_id'];
					}
					
					if (!empty($success['gsc_supplier_id'])) {
						$data['gsc_supplier_id']         = $success['gsc_supplier_id'];
						$data['gsc_supplier_display_id'] = $success['gsc_supplier_display_id'];
					}
					
					$connectionTable->create($data);
					$id = $connectionTable->getLastInsertedId('id');
					$connectionData = $connectionTable->getById(0, $id);
					
					// ログ
		    		$logTable->create(array(
				        'excutor_user_id'  => 0,
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
			
			    $this->sendJson(array('result' => 'OK', 'id' => $id, 'display_id' => $connectionData['display_id']));
		    	return;
			}
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }  


    /**
     * updateBankAccountAction
     * 銀行口座更新
     */
    public function updateBankAccountAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();

		if (empty($params['connection_id'])) {
			return $this->sendJson(array('result' => false));
		}
		
		$connectionTable  = new Shared_Model_Data_Connection();
		
		$connectionTable->updateById($params['connection_id'], array(
			'gs_bank_renewaled_datetime'=> new Zend_Db_Expr('now()'),
			'gs_bank_confirmed'         => Shared_Model_Code::BANK_CONFIRM_STATUS_RENEWALED,
			'gs_bank_confirmed_user_id' => 0,
			'gs_basic_bank_select'      => $params['basic_bank_select'],
			'gs_other_bank_name'        => $params['other_bank_name'],
			'gs_bank_code'              => $params['bank_code'],
			'gs_bank_branch_id'         => $params['bank_branch_id'],
			'gs_bank_branch_name'       => $params['bank_branch_name'],
			'gs_bank_account_type'      => $params['bank_account_type'],
			'gs_bank_account_no'        => $params['bank_account_no'],
			'gs_bank_account_name'      => $params['bank_account_name'],
			'gs_bank_account_name_kana' => $params['bank_account_name_kana'],
		));
		
		$connectionBankTable = new Shared_Model_Data_ConnectionBank();
		
		// 新バージョン
		$oldData = $connectionBankTable->getByRegisteredTypeAndId($params['connection_id'], $params['bank_registered_type'], $params['target_id']);

		if (empty($oldData)) {
			$data = array(
			    'connection_id'                     => $params['connection_id'],                   // 取引先ID
			    'status'                            => Shared_Model_Code::CONTENT_STATUS_ACTIVE,    // ステータス
			    
			    'bank_registered_type'              => $params['bank_registered_type'],             // 登録種別
			    
			    'target_id'                         => $params['target_id'],                        // 対象supplier/buyer id
			    'target_display_id'                 => $params['target_display_id'],                // 対象supplier/buyer 表示id
			    
			    'bank_code'                         => $params['bank_code'],                        // 金融機関コード
			    'bank_name'                         => $params['bank_name'],                        // 金融機関名
			    
			    'branch_code'                       => $params['bank_branch_id'],                   // 支店コード
			    'branch_name'                       => $params['bank_branch_name'],                 // 支店名
			    
			    'account_type'                      => $params['bank_account_type'],                // 口座種別
			    'account_no'                        => $params['bank_account_no'],                  // 口座番号
			    
			    'account_name'                      => $params['bank_account_name'],                // 口座名義
				'account_name_kana'                 => $params['bank_account_name_kana'],

                'created'                           => new Zend_Db_Expr('now()'),
                'updated'                           => new Zend_Db_Expr('now()'),
			);
			
			$connectionBankTable->create($data);
			
			
			$oldData = array(
				'id' => $connectionBankTable->getLastInsertedId('id'),
			);
			
		} else {
			$data = array(
			    'is_confirmed'                      => 0,
			    'bank_registered_type'              => $params['bank_registered_type'],             // 登録種別
			    
			    'target_id'                         => $params['target_id'],                        // 対象supplier/buyer id
			    'target_display_id'                 => $params['target_display_id'],                // 対象supplier/buyer 表示id
			    
			    'bank_code'                         => $params['bank_code'],                        // 金融機関コード
			    'bank_name'                         => $params['bank_name'],                        // 金融機関名
			    
			    'branch_code'                       => $params['bank_branch_id'],                   // 支店コード
			    'branch_name'                       => $params['bank_branch_name'],                 // 支店名
			    
			    'account_type'                      => $params['bank_account_type'],                // 口座種別
			    'account_no'                        => $params['bank_account_no'],                  // 口座番号
			    
			    'account_name'                      => $params['bank_account_name'],                // 口座名義
				'account_name_kana'                 => $params['bank_account_name_kana'],

                'created'                           => new Zend_Db_Expr('now()'),
                'updated'                           => new Zend_Db_Expr('now()'),
			);
			
			$connectionBankTable->updateById($oldData['id'], $data);
		}
		
		
		// 支払予定で紐付け可能なものがあれば紐付け
		$payableTable  = new Shared_Model_Data_AccountPayable();
		$payableItems = $payableTable->getItemsByRegisteredType($params['connection_id'], $params['bank_registered_type'], $params['target_id']);
		//var_dump($payableItems);exit;
		
		if (!empty($payableItems)) {
			foreach ($payableItems as $each) {
				$payableTable->updateById($each['id'], array('transfer_to_connection_bank_id' => $oldData['id']));
			}
		}
		
		
		return $this->sendJson(array('result' => true));
    } 
   
    
    /**
     * registerAsSupplierAction
     * goosaサプライヤーとして連携
     */
    public function registerAsSupplierAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();

		if (empty($params['connection_id']) || empty($params['supplier_id']) || empty($params['supplier_display_id'])) {
			return $this->sendJson(array('result' => false));
		}
		
		$connectionTable  = new Shared_Model_Data_Connection();
		
		$connectionTable->updateById($params['connection_id'], array(
			'gs_supplier_id'         => $params['supplier_id'],
			'gs_supplier_display_id' => $params['supplier_display_id'],
		));
		
		return $this->sendJson(array('result' => true));
    }  
    
    /**
     * registerAsBuyerAction
     * goosaバイヤーとして連携
     */
    public function registerAsBuyerAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();
		
		if (empty($params['connection_id']) || empty($params['buyer_id']) || empty($params['buyer_display_id'])) {
			return $this->sendJson(array('result' => false));
		}
		
		$connectionTable  = new Shared_Model_Data_Connection();
		
		$connectionTable->updateById($params['connection_id'], array(
			'gs_buyer_id'         => $params['buyer_id'],
			'gs_buyer_display_id' => $params['buyer_display_id'],
		));
		
		
		return $this->sendJson(array('result' => true));
    }
    
    /**
     * registerAsGooscaSupplierAction
     * GOOSCAサプライヤーとして連携
     */
    public function registerAsGooscaSupplierAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();
		
		if (empty($params['connection_id']) || empty($params['supplier_id']) || empty($params['supplier_display_id'])) {
			return $this->sendJson(array('result' => false));
		}
		
		$connectionTable  = new Shared_Model_Data_Connection();
		
		$connectionTable->updateById($params['connection_id'], array(
			'gsc_supplier_id'         => $params['supplier_id'],
			'gsc_supplier_display_id' => $params['supplier_display_id'],
		));
		
		return $this->sendJson(array('result' => true));
    }
}