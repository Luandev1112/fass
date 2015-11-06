<?php
/**
 * class Shared_Model_Data_CostDeliveryOverseas
 * 原単位・輸出物流
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_CostDeliveryOverseas extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_cost_delivery_overseas';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'display_id',                  // 表示ID XX＋西暦下二桁＋5桁
		'status',                      // ステータス
		
		'title',                       // 案件名
		
		'type',                        // 種別
		'type_other_text',             // 種別 その他テキスト
		
		'client_connection_id',        // 顧客 取引先ID
		'delivery_connection_id',      // 業者 取引先ID
		
		'target_item_ids',             // 対象商品ID(リスト)
		
        'export_country_id',           // 輸出国ID
		'export_place',                // 出荷地
		'export_airport',              // 輸出港・空港
		
		'import_country_id',           // 輸入国ID
		'import_place',                // 最終到着地
		'import_airport',              // 輸入港・空港
		
		'relational_supply_ids',       // 関連調達ID
				
        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    	'title',                       // 案件名
    	'type_other_text',             // 種別 その他テキスト
		'export_place',                // 出荷地
		'export_airport',              // 輸出港・空港
		
		'import_place',                // 最終到着地
		'import_airport',              // 輸入港・空港		
    );
    
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
    	$data = $selectObj->query()->fetch();
    	$data['target_item_ids']       = unserialize($data['target_item_ids']);
    	$data['relational_supply_ids'] = unserialize($data['relational_supply_ids']);
    	return $data;
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

