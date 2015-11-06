<?php
/**
 * class Shared_Model_Data_ItemStockConsumption
 * アイテム在庫消費
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemStockConsumption extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_stock_consumption';

    protected $_fields = array(
        'id',                    // ID
        
        'item_id',               // アイテムID     （廃止予定）
        'warehouse_item_id',     // 倉庫管理アイテムID
        
        'user_id',               // 担当者ID
		'status',                // ステータス
		
		'action_date',           // アクション日
		'action_code',           // アクションコード
		
		'sub_count',             // 消費数量
		
		'target_stock_id',       // 対象在庫ID
		
		'order_id',              // 注文ID（出荷のみ）
		'memo',                  // 備考（破棄理由など）
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
        
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    );
    
    /**
     * IDで取得
     * @param int $id
     * @return boolean
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 更新
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }

    /** 
     * 対象アイテムの履歴取得
     * @param  int      $warehouseItemId
     * @param  boolean  $isSelectObj
     * @return array or selectObj
     */
    public function getActiveList($warehouseItemId, $isSelectObj = false)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_item_stock_consumption.warehouse_item_id = ?', $warehouseItemId);
    	$selectObj->where('frs_item_stock_consumption.status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
        
        if ($isSelectObj) {
            return $selectObj;
        }
        
        return $selectObj->query()->fetchAll();
    }
    
    /** 
     * 日別出庫数
     * @param  int      $warehouseItemId
     * @param  boolean  $targetDate
     * @return array or $selectObj
     */
    public function getDailyCount($warehouseItemId, $targetDate)
    {
    	$selectObj = $this->select(array(new Zend_Db_Expr('SUM(`sub_count`) AS sub_count_total')));
    	$selectObj->where('frs_item_stock_consumption.warehouse_item_id = ?', $warehouseItemId);
    	$selectObj->where('frs_item_stock_consumption.status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
        $selectObj->where('DATE(frs_item_stock_consumption.action_date) = ?', $targetDate);
        $selectObj->where('frs_item_stock_consumption.action_code = ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT);
        $data = $selectObj->query()->fetch();
        
        //var_dump($targetDate);
        //var_dump($selectObj->__toString());exit;
        //var_dump($data);//exit;
        if (!empty($data)) {
            return $data['sub_count_total'];
        }
        return 0;
    }
    
    /** 
     * 期間件数
     * @param  int      $warehouseItemId
     * @param  string   $from
     * @param  string   $to
     * @return array or $selectObj
     */
    public function getTermCount($warehouseItemId, $from, $to)
    {
    	$selectObj = $this->select(array(new Zend_Db_Expr('SUM(`sub_count`) AS sub_count_total')));
    	$selectObj->where('frs_item_stock_consumption.warehouse_item_id = ?', $warehouseItemId);
    	$selectObj->where('frs_item_stock_consumption.status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
        $selectObj->where('frs_item_stock_consumption.action_date >= ?', $from);
        $selectObj->where('frs_item_stock_consumption.action_date <= ?', $to);
        $selectObj->where('frs_item_stock_consumption.action_code = ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT);
        
        $data = $selectObj->query()->fetch();

        if (!empty($data)) {
            return $data['sub_count_total'];
        }
        return 0;
    }
    
    /** 
     * 注文の在庫引当リスト
     * @param  boolean  $orderId
     * @return array or selectObj
     */
    public function getListByOrderId($orderId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('order_id = ?', $orderId);
    	$selectObj->where('status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
		$selectObj->order('id ASC');
        return $selectObj->query()->fetchAll();
    }
    
}

