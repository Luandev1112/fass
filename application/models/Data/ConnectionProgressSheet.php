<?php
/**
 * class Shared_Model_Data_ConnectionProgressSheet
 * シート
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionProgressSheet extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection_progress_sheet';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'status',                      // ステータス
        
		'sheet_name',                  // シート名
		
		'content_order',               // 並び順
		
        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時

    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'sheet_name',                          // シート名
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
     * 項目名が登録されているか？
     * @param int    $managementGroupId
     * @param string $sheetName
     * @param int    $exceptId
     * @return array
     */
    public function isExistSheetName($managementGroupId, $sheetName, $exceptId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	
    	$dbAdapter = $this->getAdapter();
    	$titleWhere = $dbAdapter->quoteInto($this->aesdecrypt('sheet_name', false) . ' = ?', $sheetName);
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

