<?php
/**
 * class Shared_Model_Data_Order
 * 注文情報
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Order extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_order';

    protected $_fields = array(
        'id',                        // ID
        
		'status',                    // ステータス
		'delivery_status',           // 配送ステータス
		
		'order_datetime',            // 注文日時
		
		'statement_exported',        // 伝票出力済み
		'inspection_datetime',       // 検品日時
		'inspection_user_id',        // 検品者ユーザーID
		
		'shipment_plan_date',        // 出荷予定日
		'shipment_datetime',         // 出荷日時
		'shipment_error',
		
		'warehouse_id',              // 倉庫ID
		'import_key',                // 取り込みキー
		'relational_order_id',       // 受注番号(EC-Cube)
		
		'customer_id',               // 顧客番号
		'is_royal_customer',         // ロイヤルカスタマー
		
		'order_customer_name',       // 注文者氏名
		'order_customer_name_kana',  // 注文者氏名カナ
		'order_email',               // 注文者 メールアドレス
		'order_tel',                 // 注文者 電話番号
		'order_zipcode',             // 注文者 郵便番号
		'order_contry',              // 注文者 国
		'order_prefecture',          // 注文者 都道府県
		'order_address1',            // 注文者 住所1
		'order_address2',            // 注文者 住所2
		'order_sex',                 // 注文者 性別
		'order_birthday',            // 注文者 誕生日
		
		'discount',                  // 値引額
		'delivery_fee',              // 配送日
		'charge',                    // 手数料
		'tax',                       // 消費税
		'total',                     // 合計
		
		'payment_method',            // 支払い方法
		'delivery_method',           // 配送方法
		'delivery_code',             // 配送伝票番号
		
		'delivery_name',             // 配送先 氏名
		'delivery_name_kana',        // 配送先 氏名カナ
        'delivery_tel',              // 配送先 電話番号
        'delivery_zipcode',          // 配送先 郵便番号
        'delivery_contry',           // 配送先 国
        'delivery_prefecture',       // 配送先 都道府県
		'delivery_address1',         // 配送先 住所1
		'delivery_address2',         // 配送先 住所2
		
		'delivery_request_date',     // 配送希望日
		'delivery_request_time',     // 配送希望時間帯
		
		'message_to_customer_1',     // 顧客連絡事項1
		'message_to_customer_2',     // 顧客連絡事項2
		'message_to_customer_3',     // 顧客連絡事項3
		'message_to_customer_4',     // 顧客連絡事項4
		'message_to_customer_5',     // 顧客連絡事項5


        'order_from_site',                // 注文サイト
        'subscription_id',                // 定期ID
        'is_subscription_first_order',    // 定期初回注文
		'subscription_count',             // 定期回数
		
		'jaccs_transaction_id',      // ジャックストランザクションID
		'jaccs_with_package',        // ジャックス同梱
		
		'jaccs_invoice_data',        // ジャックス請求書印字データ
		'jaccs_error_data',          // ジャックス請求書取得エラー
		
		'order_count',               // 注文回数

		'message_inside',            // 自由記入欄（店舗内通信欄）
		'message_for_delivery',      // 自由記入欄（配送会社向け通信欄）
		'message_for_data',          // 数値データ用
		
        'created',                   // レコード作成日時
        'updated',                   // レコード更新日時
        
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'shipment_error',
        
		'order_customer_name',      // 注文者氏名
		'order_customer_name_kana', // 注文者氏名カナ
		'order_email',           // 注文者 メールアドレス
		'order_tel',             // 注文者 電話番号
		'order_zipcode',         // 注文者 郵便番号
		'order_contry',          // 注文者 国
		'order_prefecture',      // 注文者 都道府県
		'order_address1',        // 注文者 住所1
		'order_address2',        // 注文者 住所2
		'order_sex',             // 注文者 性別
		'order_birthday',        // 注文者 誕生日
		
		'payment_method',        // 支払い方法
		'delivery_method',       // 配送方法
		
		'delivery_name',             // 配送先 氏名
		'delivery_name_kana',        // 配送先 氏名カナ
        'delivery_tel',              // 配送先 電話番号
        'delivery_zipcode',          // 配送先 郵便番号
        'delivery_contry',           // 配送先 国
        'delivery_prefecture',       // 配送先 都道府県
		'delivery_address1',         // 配送先 住所1
		'delivery_address2',         // 配送先 住所2

		'delivery_request_date',     // 配送希望日
		'delivery_request_time',     // 配送希望時間帯
			
		'message_to_customer_1',     // 顧客連絡事項1
		'message_to_customer_2',     // 顧客連絡事項2
		'message_to_customer_3',     // 顧客連絡事項3
		'message_to_customer_4',     // 顧客連絡事項4
		'message_to_customer_5',     // 顧客連絡事項5
		
		'jaccs_invoice_data',        // ジャックス請求書印字データ
		'jaccs_error_data',          // ジャックス請求書取得エラー
    );
    
    /**
     * 検品対象リスト取得(検品者振り分け用)
	 * @param int $warehouseId
     * @return array
     */
    public function getListForInspection($warehouseId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_order_item', 'frs_order.id = frs_order_item.order_id AND frs_order_item.branch_no = 1', array('product_code'));
        $selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
        $selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_NEW);
		$selectObj->where('frs_order.shipment_plan_date <= ?', date('Y-m-d'));

		$selectObj->order('product_code ASC');
		$selectObj->order('id ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 検品者リスト取得(検品アサインされているユーザーのみ)
     * @param int $warehouseId
     * @return array
     */
    public function getUserListForInspection($warehouseId)
    {
    	$selectObj = $this->select(array('inspection_user_id'));
    	$selectObj->group('inspection_user_id');
    	$selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array($this->aesdecrypt('user_name', false) . 'AS user_name'));
        $selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);
		$selectObj->where('frs_order.shipment_plan_date <= ?', date('Y-m-d'));
		$selectObj->order('frs_user.id ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 検品者別検品リスト取得(伝票出力用)
     * @param int $warehouseId
     * @param int $userId
     * @return array
     */
    public function getListForInspectionUserNotExported($warehouseId, $userId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_order_item', 'frs_order.id = frs_order_item.order_id AND frs_order_item.branch_no = 1', array('product_code'));
        $selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);
		$selectObj->where('frs_order.shipment_plan_date <= ?', date('Y-m-d'));
		$selectObj->where('inspection_user_id = ?', $userId);
		//$selectObj->where('statement_exported = 0');
		$selectObj->order('product_code ASC');
		$selectObj->order('id ASC');
		
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 検品者別検品リスト取得(アプリ用)
     * @param int $warehouseId
     * @param int $userId
     * @return array
     */
    public function getListForInspectionUser($warehouseId, $userId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_order_item', 'frs_order.id = frs_order_item.order_id AND frs_order_item.branch_no = 1', array('product_code'));
        $selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
        $selectObj->where('frs_order.status <= ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);
		$selectObj->where('frs_order.shipment_plan_date <= ?', date('Y-m-d'));
		$selectObj->where('inspection_user_id = ?', $userId);
		$selectObj->order('product_code ASC');
		$selectObj->order('id ASC');
		
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 検品履歴日付リスト取得
     * @param int $warehouseId
     * @param int $userId
     * @return array
     */
    public function getSelectObjOfInspectionHistory($warehouseId, $userId)
    {
    	$selectObj = $this->select(array(new Zend_Db_Expr('COUNT(`id`) AS order_count'), new Zend_Db_Expr('DATE_FORMAT(`inspection_datetime`, \'%Y-%m-%d\') AS inspection_date')));
		$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
		
		if (!empty($userId)) {
			$selectObj->where('inspection_user_id = ?', $userId);
		}
		$selectObj->where('frs_order.status != ?', Shared_Model_Code::SHIPMENT_STATUS_DELETED);
		
		$selectObj->group('DATE_FORMAT(`inspection_datetime`, \'%Y-%m-%d\')');
		$selectObj->order('DATE_FORMAT(`inspection_datetime`, \'%Y-%m-%d\') DESC');
		
    	return $selectObj;
    }

    /**
     * 指定した日付の検品済みリスト取得
     * @param int $warehouseId
     * @param int $userId
     * @param string $inspectionDate
     * @return array
     */
    public function getInspectionHistoryOfDate($warehouseId, $userId, $inspectionDate)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);

		if (!empty($userId)) {
			$selectObj->where('inspection_user_id = ?', $userId);
		}
		$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_DELETED);
		
		$selectObj->where('DATE_FORMAT(`inspection_datetime`, \'%Y-%m-%d\') = ?', $inspectionDate);
		$selectObj->order('id ASC');

    	return $selectObj;
    }



    /**
     * 月間 注文件数取得
     * @param int $warehouseId
     * @return array
     */
    public function getOrderCountWithTerm($warehouseId, $termFrom, $termTo)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_SHIPPED);
    	$selectObj->where('frs_order.shipment_datetime >= ?', $termFrom . ' 00:00:00');
    	$selectObj->where('frs_order.shipment_datetime <= ?', $termTo . ' 23:59:59');

        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    /**
     * 月間 注文金額取得
     * @param int $warehouseId
     * @return array
     */
    public function getOrderPriceWithTerm($warehouseId, $termFrom, $termTo)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('SUM(`total`) as order_total')
    	));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_SHIPPED);
    	$selectObj->where('frs_order.shipment_datetime >= ?', $termFrom . ' 00:00:00');
    	$selectObj->where('frs_order.shipment_datetime <= ?', $termTo . ' 23:59:59');
    	
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['order_total'];
        }
        return 0;
    } 
    

    /**
     * 新規注文 件数取得
     * @param int $warehouseId
     * @return array
     */
    public function getNewCount($warehouseId)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
		$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_NEW);

        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    /**
     * 検品済み 件数取得
     * @param int $warehouseId
     * @return array
     */
    public function getInspectedCount($warehouseId)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
		$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_INSPECTED);

        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    /**
     * 保留 件数取得
     * @param int $warehouseId
     * @return array
     */
    public function getHoldedCount($warehouseId)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
		$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_HOLDED);
		
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    /**
     * 本日発送済み 件数取得
     * @param int $warehouseId
     * @param string $shippedDate
     * @return array
     */
    public function getShippedTodayCount($warehouseId, $shippedDate)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
		$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_SHIPPED);
		$selectObj->where('DATE_FORMAT(`shipment_datetime`, \'%Y-%m-%d\') = ?', $shippedDate);
		
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    } 



    /**
     * IDで取得
     * @param int $warehouseId
     * @param int $id
     * @return array
     */
    public function getById($warehouseId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array($this->aesdecrypt('user_name', false) . 'AS inspection_user_name'));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
    	//$selectObj->where('frs_order.status != ?', Shared_Model_Code::SHIPMENT_STATUS_DELETED);
    	$selectObj->where('frs_order.id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 注文IDで取得
     * @param int $warehouseId
     * @param int   $orderId
     * @return array
     */
    public function getByOrderId($warehouseId, $orderId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_user', 'frs_order.inspection_user_id = frs_user.id', array($this->aesdecrypt('user_name', false) . 'AS inspection_user_name'));
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_order.relational_order_id = ?', $orderId);
    	$selectObj->where('frs_order.status != ?', Shared_Model_Code::SHIPMENT_STATUS_DELETED);
    	return $selectObj->query()->fetch();
    }
    
    
    /**
     * 更新
     * @param int $warehouseId
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($warehouseId, $id, $columns)
    {
		return $this->update($columns, array('warehouse_id' => $warehouseId, 'id' => $id));
    }
}

