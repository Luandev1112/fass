<?php
/**
 * class Shared_Model_Data_AccountTotalingGroupLayout
 * 会計採算コードレイアウト
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountTotalingGroupLayout extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_totaling_group_layout';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        
        'category_id',                         // カテゴリID
        'version_id',                          // バージョンID
        
        'unique_id',                           // キー
        'status',                              // ステータス
        'row_type',                            // コンテンツ種別
		'content',                             // 内容
		'calc_text',                           // 計算式
		'content_order',                       // 並び順

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'content',                             // 内容
		'calc_text',                           // 計算式
    );
    
    /**
     * 対象バージョンレイアウトを取得
     * @param int $managementGroupId
     * @param int $categoryId
     * @param int $versionId
     * @return array
     */
    public function getListByCategoryId($managementGroupId, $categoryId, $versionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('category_id = ?', $categoryId);
    	$selectObj->where('version_id = ?', $versionId);
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }
    
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
     * 次の並び順
     * @param int $managementGroupId
     * @param int $categoryId
     * @param int $id
     * @return array
     */
    public function getNextContentOrder($managementGroupId, $categoryId, $versionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('category_id = ?', $categoryId);
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
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

