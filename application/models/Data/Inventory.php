<?php
/**
 * class Shared_Model_Data_Inventory
 * 棚卸
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Inventory extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_inventory';

    protected $_fields = array(
        'id',                   // ID
        'management_group_id',  // 管理グループID
        'warehouse_id',         // 倉庫ID
        
        'status',               // ステータス
        
        'target_date',          // 実施日
        'stock_type',           // 在庫管理種別
        
        'inventory_type',       // 棚卸種別
        
        'approval_comment',     // 修正依頼コメント
        
		'created_user_id',      // 担当者
		'approval_user_id',     // 承認者
		
        'created',              // レコード作成日時
        'updated',              // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    );
    
    /**
     * 実施中の棚卸があるか
     * @param int $managementGroupId
     * @param int $warehouseId
     * @return boolean
     */
    public function hasGoing($managementGroupId, $warehouseId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('warehouse_id = ?', $warehouseId);
    	$selectObj->where('status != ?', Shared_Model_Code::INVENTORY_STATUS_APPROVED);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	
    	return false;
    }
    
    /**
     * 実施中の棚卸があるか(在庫資材種別ごと)
     * @param int $managementGroupId
     * @param int $warehouseId
     * @param int $stockType
     * @return boolean
     */
    public function hasGoingWithStockType($managementGroupId, $warehouseId, $stockType)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('warehouse_id = ?', $warehouseId);
    	$selectObj->where('stock_type = ?', $stockType);
    	$selectObj->where('status != ' . Shared_Model_Code::INVENTORY_STATUS_APPROVED . ' AND status != ' . Shared_Model_Code::INVENTORY_STATUS_DELETED);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	
    	return false;
    }
    
    /**
     * IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return boolean
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 更新
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

}

