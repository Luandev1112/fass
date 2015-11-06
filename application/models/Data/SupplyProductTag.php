<?php
/**
 * class Shared_Model_Data_SupplyProductTag
 * 調達管理 原料製品 タグ
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_SupplyProductTag extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_supply_product_tag';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
        'tag_name',                            // タグ名称
        'serach_words_list',                   // 検索ワードリスト
        'descripition',                        // 詳細

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'descripition',                        // 詳細
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
    	$data =  $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		$data['serach_words_list'] = unserialize($data['serach_words_list']);
    	}
    	
    	return $data;
    }

    /**
     * 一覧
     * @param int $managementGroupId
     * @return boolean
     */
    public function getList($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->order('id DESC');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 更新
     * @param int $managementGroupId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

}

