<?php
/**
 * class Shared_Model_Data_AccountTotalingGroup
 * 会計採算コード
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountTotalingGroup extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_totaling_group';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
        'category_id',                         // カテゴリID
		'title',                               // 項目名
		'memo',                                // 説明
		'content_order',                       // 並び順

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',                               // 項目名
		'memo',                                // 説明
    );

    /**
     * 全リスト取得
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getAllList($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_totaling_group_category', 'frs_account_totaling_group.category_id = frs_account_totaling_group_category.id', array($this->aesdecrypt('category_name', false) . 'AS category_name',));
    	$selectObj->where('frs_account_totaling_group.management_group_id = ?', $managementGroupId);
    	$selectObj->order('frs_account_totaling_group_category.content_order ASC');
    	$selectObj->order('frs_account_totaling_group.content_order ASC');
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
    	$selectObj->joinLeft('frs_account_totaling_group_category', 'frs_account_totaling_group.category_id = frs_account_totaling_group_category.id', array($this->aesdecrypt('category_name', false) . 'AS category_name',));
    	$selectObj->where('frs_account_totaling_group.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_totaling_group.id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 項目名が登録されているか？
     * @param int    $managementGroupId
     * @param int    $categoryId
     * @param string $title
     * @param int    $exceptId
     * @return array
     */
    public function isExistTitle($managementGroupId, $categoryId, $title, $exceptId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('category_id = ?', $categoryId);
    	
    	$dbAdapter = $this->getAdapter();
    	$titleWhere = $dbAdapter->quoteInto($this->aesdecrypt('title', false) . ' = ?', $title);
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
     * @param int $categoryId
     * @param int $id
     * @return array
     */
    public function getNextContentOrder($managementGroupId, $categoryId)
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
     * 一覧
     * @param int $managementGroupId
     * @param int $categoryId
     * @return boolean
     */
    public function getListByCategoryId($managementGroupId, $categoryId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('category_id = ?', $categoryId);
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

