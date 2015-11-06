<?php
/**
 * class Shared_Model_Data_Manual
 * マニュアル
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Manual extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_manual';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'status',                      // ステータス
        
        'manual_category_id',          // マニュアルカテゴリID
        'manager_user_id',             // 管理責任者
        
        'confidentiality',             // 機密度
        
		'title',                       // マニュアル名
		'memo',                        // 備考
		
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
		'title',                       // マニュアル名
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
     * 存在するマニュアル名か？
     * @param int    $managementGroupId
     * @param string $manualName
     * @param int    $exceptId
     * @return array
     */
    public function isExistManualName($managementGroupId, $manualName, $exceptId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	
    	$dbAdapter = $this->getAdapter();
    	$titleWhere = $dbAdapter->quoteInto($this->aesdecrypt('title', false) . ' = ?', $sheetName);
    	$selectObj->where($titleWhere);
    	
    	if (!empty($exceptId)) {
    		$selectObj->where('id != ?', $exceptId);
    	}
    	
    	$data = $selectObj->query()->fetch();
		
		if (!empty($data)) {
			return true;
		}
		
		return false;
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
     * @return boolean
     */
    public function getList($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
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

