<?php
/**
 * class Shared_Model_Data_WarehouseItem
 * 倉庫管理アイテム
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_WarehouseItem extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_warehouse_item';

    protected $_fields = array(
        'id',                        // ID
        'management_group_id',       // 管理グループID
        'warehouse_id',              // 倉庫ID
        'status',                    // ステータス
        
        'stock_type',                // 在庫管理種別
        
        'target_type',               // 対象種別
		'target_item_id',            // 商品ID
		'target_supply_product_id',  // 調達管理-原料製品ID
		'target_supply_fixture_id',  // 調達管理-備品ID
		
		'stock_name',                // 資材名
		'shelf_no',                  // 棚番号(4文字以下)
		'image_key',                 // 画像キー
		'unit_price',                // 棚卸単価

		'stock_count',               // 在庫数                 (frs_warehouse_itemに移行)
		'useable_count',             // 引当可能在庫数         (frs_warehouse_itemに移行)
		
		'shipped_last_month',        // 直近1ヶ月出荷数
		'shipped_3_month_average',   // 直近3ヶ月平均出荷数
		
		'alert_count',               // アラート在庫数         (frs_warehouse_itemに移行)
		
		'minimum_base_month',        // 
		'minimum_count',             // 最低在庫数             (frs_warehouse_itemに移行)
		'safety_base_month',         // 
		'safety_count',              // 安全在庫数             (frs_warehouse_itemに移行)
		
		'unit_type',                 // 単位種別               (frs_warehouse_itemに移行)
		'use_dm',                    // DM便を使う
		
        'created',                   // レコード作成日時
        'updated',                   // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
	    'stock_name',                // 資材名
    );
    
    /**
     * リスト取得
     * @param int  $managementGroupId
     * @param int  $warehouseId
     * @param int  $type
     * @param BOOL $isSelectObj
     * @return boolean
     */
    public function getActiveList($managementGroupId, $warehouseId, $type, $isSelectObj)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('warehouse_id = ?', $warehouseId);
		$selectObj->where('status = ?', Shared_Model_Code::WAREHOUSE_STATUS_ACTIVE);
		
    	if (!empty($isSelectObj)) {
    		$selectObj->order('id ASC');
    		return $selectObj;
    	}
    	
    	return $selectObj->query()->fetchAll();
    }

    /**
     * IDで取得
     * @param int  $managementGroupId
     * @param int  $warehouseId 
     * @param int  $id
     * @return boolean
     */
    public function getById($managementGroupId, $warehouseId, $id)
    {
    	$selectObj = $this->select();
		$selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($this->aesdecrypt('item_name', false) . 'AS item_name', 'frs_item.display_id AS item_display_id'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($this->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name', 'frs_supply_product_project.display_id AS supply_product_display_id'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($this->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name', 'frs_supply_fixture_project.display_id AS supply_fixture_display_id'));


    	$selectObj->where('frs_warehouse_item.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_warehouse_item.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_warehouse_item.id = ?', $id);
    	return $selectObj->query()->fetch();
    }


    /**
     * 登録されているか(商品)
     * @param int  $managementGroupId
     * @param int  $warehouseId 
     * @param int  $itemId
     * @return boolean
     */
    public function itemIsExist($managementGroupId, $warehouseId, $itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_warehouse_item.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_warehouse_item.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_warehouse_item.target_item_id = ?', $itemId);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	return false;
    }

    /**
     * 登録されているか(原料製品)
     * @param int  $managementGroupId
     * @param int  $warehouseId 
     * @param int  $supplyProductId
     * @return boolean
     */
    public function supplyProductIsExist($managementGroupId, $warehouseId, $supplyProductId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_warehouse_item.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_warehouse_item.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_warehouse_item.target_supply_product_id = ?', $supplyProductId);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	return false;
    }

    /**
     * 登録されているか(原料製品)
     * @param int  $managementGroupId
     * @param int  $warehouseId 
     * @param int  $supplyFixtureId
     * @return boolean
     */
    public function supplyFixtureIsExist($managementGroupId, $warehouseId, $supplyFixtureId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_warehouse_item.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_warehouse_item.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_warehouse_item.target_supply_fixture_id = ?', $supplyFixtureId);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	return false;
    }
    
    

    /**
     * 在庫管理種別リストを取得
     * @param  int $stockType
     * @return array
     */
    public function getItemList($stockType)
    {
    	$selectObj = $this->select();
		$selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($this->aesdecrypt('item_name', false) . 'AS item_name', 'frs_item.display_id AS item_display_id'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($this->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name', 'frs_supply_product_project.display_id AS supply_product_display_id'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($this->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name', 'frs_supply_fixture_project.display_id AS supply_fixture_display_id'));

    	$selectObj->where('frs_warehouse_item.stock_type = ?', $stockType);
    	//$selectObj->where('frs_warehouse_item.status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
    	$selectObj->order('frs_warehouse_item.id ASC');
        return $selectObj->query()->fetchAll();
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


    /**
     * 在庫アラートアイテム取得
     * @param  int $stockType
     * @return array
     */
    public function getAlertItemWithType($stockType)
    {
    	$selectObj = $this->select();
        $selectObj->joinLeft('frs_item', 'frs_warehouse_item.target_item_id = frs_item.id', array($this->aesdecrypt('item_name', false) . 'AS item_name'));
        $selectObj->joinLeft('frs_supply_product_project', 'frs_warehouse_item.target_supply_product_id = frs_supply_product_project.id', array($this->aesdecrypt('frs_supply_product_project.title', false) . 'AS supply_product_name'));
        $selectObj->joinLeft('frs_supply_fixture_project', 'frs_warehouse_item.target_supply_fixture_id = frs_supply_fixture_project.id', array($this->aesdecrypt('frs_supply_fixture_project.title', false) . 'AS supply_fixture_name'));
		
    	$selectObj->where('frs_warehouse_item.status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
    	
    	if (!empty($itemType)) {
    		$selectObj->where('frs_warehouse_item.stock_type = ?', $stockType);
    	}
    	
    	$selectObj->where('frs_warehouse_item.stock_count < frs_warehouse_item.safety_count');
    	
    	return $selectObj->query()->fetchAll();
	}



    /**
     * 在庫追加
     * @param int $managementGroupId
     * @param int $warehouseId
     * @param int $id
     * @param float $addCount
     * @return array
     */
    public function addStock($managementGroupId, $warehouseId, $id, $addCount)
    {
    	$data = $this->getById($managementGroupId, $warehouseId, $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    	}
    	
    	$this->updateById($managementGroupId, $id, array(
    		'stock_count'   => (float)$data['stock_count']   + $addCount,
    		'useable_count' => (float)$data['useable_count'] + $addCount,
    	));
	}

    /**
     * 在庫消費
     * @param int $managementGroupId
     * @param int $warehouseId
     * @param int $id
     * @param float $subCount
     * @param none
     * @return array
     */
    public function subStock($managementGroupId, $warehouseId, $id, $subCount)
    {
    	$data = $this->getById($managementGroupId, $warehouseId, $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    		
    	} else if ((float)$data['stock_count'] < (float)$subCount/* || (float)$data['useable_count'] < (float)$subCount*/) {
    		throw new Zend_Exception('Shared_Model_Data_Item - stock_count is not enough');
    		
    	}
    	
    	$this->updateById($managementGroupId, $id, array(
    		'stock_count'   => (float)$data['stock_count']   - $subCount,
    		'useable_count' => (float)$data['useable_count'] - $subCount,
    	));
	}
	
    /**
     * 在庫消費
     * @param int $managementGroupId
     * @param int $warehouseId
     * @param int $id
     * @param float $subCount
     * @param none
     * @return array
     */
    public function subStockInventry($managementGroupId, $warehouseId, $id, $subCount)
    {
    	$data = $this->getById($managementGroupId, $warehouseId, $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    		
    	} else if ((float)$data['stock_count'] < (float)$subCount/* || (float)$data['useable_count'] < (float)$subCount*/) {
    		//throw new Zend_Exception('Shared_Model_Data_Item - stock_count is not enough');
    		
    		$this->updateById($managementGroupId, $id, array(
	    		'stock_count'   => 0,
	    		'useable_count' => 0,
	    	));
	    	
    	} else {
	    	$this->updateById($managementGroupId, $id, array(
	    		'stock_count'   => (float)$data['stock_count']   - $subCount,
	    		'useable_count' => (float)$data['useable_count'] - $subCount,
	    	));
    	}
	}	

    /**
     * 引当可能在庫数を戻す(増やす)
     * @param int $managementGroupId
     * @param int $warehouseId
     * @param int $id
     * @param int $addCount
     * @return array
     */
    public function addUseableCount($managementGroupId, $warehouseId, $id, $addCount)
    {
    	$data = $this->getById($managementGroupId, $warehouseId, $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    	}
    	
    	$this->updateById($managementGroupId, $id, array(
    		'useable_count' => (float)$data['useable_count'] + $addCount,
    	));
	}
	
    /**
     * 引当可能在庫数を減らす
     * @param int $managementGroupId
     * @param int $warehouseId
     * @param int $id
     * @param float $addCount
     * @return array
     */
    public function subUseableCount($managementGroupId, $warehouseId, $id, $subCount)
    {
    	$data = $this->getById($managementGroupId, $warehouseId, $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    	}
    	
    	$this->updateById($managementGroupId, $id, array(
    		'useable_count' => (float)$data['useable_count'] - $subCount,
    	));
	}

	
}


