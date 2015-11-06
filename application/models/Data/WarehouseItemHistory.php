<?php
/**
 * class Shared_Model_Data_WarehouseItemHistory
 * 倉庫管理アイテム履歴
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_WarehouseItemHistory extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_warehouse_item_history';

    protected $_fields = array(
        'id',                        // ID
        'management_group_id',       // 管理グループID
        'warehouse_id',              // 倉庫ID
        'warehouse_item_id',         // 倉庫アイテムID
        
        'target_date',               // 対象日

		'stock_count',               // 在庫数
		'useable_count',             // 引当可能在庫数
		
		'safety_base_month',         // 注意基準期間
		'minimum_base_month',        // 警告基準期間
		'safety_count',              // 注意在庫数
		'minimum_count',             // 警告在庫数
		
        'created',                   // レコード作成日時
        'updated',                   // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    );
    
    /**
     * 対象倉庫アイテムの対象日の在庫数
     * @param  int    $warehouseItemId
     * @param  string $targetDate
     * @return array
     */
    public function getCountOfDate($warehouseItemId, $targetDate)
    {
    	$selectObj = $this->select();

    	$selectObj->where('warehouse_item_id = ?', $warehouseItemId);
    	$selectObj->where('target_date = ?', $targetDate);
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
	        return $data['stock_count'];
        }
        return 0;
    }
    
    /**
     * 期間最大在庫数
     * @param  int    $warehouseItemId
     * @param  string $from
     * @param  string $to
     * @return array
     */
    public function getMaxWithTerm($warehouseItemId, $from, $to)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_item_id = ?', $warehouseItemId);
    	$selectObj->where('target_date >= ?', $from);
    	$selectObj->where('target_date <= ?', $to);
    	$selectObj->order('stock_count DESC');
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
	        return $data['stock_count'];
        }
        return 0;
    }
    
    /**
     * 期間最小在庫数
     * @param  int    $warehouseItemId
     * @param  string $from
     * @param  string $to
     * @return array
     */
    public function getMinWithTerm($warehouseItemId, $from, $to)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_item_id = ?', $warehouseItemId);
    	$selectObj->where('target_date >= ?', $from);
    	$selectObj->where('target_date <= ?', $to);
    	$selectObj->order('stock_count ASC');
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
	        return $data['stock_count'];
        }
        return 0;
    }


    /**
     * 更新
     * @param int   $managementGroupId
     * @param int   $warehouseId
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

}


