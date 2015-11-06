<?php
/**
 * class Shared_Model_Data_CostPackage
 * 原価計算 原単位・梱包資材・作業費
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_CostPackage extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_cost_package';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'display_id',                  // 表示ID XX＋西暦下二桁＋5桁
		'status',                      // ステータス

		'title',                       // 名称
		'total',                       // 梱包資材・作業費 合計
		
		'package_cost',                // 梱包資材 合計
		'package_cost_list',           // 梱包資材項目リスト
		
		'operation_cost',              // 作業費 合計
		'operation_cost_list',         // 作業費項目リスト

		'memo',                        // メモ
		
		'created_user_id',             // 登録者ID
		'last_update_user_id',         // 更新者ID
		
        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時
    );
  
    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    	'title',                       // 名称
    	'package_cost',                // 梱包資材 合計
		'package_cost_list',           // 梱包資材項目リスト
		'operation_cost',              // 作業費 合計
		'operation_cost_list',         // 作業費項目リスト
		'memo',                        // メモ
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
    	$selectObj->where('id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	$data['package_cost_list']   = json_decode($data['package_cost_list'], true);
    	$data['operation_cost_list'] = json_decode($data['operation_cost_list'], true);
    	return $data;
    }

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	return $selectObj->query()->fetchAll();
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

