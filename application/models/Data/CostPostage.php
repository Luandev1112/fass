<?php
/**
 * class Shared_Model_Data_CostPostage
 * 原価計算 原単位・送料
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_CostPostage extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_cost_postage';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'display_id',                  // 表示ID XX＋西暦下二桁＋5桁
		'status',                      // ステータス

		'title',                       // 名称
		'description',                 // 内容
		'size',                        // 標準サイズ(cm)
		'country',                     // 国
		
		'supply_fixture_id',           // 使用輸送箱資材
		'supply_subcontracting_id',    // 調達管理ID
		'connection_id',               // 取引先

		'standard_price',              // 原価計算用一律送料
		'minimum_price',               // 地域別・送料実費の範囲 下限
		'max_price',                   // 地域別・送料実費の範囲 上限
		
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
		'description',                 // 内容
		'size',                        // 標準サイズ(cm)
		'country',                     // 国
		
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
    	return $selectObj->query()->fetch();
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

