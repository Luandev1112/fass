<?php
/**
 * class Shared_Model_Data_ManualChapter
 * マニュアル チャプター
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ManualChapter extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_manual_chapter';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'manual_id',                   // マニュアルID
        'status',                      // ステータス
        
		'chapter_name',                // チャプター名
		
		'content_order',               // 並び順

		'created_user_id',             // 初期登録者ユーザーID
		'last_update_user_id',         // 最終更新者ユーザーID
		
        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時

    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'chapter_name',                // チャプター名
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
     * 次の並び順
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getNextContentOrder($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
    }
    
    /**
     * 一覧
     * @param int $managementGroupId
     * @param int $manualId
     * @return boolean
     */
    public function getListWithManualId($managementGroupId, $manualId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('manual_id = ?', $manualId);
    	$selectObj->order('content_order ASC');
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

