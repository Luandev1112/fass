<?php
/**
 * class Shared_Model_Data_DirectOrderShipment
 * 出荷指示(EC以外の直接取引) (納入商品情報は受注管理を参照)
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_DirectOrderShipment extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_direct_order_shipment';

    protected $_fields = array(
        'id',                      // ID
        'management_group_id',     // 管理グループID
        'warehouse_id',            // 倉庫ID
        'type',                    // 種別
        
		'status',                  // ステータス
		
		'direct_order_id',         // 受注ID
		'direct_sample_id',        // サンプル出荷ID
		
		
		'target_connection_id',    // 発注元取引先ID
		'base_id',                 // 納入先拠点
		
		'inspection_datetime',     // 検品日時
		'inspection_user_id',      // 検品者ユーザーID
		
		'shipment_plan_date',      // 出荷予定日
		'shipment_datetime',       // 出荷日時
		'delivery_request_date',   // 到着希望日
		
		'delivery_method',         // 配送方法
		
		'shipment_memo',           // 伝達事項

		'created_user_id',         // 作成者ユーザーID
		'last_update_user_id',     // 最終更新者ユーザーID
		
        'created',                 // レコード作成日時
        'updated',                 // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'delivery_method',         // 配送方法
		'shipment_memo',          // メモ
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
    	$data = $selectObj->query()->fetch();
    	$data['items'] = json_decode($data['items'], true);
    	
    	return $data;
    }

    /**
     * 受注IDで取得
     * @param int $managementGroupId
     * @param int $directOrderId
     * @return array
     */
    public function getByDirectOrderId($managementGroupId, $directOrderId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('direct_order_id = ?', $directOrderId);
    	return $selectObj->query()->fetch();
    	
    }
    
    /**
     * 更新
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }


}

