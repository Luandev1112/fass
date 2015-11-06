<?php
/**
 * class Cli_ShipmentController
 *
 */
class Cli_ShipmentController extends Cli_Model_Controller
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
     * importAction
     * cmd - php cli.php -p /shipment/import (import_key) (format_id)
     * 在庫が不足の場合は保留にする
     */
    public function importAction()
    {
        $request = $this->getRequest();
        $importKey = $request->getParam(0);// 第1引数 import key
        $formatId  = $request->getParam(1);// 第2引数 format id

		$formatTable = new Shared_Model_Data_OrderImportFormat();
		$format = $formatTable->getById($formatId);
		$format['column_setting']  = unserialize($format['column_setting']);
		$format['convert_setting'] = unserialize($format['convert_setting']);

        $csvFilePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($importKey . '.csv');;

        if (file_exists($csvFilePath)) {            
            $dataCount = 0;
            
            $errors = array();
            $data = array();
            
            $handle = fopen($csvFilePath, "r");
            
            // 説明行
            $csvRow = fgetcsv($handle, 0, ","); 
            
            // 注文データの登録
            try {
                $rowCount = 1;
                
                while (($csvRow = fgetcsv($handle, 0, ",")) !== FALSE) {                
                	$result = $this->importOrder($format, $importKey, $formatId, $csvRow);
                    echo 'row: ' . $rowCount++ . "\n"; 
                }
            
            } catch (Exception $e) {
                $data[0]['error'] = $e;
            }
            
            // 同梱品の確認
            
            
            
            
            
            

        } else {
            echo 'NO FILE' . "\n";
            exit;
        }
        
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
    private function importOrder($format, $importKey, $formatId, $csvRow)
    {
    	$orderTable       = new Shared_Model_Data_Order();
    	$orderItemTable   = new Shared_Model_Data_OrderItem();
    	$productCodeTable = new Shared_Model_Data_ItemProductCode();
    	$bundleTable      = new Shared_Model_Data_ItemProductCodeBundle();
    	$logTable         = new Shared_Model_Data_OrderImportLog();
    	$cosumptionTable  = new Shared_Model_Data_ItemStockConsumption();
    	
		$data = array();
		
		$importColumnList = Shared_Model_Code::codes('order_import_column');
		
		// 初期化
		foreach ($importColumnList as $defaultKey => $eachDefault) {
			$data[$defaultKey] = '';
		}
		
		// 各列データ取り込み
		foreach ($format['column_setting'] as $key => $each) {
			$data[$each] = $csvRow[$key];
		}
		//var_dump('default delivery_method:' . $data['delivery_method']);
		

		if (empty($data['status'])) {
			$data['status'] = Shared_Model_Code::SHIPMENT_STATUS_NEW;
		}
		
		if (empty($data['order_contry'])) {
			$data['order_contry'] = '日本';
		}

		if (empty($data['delivery_contry'])) {
			$data['delivery_contry'] = '日本';
		}
		
		// 値変換
		foreach ($format['convert_setting'] as $eachConvert) {
			foreach ($data as $dataKey => &$dataVal) {
				if ($eachConvert['target_column'] == $dataKey && $dataVal == $eachConvert['base']) {
					$data[$dataKey] = $eachConvert['converted'];
				}
			}
		}

		$orderData = $orderTable->getByOrderId($data['relational_order_id']);
		
		try {
	    	if (empty($orderData)) {
	    		// 配送予定日
	    		$shipmentPlanDate = date('Y-m-d');
	    		$deliveryMethod = $csvRow[18];

		    	$orderTable->create(array(
					'status'                    => Shared_Model_Code::SHIPMENT_STATUS_NEW,
					'delivery_status'           => 0,
					
					'order_datetime'            => $data['order_datetime'],
					
					'inspection_datetime'       => NULL,
					'inspection_user_id'        => 0,
					
					'shipment_plan_date'        => $shipmentPlanDate, // 計算する
					'shipment_datetime'         => NULL,
					
					'import_key'                => $importKey,
					'relational_order_id'       => $data['relational_order_id'],
					
					'customer_id'               => $data['customer_id'],
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
	
					'delivery_request_date'     => $data['delivery_request_date'],
					'delivery_request_time'     => $data['delivery_request_time'],
									
					'message_to_customer_1'     => $data['message_to_customer_1'],
					'message_to_customer_2'     => $data['message_to_customer_2'],
					'message_to_customer_3'     => $data['message_to_customer_3'],
					'message_to_customer_4'     => $data['message_to_customer_4'],
					'message_to_customer_5'     => $data['message_to_customer_5'],
					
					'subscription_count'        => $data['subscription_count'],
					
		            'created'                   => new Zend_Db_Expr('now()'),
		            'updated'                   => new Zend_Db_Expr('now()'),
		    	));
		
				$orderId = $orderTable->getLastInsertedId('id');
			} else {
				$orderId = $orderData['id'];
			}
			
			
			// 商品の追加
	    	$orderItemTable->create(array(
				'order_id'       => $orderId,
				
				'product_code'   => $data['product_code'],
				'product_name'   => $data['product_name'],
				
				'unit_price'     => $data['unit_price'],
				'amount'         => $data['amount'],
		        
	            'created'         => new Zend_Db_Expr('now()'),
	            'updated'         => new Zend_Db_Expr('now()'),
	    	));

	    	// -----------------------------------------------------
	    	//
	        // 在庫引き当て
	        //
	        // -----------------------------------------------------
	        $orderItems = $orderItemTable->getListByOrderId($orderId);
	        
			// 商品と付属品
			foreach ($orderItems as $eachOrderItem) {
			
				// 商品コードから対象の商品を取得
				$productCodeData = $productCodeTable->getByProductCode($eachOrderItem['product_code']);
				
				/* 未対応
				$consumptionTable->create(array(
			        'item_id'      => $productCodeData['item_id'],
			        'user_id'      => 0,
					'status'       => Shared_Model_Code::STOCK_STATUS_ACTIVE,
					
					'action_date'  => $actionTimeString,           // アクション日
					'action_code'  => $success['consumption_action'],
					
					'sub_count'       => $productCodeData['item_count'],
					'target_stock_id' => $stockId,// 対象の在庫
					
					'order_id'     => 0,
					'memo'         => '',
	
	                'created'      => new Zend_Db_Expr('now()'),
	                'updated'      => new Zend_Db_Expr('now()'),
				));
				*/

			}






	    	
        } catch (Exception $e) {
            $logTable->addLog($importKey, $formatId, 0);
            return false;
        }   
    	
    	$logTable->addLog($importKey, $formatId, 1);
    	return true;
    }

}
