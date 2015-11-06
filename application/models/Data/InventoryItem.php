<?php
/**
 * class Shared_Model_Data_InventoryItem
 * 棚卸リストアイテム
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_InventoryItem extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_inventory_item';

    protected $_fields = array(
        'id',                 // ID
        'inventory_id',       // 棚卸ID
        'warehouse_item_id',  // 倉庫資材ID
        
        'unit_price',         // 棚卸単価
        
        'theory_stock',       // 理論在庫
		'input_amount',       // 入力値
		
		'memo',               // 備考
		
        'created',            // レコード作成日時
        'updated',            // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
	    'memo',               // 備考
    );

    /**
     * 棚卸IDで取得
     * @param int $inventoryId
     * @return boolean
     */
    public function getListByInventoryId($inventoryId)
    {
    	$selectObj = $this->select();
    	
    	$selectObj->joinLeft('frs_warehouse_item', 'frs_inventory_item.warehouse_item_id = frs_warehouse_item.id', array('target_type', $this->aesdecrypt('frs_warehouse_item.stock_name', false) . ' AS stock_name', 'unit_type', 'image_key'));
    	
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($this->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($this->aesdecrypt('frs_supply_product_project.title', false) . ' AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($this->aesdecrypt('frs_supply_fixture_project.title', false) . ' AS supply_fixture_name'));


    	$selectObj->where('frs_inventory_item.inventory_id = ?', $inventoryId);
    	
    	$selectObj->order('frs_inventory_item.id ASC');

    	return $selectObj->query()->fetchAll();
    }
        
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

}

