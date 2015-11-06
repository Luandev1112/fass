<?php
/**
 * class Shared_Model_Data_IncludingPlan
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_IncludingPlan extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_including_plan';

    protected $_fields = array(
        'id',                                 // ID
		'status',                             // ステータス
		
		'warehouse_id',                       // 倉庫ID
		'name',                               // 施策名
		
		'shelf_no',                           // 検品用棚番号
		
		'term_type',                          // 適用期間
		'start_date',                         // 開始日
		'end_date',                           // 終了日
		
		'condition_type',                     // 条件種別
		'condition_item_id',                  // 条件 対象商品ID
		'condition_item_ids',                 // 条件 対象商品ID (josn)
		
		'condition_subscription_start',       // 条件 定期開始回数
		'condition_subscription_end',         // 条件 定期終了回数
		'condition_subscription_intervals',   // 条件 定期間隔
		
		'including_items',                    // 同梱品リスト
		
        'created',                            // レコード作成日時
        'updated',                            // レコード更新日時  
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'name',                  // 施策名
    );
    
    /**
     * IDで取得
     * @param int $warehouseId
     * @param int $id
     * @return array
     */
    public function getById($warehouseId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_id = ?', $warehouseId);
    	$selectObj->where('id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		$data['condition_item_ids'] = json_decode($data['condition_item_ids'], true);
    		$data['including_items']    = json_decode($data['including_items'], true);
    	}
    	
    	return $data;
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

    /**
     * 有効施策リスト
     * @param int $warehouseId
     * @return array
     */
    public function getActivePlanList($warehouseId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_id = ?', $warehouseId);
    	$selectObj->where('status = ?', Shared_Model_Code::INCLUDING_PLAN_STATUS_ACTIVE);
    	return $selectObj->query()->fetchAll();
    }
	
}

