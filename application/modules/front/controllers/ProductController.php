<?php
/**
 * class ProductController
 */
 
class ProductController extends Front_Model_Controller
{
    const PER_PAGE = 50;
    
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
		$this->view->mainCategoryName = '販売商品管理';
		$this->view->menuCategory     = 'product';

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/list                                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品一覧                                                   |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$session = new Zend_Session_Namespace('product_1');

		$this->view->posTop = $request->getParam('pos');

		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions) || !array_key_exists('page', $session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		
		if (!empty($search)) {
			$session->conditions['product_class']         = $request->getParam('product_class', '');
			$session->conditions['product_category']      = $request->getParam('product_category', '');
			$session->conditions['product_sales_status']  = $request->getParam('product_sales_status', '');
			$session->conditions['product_market']        = $request->getParam('product_market', '');
			$session->conditions['keyword']               = $request->getParam('keyword', '');
			
		} else if (empty($session->conditions)) {
			$session->conditions['product_class']         = '';
			$session->conditions['product_category']      = '';
			$session->conditions['product_sales_status']  = '';
			$session->conditions['product_market']        = '';
			$session->conditions['keyword']               = '';
			
		}
		
		$this->view->conditions = $conditions = $session->conditions;

		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        
        // グループID
        $selectObj->where('frs_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);

        if (!empty($session->conditions['product_class'])) {
        	$keyword = $dbAdapter->quote('%"' . $session->conditions['product_class'] . '"%');
        	$productClassString = $itemTable->aesdecrypt('product_classes', false) . ' LIKE ' . $keyword;
            $selectObj->where($productClassString);
        }

        if (!empty($session->conditions['product_category'])) {
        	$keyword = $dbAdapter->quote('%"' . $session->conditions['product_category'] . '"%');
        	$productClassString = $itemTable->aesdecrypt('product_categories', false) . ' LIKE ' . $keyword;
            $selectObj->where($productClassString);
        }

        if (!empty($session->conditions['product_market'])) {
        	$keyword = $dbAdapter->quote('%"' . $session->conditions['product_market'] . '"%');
        	$productClassString = $itemTable->aesdecrypt('product_markets', false) . ' LIKE ' . $keyword;
            $selectObj->where($productClassString);
        }
          
        if (!empty($session->conditions['product_sales_status'])) {
        	$selectObj->where('frs_item.product_sales_status = ?', $session->conditions['product_sales_status']);
        }


        if (!empty($session->conditions['keyword'])) {
        	$keywordArray = array();
        	
        	$columns = array(
        		'item_name', 'item_name_en', 'buying_item_name', 'memo', 'jan_code', 'delivery_item_name', 'delivery_item_name_en',
        	);
        	
        	foreach ($columns as $each) {
        		if ($itemTable->isCryptField($each)) {   
        			$keyword = $dbAdapter->quote('%' . $session->conditions['keyword'] . '%');     			
        			$keywordArray[] = $itemTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordArray[] = $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $session->conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where(implode(' OR ', $keywordArray));
        }
        
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$classTable = new Shared_Model_Data_ItemProductClass();
		$this->view->productClassList = $classTable->getList();
		
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->productCategoryList = $categoryTable->getList();
		
		// 調達方法
		$supplyMethodTable = new Shared_Model_Data_SupplyMethod();
		$this->view->supplyMethodList = $supplyMethodTable->getList();
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/list-select                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品選択(ポップアップ用)                                   |
    +----------------------------------------------------------------------------*/
    public function listSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');

		$conditions = array();
		$conditions['product_class']         = $request->getParam('product_class', '');
		$conditions['product_category']      = $request->getParam('product_category', '');
		$conditions['product_sales_status']  = $request->getParam('product_sales_status', '');
		$conditions['product_market']        = $request->getParam('product_market', '');
		$conditions['keyword']               = $request->getParam('keyword', '');
		$this->view->conditions = $conditions;
		
		
		$itemTable = new Shared_Model_Data_Item();
		$dbAdapter = $itemTable->getAdapter();
        $selectObj = $itemTable->select();
        
        // グループID
        $selectObj->where('frs_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', Shared_Model_Code::ITEM_TYPE_PRODUCT);
        
        
        if (!empty($conditions['product_class'])) {
        	$keyword = $dbAdapter->quote('%"' . $conditions['product_class'] . '"%');
        	$productClassString = $itemTable->aesdecrypt('product_classes', false) . ' LIKE ' . $keyword;
            $selectObj->where($productClassString);
        }

        if (!empty($conditions['product_category'])) {
        	$keyword = $dbAdapter->quote('%"' . $conditions['product_category'] . '"%');
        	$productClassString = $itemTable->aesdecrypt('product_categories', false) . ' LIKE ' . $keyword;
            $selectObj->where($productClassString);
        }

        if (!empty($conditions['product_market'])) {
        	$keyword = $dbAdapter->quote('%"' . $conditions['product_market'] . '"%');
        	$productClassString = $itemTable->aesdecrypt('product_markets', false) . ' LIKE ' . $keyword;
            $selectObj->where($productClassString);
        }
          
        if (!empty($conditions['product_sales_status'])) {
        	$selectObj->where('frs_item.product_sales_status = ?', $conditions['product_sales_status']);
        }


        if (!empty($conditions['keyword'])) {
        	$keywordArray = array();
        	
        	$columns = array(
        		'item_name', 'item_name_en', 'buying_item_name', 'memo', 'jan_code', 'delivery_item_name', 'delivery_item_name_en',
        	);
        	
        	foreach ($columns as $each) {
        		if ($itemTable->isCryptField($each)) {   
        			$keyword = $dbAdapter->quote('%' . $conditions['keyword'] . '%');     			
        			$keywordArray[] = $itemTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordArray[] = $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where(implode(' OR ', $keywordArray));
        }
        
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        
        $url = 'javascript:pageProduct($page);';
        $this->view->pager($paginator, $url);
        
		$classTable = new Shared_Model_Data_ItemProductClass();
		$this->view->productClassList = $classTable->getList();
		
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->productCategoryList = $categoryTable->getList();
		
		// 調達方法
		$supplyMethodTable = new Shared_Model_Data_SupplyMethod();
		$this->view->supplyMethodList = $supplyMethodTable->getList();
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/delete                                            |
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
			$itemTable = new Shared_Model_Data_Item();

			try {
				$itemTable->getAdapter()->beginTransaction();
				
				$itemTable->updateById($id, array(
					'status' => Shared_Model_Code::ITEM_STATUS_REMOVE,
				));
			
                // commit
                $itemTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $itemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/product/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/detail-selected                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 選択済み商品情報(ポップアップ用)                           |
    +----------------------------------------------------------------------------*/
    public function detailSelectedAction()
    {
        $this->_helper->layout->setLayout('blank');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->confirm = $request->getParam('confirm');

		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/import                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取引先取込画面                                             |
    +----------------------------------------------------------------------------*/
    public function importAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/product/list';
        
		$request    = $this->getRequest();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/import-csv                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品管理 CSV取込                                           |
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
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/add                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品新規登録                                               |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		
		$classTable = new Shared_Model_Data_ItemProductClass();
		$this->view->productClassList = $classTable->getList();
		
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->productCategoryList = $categoryTable->getList();
		
		// 調達方法
		$supplyMethodTable = new Shared_Model_Data_SupplyMethod();
		$this->view->supplyMethodList = $supplyMethodTable->getList();
		
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/add-post                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品新規登録(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$typeCode = Shared_Model_Code::ITEM_TYPE_PRODUCT;

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				
				if (!empty($errorMessage['product_name_type']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「商品名の区分」を選択してください'));
                    return;
				} else if (!empty($errorMessage['product_classes']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「調達製造区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['product_categories']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「商品区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['product_sales_status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「販売状況」を選択してください'));
                    return;
                } else if (!empty($errorMessage['product_markets']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「販売可能範囲」を選択してください'));
                    return;
                } else if (!empty($errorMessage['strategy']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「商品戦略」を選択してください'));
                    return;
                }
                
	    		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
                return;
	    		
			} else {
				if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES
				 || $success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES_AND_SUPPLY) {
					if (empty($success['item_name'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「販売商品名(日本語)」を入力してください'));
                    	return;
					}
					
				}
				
				if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SUPPLY
				 || $success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES_AND_SUPPLY) {
					if (empty($success['buying_item_name'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「仕入商品名」を入力してください'));
                    	return;
					}
				}
				
				if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES) {
					$success['buying_item_name'] = '';
				} else if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SUPPLY) {
					$success['item_name']    = '';
					$success['item_name_en'] = '';
				}
			
				$itemTable     = new Shared_Model_Data_Item();
				
				$itemTypeId = $itemTable->getNextItemTypeId(Shared_Model_Code::ITEM_TYPE_PRODUCT);
				$displayId  = $itemTable->getNextDisplayIdWithItemType(Shared_Model_Code::ITEM_TYPE_PRODUCT);
				
				// 新規登録
				$data = array(
			        'management_group_id'           => '1',                                         // 管理グループID
					'status'                        => Shared_Model_Code::ITEM_STATUS_ACTIVE,       // ステータス
					'item_type'                     => Shared_Model_Code::ITEM_TYPE_PRODUCT,        // アイテム種別
					'item_type_id'                  => $itemTypeId,                                 // アイテム種別ID
					'display_id'                    => $displayId,                                  // 表示ID XX＋西暦下二桁＋5桁
					
					'strategy'                      => $success['strategy'],                        // 商品戦略
					
					'category_id'                   => 0,
					'product_name_type'             => $success['product_name_type'],               // 商品名の区分
					'item_name'                     => $success['item_name'],                       // アイテム名(日本語)
					'item_name_en'                  => $success['item_name_en'],                    // アイテム名(英語)
					'buying_item_name'              => $success['buying_item_name'],                // 仕入商品名
					
					'delivery_item_name'            => '',                                          // 配送向け内容品表記(日本語)
					'delivery_item_name_en'         => '',                                          // 配送向け内容品表記(英語)
					
					'jan_code'                      => $success['jan_code'],                        // JANコード
					
					'connection_id'                 => 0,                                           // (廃止)
					'connection_base_name'          => '',                                          // ?      取引拠点名

					'product_classes'               => serialize($success['product_classes']),      // 区分
					'product_class_other_text'      => $success['product_class_other_text'],        // 区分 その他テキスト
					'product_categories'            => serialize($success['product_categories']),   // 分類
					'product_category_other_text'   => $success['product_category_other_text'],     // 分類 その他テキスト
					'product_markets'               => serialize($success['product_markets']),      // 販売可能範囲
					
					'product_sales_status'          => $success['product_sales_status'],            // 販売状況
					'next_generation_item_id'       => 0,                                           // 後継品商品ID
					'sales_status_memo'             => $success['sales_status_memo'],               // 取扱状況メモ
			
					'supply_methods'                => serialize($success['supply_methods']),   // 調達方法
					'supply_method_other_text'      => $success['supply_method_other_text'],    // 調達方法 その他 テキスト
					'supply_method_memo'            => $success['supply_method_memo'],          // 調達方法メモ
					
					'production_process'            => $success['production_process'],
					'production_process_other_text' => $success['production_process_other_text'],
					
					'registered_user_id'            => 0,   // 登録者社員ID
					
					'estimation_date'               => NULL,   // 入手見積書日付 
					'estimation_files'              => '',   // 入手見積書・補足資料アップロード
					'memo_estimation'               => '',   // 見積メモ
					
					'requested_sale_price'          => 0,   // 希望小売価格
					'requested_wholesale_price'     => 0,   // 希望卸価格
					'restriction_price'             => '',   // 販売価格制約
					'restriction_method'            => '',   // 販売方法制約
					'sales_condition_memo'          => '',   // 販売条件メモ
					
					'purchasing_lot_1'              => 0,   // 購入ロット1
					'purchasing_lot_unit1'          => '',  // ロット単位1
					'purchasing_lot_price1'         => 0,   // 仕入価格1
					'purchasing_logistics_cost1'    => 0,   // 物流費1
			
					'purchasing_lot_2'              => 0,   // 購入ロット2
					'purchasing_lot_unit2'          => '',  // ロット単位2
					'purchasing_lot_price2'         => 0,   // 仕入価格2
					'purchasing_logistics_cost2'    => 0,   // 物流費2
			
					'purchasing_lot_3'              => 0,   // 購入ロット3
					'purchasing_lot_unit3'          => '',  // ロット単位3
					'purchasing_lot_price3'         => 0,   // 仕入価格3
					'purchasing_logistics_cost3'    => 0,   // 物流費3
					
					'purchasing_lot_4'              => 0,   // 購入ロット4
					'purchasing_lot_unit4'          => '',  // ロット単位4
					'purchasing_lot_price4'         => 0,   // 仕入価格4
					'purchasing_logistics_cost4'    => 0,   // 物流費4
					
					'purchasing_lot_5'              => 0,   // 購入ロット5
					'purchasing_lot_unit5'          => '',  // ロット単位5
					'purchasing_lot_price5'         => 0,   // 仕入価格5
					'purchasing_logistics_cost5'    => 0,   // 物流費5
					
					'stock_count'                   => 0,  // 在庫数
					'useable_count'                 => 0,  // 引当可能在庫数
					'alert_count'                   => 0,  // アラート在庫数
					'minimum_count'                 => 0,  // 最低在庫数
					'safety_count'                  => 0,  // 安全在庫数
					
					
					'unit_type'                     => 0,  // 単位種別
					'image_file_name'               => '', // 画像ファイル名
					'memo'                          => $success['memo'], // 商品内容
					
					'created_user_id'               => $this->_adminProperty['id'],
					'last_update_user_id'           => $this->_adminProperty['id'],
					
	                'created'                       => new Zend_Db_Expr('now()'),
	                'updated'                       => new Zend_Db_Expr('now()'),
				);

				if (!empty($success['next_generation_item_id'])) {
					$data['next_generation_item_id'] = $success['next_generation_item_id'];
				}
				
				$itemTable->getAdapter()->beginTransaction();
            	  
	            try {
					$itemTable->create($data);
					$itemId = $itemTable->getLastInsertedId('id');
					
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/prodict/add-post/:type transaction faied: ' . $e);
	                
	            }
				
			    $this->sendJson(array('result' => 'OK', 'id' => $itemId));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/basic                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報                                                   |
    +----------------------------------------------------------------------------*/
    public function basicAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

		$classTable = new Shared_Model_Data_ItemProductClass();
		$this->view->productClassList = $classTable->getList();
		
		$categoryTable = new Shared_Model_Data_ItemProductCategory();
		$this->view->productCategoryList = $categoryTable->getList();
		
		// 調達方法
		$supplyMethodTable = new Shared_Model_Data_SupplyMethod();
		$this->view->supplyMethodList = $supplyMethodTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-basic                                      |
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

				if (!empty($errorMessage['product_name_type']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「商品名の区分」を選択してください'));
                    return;
				} else if (!empty($errorMessage['product_classes']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「調達製造区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['product_categories']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「商品区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['product_sales_status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「販売状況」を選択してください'));
                    return;
                } else if (!empty($errorMessage['product_markets']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「販売可能範囲」を選択してください'));
                    return;
                } else if (!empty($errorMessage['strategy']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「商品戦略」を選択してください'));
                    return;
                }
                
	    		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
                return;
	    		
			} else {
				if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES
				 || $success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES_AND_SUPPLY) {
					if (empty($success['item_name'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「販売商品名(日本語)」を入力してください'));
                    	return;
					}
				}
				
				if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SUPPLY
				 || $success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES_AND_SUPPLY) {
					if (empty($success['buying_item_name'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「仕入商品名」を入力してください'));
                    	return;
					}
				}
				
				if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SALES) {
					$success['buying_item_name'] = '';
				} else if ($success['product_name_type'] === (string)Shared_Model_Code::PRODUCT_NAME_TYPE_SUPPLY) {
					$success['item_name']    = '';
					$success['item_name_en'] = '';
				}
			
				$itemTable = new Shared_Model_Data_Item();
	
				// 更新
				$data = array(
					'product_name_type'             => $success['product_name_type'],               // 商品名の区分
					'item_name'                     => $success['item_name'],
					'item_name_en'                  => $success['item_name_en'],
					'buying_item_name'              => $success['buying_item_name'],
					'delivery_item_name'            => $success['delivery_item_name'],
					'delivery_item_name_en'         => $success['delivery_item_name_en'],
					
					'strategy'                      => $success['strategy'],                        // 商品戦略
					
					'memo'                          => $success['memo'],
					'jan_code'                      => $success['jan_code'],
					
					'product_classes'               => serialize($success['product_classes']),      // 区分
					'product_class_other_text'      => $success['product_class_other_text'],        // 区分 その他テキスト
					'product_categories'            => serialize($success['product_categories']),   // 分類
					'product_category_other_text'   => $success['product_category_other_text'],     // 分類 その他テキスト
					'product_markets'               => serialize($success['product_markets']),      // 販売可能範囲
					
					'product_sales_status'          => $success['product_sales_status'],            // 販売状況
					'next_generation_item_id'       => 0,                                           // 後継品商品ID
					'sales_status_memo'             => $success['sales_status_memo'],               // 取扱状況メモ
			
					'supply_methods'                => serialize($success['supply_methods']),   // 調達方法
					'supply_method_other_text'      => $success['supply_method_other_text'],    // 調達方法 その他 テキスト
					'supply_method_memo'            => $success['supply_method_memo'],          // 調達方法メモ
					
					'production_process'            => $success['production_process'],
					'production_process_other_text' => $success['production_process_other_text'],
					
					'last_update_user_id'           => $this->_adminProperty['id'],
				);
				
				if (!empty($success['next_generation_item_id'])) {
					$data['next_generation_item_id'] = $success['next_generation_item_id'];
				}

				$itemTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-connection-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 販売条件更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateConnectionListAction()
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
				$connectionList = array();
	            if (!empty($success['relational_connection_list'])) {
	            	$connectionIdList = explode(',', $success['relational_connection_list']);
	            	$count = 1;
		            foreach ($connectionIdList as $eachId) {
		                $connectionList[] = array(
							'id'                         => $count,
							'relational_connection_type' => $request->getParam($eachId . '_relational_connection_type'),
							'target_connection_id'       => $request->getParam($eachId . '_target_connection_id'),
		                );
		                $count++;
		            }
	            }

				$itemTable = new Shared_Model_Data_Item();
	            $itemTable->getAdapter()->beginTransaction();
            	
				$data = array(
					'relational_connection_list'  => json_encode($connectionList),
					
					'last_update_user_id'         => $this->_adminProperty['id'],
				);

				$itemTable->updateById($id, $data);
					
                // commit
                $itemTable->getAdapter()->commit();
				
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-sales-condition                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 販売条件更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateSalesConditionAction()
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
				$itemTable = new Shared_Model_Data_Item();
	
				// 更新
				$data = array(
	        		'price_restriction_for_customer'        => $success['price_restriction_for_customer'],         // 顧客向け当社販売価格制約
					'method_restriction_for_customer_text'  => $success['method_restriction_for_customer_text'],   // 顧客向け当社販売価格制約メモ
					'price_restriction_from_supplier'       => $success['price_restriction_from_supplier'],        // 仕入先上代遵守ルール有無
					'price_restriction_from_supplier_price' => $success['price_restriction_from_supplier_price'],  // 仕入先上代遵守ルール有無 金額
					'method_restriction_from_supplyer_text' => $success['method_restriction_from_supplyer_text'],  // 仕入先指定販売条件
                
					'sales_condition_memo'                  => $success['sales_condition_memo'],                   // 販売条件メモ

					'last_update_user_id'                   => $this->_adminProperty['id'],
				);

				$itemTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-cost                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原価更新(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function updateCostAction()
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
  
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable = new Shared_Model_Data_Item();
	
				// 更新
				$data = array(
					'cost_memo'              => $success['cost_memo'],
					'last_update_user_id'    => $this->_adminProperty['id'],
				);

				$itemTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-purchasing-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 仕入購入条件更新(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function updatePurchasingListAction()
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
				$purchasingList = array();
				
	            if (!empty($success['purchasing_list'])) {
	            	$purchasingIdList = explode(',', $success['purchasing_list']);
	            	$count = 1;
		            foreach ($purchasingIdList as $eachId) {
		                $purchasingList[] = array(
							'id'                    => $count,
							'lot_amount'            => $request->getParam($eachId . '_lot_amount'),
							'lot_unit'              => $request->getParam($eachId . '_lot_unit'),
							'unit_price'            => $request->getParam($eachId . '_unit_price'),
							'total_price'           => $request->getParam($eachId . '_total_price'),
							'delivery_cost'         => $request->getParam($eachId . '_delivery_cost'),
							'purchasing_memo'       => $request->getParam($eachId . '_purchasing_memo'),
		                );
		                $count++;
		            }
	            }

				$itemTable = new Shared_Model_Data_Item();
	            $itemTable->getAdapter()->beginTransaction();
            	
	            try {
					$data = array(
						'purchasing_list'       => json_encode($purchasingList),
						
						'last_update_user_id'    => $this->_adminProperty['id'],
					);

					$itemTable->updateById($id, $data);
						
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/product/update-purchasing-list transaction failed: ' . $e);
	                
	            }
				
				$this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/supplying-price                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 調達・購入条件                                             |
    +----------------------------------------------------------------------------*/
    public function supplyingPriceAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		$this->view->materialKind = $request->getParam('material_kind');
		
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$productProjectTable        = new Shared_Model_Data_SupplyProductProject();
		$productionProjectTable     = new Shared_Model_Data_SupplyProductionProject();
		$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
		$fixtureProjectTable        = new Shared_Model_Data_SupplyFixtureProject();
		
		
		$dbAdapter = $productProjectTable->getAdapter();
		$keyword = $dbAdapter->quote('%"' . $id .'"%');  
		$keywordString = $itemTable->aesdecrypt('item_ids', false) . ' LIKE ' . $keyword;
	
		// 原料・製品
		$selectObjProduct = $productProjectTable->select();
        $selectObjProduct->where($keywordString);
        $this->view->supplyProductList = $selectObjProduct->query()->fetchAll();
		
		// 製造加工委託
		$selectObjProduction = $productionProjectTable->select();
        $selectObjProduction->where($keywordString);
        $this->view->supplyProductionList = $selectObjProduction->query()->fetchAll();
		
		// 業務委託
		$selectObjSubcontracting = $subcontractingProjectTable->select();
        $selectObjSubcontracting->where($keywordString);
        $this->view->supplySubcontractingList = $selectObjSubcontracting->query()->fetchAll();

		// 備品・資材
		$selectObjFixture = $fixtureProjectTable->select();
        $selectObjFixture->where($keywordString);
        $this->view->supplyFixtureList = $selectObjFixture->query()->fetchAll();
        
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/supplying-material                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 調達・資料                                                 |
    +----------------------------------------------------------------------------*/
    public function supplyingMaterialAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		$this->view->materialKind = $request->getParam('material_kind');
		
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$productProjectTable        = new Shared_Model_Data_SupplyProductProject();
		$productionProjectTable     = new Shared_Model_Data_SupplyProductionProject();
		$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
		$fixtureProjectTable        = new Shared_Model_Data_SupplyFixtureProject();
		
		
		$dbAdapter = $productProjectTable->getAdapter();
		$keyword = $dbAdapter->quote('%"' . $id .'"%');  
		$keywordString = $itemTable->aesdecrypt('item_ids', false) . ' LIKE ' . $keyword;
	
		// 原料・製品
		$selectObjProduct = $productProjectTable->select();
        $selectObjProduct->where($keywordString);
        $this->view->supplyProductList = $selectObjProduct->query()->fetchAll();
		
		// 製造加工委託
		$selectObjProduction = $productionProjectTable->select();
        $selectObjProduction->where($keywordString);
        $this->view->supplyProductionList = $selectObjProduction->query()->fetchAll();
		
		// 業務委託
		$selectObjSubcontracting = $subcontractingProjectTable->select();
        $selectObjSubcontracting->where($keywordString);
        $this->view->supplySubcontractingList = $selectObjSubcontracting->query()->fetchAll();

		// 備品・資材
		$selectObjFixture = $fixtureProjectTable->select();
        $selectObjFixture->where($keywordString);
        $this->view->supplyFixtureList = $selectObjFixture->query()->fetchAll();
        
		// 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$this->view->currencyList = $currencyTable->getList($this->_adminProperty['management_group_id']);
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/image                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品画像                                                   |
    +----------------------------------------------------------------------------*/
    public function imageAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$imageTable  = new Shared_Model_Data_ItemImage();
		
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		// 商品画像リスト
		$this->view->items = $imageTable->getListByItemId($this->_adminProperty['management_group_id'], $id);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/upload-image                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品画像アップロード                                       |
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

		$fileName = uniqid() . '.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE;

		$tempFilePath = Shared_Model_Resource_Temporary::getResourceObjectPath($fileName);
		$img =  $this->imageCreateFromAny($_FILES['image']['tmp_name']);

        if (empty($img)) {
        	throw new Zend_Exception('/product/upload-image no object image');
        }
        
        $width = ImageSx($img);
        $height = ImageSy($img);
        
        if ($width > 840) {
	        $resizedWidth = 840;
	        $out = ImageCreateTrueColor($resizedWidth, $height/ $width * $resizedWidth);
	        ImageCopyResampled($out, $img, 0,0,0,0, $resizedWidth, floor($height/ $width * $resizedWidth), $width, $height);
        } else {
        	$out = ImageCreateTrueColor($width, $height);
        	ImageCopyResampled($out, $img, 0,0,0,0, $width, $height, $width, $height);
        }
        
    	// 画像を保存
        $result = ImageJPEG($out, $tempFilePath);
        
        if ($result === false) {
        	throw new Zend_Exception('/product/upload-image save failed');
        }
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName, 'file_url' => Shared_Model_Resource_Temporary::getImageUrl($fileName)));
        return;
	}
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/upload-file                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ファイルアップロード(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function uploadFileAction()
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
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($fileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName));
        return;
	}
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/image-edit                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品画像 追加・編集                                        |
    +----------------------------------------------------------------------------*/
    public function imageEditAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->imageId  = $imageId = $request->getParam('image_id');
		
		$itemTable  = new Shared_Model_Data_Item();
		$imageTable = new Shared_Model_Data_ItemImage();

		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (empty($imageId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->imageData = array(
				'file_name'           => '',
				'summary'             => '',
			);
			
		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	if ($imageId != 'inspection') {
	        	$data = $imageTable->getById($this->_adminProperty['management_group_id'], $imageId);
		        if (empty($data)) {
					throw new Zend_Exception('/product/imaget-edit filed to fetch data');
				}
				
				$this->view->imageData = $data;
        	}
        	
        }
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/gs-item-add                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GS商品登録                                                 |
    +----------------------------------------------------------------------------*/
    public function gsItemAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		$apiResult = Shared_Model_Gs_Item::getMainCatgeoryList($clientData);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->mainCategoryList = $apiResult['list'];
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/gs-item-add-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function gsItemAddPostAction()
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
	    		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しましたあ'));
                return;
	    		
			} else {
				$itemTable = new Shared_Model_Data_Item();
				$itemData = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
				$success['supplier_item_id'] = $itemData['display_id'];
				
				if ($success['connect_method'] === '2') {
					// Goosa連携
					$clientData = array(
						'management_web_use_basic_auth' => true,
						'management_web_basic_user' => 'goosa',
						'management_web_basic_pass' => 'goosa',
						'supplier_id'               => '8',
					);
					
					$apiResult = Shared_Model_Gs_Item::addItem($clientData, $success);
			
					if ($apiResult === false) {
						throw new Zend_Exception('api Failed');
					} else if ($apiResult['result'] === false) {
						$this->sendJson(array('result' => 'NG', 'message' => $apiResult['message']));
						return;
					}
					
					$success['gs_display_id'] = $apiResult['id'];
					
				} else {
					if (empty($success['gs_display_id'])) {
						$this->sendJson(array('result' => 'NG', 'message' => '「goosa商品管理番号」を入力してください'));
						return;
					}
					
					// Goosa連携
					$clientData = array(
						'management_web_use_basic_auth' => true,
						'management_web_basic_user' => 'goosa',
						'management_web_basic_pass' => 'goosa',
						'supplier_id'               => '8',
					);
					
					$apiResult = Shared_Model_Gs_Item::connectItem($clientData, $success);
			
					if ($apiResult === false) {
						throw new Zend_Exception('api Failed');
					} else if ($apiResult['result'] === false) {
						$this->sendJson(array('result' => 'NG', 'message' => $apiResult['message']));
						return;
					}
				}
	
				// 更新
				$data = array(
					'gs_display_id' => $success['gs_display_id'], // 商品名の区分
				);

				$itemTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/load-sub-category                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サブカテゴリ読み込み                                       |
    +----------------------------------------------------------------------------*/
    public function loadSubCategoryAction()
    {
        $this->_helper->layout->setLayout('blank');
        
		$request = $this->getRequest();
		$maincategoryId = $request->getParam('category_id');

		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		$apiResult = Shared_Model_Gs_Item::getSubCatgeoryList($clientData, $maincategoryId);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->subCategoryList = $apiResult['list'];
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/load-third-category                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 第3カテゴリ読み込み                                        |
    +----------------------------------------------------------------------------*/
    public function loadThirdCategoryAction()
    {
        $this->_helper->layout->setLayout('blank');
        
		$request = $this->getRequest();
		$subcategoryId = $request->getParam('sub_category_id');

		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		$apiResult = Shared_Model_Gs_Item::getThirdCatgeoryList($clientData, $subcategoryId);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->thirdCategoryList = $apiResult['list'];
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/load-fourth-category                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 第4カテゴリ読み込み                                        |
    +----------------------------------------------------------------------------*/
    public function loadForthCategoryAction()
    {
        $this->_helper->layout->setLayout('blank');
        
		$request = $this->getRequest();
		$thirdCategoryId = $request->getParam('third_category_id');

		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		$apiResult = Shared_Model_Gs_Item::getForthCatgeoryList($clientData, $thirdCategoryId);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->fourthCategoryList = $apiResult['list'];
    }
    
        
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/gs-basic                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GS基本条件                                                 |
    +----------------------------------------------------------------------------*/
    public function gsBasicAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		$apiResult = Shared_Model_Gs_Item::getDataByDisplayId($clientData, $data['gs_display_id']);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->gsData = $apiResult['data'];
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-gs-basic                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 卸サイト連携情報更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function updateGsBasicAction()
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
				$itemTable = new Shared_Model_Data_Item();
	
				// 更新
				$data = array(
			        'gs_item_name'                 => $success['gs_item_name'],             // GS商品名
			        'gs_item_name_kana'            => $success['gs_item_name_kana'],        // GS商品名（ふりがな）
			        'gs_price_display_method'      => $success['gs_price_display_method'],  // GS卸価格提示条件
			        
			        'gs_sales_status'              => $success['gs_sales_status'],          // GS販売状況
			        'gs_sales_method'              => $success['gs_sales_method'],          // GS販売方法
			        'gs_sales_start_date'          => $success['gs_sales_start_date'],      // GS掲載開始日
			        'gs_sales_end_date'            => $success['gs_sales_end_date'],        // GS掲載終了日
			        'gs_price_discount'            => $success['gs_price_discount'],        // 割引設定
			        'gs_price_discount_percent'    => $success['gs_price_discount_percent'],     // 割引%
			        'gs_price_discount_start_date' => $success['gs_price_discount_start_date'],  // 割引開始日
			        'gs_price_discount_end_date'   => $success['gs_price_discount_end_date'],    // 割引終了日
			        
			        'gs_stock_status'              => $success['gs_stock_status'],  // 在庫状況
			        'gs_stamp'                     => $success['gs_stamp'],         // スタンプ
			        'gs_img_license'               => $success['gs_img_license'],   // 画像転載許可
			        'gs_img_supplying'             => $success['gs_img_supplying'], // GS広告作成用の画像提供
			        'gs_bulk_discount'             => $success['gs_bulk_discount'], // まとめ買い割引設定
				);

				$itemTable->updateById($id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/gs-price                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GS価格設定                                                 |
    +----------------------------------------------------------------------------*/
    public function gsPriceAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id     = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->direct = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		// マスター標準卸価格
		$itemPriceTable = new Shared_Model_Data_ItemPrice();
		$this->view->masterPriceList = $itemPriceTable->getDefaultActiveListByItemId($this->_adminProperty['management_group_id'], $id);

		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		
		// 商品データ
		$apiResult = Shared_Model_Gs_Item::getDataByDisplayId($clientData, $data['gs_display_id']);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->gsItemData = $apiResult['data'];
		
		// 標準卸価格リスト
		$apiResult = Shared_Model_Gs_Item::getPriceListByDisplayId($clientData, $data['gs_display_id']);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->gsPriceList = $apiResult['list'];
		
		// 送料設定リスト
		$apiResult = Shared_Model_Gs_SupplierSetting::getShippingSettingList($clientData);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$shippingSettingList = array();
		
		foreach ($apiResult['list'] as $each) {
			$shippingSettingList[$each['id']] = $each;
		}
		
		$this->view->shippingSettingList = $shippingSettingList;
		
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /supplier/item/update-gs-price                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品 - 価格前提条件更新(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function updateGsPriceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);
			
			$postData = $request->getPost();
			
			if (empty($postData['price_open_condition'])) {
                $this->sendJson(array('result' => 'NG', 'message' => '「卸価格提示方法」を選択してください'));
                return;
            }
                
			if ($postData['price_open_condition'] !== (string)Shared_Model_Code::GS_PRICE_OPEN_COMUNICATION) {
				$postData['price_default'] = Shared_Model_Code::GS_PRICE_DEFAULT_ON; 
			}
			
            $validationResult = $validate->execute($postData);
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['price_default']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「標準卸価格」を選択してください'));
                    return;
                } else if (!empty($errorMessage['sales_domestic']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「国内小売可否」を選択してください'));
                    return;
                } else if (!empty($errorMessage['sales_price_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「上代価格種類」を選択してください'));
                    return;
                } else if (!empty($errorMessage['tax_rate']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「消費税率」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable = new Shared_Model_Data_Item();
				$data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
				
				if (empty($data)) {
					throw new Zend_Exception('/product/update-gs-price no data');
				}
		
				// isActive
				
				if ($success['price_default'] === (string)Shared_Model_Code::GS_PRICE_DEFAULT_OFF) {
					$success['estimate_advisability'] = Shared_Model_Code::GS_ESTIMATE_ADVISABILITY_OK;
				}
				
				
				// 更新
				$updateParams = array(
					'price_open_condition'   => $success['price_open_condition'],   // 卸価格提示条件
					'price_default'          => $success['price_default'],          // 卸標準価格
					'estimate_advisability'  => $success['estimate_advisability'],  // 個別見積可否
					'sales_domestic'         => $success['sales_domestic'],         // 国内小売可否
					'sales_price_type'       => $success['sales_price_type'],       // 上代価格種類
					'tax_rate'               => $success['tax_rate'],               // 消費税率
				);


				// Goosa連携
				$clientData = array(
					'management_web_use_basic_auth' => true,
					'management_web_basic_user' => 'goosa',
					'management_web_basic_pass' => 'goosa',
					'supplier_id'               => '8',
				);
				
				$apiResult = Shared_Model_Gs_Item::update($clientData, $data['gs_display_id'], $updateParams);
		
				if ($apiResult === false) {
					throw new Zend_Exception('api Failed');
				} else if ($apiResult['result'] === false) {
					$this->sendJson(array('result' => 'NG', 'message' => $apiResult['message']));
					return;
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/gs-price-add                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GS価格設定 - 新規登録                                      |
    +----------------------------------------------------------------------------*/
    public function gsPriceAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->itemId = $itemId = $request->getParam('item_id');
		$this->view->direct = $direct = $request->getParam('direct');
		
		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		// 送料設定リスト
		$apiResult = Shared_Model_Gs_SupplierSetting::getShippingSettingList($clientData);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->shippingSettingList = $apiResult['list'];
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/gs-price-edit                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GS価格設定 - 編集                                          |
    +----------------------------------------------------------------------------*/
    public function gsPriceEditAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '保存';
        
		$request = $this->getRequest();
		$this->view->itemId  = $itemId  = $request->getParam('item_id');
		$this->view->direct  = $direct  = $request->getParam('direct');
		$this->view->lotId   = $lotId   = $request->getParam('lot_id');
        
        $itemPriceTable = new Shared_Model_Data_ItemPrice();
		$this->view->data = $itemPriceTable->getById($this->_adminProperty['management_group_id'], $lotId);
		
		// Goosa連携
		$clientData = array(
			'management_web_use_basic_auth' => true,
			'management_web_basic_user' => 'goosa',
			'management_web_basic_pass' => 'goosa',
			'supplier_id'               => '8',
		);
		// 送料設定リスト
		$apiResult = Shared_Model_Gs_SupplierSetting::getShippingSettingList($clientData);

		if ($apiResult === false) {
			throw new Zend_Exception('Export Failed');
		}
		
		$this->view->shippingSettingList = $apiResult['list'];  
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-gs-price-lot                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * GS価格設定 - 更新                                          |
    +----------------------------------------------------------------------------*/
    public function updateGsPriceLotAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id');
		$lotId   = $request->getParam('lot_id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['branch_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「商品枝番号」を入力してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「内訳・内容」を入力してください'));
                    return;            
                } else if (!empty($errorMessage['lot']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「注文ロット（セット）数」を入力してください'));
                    return;

                } else if (!empty($errorMessage['lot']['notNumericInteger'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「注文ロット（セット）数」は数字のみ(コンマ可)で入力してください'));
                    return;  

                } else if (!empty($errorMessage['lot_unit_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「注文ロット（セット）単位名称」を入力してください'));
                    return;
                    

                } else if (!empty($errorMessage['sales_price']['notNumericInteger'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「上代価格(税抜)」は数字のみ(コンマ可)で入力してください'));
                    return;

                              
                } else if (!empty($errorMessage['unit_price']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「卸価格単価(税抜)」を入力してください'));
                    return;
                     
                } else if (!empty($errorMessage['unit_price']['notNumericInteger'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「卸価格単価(税抜)」は数字のみ(コンマ可)で入力してください'));
                    return;

                } else if (!empty($errorMessage['setting_shipping_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「送料設定」を選択してください'));
                    return;   
                }
	            
			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				$itemPriceTable = new Shared_Model_Data_ItemPrice();
				
				$existBranchData = $itemPriceTable->getByBranchId($this->_adminProperty['management_group_id'], $success['branch_id'], $lotId);
				
				if (!empty($existBranchData)) {
					$this->sendJson(array('result' => 'NG', 'message' => 'この「枝番号」はすでに使用されています'));
                    return;
				}

				if (empty($lotId)) {
					// 新規登録
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'], // 管理グループID
				        'item_id'             => $itemId,                                      // 商品ID
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,     // ステータス
				
						'branch_id'           => $success['branch_id'],            // 商品枝番号
						
						'title'               => $success['title'],                // ロット名称					
						'lot'                 => $success['lot'],                  // ロット単位
						'lot_unit_name'       => $success['lot_unit_name'],        // ロット単位名称
						
						'sales_price'         => $success['sales_price'],          // 上代価格(税抜)
						'unit_price'          => $success['unit_price'],           // 卸単価(税抜)
						
						'setting_shipping_id' => $success['setting_shipping_id'],  // 送料設定

						'display_order'       => $itemPriceTable->getNextOrderWithItemId($this->_adminProperty['management_group_id'], $itemId),
	
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$itemPriceTable->create($data);
					$lotId = $itemPriceTable->getLastInsertedId('id');
					
				} else {
					// 更新
					$data = array(
						'branch_id'           => $success['branch_id'],            // 商品枝番号
						
						'title'               => $success['title'],                // ロット名称					
						'lot'                 => $success['lot'],                  // ロット単位
						'lot_unit_name'       => $success['lot_unit_name'],        // ロット単位名称
						
						'sales_price'         => $success['sales_price'],          // 上代価格(税抜)
						'unit_price'          => $success['unit_price'],           // 卸単価(税抜)
						
						'setting_shipping_id' => $success['setting_shipping_id'],  // 送料設定
					);
					
					$itemPriceTable->updateById($this->_adminProperty['management_group_id'], $lotId, $data);
				}
				
				
			    $this->sendJson(array('result' => 'OK', 'id' => $lotId));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }


    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/price-lot-copy                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 標準卸価格コピー(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function priceLotCopyAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id');
		$lotId   = $request->getParam('lot_id');

		// POST送信時
		if ($request->isPost()) {
			$itemTable = new Shared_Model_Data_Item();
			$data = $itemTable->getById($this->_adminProperty['management_group_id'], $itemId);
			
			if (empty($data)) {
				throw new Zend_Exception('/product/price-lot-copy no data');
			}
	
			$itemTable->getAdapter()->beginTransaction();
			
			try {					
				$itemPriceTable = new Shared_Model_Data_ItemPrice();
				$priceData = $itemPriceTable->getById($this->_adminProperty['management_group_id'], $lotId);
				
				unset($priceData['id']);
				$priceData['branch_id']     = $priceData['branch_id'] . '_copy';
				$priceData['title']         = $priceData['title'] . 'のコピー';
				$priceData['display_order'] = $itemPriceTable->getNextOrderWithItemId($this->_adminProperty['management_group_id'], $itemId);
				$itemPriceTable->create($priceData);
				
				// commit
				$itemTable->getAdapter()->commit();
           
			} catch (Exception $e) {
            	$itemTable->getAdapter()->rollBack();
				throw new Zend_Exception('/product/price-lot-copy transaction faied: ' . $e);
				
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/price-lot-order                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 標準卸価格並び順(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function priceLotOrderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request   = $this->getRequest();
		$id        = $request->getParam('id');
		$lotId     = $request->getParam('lot_id');
		$direction = $request->getParam('direction');
		
		// POST送信時
		if ($request->isPost()) {
			$itemTable = new Shared_Model_Data_Item();
			$itemPriceTable = new Shared_Model_Data_ItemPrice();
			
			$data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
			
			if (empty($data)) {
				throw new Zend_Exception('/product/copy-price-lot no data');
			}

			$itemPriceTable->getAdapter()->beginTransaction();
			
			try {
				$targetItem = $itemPriceTable->getById($this->_adminProperty['management_group_id'], $lotId);
				
				if ($direction == 'up') {
					$preItem = $itemPriceTable->getPreOrderItem($this->_adminProperty['management_group_id'], $id, $targetItem['display_order']);
					$itemPriceTable->updateById($this->_adminProperty['management_group_id'], $lotId, array('display_order' => $preItem['display_order']));
					$itemPriceTable->updateById($this->_adminProperty['management_group_id'], $preItem['id'], array('display_order' => $targetItem['display_order']));
				} else {
					$nextItem = $itemPriceTable->getNextOrderItem($this->_adminProperty['management_group_id'], $id, $targetItem['display_order']);
					
					$itemPriceTable->updateById($this->_adminProperty['management_group_id'], $lotId, array('display_order' => $nextItem['display_order']));
					$itemPriceTable->updateById($this->_adminProperty['management_group_id'], $nextItem['id'], array('display_order' => $targetItem['display_order']));
				}

				// commit
				$itemPriceTable->getAdapter()->commit();
           
			} catch (Exception $e) {
            	$itemPriceTable->getAdapter()->rollBack();
				throw new Zend_Exception('/supplier/item/price-lot-order transaction faied: ' . $e);
				
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/price-lot-delete                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品詳細 - ロット単位別価格削除(Ajax)                      |
    +----------------------------------------------------------------------------*/
    public function priceLotDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id');
		$lotId   = $request->getParam('lot_id');

		// POST送信時
		if ($request->isPost()) {
			$itemPriceTable = new Shared_Model_Data_ItemPrice();
			
			$data = $itemPriceTable->getById($this->_adminProperty['management_group_id'], $lotId);
			
			if (empty($data)) {
				throw new Zend_Exception('/product/price-lot-delete no data');
			}
	
			$itemPriceTable->getAdapter()->beginTransaction();
			
			try {
				// 更新
				$data = array(
					'status'          => Shared_Model_Code::CONTENT_STATUS_INACTIVE, 
				);

				$itemPriceTable->updateById($this->_adminProperty['management_group_id'], $lotId, $data);

				// commit
				$itemPriceTable->getAdapter()->commit();
           
			} catch (Exception $e) {
            	$itemPriceTable->getAdapter()->rollBack();
				throw new Zend_Exception('/product/price-lot-delete transaction faied: ' . $e);
				
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    
    

      
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/update-introduction                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品紹介更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateIntroductionAction()
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
				$itemTable = new Shared_Model_Data_Item();
	
				// 更新
				$data = array(
			        'gs_catch_copy'   => $success['gs_catch_copy'],  // キャッチコピー
			        'gs_comment'      => $success['gs_comment'],     // コメント
			        'gs_superiority'  => $success['gs_superiority'], // 商品の競合優位性
			        'gs_bland_name'   => $success['gs_bland_name'],  // ブランド名
			        'gs_size'         => $success['gs_size'],        // サイズ・容量
			        'gs_standard'     => $success['gs_standard'],    // 規格
			        'gs_attention'    => $success['gs_attention'],   // 注意事項
				);

				$itemTable->updateById($id, $data);
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }

    


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/document-list                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 関連資料                                                   |
    +----------------------------------------------------------------------------*/
    public function documentListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id      = $id      = $request->getParam('id');
		$this->view->docType = $docType = $request->getParam('doc_type');
		$this->view->direct  = $direct  = $request->getParam('direct');
		if (empty($direct)) {
			$this->view->backUrl = '/product/list';
		}
		
		$itemTable = new Shared_Model_Data_Item();
		$docTable  = new Shared_Model_Data_ItemDocument();
		
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		$this->view->items = $docTable->getListByItemIdAndDocType($this->_adminProperty['management_group_id'], $id, $docType);
		
        // ドキュメント種類
        $kindTable = new Shared_Model_Data_ItemDocumentKind();
        $this->view->kindList = $kindTable->getList();
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/document-edit                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 関連資料 追加編集                                          |
    +----------------------------------------------------------------------------*/
    public function documentEditAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id         = $id         = $request->getParam('id');
		$this->view->docType    = $docType    = $request->getParam('doc_type');
		$this->view->documentId = $documentId = $request->getParam('document_id');
		
		$itemTable = new Shared_Model_Data_Item();
		$docTable  = new Shared_Model_Data_ItemDocument();

		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		if (empty($documentId)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->documentData = array(
				'kind'                => '',
				'file_name'           => '',
				'summary'             => '',
			);
			
		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$documentData = $docTable->getById($this->_adminProperty['management_group_id'], $documentId);
	        if (empty($data)) {
				throw new Zend_Exception('/management/product/document-edit filed to fetch data');
			}

        	$this->view->documentData = $documentData;
        }
        
        // ドキュメント種類
        $kindTable = new Shared_Model_Data_ItemDocumentKind();
        $this->view->kindList = $kindTable->getList();
        
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/document-update                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 関連資料 登録・編集(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function documentUpdateAction()
    {		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$docType    = $request->getParam('doc_type');
		$documentId = $request->getParam('document_id');
		
		$docTable = new Shared_Model_Data_ItemDocument();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/management/product/document-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                if (!empty($errorMessage['kind']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「ドキュメント種類」を選択してください'));
                    return;
                }
                
                
                
				$result = array('result' => 'NG', 'message' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				if (empty($documentId)) {
					// 新規登録
					if (empty($success['file_name'])) {
	                	$this->sendJson(array('result' => 'NG', 'message' => '「添付資料ファイル」をアップロードしてください'));
	                    return;
					}
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,
						'item_id'             => $id,
						'doc_type'            => $docType,
						'kind'                => $success['kind'],
						'file_name'           => $success['file_name'],
						'summary'             => $success['summary'],
						
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);
					
					$docTable->create($data);
					$documentId = $docTable->getLastInsertedId('id');
				} else {
					$oldDocData = $docTable->getById($this->_adminProperty['management_group_id'], $documentId);
					
					// 編集
					$data = array(
						'kind'    => $success['kind'],
						'summary' => $success['summary'],
					);
					
					if (!empty($success['file_name'])) {
						$data['file_name'] = $success['file_name'];
					}

					$docTable->updateById($this->_adminProperty['management_group_id'], $documentId, $data);
					
					// 古いファイルを削除
					if ($oldDocData['file_name'] != $success['file_name']) {
						//Shared_Model_Resource_ItemDocument::removeResource($id, $documentId, $oldDocData['file_name']);
					}
					
				}
				
				if (!empty($success['file_name'])) {
					// ファイルを正式な場所に配置
					Shared_Model_Resource_ItemDocument::makeResource($id, $documentId, $success['file_name'], Shared_Model_Resource_TemporaryPrivate::getBinary($success['file_name']));
	
					// 仮ファイルの削除
					Shared_Model_Resource_TemporaryPrivate::removeResource($success['file_name']);
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/upload-docment                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 関連資料アップロード(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function uploadDocumentAction()
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
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($fileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName));
        return;
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/delete-document                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ドキュメント削除(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function deleteDocumentAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('id');
		$documentId = $request->getParam('document_id');
		
		$docTable = new Shared_Model_Data_ItemDocument();
				
		// POST送信時
		if ($request->isPost()) {
			if (empty($documentId)) {
				$result = array('result' => 'NG', 'message' => message);
			    $this->sendJson($result);
			}
			
			$oldDocData = $docTable->getById($this->_adminProperty['management_group_id'], $documentId);
			
			$data = array(
				'management_group_id' => $this->_adminProperty['management_group_id'],
				'status'              => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
			);
			
			$docTable->updateById($this->_adminProperty['management_group_id'], $documentId, $data);
			
			
			if (!empty($oldDocData['file_name'])) {
				Shared_Model_Resource_ItemDocument::removeResource($id, $documentId, $oldDocData['file_name']);
			}
			
			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
			
		}
		
		$result = array('result' => 'NG');
	    $this->sendJson($result);
    }
    	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /product/estimate-list                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入手見積                                                   |
    +----------------------------------------------------------------------------*/
/*
    public function estimateListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$itemTable = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$this->view->backUrl = '/product/list';
    }
*/   
    
    

	
}

