<?php
/**
 * class Shared_Model_Data_ItemPrice
 * 商品価格設定
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemPrice extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_price_gs';

    protected $_fields = array(
        'id',                    // ID
        'management_group_id',   // 管理グループID
        'item_id',               // 商品ID
		'status',                // ステータス
		
		'branch_id',             // 商品枝番号
		
		'title',                 // ロット名称
		'lot',                   // ロット単位
		'lot_unit_name',         // ロット単位名称
		
		'sales_price',           // 上代価格(税抜)
		'unit_price',            // 卸価格単価(税抜)
		
		'estimate_item_name',        // 商品名(個別見積用)
		'estimate_sales_price_type', // 上代価格種類(個別見積用)
		'estimate_tax_rate',         // 消費税率(個別見積用)
		
		'setting_shipping_id',   // 送料設定ID
		
		'stock_count',           // 在庫数
		'stock_count_free',      // 在庫数を指定しない
		
		
		'display_order',         // 並び順
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',                 // ロット名称
		'sales_price',           // 上代価格(税抜)
		'unit_price',            // 卸価格単価(税抜)
    );
 
    /**
     * IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }


    /**
     * 枝番号で取得
     * @param int $managementGroupId
     * @param int $branchId
     * @param int $esceptId
     * @return array
     */
    public function getByBranchId($managementGroupId, $branchId, $esceptId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('branch_id = ?', $branchId);
    	
    	if (!empty($esceptId)) {
	    	$selectObj->where('id != ?', $esceptId);
    	}
    	
    	return $selectObj->query()->fetch();
    } 
    

    /**
     * 次のアイテム
     * @param int $managementGroupId
     * @param int $itemId
     * @param int $currentOrder
     * @return array
     */
    public function getNextOrderItem($managementGroupId, $itemId, $currentOrder)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('display_order > ?', $currentOrder);
    	$selectObj->order('display_order ASC');
    	
    	return $selectObj->query()->fetch();
    }

    /**
     * 前のアイテム
     * @param int $managementGroupId
     * @param int $itemId
     * @param int $currentOrder
     * @return array
     */
    public function getPreOrderItem($managementGroupId, $itemId, $currentOrder)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('display_order < ?', $currentOrder);
    	$selectObj->order('display_order DESC');
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 商品IDで卸標準価格リスト取得
     * @param int $managementGroupId
     * @param int $itemId
     * @return array
     */
    public function getDefaultActiveListByItemId($managementGroupId, $itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status != ?', Shared_Model_Code::CONTENT_STATUS_INACTIVE);
    	$selectObj->order('display_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 次の並び順
     * @param int $managementGroupId
     * @param int $itemId
     * @param int $estimateId
     * @return int
     */
    public function getNextOrderWithItemId($managementGroupId, $itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->order('display_order DESC');
    	
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['display_order'] + 1;
    	}
    	
    	return 1;	
    }

    /**
     * 更新
     * @param int   $managementGroupId
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }
}

