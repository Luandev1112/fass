<?php
/**
 * class Shared_Model_Data_Material
 * 資料
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Material extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_material';

    protected $_fields = array(
        'id',                    // ID
        'management_group_id',   // 管理グループID
        'item_type',             // アイテム種別
        'type',                  // 種別
        'product_id',
        'supply_id',
		'status',                // ステータス

		'kind',                  // 種別
		
		'title',                 // 資料名
		'explanation',           // 資料説明及び注意事項
		
		'not_for_shared',        // 配布禁止
		
		'file_type',             // ファイル種類
		'file_size',             // ファイルサイズ
		'file_name',             // 保存ファイル名
		'default_file_name',     // 初期ファイル名
		
		'display_order',         // 並び順
		
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
     * @param int   $id
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
     * 対象商品・調達の資料リスト(削除も含む)
     * @param int   $itemType
     * @param int   $itemId
     * @param int   $type
     * @param int   $materialKind
     * @return array
     */
    public function getList($itemType, $itemId, $type, $materialKind)
    {
	    $selectObj = $this->select();
	    $selectObj->where('item_type = ?', $itemType);
	    $selectObj->where('type = ?', $type);
	    
	    if ($itemType === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_PRODUCT) {
	    	$selectObj->where('product_id = ?', $itemId);
	    	$selectObj->where('type = ?', $type);
	    	$selectObj->order('display_order ASC');
	    } else {
		    $selectObj->where('item_type = ?', $itemType);
	    	$selectObj->where('supply_id = ?', $itemId);
	    }
	    
	    if (!empty($materialKind)) {
		    $selectObj->where('kind = ?', $materialKind);
	    }
    	
    	$selectObj->order('display_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 対象商品の公開資料リスト(削除以外)
     * @param int   $itemId
     * @return array
     */
    public function getActiveListByItemId($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status != ?', Shared_Model_Code::MATERIAL_STATUS_DELETED);
    	$selectObj->order('display_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 対象商品の公開資料リスト(削除以外) バイヤー共有状況を含む
     * @param int   $supplierId
     * @param int   $itemId
     * @param int   $buyerId
     * @return array
     */
    public function getActiveListByItemIdForBuyer($itemId, $buyerId)
    {
	    $dbAdapter = $this->getAdapter();
	    $buyerCondition = $dbAdapter->quoteInto('gs_item_material_shared.buyer_id = ?', $buyerId);
	    
    	$selectObj = $this->select();
    	$selectObj->joinLeft('gs_item_material_shared', 'gs_item_material.id = gs_item_material_shared.material_id AND ' . $buyerCondition, array('shared_status'));
    	$selectObj->where('gs_item_material.item_id = ?', $itemId);
    	$selectObj->where('gs_item_material.status != ?', Shared_Model_Code::MATERIAL_STATUS_DELETED);
    	$selectObj->order('gs_item_material.display_order ASC');
    	//var_dump($selectObj->__toString());exit;
    	return $selectObj->query()->fetchAll();
    }
    
    
    
    /**
     * 対象商品の限定開示資料リスト(削除以外)
     * @param int   $itemId
     * @return array
     */
    public function getActiveListByItemIdForPrivate($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status != ?', Shared_Model_Code::MATERIAL_STATUS_DELETED);
    	$selectObj->order('display_order ASC');
    	return $selectObj->query()->fetchAll();
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

    /**
     * 次の並び順
     * @param $itemId
     * @return int
     */
    public function getNextOrder($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status = ?', Shared_Model_Code::ITEM_MATERIAL_STATUS_AVAILABLE);
    	$selectObj->order('display_order DESC');
    	
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['display_order'] + 1;
    	}
    	
    	return 1;	
    }
    

}

