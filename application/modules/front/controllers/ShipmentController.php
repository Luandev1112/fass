<?php
/**
 * class ShipmentController
 */
 
class ShipmentController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '出荷・在庫管理';
		$this->view->menuCategory     = 'shipment';
		$this->view->menu             = 'shipment';
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		$request = $this->getRequest();

		// Ajax以外判定
		if ($request->isPost() === false && !(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {		$orderTable = new Shared_Model_Data_Order();
			// 未出荷件数
			if (!empty($this->_warehouseSession->warehouseId)) {
		        $selectObj = $orderTable->select(array(new Zend_Db_Expr('COUNT(id) AS item_count')));
		        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
		        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_HOLDED);
		        $data = $selectObj->query()->fetch();
		        
		        if (!empty($data)) {
		        	$this->view->planCount = $data['item_count'];
		        }
			}
			
			// 卸未出荷数
			$this->view->wholesalePlanCount = 0;
			
		}
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-order-item                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 注文商品修正                                              |
    +----------------------------------------------------------------------------*/
    public function updateOrderItemAction()
    {
		$orderItemTable  = new Shared_Model_Data_OrderItem();
		
		$orderItemTable->updateById('31360', array(
			'product_code' => '4589782700477-3',
		));
		
		echo 'OK';
		exit;
    }
    
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/app-login                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アプリログイン(開発用)                                     |
    +----------------------------------------------------------------------------*/
    public function appLoginAction()
    {
    	$request = $this->getRequest();
    	
    	$this->_helper->layout->setLayout('back_menu');
	    $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = 'ログイン';
    }
    
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/login                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷管理ログイン                                           |
    +----------------------------------------------------------------------------*/
    public function loginAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
	    $request = $this->getRequest();
	    $warehouseId = $request->getParam('target_id');
	    
	    if (empty($warehouseId)) {
	        $this->sendJson(array('result' => 'NG'));
	        return;
	    }
	    
	    $this->_warehouseSession->warehouseId = $warehouseId;

        $this->sendJson(array('result' => 'OK'));
        return;
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/monthly                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 月間出荷                                                   |
    +----------------------------------------------------------------------------*/
    public function monthlyAction()
    {
    	$request = $this->getRequest();
    	$id = $request->getParam('id');
		$this->view->targetDate = $targetDate = $request->getParam('month_select');

        if (!empty($targetDate)) {
            $startDate   = date('Y-m', strtotime($targetDate)) . '-01'; // 月初日
            $targetYear  = date('Y', strtotime($targetDate));
            $targetMonth = date('m', strtotime($targetDate));

			// 月末日を取得
	        $endDate = date($targetYear . '-' . $targetMonth . '-' . Nutex_Date::getMonthEndDay($targetYear, $targetMonth)); 

	    	$orderTable = new Shared_Model_Data_Order();
	    	$selectObj = $orderTable->select();
			$selectObj->where('order_datetime >= ?', $startDate);
			$selectObj->where('order_datetime <= ?', $endDate);
			$this->view->items = $selectObj->query()->fetchAll();
			
			$this->view->startDate = $startDate;
			$this->view->endDate   = $endDate;
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/kari                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 仮対応                                                     |
    +----------------------------------------------------------------------------*/
    public function kariAction()
    {
    	$request = $this->getRequest();
    	$id = $request->getParam('id');
    
    	//if (empty($id)) {
    	//	echo 'NG';exit;
    	//}
    	$ids = array(
    	'28232',
    	'28233',
    	'28234',
    	'28235',
    	'28236',
    	'28238',
    	);
    	
    	$orderTable = new Shared_Model_Data_Order();
    	//$orderTable->updateById($id, array('delivery_method' => Shared_Model_Code::DELIVERY_TYPE_YAMATO_COMPACT));
    	
    	foreach ($ids as $eachId) {
	    	$orderTable->updateById('1', $eachId, array(
		    	//'delivery_method' => 10,
		    	
				//'delivery_request_date' => '2018-02-01',
				//'order_customer_name' => '髙木 和美',
				//'delivery_name' => '髙木 和美',
				'payment_method' => Shared_Model_Code::PAYMENT_TYPE_RAKUTEN,
	
				//'payment_method'  => Shared_Model_Code::PAYMENT_TYPE_NISSEN_DEFERRED,
				//'delivery_method' => Shared_Model_Code::DELIVERY_TYPE_YAMATO,
				//'delivery_fee'    => 0,
				//'charge'          => 0,
				//'tax'   => '198',
				//'total' => '2664',
				
				//'delivery_code' => '767216677581',
	    	));
	    }
    	
    	echo 'OK';
    	exit;
    }
    



	/**
     * createMonthList
     * 月選択肢
     * @param void
     * @return void
     */
    private function createMonthList()
    {
        $day = new Zend_Date(date('Y-m-01'), NULL, 'ja_JP');
        
	    // 戻り値用配列
		$array = array();
		for($count = 0; $count < 12; $count++) {
	        $ymd = $day->get(Zend_Date::YEAR) . '-' . $day->get(Zend_Date::MONTH) . '-' . $day->get(Zend_Date::DAY);
	        $text = $day->get(Zend_Date::YEAR) . '年' . $day->get(Zend_Date::MONTH) . '月';
		    $array[$ymd] = $text;
	        $day->sub('1', Zend_Date::MONTH);
		}

	    return $array;
	} 
	 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/index                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷管理ダッシュボード                                     |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
        $this->view->menu = 'dashboard';
        
		$request = $this->getRequest();
		
		$orderTable = new Shared_Model_Data_Order();
		
		// 新規注文
		$this->view->newOrderCount  = $orderTable->getNewCount($this->_warehouseSession->warehouseId);
		
		// 検品済み
		$this->view->inspectedCount = $orderTable->getInspectedCount($this->_warehouseSession->warehouseId);
		
		// 本日発送済み
		$this->view->shippedCount   = $orderTable->getShippedTodayCount($this->_warehouseSession->warehouseId, date('Y-m-d'));
		
		// 保留
		$this->view->holdedCount    = $orderTable->getHoldedCount($this->_warehouseSession->warehouseId);
		
		// アラートアイテム
		$warehouseItemTable = new Shared_Model_Data_WarehouseItem();
		$this->view->items = $warehouseItemTable->getAlertItemWithType(NULL);
		
		
		$historyItems = array();
		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		
		for ($count = 0; $count < 30; $count++) {
			$zDate->sub('1', Zend_Date::DAY);
			$targetDate = $zDate->get('yyyy-MM-dd');
			
			$historyItems[] = array(
				'target_date' => $targetDate,
				'count'       => $orderTable->getShippedTodayCount($this->_warehouseSession->warehouseId, $targetDate),
			);
		}
		
		$this->view->historyItems = $historyItems;

		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		$this->view->today = $zDate->get('yyyy-MM-dd');
		
		$zDate->sub('2', Zend_Date::DAY);
		
		$this->view->twoDaysAgo = $zDate->get('yyyy-MM-dd');
		
		
		// 4週間前
		$zDate->sub('28', Zend_Date::DAY);
		$this->view->fourWeekAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->sub('3', Zend_Date::DAY);
		$this->view->oneMonthAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->add('3', Zend_Date::DAY);
		$zDate->sub('31', Zend_Date::DAY);
		$this->view->twoMonthAgo = $zDate->get('yyyy-MM-dd');
			
    }
  
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/import                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注データ取り込み                                         |
    +----------------------------------------------------------------------------*/
    public function importAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/shipment/plan-list';
        
		$request    = $this->getRequest();
		
		$formatTable = new Shared_Model_Data_OrderImportFormat();
        $this->view->formatList = $formatTable->getActiveList(false);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/import-result                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取り込み結果(Ajax/html)                                    |
    +----------------------------------------------------------------------------*/
    public function importResultAction()
    {
        $this->_helper->layout->setLayout('blank');
        $this->view->backUrl = '/shipment/plan-list';
        
    	$request   = $this->getRequest();
    	$importKey = $request->getParam('key', '');
    	
    	if (!empty($importKey)) {
    		$logTable = new Shared_Model_Data_OrderImportLog();
    		$this->view->items = $logTable->getItemsByImportKey($importKey);
    	}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/import-csv                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注データ取り込み実行                                     |
    +----------------------------------------------------------------------------*/
    public function importCsvAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        ini_set('display_errors', 0);
        
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
		
        $importKey = $key;// 第1引数 import key
        $formatId  = $request->getParam('format_id', '');
		
		// 取り込みフォーマット
		$formatTable = new Shared_Model_Data_OrderImportFormat();
		$format = $formatTable->getById($formatId);
		$format['column_setting']  = unserialize($format['column_setting']);
		$format['convert_setting'] = unserialize($format['convert_setting']);

        $csvFilePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($importKey . '.csv');;
		
        if (file_exists($csvFilePath)) {  
            $handle = fopen($csvFilePath, "r");
            
            // 説明行
            $csvRow = fgetcsv($handle, 0, ","); 
            
            // 注文データの登録
            $rowCount = 1;
            $orderIds = array();
            
            while (($csvRow = fgetcsv($handle, 0, ",")) !== FALSE) {
            	$result = $this->importOrder($rowCount, $format, $importKey, $formatId, $csvRow);
            	
            	if (!empty($result)) {
	            	$orderIds[] = $result;
            	}
            	
            	$rowCount++;
            }
            
			$orderTable      = new Shared_Model_Data_Order();
			$orderItemTable  = new Shared_Model_Data_OrderItem();
			$packageTable    = new Shared_Model_Data_ItemPackage();
			$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
			
			foreach ($orderIds as $eachId) {
				$orderData = $orderTable->getById($this->_warehouseSession->warehouseId, $eachId);
				
				// ネコポスでDM便該当商品だけの場合はDM便への変更
				if ($orderData['delivery_method'] === (string)Shared_Model_Code::DELIVERY_TYPE_YAMATO_POST) {
		        	$useDM = true;
		        
					$orderItems = $orderItemTable->getListByOrderId($eachId);
					
					foreach ($orderItems as $each) {
						$package = $packageTable->getByProductCode($each['product_code']);						
						$productItems = $packageProductTable->getProductItemsByPackageId($package['id']);
						
						foreach ($productItems as $eachProduct) {
							
							if (empty($eachProduct['use_dm'])) {
								$useDM = false;
							}
						}

					}
					
					if (true === $useDM) {
						$orderTable->updateById($this->_warehouseSession->warehouseId, $eachId, array(
							'delivery_method'  =>  Shared_Model_Code::DELIVERY_TYPE_YAMATO_DM,
						));
					}
				}
			}

        } else {
	        $this->sendJson(array('result' => false));
	        return;
        }

    	$this->sendJson(array('result' => 'OK', 'key' => $key, 'count' => $rowCount, 'data' => $data));
    	return;
    }
    
    
/* EC-CUBE CSVデータ
[0]=> 注文番号
[1]=> お名前
[2]=> フリガナ
[3]=>メールアドレス
[4]=>電話番号
[5]=>顧客番号
[6]=>郵便番号
[7]=>都道府県
[8]=>住所1
[9]=>住所2
[10]=>性別
[11]=>生年月日
[12]=>値引き
[13]=>送料
[14]=>決済手数料
[15]=>利用ポイント
[16]=>加算ポイント
[17]=>お支払い合計
[18]=>配送業者ID
[19]=>支払い方法
[20]=>注文日(-)
[21]=>発送完了日
[22]=>商品コード
[23]=>商品名
[24]=>単価
[25]=>個数
[26]=>お届け先お名前
[27]=>お届け先フリガナ
[28]=>お届け先電話番号
[29]=>お届け先都道府県
[30]=>お届け先郵便番号
[31]=>お届け先住所1
[32]=>お届け先住所2
[33]=>お届け時間
[34]=>お届け日(-)
[35]=>お客様への通信欄
[36]=>定期回数
[37]=>消費税
*/

	/*
	 * 注文データ1件取込
	*/
    private function importOrder($rowCount, $format, $importKey, $formatId, $csvRow)
    {
    	$orderTable          = new Shared_Model_Data_Order();
    	$orderItemTable      = new Shared_Model_Data_OrderItem();
    	$logTable            = new Shared_Model_Data_OrderImportLog();
		$packageTable        = new Shared_Model_Data_ItemPackage();
		
		$data = array();
		
		$importColumnList = Shared_Model_Code::codes('order_import_column');
		
		// --------------------------------------
		// 初期化
		// --------------------------------------
		foreach ($importColumnList as $defaultKey => $eachDefault) {
			$data[$defaultKey] = '';
		}
		
		// --------------------------------------
		// 各列データ取り込み
		// --------------------------------------
		foreach ($format['column_setting'] as $key => $each) {
			if (!empty($csvRow[$key])) {
				$data[$each] = $csvRow[$key];
			}
		}

		// 注文ステータス
		if (empty($data['status'])) {
			$data['status'] = Shared_Model_Code::SHIPMENT_STATUS_NEW;
		}
		
		if (empty($data['order_contry'])) {
			$data['order_contry'] = '日本';
		}

		if (empty($data['delivery_contry'])) {
			$data['delivery_contry'] = '日本';
		}
		
		// 配達時間
		if (!empty($data['delivery_request_time'])) {
			if ($data['delivery_request_time'] == '午前中') {
				$data['delivery_request_time'] = '0812';
				
			} else {
				$deliveryTime = str_replace(array('〜', '～'), '-', $data['delivery_request_time']);
				
				$deliveryTimeArray = explode('-', $deliveryTime);
				
				foreach ($deliveryTimeArray as &$eachTime) {
					$eachTime = str_replace(':00', '', $eachTime);
				}
				
				$data['delivery_request_time'] = implode($deliveryTimeArray);
			}
		}
		
		
		// --------------------------------------
		// 値変換
		// --------------------------------------

		foreach ($format['convert_setting'] as $eachConvert) {
			foreach ($data as $dataKey => &$dataVal) {
				/*
				if ($eachConvert['target_column'] == $dataKey && $eachConvert['target_column'] === 'delivery_method') {
					var_dump($eachConvert['base']);
					var_dump($dataVal);
					exit;
				}
				*/
				
				if ($eachConvert['target_column'] == $dataKey && $dataVal == $eachConvert['base']) {
					$data[$dataKey] = $eachConvert['converted'];
				}
			}
		}
		
		// --------------------------------------
		// バリデーション
		// --------------------------------------
		$orderData = $orderTable->getByOrderId($this->_warehouseSession->warehouseId, $data['relational_order_id']);
		
		if (!empty($orderData)) {
			// 取込済み
			if ($importKey != $orderData['import_key']) {
				$logTable->addLog($importKey, $formatId, $rowCount, $data['relational_order_id'], 0, 'この注文番号はすでに取込済みです');
				return false;
			}
		}
		
		$branchNo = 1;
		
		$orderIds = array();
		
		try {
	    	if (empty($orderData)) {
	    		// 配送予定日
	    		$shipmentPlanDate = date('Y-m-d');
	    		
	    		// 日付が指定できる配達方法の場合
	    		if ($data['delivery_method'] == Shared_Model_Code::DELIVERY_TYPE_YAMATO || $data['delivery_method'] == Shared_Model_Code::DELIVERY_TYPE_YAMATO_COMPACT) {
		    		// 配達希望日がある場合
		    		if (!empty($data['delivery_request_date'])) {
		    			$requetDate = new Zend_Date($data['delivery_request_date'], NULL, 'ja_JP');
		    			$requetDate->sub('7', Zend_Date::DAY);
		    			
		    			$today = new Zend_Date(NULL, NULL, 'ja_JP');
		    			
		    			if (!$requetDate->isEarlier($today)) {
		    				// 今日以前でなければ発送日指定
		    				$shipmentPlanDate = $requetDate->get('yyyy-MM-dd');	
		    			}
		    		}
	    		}
	    		
	    		
				
		    	$orderTable->create(array(
					'status'                    => Shared_Model_Code::SHIPMENT_STATUS_NEW,
					'delivery_status'           => 0,
					
					'order_datetime'            => $data['order_datetime'],
					
					'inspection_datetime'       => NULL,
					'inspection_user_id'        => 0,
					
					'shipment_plan_date'        => $shipmentPlanDate, // 計算する
					'shipment_datetime'         => NULL,
					
					'warehouse_id'              => $this->_warehouseSession->warehouseId,
					'import_key'                => $importKey,
					'relational_order_id'       => $data['relational_order_id'],
					
					'customer_id'               => $data['customer_id'],
					
					'is_royal_customer'         => $data['is_royal_customer'],
					
					'order_customer_name'       => $data['order_customer_name'],
					'order_customer_name_kana'  => $data['order_customer_name_kana'],
					'order_email'               => $data['order_email'],
					'order_tel'                 => $data['order_tel'],
					'order_zipcode'             => $data['order_zipcode'],
					'order_contry'              => $data['order_contry'],
					'order_prefecture'          => $data['order_prefecture'],
					'order_address1'            => $data['order_address1'],
					'order_address2'            => $data['order_address2'],
					'order_sex'                 => $data['order_sex'],
					'order_birthday'            => $data['order_birthday'],
					
					'discount'                  => $data['discount'],
					'delivery_fee'              => $data['delivery_fee'],
					'charge'                    => $data['charge'],
					'tax'                       => $data['tax'],
					'total'                     => $data['total'],
					
					'payment_method'            => $data['payment_method'],
					'delivery_method'           => $data['delivery_method'],
					
					'delivery_name'             => $data['delivery_name'],
					'delivery_name_kana'        => $data['delivery_name_kana'],
			        'delivery_tel'              => $data['delivery_tel'],
			        'delivery_zipcode'          => $data['delivery_zipcode'],
			        'delivery_contry'           => $data['delivery_contry'],
			        'delivery_prefecture'       => $data['delivery_prefecture'],
					'delivery_address1'         => $data['delivery_address1'],
					'delivery_address2'         => $data['delivery_address2'],
	
					'delivery_request_date'       => $data['delivery_request_date'],
					'delivery_request_time'       => $data['delivery_request_time'],
									
					'message_to_customer_1'       => $data['message_to_customer_1'],
					'message_to_customer_2'       => $data['message_to_customer_2'],
					'message_to_customer_3'       => $data['message_to_customer_3'],
					'message_to_customer_4'       => $data['message_to_customer_4'],
					'message_to_customer_5'       => $data['message_to_customer_5'],

			        'order_from_site'             => $data['order_from_site'],                // 注文サイト
			        'subscription_id'             => $data['subscription_id'],                // 定期ID
			        'is_subscription_first_order' => $data['is_subscription_first_order'],    // 定期初回注文
			        
					'subscription_count'          => $data['subscription_count'],             // 定期回数
					
					'jaccs_transaction_id'        => $data['jaccs_transaction_id'],           // ジャックストランザクションID
					'jaccs_with_package'          => !empty($data['jaccs_with_package']) ? 1 : 0,
					
		            'created'                     => new Zend_Db_Expr('now()'),
		            'updated'                     => new Zend_Db_Expr('now()'),
		    	));
		
				$orderId = $orderTable->getLastInsertedId('id');
				
			} else {
				$orderId  = $orderData['id'];
				$branchNo = $orderItemTable->getNextBranchNo($orderData['id']);
			}
			
			
			// 注文商品の追加
			$orderItemData = array(
				'order_id'            => $orderId,
				'branch_no'           => $branchNo,
				'product_code'        => $data['product_code'],
				'product_name'        => $data['product_name'],
				
				'unit_price'          => $data['unit_price'],
				'amount'              => $data['amount'],
				'item_tax_rate'       => $data['item_tax_rate'],         // 商品税率
				'item_total'          => $data['item_total'],            // 商品小計(税抜)
				'item_total_with_tax' => $data['item_total_with_tax'],   // 商品小計(税込)
		
	            'created'             => new Zend_Db_Expr('now()'),
	            'updated'             => new Zend_Db_Expr('now()'),
	    	);

	    	$orderItemTable->create($orderItemData);
	    	
	    	
	    	// 対象の商品が存在しない場合
	    	$packageData = $packageTable->getByProductCode($data['product_code']);
			if (empty($packageData)) {
				// 保留にする
				$orderTable->updateById($this->_warehouseSession->warehouseId, $orderId, array('status' => Shared_Model_Code::SHIPMENT_STATUS_HOLDED));
			
				$logTable->addLog($importKey, $formatId, $rowCount, $data['relational_order_id'], 0, '対象の商品パッケージが見当たりません(商品コード: ' . $data['product_code'] . ')');
				return false;
	    	}
	    	
        } catch (Exception $e) { 
            $logTable->addLog($importKey, $formatId, $rowCount, $data['relational_order_id'], 0, '予期せぬエラー');
            return false;
        }	
    	
    	$logTable->addLog($importKey, $formatId, $rowCount, $data['relational_order_id'], 1);
    	return $orderId;
    }


	/*
		$itemTable           = new Shared_Model_Data_Item();
    	$packageTable        = new Shared_Model_Data_ItemPackage();
    	$packageProductTable = new Shared_Model_Data_ItemPackageProduct();
    	$packageBundleTable  = new Shared_Model_Data_ItemPackageBundle();
	    	// -----------------------------------------------------
	    	//
	        // 在庫引き当て
	        //
	        // -----------------------------------------------------
			
			// 引き当て在庫数を減らす
			
			// 商品コードから対象の商品パッケージを取得
			$productData = $packageTable->getByProductCode($orderItemData['product_code']);
			
			// 構成商品
			$productList = $packageProductTable->getProductItemsByPackageId($packageData['id']);
			
			foreach ($productList as $eachProduct) {
				$itemTable->subUseableCount($eachProduct['product_item_id'], (int)$eachProduct['product_item_amount'] * (int)$orderItemData['amount']);
			}
			
			// 付属品
			$bundleList = $packageBundleTable->getBundleItemsByPackageId($packageData['id']);
			
			foreach ($bundleList as $eachBundle) {
				$itemTable->subUseableCount($eachBundle['bundle_item_id'], (int)$eachBundle['bundle_item_amount'] * (int)$orderItemData['amount']);
			}
			
			// 同梱施策から
	*/	
			
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/plan-list-test                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 未出荷リスト(デバッグ用)                                   |
    +----------------------------------------------------------------------------*/
    public function planListTestAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		
		$orderTable = new Shared_Model_Data_Order();
		
		$dbAdapter = $orderTable->getAdapter();

        $selectObj = $orderTable->select();
        $selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array($orderTable->aesdecrypt('user_name', false) . 'AS inspection_user_name'));
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_HOLDED);
		$selectObj->order('frs_order.shipment_plan_date ASC');
		$selectObj->order('frs_order.id ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
        // アサイン済み検品者リスト
        $this->view->inspectionUserList = $orderTable->getUserListForInspection();
    }
    		
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/plan-list                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 未出荷リスト                                               |
    +----------------------------------------------------------------------------*/
    public function planListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		
		$conditions = array();
		$conditions['order_from_site']     = $request->getParam('order_from_site', '');
		$conditions['relational_order_id'] = $request->getParam('relational_order_id', '');
		
		$this->view->conditions = $conditions;
		
		$orderTable = new Shared_Model_Data_Order();
		$dbAdapter = $orderTable->getAdapter();

        $selectObj = $orderTable->select();
        $selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array($orderTable->aesdecrypt('user_name', false) . 'AS inspection_user_name'));
        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_HOLDED);
        
        if ($conditions['order_from_site'] != '') {
        	$selectObj->where('order_from_site = ?', $conditions['order_from_site']);
        } 

        if ($conditions['relational_order_id'] != '') {
        	$selectObj->where('relational_order_id = ?', $conditions['relational_order_id']);
        } 
        
		$selectObj->order('frs_order.shipment_plan_date ASC');
		$selectObj->order('frs_order.id ASC');
		//var_dump($selectObj->__toString());exit;
        $this->view->items = $selectObj->query()->fetchAll();



        
        // アサイン済み検品者リスト
        $this->view->inspectionUserList = $orderTable->getUserListForInspection($this->_warehouseSession->warehouseId);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/jaccs/jaccs-import-invoice-data                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書データ取得(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function jaccsImportInvoiceDataAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	// 未出荷リストのうちアトディーネ
    	$orderTable     = new Shared_Model_Data_Order();
        $selectObj = $orderTable->select();
        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_HOLDED);
        $selectObj->where($orderTable->aesdecrypt('payment_method', false) . ' = ' . Shared_Model_Code::PAYMENT_TYPE_NP_DEFERRED_ATODINE);
        $selectObj->where('jaccs_with_package = 1');
		$selectObj->order('frs_order.shipment_plan_date ASC');
		$selectObj->order('frs_order.id ASC');
        $items = $selectObj->query()->fetchAll();
    	
    	foreach ($items as $each) {
    		$responseData = Shared_Model_Payment_Jaccs::importInvoiceData($each['jaccs_transaction_id']);

			if ($responseData->result[0]->__toString() === 'OK') {
				$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'],array(
					'jaccs_invoice_data' => serialize(json_decode(json_encode($responseData), true)),
				));
				
				
			} else {
				$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'],array(
					'jaccs_error_data' => serialize(array('error' => 'error')),
				));	
			}

    	}
    	
        $this->sendJson(array('result' => 'OK'));
        return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/export-jaccs                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ジャックス印字用csv出力                                    |
    +----------------------------------------------------------------------------*/
    public function exportJaccsAction()
    {
    	$request = $this->getRequest();
   		$inspectionUserId = $request->getParam('inspection_user_id');
   		
		$orderTable = new Shared_Model_Data_Order();
		
    	// 未出荷リストのうちアトディーネ
    	$orderTable     = new Shared_Model_Data_Order();
        $selectObj = $orderTable->select();
        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_HOLDED);
        $selectObj->where($orderTable->aesdecrypt('payment_method', false) . ' = ' . Shared_Model_Code::PAYMENT_TYPE_NP_DEFERRED_ATODINE);
		$selectObj->order('frs_order.shipment_plan_date ASC');
		$selectObj->order('frs_order.id ASC');
        $items = $selectObj->query()->fetchAll();
        
    	
		$basicTable = new Shared_Model_Data_BasicInfo();
		$shopInfo =  $basicTable->get($this->_warehouseSession->warehouseId);
		
		
   		$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '.csv');
    	//var_dump($path);exit;
    	
    	$fp = fopen($path, 'w');
		
		
		$csvRow = array(
			'1'  => '郵便番号',
			'2'  => '住所1',
			'3'  => '住所2',
			'4'  => '会社名',
			'5'  => '部署名',
			'6'  => '氏名',
			'7'  => '加盟店名タイトル',
			'8'  => '請求書記載店舗名',
			'9'  => '加盟店取引IDタイトル',
			'10' => 'ご購入店受注番号',
			
			'11' => '請求書記載事項1',
			'12' => '請求書記載事項2',
			'13' => '請求書記載事項3',
			'14' => '請求書記載事項4',
			'15' => '請求書記載事項5',
			'16' => '請求書発行元企業名',
			'17' => '請求書発行元情報1',
			'18' => '請求書発行元情報2',
			'19' => '請求書発行元情報3',
			'20' => '請求書発行元情報4',
			
			'21' => '請求書ステータス',
			'22' => '宛名欄挨拶文欄1',
			'23' => '宛名欄挨拶文欄2',
			'24' => '宛名欄挨拶文欄3',
			'25' => '宛名欄挨拶文欄4',
			'26' => 'お問合せ先タイトル',
			'27' => 'お問合せ先電話番号',
			'28' => '予備項目3',
			'29' => '予備項目4',
			'30' => '予備項目5',
			'31' => '予備項目6',
			'32' => '予備項目7',
			'33' => '予備項目8',
			'34' => '予備項目9',
			'35' => '予備項目10',
			'36' => '請求金額タイトル',
			'37' => '請求金額',	
			'38' => '請求金額消費税',
			
			'39' => '注文日タイトル',
			'40' => '注文日',
			'41' => '請求書発行日タイトル',
			'42' => '請求書発行日',
			'43' => 'お支払期限日タイトル',
			'44' => 'お支払期限日',
			'45' => 'お問合せ番号タイトル',
			'46' => 'お問合せ番号',
			'47' => '銀行振込注意文言',
			'48' => '銀行名タイトル',
			'49' => '銀行名漢字',
			'50' => '銀行コード',      
			'51' => '支店名タイトル',      
			'52' => '支店名漢字',      
			'53' => '支店コード',      
			'54' => '口座番号タイトル',      
			'55' => '預金種別',      
			'56' => '口座番号',      
			'57' => '口座名義タイトル',
			'58' => '銀行口座名義',
			
			'59' => '払込取扱用支払期限日',    
			'60' => '払込取扱用購入者氏名',    
			'61' => 'バーコード情報',
			'62' => '収納代行会社名タイトル',
			'63' => '収納代行会社名',
			'64' => '請求金額',
			'65' => '受領証用購入者会社名',
			'66' => '受領証用購入者部署名',
			'67' => '受領証用購入者氏名',
			'68' => 'お問い合せ番号タイトル',

			'69' => 'お問い合せ番号',
			'70' => '払込受領書用購入者会社名',
			'71' => '払込受領書用購入者部署名',
			'72' => '払込受領書用購入者氏名',
			'73' => '払込受領書用お問合せ番号タイトル',
			'74' => '払込受領書用お問合せ番号',
			'75' => '払込受領書用請求金額',
			'76' => '払込受領書用消費税金額',
			'77' => '収入印紙文言',
			'78' => '明細内容タイトル',
			'79' => '注文数タイトル',
			'80' => '単価タイトル',
			'81' => '金額タイトル',
		);
		
		for ($count = 0; $count <= 15; $count++) {
			$csvRow[(string)(82 +  $count * 5)] = '明細内容' . ($count + 1);
			$csvRow[(string)(83 +  $count * 5)] = '注文数' . ($count + 1);
			$csvRow[(string)(84 +  $count * 5)] = '単価' . ($count + 1);
			$csvRow[(string)(85 +  $count * 5)] = '金額' . ($count + 1);
			$csvRow[(string)(86 +  $count * 5)] = '金額消費税' . ($count + 1);
		}

		$csvRow['157'] = '明細注意事項';
		$csvRow['158'] = 'ゆうちょ口座番号';
		$csvRow['159'] = 'ゆうちょ加入者名';
		$csvRow['160'] = 'OCR-Bフォント印字項目上段情報';
		$csvRow['161'] = 'OCR-Bフォント印字項目下段情報';
		$csvRow['162'] = '払込取扱用購入者住所';
		$csvRow['163'] = '印字ズレチェックマーク';
		$csvRow['164'] = '予備項目17';
		$csvRow['165'] = '予備項目18';
		$csvRow['166'] = '予備項目19';
		$csvRow['167'] = '予備項目20';
		

		mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);
		
		
		foreach ($items as $each) {
			$array = unserialize($each['jaccs_invoice_data']);

			if (!empty($array)) {
				$invoiceInfo = $array['invoiceInfo'];
				$csvRow = array(
					'1'  => $invoiceInfo['zip'],              // 郵便番号
					'2'  => $invoiceInfo['address1'],         // 住所1
					'3'  => $invoiceInfo['address2'],         // 住所2
					'4'  => $invoiceInfo['companyName'],      // 会社名
					'5'  => $invoiceInfo['sectionName'],      // 部署名
					'6'  => $invoiceInfo['name'],             // 氏名
					'7'  => $invoiceInfo['siteNameTitle'],    // 加盟店名タイトル
					'8'  => $invoiceInfo['siteName'],         // 請求書記載店舗名
					'9'  => $invoiceInfo['shopOrderIdTitle'], // 加盟店取引IDタイトル
					'10' => $invoiceInfo['shopOrderId'],      // ご購入店受注番号

			
					'11' => $invoiceInfo['descriptionText1'], // 請求書記載事項1
					'12' => $invoiceInfo['descriptionText2'],
					'13' => $invoiceInfo['descriptionText3'],
					'14' => $invoiceInfo['descriptionText4'],
					'15' => $invoiceInfo['descriptionText5'],
					'16' => $invoiceInfo['billServiceName'],  // 請求書発行元企業名
					'17' => $invoiceInfo['billServiceInfo1'], // 請求書発行元情報1
					'18' => $invoiceInfo['billServiceInfo2'], // 請求書発行元情報2
					'19' => $invoiceInfo['billServiceInfo3'],
					'20' => $invoiceInfo['billServiceInfo4'],
					
					'21' => $invoiceInfo['billState1'],       // 請求書ステータス
					'22' => $invoiceInfo['billFirstGreet1'],  // 宛名欄挨拶文欄1
					'23' => $invoiceInfo['billFirstGreet2'],  // 宛名欄挨拶文欄2
					'24' => $invoiceInfo['billFirstGreet3'],  // 宛名欄挨拶文欄3
					'25' => $invoiceInfo['billFirstGreet4'],  // 宛名欄挨拶文欄4
					'26' => $invoiceInfo['expand1'],          // お問合せ先タイトル
					'27' => $invoiceInfo['expand2'],          // お問合せ先電話番号
					'28' => $invoiceInfo['expand3'],          // 予備項目3
					'29' => $invoiceInfo['expand4'],
					'30' => $invoiceInfo['expand5'],
					'31' => $invoiceInfo['expand6'],
					'32' => $invoiceInfo['expand7'],
					'33' => $invoiceInfo['expand8'],
					'34' => $invoiceInfo['expand9'],
					'35' => $invoiceInfo['expand10'],
					'36' => $invoiceInfo['billedAmountTitle'],      // 請求金額タイトル
					'37' => $invoiceInfo['billedAmount'],	        // 請求金額
					'38' => $invoiceInfo['billedFeeTax'],           // 請求金額消費税
					
					'39' => $invoiceInfo['billOrderdayTitle'],      // 注文日タイトル
					'40' => $invoiceInfo['shopOrderDate'],          // 注文日
					'41' => $invoiceInfo['billSendDateTitle'],      // 請求書発行日タイトル
					'42' => $invoiceInfo['billSendDate'],           // 請求書発行日
					'43' => $invoiceInfo['billDeadlineDateTitle'],  // お支払期限日タイトル
					'44' => $invoiceInfo['billDeadlineDate'],       // お支払期限日
					'45' => $invoiceInfo['transactionIdTitle'],     // お問合せ番号タイトル
					'46' => $invoiceInfo['transactionId'],          // お問合せ番号
					'47' => $invoiceInfo['billBankInfomation'],     // 銀行振込注意文言
					'48' => $invoiceInfo['bankNameTitle'],          // 銀行名タイトル
					'49' => $invoiceInfo['bankName'],               // 銀行名漢字
					'50' => $invoiceInfo['bankCode'],               // 銀行コード
					'51' => $invoiceInfo['branchNameTitle'],        // 支店名タイトル
					'52' => $invoiceInfo['branchName'],             // 支店名漢字
					'53' => $invoiceInfo['bankCode'],               // 支店コード
					'54' => $invoiceInfo['bankAccountNumberTitle'], // 口座番号タイトル
					'55' => $invoiceInfo['bankAccountKind'],        // 預金種別
					'56' => $invoiceInfo['bankAccountNumber'],      // 口座番号
					'57' => $invoiceInfo['bankAccountNameTitle'],   // 口座名義タイトル
					'58' => $invoiceInfo['bankAccountName'],        // 銀行口座名義
					
					'59' => $invoiceInfo['receiptBillDeadlineDate'],     // 払込取扱用支払期限日
					'60' => $invoiceInfo['receiptName'],                 // 払込取扱用購入者氏名
					'61' => $invoiceInfo['invoiceBarcode'],              // バーコード情報
					'62' => $invoiceInfo['receiptCompanyTitle'],         // 収納代行会社名タイトル
					'63' => $invoiceInfo['receiptCompany'],              // 収納代行会社名
					'64' => $invoiceInfo['docketbilledAmount'],          // 請求金額
					'65' => $invoiceInfo['docketCompanyName'],           // 受領証用購入者会社名
					'66' => $invoiceInfo['docketSectionName'],           // 受領証用購入者部署名
					'67' => $invoiceInfo['docketName'],                  // 受領証用購入者氏名
					'68' => $invoiceInfo['voucherTransactionIdTitle'],   // お問い合せ番号タイトル

					'69' => $invoiceInfo['docketName'],                  // お問い合せ番号
					'70' => $invoiceInfo['docketTransactionIdTitle'],    // 払込受領書用購入者会社名
					'71' => $invoiceInfo['docketTransactionId'],         // 払込受領書用購入者部署名
					'72' => $invoiceInfo['voucherCompanyName'],          // 払込受領書用購入者氏名
					'73' => $invoiceInfo['voucherSectionName'],          // 払込受領書用お問合せ番号タイトル
					'74' => $invoiceInfo['voucherCustomerFullName'],     // 払込受領書用お問合せ番号
					'75' => $invoiceInfo['voucherTransactionIdTitle'],   // 払込受領書用請求金額
					'76' => $invoiceInfo['voucherTransactionId'],        // 払込受領書用消費税金額
					'77' => $invoiceInfo['voucherBilledAmount'],         // 収入印紙文言
					'78' => $invoiceInfo['voucherBilledFeeTax'],         // 明細内容タイトル
					'79' => $invoiceInfo['revenueStampRequired'],        // 注文数タイトル
					'80' => $invoiceInfo['revenueStampRequired'],        // 単価タイトル
					'81' => $invoiceInfo['revenueStampRequired'],        // 金額タイトル
				);
				

				if (array_key_exists(0, $invoiceInfo['details']['detail'])) {
					for ($count = 0; $count <= 15; $count++) {
						//var_dump($invoiceInfo['details']['detail'][$count]);	
						
						if (!empty($invoiceInfo['details']['detail'][$count])) {
							$csvRow[(string)(82 +  $count * 5)] = $invoiceInfo['details']['detail'][$count]['goods'];         // 明細内容
							$csvRow[(string)(83 +  $count * 5)] = $invoiceInfo['details']['detail'][$count]['goodsAmount'];    // 注文数
							$csvRow[(string)(84 +  $count * 5)] = $invoiceInfo['details']['detail'][$count]['goodsPrice'];    // 単価
							$csvRow[(string)(85 +  $count * 5)] = $invoiceInfo['details']['detail'][$count]['goodsSubtotal']; // 金額
							$csvRow[(string)(86 +  $count * 5)] = $invoiceInfo['details']['detail'][$count]['goodsExpand'];   // 金額消費税
						} else {
							$csvRow[(string)(82 +  $count * 5)] = ''; // 明細内容1
							$csvRow[(string)(83 +  $count * 5)] = ''; // 注文数1
							$csvRow[(string)(84 +  $count * 5)] = ''; // 単価1
							$csvRow[(string)(85 +  $count * 5)] = ''; // 金額1
							$csvRow[(string)(86 +  $count * 5)] = ''; // 金額消費税1	
						}
					}
				} else {
					for ($count = 0; $count <= 15; $count++) {
						if ($count === 0) {
							//var_dump($invoiceInfo['details']['detail']);
							
							$csvRow[(string)(82 +  $count * 5)] = $invoiceInfo['details']['detail']['goods'];         // 明細内容
							$csvRow[(string)(83 +  $count * 5)] = $invoiceInfo['details']['detail']['goodsAmount'];    // 注文数
							$csvRow[(string)(84 +  $count * 5)] = $invoiceInfo['details']['detail']['goodsPrice'];    // 単価
							$csvRow[(string)(85 +  $count * 5)] = $invoiceInfo['details']['detail']['goodsSubtotal']; // 金額
							$csvRow[(string)(86 +  $count * 5)] = $invoiceInfo['details']['detail']['goodsExpand'];   // 金額消費税
						} else {
							$csvRow[(string)(82 +  $count * 5)] = ''; // 明細内容1
							$csvRow[(string)(83 +  $count * 5)] = ''; // 注文数1
							$csvRow[(string)(84 +  $count * 5)] = ''; // 単価1
							$csvRow[(string)(85 +  $count * 5)] = ''; // 金額1
							$csvRow[(string)(86 +  $count * 5)] = ''; // 金額消費税1	
						}
					}
				}
				
				$csvRow['157'] = $invoiceInfo['detailInfomation']; // 明細注意事項
				$csvRow['158'] = $invoiceInfo['expand11'];         // ゆうちょ口座番号
				$csvRow['159'] = $invoiceInfo['expand12'];
				$csvRow['160'] = $invoiceInfo['expand13'];
				$csvRow['161'] = $invoiceInfo['expand14'];
				$csvRow['162'] = $invoiceInfo['expand15'];
				$csvRow['163'] = $invoiceInfo['expand16'];
				$csvRow['164'] = $invoiceInfo['expand17'];
				$csvRow['165'] = $invoiceInfo['expand18'];
				$csvRow['166'] = $invoiceInfo['expand19'];
				$csvRow['167'] = $invoiceInfo['expand20'];
				
				// 空がから配列になってしまっているものを修正
				foreach ($csvRow as &$eachColumn) {
					if (is_array($eachColumn)) {
						$eachColumn = '';
					}
				}
				
				//var_dump($csvRow);
				
				mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
				fputcsv($fp, $csvRow);
			}
		}
		//exit;
		fclose($fp);
		
		
		$path2 = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '.csv');
		
		$handle = fopen($path, 'r');
	    $contents = fread($handle, filesize($path));
	    
	    // 改行コードをCRLFに置換
	    $str = str_replace(array("\r","\n"), "\r\n", $contents);
	    
	    $fp = fopen($path2, 'w');
	    fwrite($fp, $str);
	    fclose($fp);
    
        $this->_helper->binaryOutput(file_get_contents($path2), array(
            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
        ));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/delete                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 不要注文データ削除(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
    	$id = $request->getParam('target_id');
    	
		// POST送信時
		if ($request->isPost()) {
			$orderTable     = new Shared_Model_Data_Order();
			$orderItemTable = new Shared_Model_Data_OrderItem();
			$logTable       = new Shared_Model_Data_OrderImportLog();
			
			$oldData = $orderTable->getById($this->_warehouseSession->warehouseId, $id);
			
			
			$orderTable->getAdapter()->beginTransaction();
	    	
	        try {
				$orderTable->updateById($this->_adminProperty['management_group_id'], $id, array('status' => Shared_Model_Code::SHIPMENT_STATUS_DELETED));
				
				$orderItems = $orderItemTable->getListByOrderId($id);
				
				foreach ($orderItems as $eachOrderItem) {
					$orderItemTable->updateById($eachOrderItem['id'], array('status' => Shared_Model_Code::ORDER_ITEM_STATUS_DELETED));
				}
				
				$logTable->updateByOrderId($oldData['relational_order_id'], array(
					'status' => 0,
				));

	            // commit
	            $orderTable->getAdapter()->commit();
	            
	        } catch (Exception $e) {
	            $orderTable->getAdapter()->rollBack();
	            throw new Zend_Exception('/shipment/delete transaction failed: ' . $e);  
	        }
        
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
    
	    $this->sendJson(array('result' => 'NG'));
    	return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/assign                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 検品者割り当て                                             |
    +----------------------------------------------------------------------------*/
    public function assignAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/shipment/plan-list';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '実行';

        // 検品者リスト
        $userTable = new Shared_Model_Data_User();
        $selectObj = $userTable->select();
        $selectObj->where('frs_user.management_group_id = ?', $this->_adminProperty['management_group_id']);
        $selectObj->where('status = ?', Shared_Model_Code::USER_STATUS_ACTIVE);
        $this->view->inspectionUserList = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/assign-post                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 検品者割り当て実行(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function assignPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
    	
		// POST送信時
		if ($request->isPost()) {
			
			$userIdList = $request->getParam('user_id');
			
			if (empty($userIdList)) {
				// 検品者が未選択
			    $this->sendJson(array('result' => 'NG'));
		    	return;
			}
			
			// 検品者リスト
			$inspectionUserCount = count($userIdList);
			$inspectionUserList = array();
			
			foreach ($userIdList as $eachId) {
				$inspectionUserList[] = array(
					'user_id'    => $eachId,
					'item_count' => 0,
				);
			}
			
			$orderTable = new Shared_Model_Data_Order();
			
			// 本日検品する注文リスト(発送予定日が本日まで)
			$orderList = $orderTable->getListForInspection($this->_warehouseSession->warehouseId);
			
			$itemCountPerUser = ceil(count($orderList) / $inspectionUserCount);

			$userCount = 0;
			foreach ($orderList as $eachOrder) {
				
				// 割り当て
				$orderTable->updateById($this->_warehouseSession->warehouseId, $eachOrder['id'], array(
					'inspection_user_id' => $inspectionUserList[$userCount]['user_id'],
				));
				
				$inspectionUserList[$userCount]['item_count']++;
				
				// 最後の検品者以外
				if ($userCount < $inspectionUserCount - 1) {
					if ($itemCountPerUser <= $inspectionUserList[$userCount]['item_count']) {
						$userCount++;
					}
				}
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
    
	    $this->sendJson(array('result' => 'NG'));
    	return;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/canceled-list                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * キャンセル済みリスト                                       |
    +----------------------------------------------------------------------------*/
    public function canceledListAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		
		// 検索条件
		$conditions = array();
		$conditions['relational_order_id'] = $request->getParam('relational_order_id', '');
		$conditions['payment_method']      = $request->getParam('payment_method', '');
		$conditions['delivery_method']     = $request->getParam('delivery_method', '');
		$conditions['delivery_code']       = $request->getParam('delivery_code', '');
		$conditions['inspection_user_id']  = $request->getParam('inspection_user_id', '');
		$this->view->conditions = $conditions;
		
		$orderTable = new Shared_Model_Data_Order();
		
		$dbAdapter = $orderTable->getAdapter();

        $selectObj = $orderTable->select();
        $selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array('user_name AS inspection_user_name'));
        
        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        
        if ($conditions['relational_order_id'] != '') {
        	$selectObj->where('relational_order_id = ?', $conditions['relational_order_id']);
        }
              
        if ($conditions['payment_method'] != '') {
        	$selectObj->where($orderTable->aesdecrypt('payment_method', false) . ' = ?', $conditions['payment_method']);
        }

        if ($conditions['delivery_method'] != '') {
        	$selectObj->where($orderTable->aesdecrypt('delivery_method', false) . ' = ?', $conditions['delivery_method']);
        }

        if ($conditions['delivery_code'] != '') {
        	$selectObj->where('delivery_code = ?', $conditions['delivery_code']);
        }

        $selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_CANCELED);
		$selectObj->order('frs_order.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);	
        
        
        // 検品者リスト
        $userTable = new Shared_Model_Data_User();
        $selectObj = $userTable->select();
        $selectObj->where('status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
        $this->view->inspectionUserList = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/shipped-list                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷済みリスト                                             |
    +----------------------------------------------------------------------------*/
    public function shippedListAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');

		$session = new Zend_Session_Namespace('shipment_shipped_list_1');

		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['relational_order_id'] = '';
			$session->conditions['payment_method']      = '';
			$session->conditions['delivery_method']     = '';
			$session->conditions['delivery_code']       = '';
			$session->conditions['inspection_user_id']  = '';
			
			$session->conditions['order_from_site']     = '';
			$session->conditions['order_customer_name'] = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['relational_order_id'] = $request->getParam('relational_order_id', '');
			$session->conditions['payment_method']      = $request->getParam('payment_method', '');
			$session->conditions['delivery_method']     = $request->getParam('delivery_method', '');
			$session->conditions['delivery_code']       = $request->getParam('delivery_code', '');
			$session->conditions['inspection_user_id']  = $request->getParam('inspection_user_id', '');
			
			$session->conditions['order_from_site']     = $request->getParam('order_from_site', '');
			$session->conditions['order_customer_name'] = $request->getParam('order_customer_name', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		
		
		// 検索条件
		/*
		$conditions = array();
		$conditions['relational_order_id'] = $request->getParam('relational_order_id', '');
		$conditions['payment_method']      = $request->getParam('payment_method', '');
		$conditions['delivery_method']     = $request->getParam('delivery_method', '');
		$conditions['delivery_code']       = $request->getParam('delivery_code', '');
		$conditions['inspection_user_id']  = $request->getParam('inspection_user_id', '');
		$this->view->conditions = $conditions;
		*/
		
		$orderTable = new Shared_Model_Data_Order();
		
		$dbAdapter = $orderTable->getAdapter();

        $selectObj = $orderTable->select();
        $selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array('user_name AS inspection_user_name'));
        
        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        
        if ($conditions['relational_order_id'] != '') {
        	$selectObj->where('relational_order_id = ?', $conditions['relational_order_id']);
        }
              
        if ($conditions['payment_method'] != '') {
        	$selectObj->where($orderTable->aesdecrypt('payment_method', false) . ' = ?', $conditions['payment_method']);
        }

        if ($conditions['delivery_method'] != '') {
        	$selectObj->where($orderTable->aesdecrypt('delivery_method', false) . ' = ?', $conditions['delivery_method']);
        }

        if ($conditions['delivery_code'] != '') {
        	$selectObj->where('delivery_code = ?', $conditions['delivery_code']);
        }

        if ($conditions['order_from_site'] != '') {
        	$selectObj->where('order_from_site = ?', $conditions['order_from_site']);
        } 
		
		
        if ($conditions['order_customer_name'] != '') {
        	$name = $dbAdapter->quote('%' . $conditions['order_customer_name'] . '%');
			$where = $orderTable->aesdecrypt('order_customer_name', false) . ' LIKE ' . $name;
        	
        	//var_dump($where);exit;
       		$selectObj->where($where);
        	
        	//var_dump($selectObj->__toString());exit;
        } 

/*
		if ($session->conditions['order_customer_name'] != '') {
			//var_dump($session->conditions['order_customer_name']);exit;
			$text = $session->conditions['order_customer_name'];
			
			//$keywords = Shared_Model_Utility_Text::extractKeywords($session->conditions['order_customer_name']);
			
        	$columns = array(
        		'order_customer_name',
        	);
			
			$where = array();
			foreach ($keywords as $eachKeyword) {
				$whereEach = array();
				
				$name = $dbAdapter->quote('%' . Shared_Model_Utility_Text::hiraganaToKatakana($eachKeyword) . '%');
				
				foreach ($columns as $each) {
		        	$whereEach[] = $repeatTable->aesdecrypt($each, false) . ' LIKE ' . $name;
				}
				
				$where[] = '(' . implode($whereEach, ' OR ') . ')';
			}
			
			//var_dump($where);
			
			$selectObj->where(new Zend_Db_Expr(implode($where, ' AND ')));
		}
*/		

        $selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_SHIPPED);
        
		$selectObj->order('frs_order.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);	
        
        
        // 検品者リスト
        $userTable = new Shared_Model_Data_User();
        $selectObj = $userTable->select();
        $selectObj->where('status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
        $this->view->inspectionUserList = $selectObj->query()->fetchAll();

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/detail                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷情報詳細                                               |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$this->view->warehouseId = $this->_warehouseSession->warehouseId;
		
		if (empty($id)) {
			throw new Zend_Exception('/shipment/detail - no target id');
		}
		
		$orderTable     = new Shared_Model_Data_Order();
		$orderItemTable = new Shared_Model_Data_OrderItem();
		
		// 注文データ
		$this->view->orderData = $data = $orderTable->getById($this->_warehouseSession->warehouseId, $id);
		
		if (empty($data)) {
			throw new Zend_Exception('/shipment/detail - no target data');
		}
		
		if ((int)$data['status'] <= Shared_Model_Code::SHIPMENT_STATUS_HOLDED) {
			$this->view->backUrl = '/shipment/plan-list';
		} else if ((int)$data['status'] === (int)Shared_Model_Code::SHIPMENT_STATUS_CANCELED) {
			$this->view->backUrl = '/shipment/canceled-list';
		} else {
			$this->view->backUrl = '/shipment/shipped-list';
		}
		
		// 注文商品
		$this->view->orderItems = $orderItems = $orderItemTable->getListByOrderId($id);

		// 引当在庫
		$consumptionTable = new Shared_Model_Data_ItemStockConsumption();
		$this->view->consumptionList = $consumptionTable->getListByOrderId($id);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-basic                                     |
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

                if (!empty($errorMessage['shipment_plan_date']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発送予定日」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$orderTable = new Shared_Model_Data_Order();
	
				// 更新
				$data = array(
					'shipment_plan_date'    => $success['shipment_plan_date'],
					'delivery_request_date' => '',
					'delivery_request_time' => '',
					'delivery_code'         => $success['delivery_code'],
				);
				
				if (!empty($success['delivery_request_date'])) {
					$data['delivery_request_date'] = $success['delivery_request_date'];
				}

				if (!empty($success['delivery_request_time'])) {
					$data['delivery_request_time'] = $success['delivery_request_time'];
				}     

				$orderTable->updateById($this->_warehouseSession->warehouseId, $id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-to-cancel                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * キャンセルに変更(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function updateToCancelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$id      = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$orderTable = new Shared_Model_Data_Order();

			// 更新
			$data = array(
				'status' => Shared_Model_Code::SHIPMENT_STATUS_CANCELED,
			);
			$orderTable->updateById($this->_warehouseSession->warehouseId, $id, $data);

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-to-new                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 新規注文に変更(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function updateToNewAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$id      = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$orderTable = new Shared_Model_Data_Order();

			// 更新
			$data = array(
				'status' => Shared_Model_Code::SHIPMENT_STATUS_NEW,
			);
			$orderTable->updateById($this->_warehouseSession->warehouseId, $id, $data);

		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-amount                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 支払情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateAmountAction()
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

                if (!empty($errorMessage['charge']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「手数料」は半角数字のみで入力してください'));
                    return;
				} else if (!empty($errorMessage['delivery_fee']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「送料」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['tax']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「消費税」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['discount']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「値引き額」は半角数字のみで入力してください'));
                    return;
                } else if (!empty($errorMessage['total']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「合計金額」は半角数字のみで入力してください'));
                    return;
                }
				
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$orderTable = new Shared_Model_Data_Order();

				// 更新
				$data = array(
					'charge'       => '0',
					'delivery_fee' => '0',
					'tax'          => '0',
					'discount'     => '0',
					'total'        => '0',
				);
				
				if (!empty($success['charge'])) {
					$data['charge'] = $success['charge'];
				}

				if (!empty($success['delivery_fee'])) {
					$data['delivery_fee'] = $success['delivery_fee'];
				}

				if (!empty($success['tax'])) {
					$data['tax'] = $success['tax'];
				}
				
				if (!empty($success['discount'])) {
					$data['discount'] = $success['discount'];
				}
				
				if (!empty($success['total'])) {
					$data['total'] = $success['total'];
				}
				
				$orderTable->updateById($this->_warehouseSession->warehouseId, $id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-customer                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 依頼主更新(Ajax)                                           |
    +----------------------------------------------------------------------------*/
    public function updateCustomerAction()
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

                if (!empty($errorMessage['order_customer_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「依頼主名」を入力してください'));
                    return;
				} else if (!empty($errorMessage['order_customer_name_kana']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「依頼主名カナ」を入力してください'));
                    return;
                } else if (!empty($errorMessage['order_zipcode']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「郵便番号」を入力してください'));
                    return;
                } else if (!empty($errorMessage['order_prefecture']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「都道府県」を入力してください'));
                    return;
                } else if (!empty($errorMessage['order_address1']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「住所」を入力してください'));
                    return;
                } else if (!empty($errorMessage['order_address2']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「建物」を入力してください'));
                    return;
                } else if (!empty($errorMessage['order_tel']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「電話番号」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$orderTable = new Shared_Model_Data_Order();

				// 更新
				$data = array(
					'order_customer_name'       => $success['order_customer_name'],
					'order_customer_name_kana'  => $success['order_customer_name_kana'],
					'order_zipcode'             => $success['order_zipcode'],
					'order_prefecture'          => $success['order_prefecture'],
					'order_address1'            => $success['order_address1'],
					'order_address2'            => $success['order_address2'],
					'order_tel'                 => $success['order_tel'],
				);
				
				$orderTable->updateById($this->_warehouseSession->warehouseId, $id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    } 


    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-delivery                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 依頼主更新(Ajax)                                           |
    +----------------------------------------------------------------------------*/
    public function updateDeliveryAction()
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

                if (!empty($errorMessage['delivery_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「依頼主名」を入力してください'));
                    return;
				} else if (!empty($errorMessage['delivery_name_kana']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「依頼主名カナ」を入力してください'));
                    return;
                } else if (!empty($errorMessage['delivery_zipcode']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「郵便番号」を入力してください'));
                    return;
                } else if (!empty($errorMessage['delivery_prefecture']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「都道府県」を入力してください'));
                    return;
                } else if (!empty($errorMessage['delivery_address1']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「住所」を入力してください'));
                    return;
                } else if (!empty($errorMessage['delivery_address2']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「建物」を入力してください'));
                    return;
                } else if (!empty($errorMessage['delivery_tel']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「電話番号」を入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$orderTable = new Shared_Model_Data_Order();

				// 更新
				$data = array(
					'delivery_name'       => $success['delivery_name'],
					'delivery_name_kana'  => $success['delivery_name_kana'],
					'delivery_zipcode'    => $success['delivery_zipcode'],
					'delivery_prefecture' => $success['delivery_prefecture'],
					'delivery_address1'   => $success['delivery_address1'],
					'delivery_address2'   => $success['delivery_address2'],
					'delivery_tel'        => $success['delivery_tel'],
				);
				
				$orderTable->updateById($this->_warehouseSession->warehouseId, $id, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    } 

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/export-yamato                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ヤマトB2クラウド向けcsv出力                                |
    +----------------------------------------------------------------------------*/
    public function exportYamatoAction()
    {
    	$request = $this->getRequest();
    	$inspectionUserId = $request->getParam('inspection_user_id');

		$orderTable = new Shared_Model_Data_Order();
		
		// 対象検品者の検品リスト
        $data = $orderTable->getListForInspectionUser($this->_warehouseSession->warehouseId, $inspectionUserId);

		$basicTable = new Shared_Model_Data_BasicInfo();
		$shopInfo =  $basicTable->get($this->_warehouseSession->warehouseId);
			
    	$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '.csv');
    	//var_dump($path);exit;
    	
    	$fp = fopen($path, 'w');

		$csvRow = array(
			'0'  => 'お客様管理番号(半角英数字50文字)',
			'1'  => '送り状種類',
			'2'  => 'クール区分',
			'3'  => '伝票番号(※B2クラウドにて付与)',
			'4'  => '出荷予定日(YYYY/MM/DD)',
			'5'  => 'お届け予定日(YYYY/MM/DD)',
			'6'  => '配達時間帯 例:0812 8〜12時',
			'7'  => 'お届け先コード',
			'8'  => 'お届け先電話番号(ハイフン含む)',
			'9'  => 'お届け先電話番号枝番',
			'10' => 'お届け先郵便番号',
			'11' => 'お届け先住所',
			'12' => 'お届け先アパートマンション名',
			'13' => 'お届け先会社・部門１',
			'14' => 'お届け先会社・部門２',
			'15' => 'お届け先名',
			'16' => 'お届け先名(ｶﾅ)', 
			'17' => '敬称(ＤＭ便の場合に指定可能)',
			'18' => 'ご依頼主コード', 
			'19' => 'ご依頼主電話番号',
			'20' => 'ご依頼主電話番号枝番',
			'21' => 'ご依頼主郵便番号',
			'22' => 'ご依頼主住所',
			'23' => 'ご依頼主アパートマンション',
			'24' => 'ご依頼主名',
			'25' => 'ご依頼主名(ｶﾅ)',
			'26' => '品名コード１',
			'27' => '品名１',
			'28' => '品名コード２',
			'29' => '品名２',
			'30' => '荷扱い１',
			'31' => '荷扱い２',
			'32' => '記事',
			'33' => 'ｺﾚｸﾄ代金引換額（税込)',
			'34' => '内消費税額等',
			'35' => '止置き',
			'36' => '営業所コード',
			'37' => '発行枚数',
			'38' => '個数口表示フラグ',
			'39' => '請求先顧客コード (半角数字12文字)',
			'40' => '請求先分類コード',
			'41' => '運賃管理番号 (半角数字2文字)',
			'42' => 'クロネコwebコレクトデータ登録', 
			'43' => 'クロネコwebコレクト加盟店番号',
			'44' => 'クロネコwebコレクト申込受付番号１',
			'45' => 'クロネコwebコレクト申込受付番号２',
			'46' => 'クロネコwebコレクト申込受付番号３',
			'47' => 'お届け予定ｅメール利用区分',
			'48' => 'お届け予定ｅメールe-mailアドレス',
			'49' => '入力機種',
			'50' => 'お届け予定ｅメールメッセージ',
			'51' => 'お届け完了ｅメール利用区分',
			'52' => 'お届け完了ｅメールe-mailアドレス',
			'53' => 'お届け完了ｅメールメッセージ',
			'54' => 'クロネコ収納代行利用区分', 
			'55' => '予備',
			'56' => '収納代行請求金額(税込)',
			'57' => '収納代行内消費税額等',
			'58' => '収納代行請求先郵便番号',
			'59' => '収納代行請求先住所', 
			'60' => '収納代行請求先住所（アパートマンション名）',
			'61' => '収納代行請求先会社・部門名１',
			'62' => '収納代行請求先会社・部門名２',
			'63' => '収納代行請求先名(漢字)',
			'64' => '収納代行請求先名(カナ)',
			'65' => '収納代行問合せ先名(漢字)',
			'66' => '収納代行問合せ先郵便番号',
			'67' => '収納代行問合せ先住所',
			'68' => '収納代行問合せ先住所（アパートマンション名）',
			'69' => '収納代行問合せ先電話番号',
			'70' => '収納代行管理番号',
			'71' => '収納代行品名', 
			'72' => '収納代行備考',
			'73' => '複数口くくりキー',
			'74' => '検索キータイトル1',
			'75' => '検索キー1',
			'76' => '検索キータイトル2',
			'77' => '検索キー2',
			'78' => '検索キータイトル3',
			'79' => '検索キー3',
			'80' => '検索キータイトル4',
			'81' => '検索キー4',
			'82' => '検索キータイトル5',
			'83' => '検索キー5',
			'84' => '予備',
			'85' => '予備',
			'86' => '投函予定メール利用区分',
			'87' => '投函予定メールe-mailアドレス',
			'88' => '投函予定メールメッセージ',
			'89' => '投函完了メール（お届け先宛）利用区分',
			'90' => '投函完了メール（お届け先宛）e-mailアドレス',
			'91' => '投函完了メール（お届け先宛）メールメッセージ',
			'92' => '投函完了メール（ご依頼主宛）利用区分',
			'93' => '投函完了メール（ご依頼主宛）e-mailアドレス', 
			'94' => '投函完了メール（ご依頼主宛）メールメッセージ', 
		);
	
		mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);
		
		$count = 0;
		
		$shipmentDate = new Zend_Date(NULL, NULL, 'ja_JP');
		if ((int)$shipmentDate->get('HH') > 16) {
			$shipmentDate->add('1', Zend_Date::DAY);
		}
		$shipmentDateString = $shipmentDate->get('yyyy/MM/dd');
		
		foreach ($data as $row) {
			
			// 送り状種類(0:発払い, 7:ネコポス) ★
			$recieptType = 0;
			if ((string)Shared_Model_Code::DELIVERY_TYPE_YAMATO_POST === $row['delivery_method']) {
				// ネコポス
				$recieptType = 7;
				
			} else if ((string)Shared_Model_Code::DELIVERY_TYPE_YAMATO_COMPACT === $row['delivery_method']) {
				// 宅急便コンパクト
				$recieptType = 8;
				
			} else if ((string)Shared_Model_Code::DELIVERY_TYPE_YAMATO_DM === $row['delivery_method']) {
				// DM便
				$recieptType = 3;
			}
			
			if ((string)Shared_Model_Code::PAYMENT_TYPE_CASH_ON_DELIVERY === $row['payment_method']) {
				if ((string)Shared_Model_Code::DELIVERY_TYPE_YAMATO === $row['delivery_method']) {
					// 宅急便コレクト
					$recieptType = 2;
					
					
				} else if ((string)Shared_Model_Code::DELIVERY_TYPE_YAMATO_COMPACT === $row['delivery_method']) {
					// コンパクトコレクト
					$recieptType = 9;
				}
			}
			
			$deliveryDate = '';
			if (!empty($row['delivery_request_date'])) {
				$deliveryDate = date('Y/m/d', strtotime($row['delivery_request_date']));
			}
			//var_dump($row['delivery_method']);exit;
			
			$reglex = '/(東京都|北海道|(?:京都|大阪)府|.{6,9}県)((?:四日市|廿日市|野々市|臼杵|かすみがうら|つくばみらい|いちき串木野)市|(?:杵島郡大町|余市郡余市|高市郡高取)町|.{3,12}市.{3,12}区|.{3,9}区|.{3,15}市(?=.*市)|.{3,15}市|.{6,27}町(?=.*町)|.{6,27}町|.{9,24}村(?=.*村)|.{9,24}村)(.*)/';
			//$addressDelivery = str_replace(' ', '', $row['delivery_prefecture'] . $row['delivery_address1']);
			//preg_match($reglex, $addressDelivery, $deliveryArray);
			//var_dump($deliveryArray);exit;
			
			$addressBase = $row['delivery_prefecture'] . $row['delivery_address1'] . $row['delivery_address2'];
			$addressBase = str_replace(array(' ', '　'), '', $addressBase);
			
			$addressParts = preg_split(
				'/^([^\d０-９－―-]*+[\d０-９－―ー丁目番号-]*+[\d０-９－―ー号-]*+)/u',
				$addressBase,
				2,
				PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
			);
			//var_dump($count);
			//var_dump($addressParts[0]);
			//var_dump(!empty($addressParts[1]) ? $addressParts[1] : '');
			
			$addressShop = str_replace(' ', '', $shopInfo['prefecture'] . $shopInfo['address1']);
			preg_match($reglex, $addressShop, $shopArray);
			//var_dump($shopArray);exit;
			
			$csvRow = array(
				'0'  => $row['relational_order_id'], // お客様管理番号(半角英数字50文字)
				'1'  => $recieptType, // 送り状種類(0:発払い, 7:ネコポス) ★
				'2'  => '0', // クール区分
				'3'  => '',  // 伝票番号(※B2クラウドにて付与)
				'4'  => $shipmentDateString, //date('Y/m/d', strtotime($row['shipment_plan_date'])), // 出荷予定日(YYYY/MM/DD) ★
				'5'  => $deliveryDate,  // お届け予定日(YYYY/MM/DD)
				'6'  => $row['delivery_request_time'],  // 配達時間帯 例:0812 8〜12時
				'7'  => '',  // お届け先コード
				'8'  => $row['delivery_tel'],  // お届け先電話番号(ハイフン含む) ★
				'9'  => '',  // お届け先電話番号枝番
				'10' => str_replace('-', '', $row['delivery_zipcode']),  // お届け先郵便番号 ★
				'11' => $addressParts[0],  // お届け先住所 ★
				'12' => !empty($addressParts[1]) ? $addressParts[1] : '',  // お届け先アパートマンション名
				'13' => '',  // お届け先会社・部門１
				'14' => '',  // お届け先会社・部門２
				'15' => $row['delivery_name'],  // お届け先名 ★
				'16' => mb_convert_kana($row['delivery_name_kana'], 'k'),  // お届け先名(ｶﾅ)
				'17' => '',  // 敬称(ＤＭ便の場合に指定可能)
				'18' => '',  // ご依頼主コード
				'19' => $shopInfo['shop_tel'],  // ご依頼主電話番号 ★
				'20' => '',  // ご依頼主電話番号枝番
				'21' => $shopInfo['zipcode'],  // ご依頼主郵便番号 ★
				'22' => $shopArray[1] . $shopArray[2] . $shopArray[3],  // ご依頼主住所 ★
				'23' => $shopInfo['address2'],  // ご依頼主アパートマンション
				'24' => $shopInfo['shop_name'],  // ご依頼主名 ★
				'25' => '',  // ご依頼主名(ｶﾅ)
				'26' => '',  // 品名コード１
				'27' => '健康食品',  // 品名１ ★             ------------------------------------ 確認
				'28' => '',  // 品名コード２
				'29' => '',  // 品名２
				'30' => '',  // 荷扱い１
				'31' => '',  // 荷扱い２
				'32' => '',  // 記事
				'33' => '',  // ｺﾚｸﾄ代金引換額（税込)
				'34' => '',  // 内消費税額等
				'35' => '',  // 止置き
				'36' => '',  // 営業所コード
				'37' => '',  // 発行枚数
				'38' => '',  // 個数口表示フラグ
				'39' => '05053704159',  // 請求先顧客コード (半角数字12文字)★------------------------------------ 確認
				'40' => '',  // 請求先分類コード
				'41' => '01',  // 運賃管理番号 (半角数字2文字)★------------------------------------ 確認
				'42' => '',  // クロネコwebコレクトデータ登録
				'43' => '',  // クロネコwebコレクト加盟店番号
				'44' => '',  // クロネコwebコレクト申込受付番号１
				'45' => '',  // クロネコwebコレクト申込受付番号２
				'46' => '',  // クロネコwebコレクト申込受付番号３
				'47' => '',  // お届け予定ｅメール利用区分
				'48' => '',  // お届け予定ｅメールe-mailアドレス
				'49' => '',  // 入力機種
				'50' => '',  // お届け予定ｅメールメッセージ
				'51' => '',  // お届け完了ｅメール利用区分
				'52' => '',  // お届け完了ｅメールe-mailアドレス
				'53' => '',  // お届け完了ｅメールメッセージ
				'54' => '',  // クロネコ収納代行利用区分
				'55' => '',  // 予備
				'56' => '',  // 収納代行請求金額(税込)
				'57' => '',  // 収納代行内消費税額等
				'58' => '',  // 収納代行請求先郵便番号
				'59' => '',  // 収納代行請求先住所
				'60' => '',  // 収納代行請求先住所（アパートマンション名）
				'61' => '',  // 収納代行請求先会社・部門名１
				'62' => '',  // 収納代行請求先会社・部門名２
				'63' => '',  // 収納代行請求先名(漢字)
				'64' => '',  // 収納代行請求先名(カナ)
				'65' => '',  // 収納代行問合せ先名(漢字)
				'66' => '',  // 収納代行問合せ先郵便番号
				'67' => '',  // 収納代行問合せ先住所
				'68' => '',  // 収納代行問合せ先住所（アパートマンション名）
				'69' => '',  // 収納代行問合せ先電話番号
				'70' => '',  // 収納代行管理番号
				'71' => '',  // 収納代行品名
				'72' => '',  // 収納代行備考
				'73' => '',  // 複数口くくりキー
				'74' => '',  // 検索キータイトル1
				'75' => '',  // 検索キー1
				'76' => '',  // 検索キータイトル2
				'77' => '',  // 検索キー2
				'78' => '',  // 検索キータイトル3
				'79' => '',  // 検索キー3
				'80' => '',  // 検索キータイトル4
				'81' => '',  // 検索キー4
				'82' => '',  // 検索キータイトル5
				'83' => '',  // 検索キー5
				'84' => '',  // 予備
				'85' => '',  // 予備
				'86' => '',  // 投函予定メール利用区分
				'87' => '',  // 投函予定メールe-mailアドレス
				'88' => '',  // 投函予定メールメッセージ
				'89' => '',  // 投函完了メール（お届け先宛）利用区分
				'90' => '',  // 投函完了メール（お届け先宛）e-mailアドレス
				'91' => '',  // 投函完了メール（お届け先宛）メールメッセージ
				'92' => '',  // 投函完了メール（ご依頼主宛）利用区分
				'93' => '',  // 投函完了メール（ご依頼主宛）e-mailアドレス
				'94' => '',  // 投函完了メール（ご依頼主宛）メールメッセージ
			);
			
			// ネコポス/DMの場合は指定日クリア
			if (Shared_Model_Code::DELIVERY_TYPE_YAMATO_POST == (string)$row['delivery_method'] || Shared_Model_Code::DELIVERY_TYPE_YAMATO_DM == (string)$row['delivery_method']) {
				$csvRow['5']  = '';
			}
			
			mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
			fputcsv($fp, $csvRow);
			
			$count++;
			/*
			if ($count >= 10) {
				break;
			}
			*/
		}
		
		//exit;
		
		fclose($fp);
		
        $this->_helper->binaryOutput(file_get_contents($path), array(
            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
        ));
    
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/export-nissen                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ニッセン後払い支払い用紙印刷用CSV                          |
    +----------------------------------------------------------------------------*/
    public function exportNissenAction()
    {
    	$request = $this->getRequest();
    	$inspectionUserId = $request->getParam('inspection_user_id');
    	
		$orderTable = new Shared_Model_Data_Order();
		
		// 対象検品者の検品リスト
        $data = $orderTable->getListForInspectionUser($this->_warehouseSession->warehouseId, $inspectionUserId);
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/export-statement                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 明細書PDF(検品者分)                                        |
    +----------------------------------------------------------------------------*/
    public function exportStatementAction()
    {
    	$request = $this->getRequest();
    	$inspectionUserId = $request->getParam('inspection_user_id');
    	
    	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
		$deliveryTypeList = Shared_Model_Code::codes('delivery_type');
		$paymentTypeList  = Shared_Model_Code::codes('payment_type');

		$orderTable     = new Shared_Model_Data_Order();
		$orderItemTable = new Shared_Model_Data_OrderItem();
		$basicTable     = new Shared_Model_Data_BasicInfo();
		$templateTable  = new Shared_Model_Data_MessageTemplate();
		
		$basicInfo    = $basicTable->get($this->_warehouseSession->warehouseId);

		$params = Shared_Model_Pdf_ShipmentReceipt::createDefaultParams();
		$params['logo_path'] = Shared_Model_Resource_Logo::getResourceObjectPath($basicInfo['logo_file_name']);
		$params['shop_info'] = $basicInfo['statement_shop_info'];
		
		$templateData1 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_1']);
		$templateData2 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_2']);
		$templateData3 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_3']);
		
		$templateData1 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_1']);
		$templateData2 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_2']);
		$templateData3 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_3']);
		
		// 対象検品者の検品リスト
    	$items = $orderTable->getListForInspectionUserNotExported($this->_warehouseSession->warehouseId, $inspectionUserId);
		$pdfItems = array();
		
		foreach ($items as $each) {
			$pdfData = $params;
			$pdfData['customer_id']          = $each['customer_id'];
			$pdfData['order_customer_name']  = $each['order_customer_name'];
			$pdfData['jaccs_with_package']   = $each['jaccs_with_package'];
			
			if (!empty($each['subscription_count'])) {
				// 定期
				$pdfData['template_1'] = $templateData1['message'];
				$pdfData['template_2'] = $templateData2['message'];
				$pdfData['template_3'] = $templateData3['message'];
				
			} else {	
				// 通常
				$pdfData['template_1'] = $templateData1['message'];
				$pdfData['template_2'] = $templateData2['message'];
				$pdfData['template_3'] = $templateData3['message'];
			}
			
			$pdfData['template_1'] = str_replace('@@name@@', $each['order_customer_name'], $pdfData['template_1']);
			$pdfData['template_2'] = str_replace('@@name@@', $each['order_customer_name'], $pdfData['template_2']);
			$pdfData['template_3'] = str_replace('@@name@@', $each['order_customer_name'], $pdfData['template_3']);
			
			
			
			$pdfData['order_no']              = $each['relational_order_id'];
			$pdfData['order_date']            = date('Y年m月d日', strtotime($each['order_datetime']));
			
			if (!empty($paymentTypeList[$each['payment_method']])) {
				$pdfData['payment_method']        = $paymentTypeList[$each['payment_method']];
			} else {
				$pdfData['payment_method']        = 'その他';
			}
			
			$pdfData['delivery_method']       = $deliveryTypeList[$each['delivery_method']];
			$pdfData['delivery_zipcode']      = $each['delivery_zipcode'];
			$pdfData['delivery_full_address'] = $each['delivery_prefecture'] . $each['delivery_address1'] . $each['delivery_address2'];

			$pdfData['delivery_name']         = $each['delivery_name'];
			
			$pdfData['order_zipcode']         = $each['order_zipcode'];
			$pdfData['order_full_address']    = $each['order_prefecture'] . $each['order_address1'] . $each['order_address2'];
			$pdfData['order_name']            = $each['order_customer_name'];
			
			$pdfData['tax']                   = $each['tax'];
			$pdfData['delivery_fee']          = $each['delivery_fee'];
			$pdfData['charge']                = $each['charge'];
			$pdfData['discount']              = $each['discount'];
			$pdfData['total']                 = $each['total'];
			
			$pdfData['is_royal_customer']     = $each['is_royal_customer'];
			
			$pdfData['order_from_site']       = $each['order_from_site'];
			
			// 購入商品リスト
			$purchasedList = $orderItemTable->getListByOrderId($each['id']);
			
	    	$items = array();
	    	
	    	foreach ($purchasedList as $eachItem) {
		    	$items[] = array(
					'product_code'        => $eachItem['product_code'],
					'product_name'        => $eachItem['product_name'],
					'amount'              => $eachItem['amount'],
					'product_unit_price'  => number_format($eachItem['unit_price']),
					'row_price'           => number_format((int)$eachItem['unit_price'] * (int)$eachItem['amount']),
					'item_tax_rate'              => $eachItem['item_tax_rate'],
					'item_total'              => $eachItem['item_total'],
					'item_total_with_tax'              => $eachItem['item_total_with_tax'],
		    	);
	    	}
	    	$pdfData['product_items'] = $items;
			
			
			$pdfItems[] = $pdfData;
			
			
			$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'], array('statement_exported' => 1));
			
		}
		
		$helper = $this->view->getHelper('numberFormat');
    	Shared_Model_Pdf_ShipmentReceipt::makeMultiple($pdfItems, $helper);
    
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/export-statement-each                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 明細書PDF(1件ごと)                                         |
    +----------------------------------------------------------------------------*/
    public function exportStatementEachAction()
    {
    	$request = $this->getRequest();
    	$orderId = $request->getParam('order_id');
    	
		$deliveryTypeList = Shared_Model_Code::codes('delivery_type');
		$paymentTypeList  = Shared_Model_Code::codes('payment_type');

		$orderTable     = new Shared_Model_Data_Order();
		$orderItemTable = new Shared_Model_Data_OrderItem();
		$basicTable     = new Shared_Model_Data_BasicInfo();
		$templateTable  = new Shared_Model_Data_MessageTemplate();
		
		$basicInfo    = $basicTable->get($this->_warehouseSession->warehouseId);

		$params = Shared_Model_Pdf_ShipmentReceipt::createDefaultParams();
		$params['logo_path'] = Shared_Model_Resource_Logo::getResourceObjectPath($basicInfo['logo_file_name']);
		$params['shop_info'] = $basicInfo['statement_shop_info'];

		$templateData1 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_1']);
		$templateData2 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_2']);
		$templateData3 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_3']);
		
		$templateData1 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_1']);
		$templateData2 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_2']);
		$templateData3 = $templateTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_3']);

		// 対象検品者の検品リスト
		$items = array();
    	$items[] = $orderTable->getById($this->_warehouseSession->warehouseId, $orderId);

		$pdfItems = array();
		
		foreach ($items as $each) {
			$pdfData = $params;

			$pdfData['customer_id']          = $each['customer_id'];
			$pdfData['order_customer_name']  = $each['order_customer_name'];
			
			if (!empty($each['subscription_count'])) {
				// 定期
				$pdfData['template_1'] = $templateData1['message'];
				$pdfData['template_2'] = $templateData2['message'];
				$pdfData['template_3'] = $templateData3['message'];
				
			} else {	
				// 通常
				$pdfData['template_1'] = $templateData1['message'];
				$pdfData['template_2'] = $templateData2['message'];
				$pdfData['template_3'] = $templateData3['message'];
			}
			
			$pdfData['template_1'] = str_replace('@@name@@', $each['order_customer_name'], $pdfData['template_1']);
			$pdfData['template_2'] = str_replace('@@name@@', $each['order_customer_name'], $pdfData['template_2']);
			$pdfData['template_3'] = str_replace('@@name@@', $each['order_customer_name'], $pdfData['template_3']);
			
			$pdfData['jaccs_with_package']    = $each['jaccs_with_package'];
			
			$pdfData['order_no']              = $each['relational_order_id'];
			$pdfData['order_date']            = date('Y年m月d日', strtotime($each['order_datetime']));
			$pdfData['payment_method']        = $paymentTypeList[$each['payment_method']];
			$pdfData['delivery_method']       = $deliveryTypeList[$each['delivery_method']];
			$pdfData['delivery_zipcode']      = $each['delivery_zipcode'];
			$pdfData['delivery_full_address'] = $each['delivery_prefecture'] . $each['delivery_address1'] . $each['delivery_address2'];
			$pdfData['delivery_name']         = $each['delivery_name'];
			
			$pdfData['order_zipcode']         = $each['order_zipcode'];
			$pdfData['order_full_address']    = $each['order_prefecture'] . $each['order_address1'] . $each['order_address2'];
			$pdfData['order_name']            = $each['order_customer_name'];
			//var_dump($each['order_prefecture']);exit;
			$pdfData['tax']                   = $each['tax'];
			$pdfData['delivery_fee']          = $each['delivery_fee'];
			$pdfData['charge']                = $each['charge'];
			$pdfData['discount']              = $each['discount'];
			$pdfData['total']                 = $each['total'];
			
			$pdfData['is_royal_customer']     = $each['is_royal_customer'];
			
			$pdfData['order_from_site']       = $each['order_from_site'];
			
			// 購入商品リスト
			$purchasedList = $orderItemTable->getListByOrderId($each['id']);
			
	    	$items = array();
	    	
	    	foreach ($purchasedList as $eachItem) {
		    	$items[] = array(
					'product_code'        => $eachItem['product_code'],
					'product_name'        => $eachItem['product_name'],
					'amount'              => $eachItem['amount'],
					'product_unit_price'  => number_format($eachItem['unit_price']),
					'row_price'           => number_format((int)$eachItem['unit_price'] * (int)$eachItem['amount']),
		    	);
	    	}
	    	$pdfData['product_items'] = $items;

			$pdfItems[] = $pdfData;
			
			
			$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'], array('statement_exported' => 1));
			
			
		}
		$helper = $this->view->getHelper('numberFormat');
    	Shared_Model_Pdf_ShipmentReceipt::makeMultiple($pdfItems, $helper);
    
    }
    	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/b2-import                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * B2クラウド出力データ読み込み                               |
    +----------------------------------------------------------------------------*/
    public function b2ImportAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/shipment/plan-list';
        
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/b2-import-result                                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * B2クラウド伝票番号取り込み結果(Ajax/html)                  |
    +----------------------------------------------------------------------------*/
    public function b2ImportResultAction()
    {
        $this->_helper->layout->setLayout('blank');
        $this->view->backUrl = '/shipment/plan-list';
        
    	$request   = $this->getRequest();
    	$importKey = $request->getParam('key', '');
    	
    	if (!empty($importKey)) {
    		$logTable = new Shared_Model_Data_OrderDeliveryCodeImportLog();
    		$this->view->items = $logTable->getItemsByImportKey($importKey);
    	}
    
    }
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/b2-import-csv                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * B2クラウド出力データ読み込み                               |
    +----------------------------------------------------------------------------*/
    public function b2ImportCsvAction()
    {
 		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        ini_set('display_errors', 0);
        
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
		
        if (file_exists($savePath)) {  
            $handle = fopen($savePath, "r");
            
            $rowCount = 1;
            
            // 1行ずつ処理
            while (($csvRow = fgetcsv($handle, 0, ",")) !== FALSE) {
            	$result = $this->importB2Data($rowCount, $key, $csvRow);
            	$rowCount++;
            }

        } else {
	        $this->sendJson(array('result' => false));
	        return;
        }

    	$this->sendJson(array('result' => 'OK', 'key' => $key, 'count' => $dataCount, 'data' => $data));
    	return;
    }

	/*
	 * B2データ1件取込
	*/
    private function importB2Data($rowCount, $importKey, $csvRow)
    {
    	$orderTable = new Shared_Model_Data_Order();
		$logTable   = new Shared_Model_Data_OrderDeliveryCodeImportLog();
		
		if (mb_strlen($csvRow[3]) != 12) {
			$logTable->addLog($importKey, $csvRow[0], $rowCount, 0, '伝票番号が不適当な値です');
        	return false;
		}
		
		// 対象の注文データ
		$targetOrderData = $orderTable->getByOrderId($this->_warehouseSession->warehouseId, $csvRow[0]);
		
		if (empty($targetOrderData)) {
			// 対象の注文データが見つかりません
        	$logTable->addLog($importKey, $csvRow[0], $rowCount, 0, '対象の注文データが見つかりません');
        	return false;
		}
		
		try {	
	    	$orderTable->updateById($this->_warehouseSession->warehouseId, $targetOrderData['id'], array(
	    		'delivery_code' => $csvRow[3],
	    	));
	    	
        } catch (Exception $e) {        
            $logTable->addLog($importKey, $csvRow[0], $rowCount, 0, '予期せぬエラー');
            return false;
        }   
    	
    	$logTable->addLog($importKey, $csvRow[0], $rowCount, 1, NULL);
    	return true;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/check-delivery-code                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 伝票番号有無確認                                           |
    +----------------------------------------------------------------------------*/
    public function checkDeliveryCodeAction()
    {
 		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
 
    	$orderTable = new Shared_Model_Data_Order();
    	
    	$selectObj = $orderTable->select();
    	$selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);
		$selectObj->order('frs_order.id ASC');
		$data = $selectObj->query()->fetchAll();
		
		if (empty($data)) {
			$this->sendJson(array('result' => 'NG', 'message' => '検品済みの注文がありません'));
			return;
		} else {
			foreach ($data as $each) {
				if (empty($each['delivery_code'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '検品済みの注文に伝票番号がないデータがあります'));
					return;
				}
			}
		}
		
        $this->sendJson(array('result' => 'OK'));
        return;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/export-delivery-code                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * EC-Cube用伝票番号出力                                      |
    +----------------------------------------------------------------------------*/
    public function exportDeliveryCodeAction()
    {
		$orderTable = new Shared_Model_Data_Order();
		
		$dbAdapter = $orderTable->getAdapter();

        $selectObj = $orderTable->select();
        $selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);
		$selectObj->order('frs_order.id ASC');
		$data = $selectObj->query()->fetchAll();
			
    	$path = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath(uniqid() . '.csv');
    	//var_dump($path);exit;
    	
    	$fp = fopen($path, 'w');

		$csvRow = array(
			'0'  => '注文番号',
			'1'  => '配送伝票番号',
		);
	
		mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
		fputcsv($fp, $csvRow);
		
		$count = 0;
			
		foreach ($data as $row) {
			$csvRow = array(
				'0'  => $row['relational_order_id'],
				'1'  => $row['delivery_code'],
			);
		
			mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
			fputcsv($fp, $csvRow);
			
			$count++;
		}
		 
		fclose($fp);
		
        $this->_helper->binaryOutput(file_get_contents($path), array(
            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
        ));
	}


    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/shipped                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発送済み処理画面                                           |
    +----------------------------------------------------------------------------*/
    public function shippedAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/shipment/plan-list';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '実行';
        
        $this->view->today = date('Y-m-d H:i:s');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/shipped-post-solo                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発送済み実行                                               |
    +----------------------------------------------------------------------------*/
    public function shippedPostSoloAction()
    {
    	$request = $this->getRequest();
    	$id = $request->getParam('id');
    	
    	$orderTable = new Shared_Model_Data_Order();
    	
    	$selectObj = $orderTable->select();
    	$selectObj->where('frs_order.id = ?', $id);
		$item = $selectObj->query()->fetch();

		
		// GOOSCA連携
		$clientData = array(
			'management_web_use_basic_auth' => false,
		);
		$result = Shared_Model_Gcs_Shipment::updateToShipped($clientData, $item);
		
		var_dump($result);
		echo 'OK';
		exit;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/shipped-post                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発送済み実行                                               |
    +----------------------------------------------------------------------------*/
    public function shippedPostAction()
    {
 		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
 		
 		$request = $this->getRequest();
 			
 		$targetDate    = str_replace('/', '-', $request->getParam('target_date', date('Y-m-d')));
 		$actionTimeString    = str_replace('/', '-', $request->getParam('action_time_day', date('Y-m-d'))) . ' ' . $request->getParam('action_time_hour', date('H')) . ':' . $request->getParam('action_time_min', date('i')) . ':00';
 		
    	$orderTable = new Shared_Model_Data_Order();
    	
    	$selectObj = $orderTable->select();
    	$selectObj->where('frs_order.warehouse_id = ?', $this->_warehouseSession->warehouseId);
        $selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);
		//$selectObj->where('frs_order.shipment_plan_date = ?', $targetDate);
		$selectObj->order('frs_order.id ASC');
		$items = $selectObj->query()->fetchAll();

		foreach ($items as $each) {
			if (empty($each['delivery_code'])) {
				$this->sendJson(array('result' => 'NG', 'message' => '検品済みの注文に伝票番号がないデータがあります'));
				return;
			}
			
			if (empty($each['delivery_code'])) {
				// 同梱でAPI未取得のものがあればNG
				if (!empty($each['jaccs_with_package'])) {
					
					if (empty($each['jaccs_invoice_data'])) {
						$this->sendJson(array('result' => 'NG', 'message' => 'ジャックス請求書データ未取得があります'));
						return;
					}
				}
			}
		}

		$clientData = array(
			'management_web_use_basic_auth' => false,
		);
			
		// GOOSCA連携
		if (APPLICATION_DOMAIN === 'localhost') {
			$clientData['management_web_use_basic_auth'] = true;
			$clientData['management_web_basic_user']     = 'goosca';
			$clientData['management_web_basic_pass']     = 'goosca';
		}

		
		$count = 0;
		$errors = array();
		foreach ($items as $each) {
			$result = Shared_Model_Gcs_Shipment::updateToShipped($clientData, $each);

			if (!empty($result)) {
				if ($result['result'] === 'OK') {
					$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'], array(
						'status'            => Shared_Model_Code::SHIPMENT_STATUS_SHIPPED,
						'shipment_datetime' => $actionTimeString,
					));
					
					$count++;
					
				} else {
					$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'], array(
						'shipment_error'    => $result['message'],
					));
					
					$errors[$each['relational_order_id']] = $result['message'];
				}
			} else if (!empty($result)) {
				$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'], array(
					'shipment_error'    => $result['message'],
				));
				
				$errors[$each['relational_order_id']] = $result['message'];
				
			} else {
				$orderTable->updateById($this->_warehouseSession->warehouseId, $each['id'], array(
					'shipment_error'    => '予期せぬエラー',
				));
				
				$errors[$each['relational_order_id']] = '予期せぬエラー';
			}

		}
		
		$message = '';
		
		foreach ($errors as $orderId => $eachMessage) {
			$message .= $orderId . '：' . $eachMessage . "\n";
		}
		
        $this->sendJson(array('result' => 'OK', 'count' => $count, 'message' => $message));
        return;
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/update-shipped                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 発送済み                                                   |
    +----------------------------------------------------------------------------*/
    public function updateShippedAction()
    {
 		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
 		
 		$request = $this->getRequest();
 		$id = $request->getParam('target_id');

		$clientData = array(
			'management_web_use_basic_auth' => false,
		);
		
 		// 注文データ
		$orderTable     = new Shared_Model_Data_Order();
		$data = $orderTable->getById($this->_warehouseSession->warehouseId, $id);
		
		Shared_Model_Gcs_Shipment::updateReShipped($clientData, $data);
		
		$this->sendJson(array('result' => 'OK'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/inspection-history                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 検品履歴 - 検品履歴                                        |
    +----------------------------------------------------------------------------*/
    public function inspectionHistoryAction()
    {
		$request = $this->getRequest();
		$this->view->page = $page = $request->getParam('page', 1);
		$this->view->inspectionUserId = $inspectionUserId = $request->getParam('inspection_user_id');
    
        // 検品者リスト
        $userTable = new Shared_Model_Data_User();
        $selectObj = $userTable->select();
        $selectObj->where('status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
        $this->view->inspectionUserList = $selectObj->query()->fetchAll();
		
		// 履歴日付リスト
		if (!empty($inspectionUserId)) {
			$orderTable = new Shared_Model_Data_Order();
			$selectObj = $orderTable->getSelectObjOfInspectionHistory($this->_warehouseSession->warehouseId, $inspectionUserId);
	
	        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
	        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
			$paginator->setCurrentPageNumber($page);
	
			$items = array();
	        
			foreach ($paginator->getCurrentItems() as $eachItem) {
				$items[] = $eachItem; 
			}
	
	        $this->view->items = $items;
	        $this->view->pager($paginator);
        } else {
	        // 合計
	        $orderTable = new Shared_Model_Data_Order();
	        
	        $selectObj = $orderTable->getSelectObjOfInspectionHistory($this->_warehouseSession->warehouseId, $inspectionUserId);
	        
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
			
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/inspection-date-items                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 検品履歴 - 日付別リスト                                    |
    +----------------------------------------------------------------------------*/
    public function inspectionDateItemsAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->inspectionUserId = $inspectionUserId = $request->getParam('inspection_user_id');
		$this->view->inspectionDate = $inspectionDate = $request->getParam('inspection_date');
		
		if (!empty($inspectionUserId)) {
			$this->view->backUrl = '/shipment/inspection-history?inspection_user_id=' . $inspectionUserId;
			
	        // 検品者リスト
	        $userTable = new Shared_Model_Data_User();
	        $this->view->inspectionUser = $userTable->getById($inspectionUserId);
		} else {
			$this->view->backUrl = '/shipment/inspection-history';
		}
		

		$orderTable = new Shared_Model_Data_Order();	
		$selectObj = $orderTable->getInspectionHistoryOfDate($this->_warehouseSession->warehouseId, $inspectionUserId, $inspectionDate);

        $this->view->items = $selectObj->query()->fetchAll();

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/inspection-detail                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 検品履歴 - 詳細                                            |
    +----------------------------------------------------------------------------*/
    public function inspectionDetailAction()
    {
    	
		
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/format-list                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取込フォーマット定義リスト                                 |
    +----------------------------------------------------------------------------*/
    public function formatListAction()
    {
    	$this->view->menu = 'setting';
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		
		$formatTable = new Shared_Model_Data_OrderImportFormat();
		
        $selectObj = $formatTable->getActiveList(true);
		$selectObj->order('id ASC');

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
    |  action_URL    * /shipment/format-add                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取込フォーマット新規登録                                   |
    +----------------------------------------------------------------------------*/
    public function formatAddAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$id      = $request->getParam('id');
		
		$formatTable = new Shared_Model_Data_OrderImportFormat();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/format-add-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 注文取込フォーマット新規登録(Ajax)                         |
    +----------------------------------------------------------------------------*/
    public function formatAddPostAction()
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

                if (!empty($errorMessage['name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「フォーマット名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$formatTable = new Shared_Model_Data_OrderImportFormat();
				
				// 新規登録
				$formatTable->create(array(
					'status'          => Shared_Model_Code::ORDER_IMPORT_FORMAT_STATUS_ACTIVE,
					'name'            => $success['name'],
					'column_setting'  => serialize(array()),
					'convert_setting' => serialize(array()),
	                'created'         => new Zend_Db_Expr('now()'),
	                'updated'         => new Zend_Db_Expr('now()'),
				));

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/format-detail                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 取込フォーマット詳細                                       |
    +----------------------------------------------------------------------------*/
    public function formatDetailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
    	$this->view->backUrl = '/shipment/format-list';
    	
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$formatTable = new Shared_Model_Data_OrderImportFormat();
		
		if (empty($id)) {
			throw new Zend_Exception('/shipment/format-detail id is empty');
		}
		
		$data = $formatTable->getById($id);
		$data['column_setting']  = unserialize($data['column_setting']);
		$data['convert_setting'] = unserialize($data['convert_setting']);
		$this->view->data = $data;
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/format-update-basic                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 注文取込フォーマット 基本情報 更新(Ajax)                   |
    +----------------------------------------------------------------------------*/
    public function formatUpdateBasicAction()
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
                
                if (!empty($errorMessage['name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'OK', 'message' => '「フォーマット名」を入力してください'));
                    return;
                }
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$formatTable = new Shared_Model_Data_OrderImportFormat();
				
				$formatTable->updateById($id, array(
					'name' => $success['name'],
				));

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    } 
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/format-update-column                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 注文取込フォーマット CSVデータ順更新(Ajax)                 |
    +----------------------------------------------------------------------------*/
    public function formatUpdateColumnAction()
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
                
			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
	            $order   = explode(',', $success['order']);
	            $columns = array();
	            foreach ($order as $columnKey) {
	                $columns[] = $request->getParam($columnKey . '_value');                
	            }				
				
				$formatTable = new Shared_Model_Data_OrderImportFormat();
				
				// 新規登録
				$formatTable->updateById($id, array(
					'column_setting'  => serialize($columns),
				));

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment/format-update-convert                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 注文取込フォーマット 値変換 更新(Ajax)                     |
    +----------------------------------------------------------------------------*/
    public function formatUpdateConvertAction()
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
                
			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
	            $order   = explode(',', $success['order']);
	            $columns = array();
	            foreach ($order as $columnKey) {
	            	$columns[$columnKey] = array(
	            		'target_column' => $request->getParam($columnKey . '_target_column'),
	                	'base'          => $request->getParam($columnKey . '_base'),
	                	'converted'     => $request->getParam($columnKey . '_converted'),
	                );           
	            }				
				
				$formatTable = new Shared_Model_Data_OrderImportFormat();
				
				// 新規登録
				$formatTable->updateById($id, array(
					'convert_setting'  => serialize($columns),
				));

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    } 
}

