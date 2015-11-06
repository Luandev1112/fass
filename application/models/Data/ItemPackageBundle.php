<?php
/**
 * class Shared_Model_Data_ItemProductCodeBundle
 * 商品パッケージ付属品
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemPackageBundle extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_package_bundle';

    protected $_fields = array(
        'id',                    // ID
        'status',
        'item_package_id',       // 対象パッケージID

		'bundle_item_id',        // 付属品アイテムID
		'bundle_item_amount',    // 付属品数量
  
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
     * product_codeに紐づく同梱物リスト
     * @param int $itemPackageId
     * @return boolean
     */
    public function getBundleItemsByPackageId($itemPackageId)
    {
    	$selectObj = $this->select();
    	
    	$selectObj->joinLeft('frs_warehouse_item', 'frs_item_package_bundle.bundle_item_id = frs_warehouse_item.id', array('id AS warehouse_item_id', 'target_type', 'shelf_no', 'image_key'));
    	
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($this->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($this->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($this->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
    	/*
    	$selectObj->joinLeft('frs_item', 'frs_item_package_bundle.bundle_item_id = frs_item.id', array(
    		$this->aesdecrypt('item_name', false) . 'AS item_name',
    		$this->aesdecrypt('image_file_name', false) . 'AS image_file_name',
    		'item_type',
    		'item_type_id',
    		'jan_code',
    	));
    	*/
    	
    	$selectObj->joinLeft('frs_item_base', 'frs_item_base.item_id = frs_item.id AND frs_item_base.base_id = ' . '1', array('shelf_no'));
    	$selectObj->where('item_package_id = ?', $itemPackageId);
    	$selectObj->where('frs_item_package_bundle.status = ?', Shared_Model_Code::ITEM_CODE_BUNDLE_STATUS_ACTIVE);
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 登録されているか
     * @param int   $categoryId
     * @return array
     */
    public function isExist($id)
    {
        $selectObj = $this->select();
        $selectObj->where('id = ?', $id);
        $selectObj->where('status = ?', Shared_Model_Code::ITEM_CODE_STATUS_ACTIVE);
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
        	return true;
        }
        
        return false;
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

