<?php
/**
 * class Shared_Model_Data_ManualItem
 * マニュアルアイテム
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ManualItem extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_manual_item';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'manual_id',                   // マニュアルID
        'chapter_id',                  // チャプターID
        
        'title',                       // タイトル
		'status',                      // ステータス
		
		'content_type',                // コンテンツ種別
		'content',                     // コンテンツ
		
		'keyword_1',
		'keyword_2',
		'keyword_3',
		'keyword_4',
		'keyword_5',
		'keyword_6',
		
		'content_order',               // 並び順

		'created_user_id',             // 初期登録者ユーザーID
		'last_update_user_id',         // 最終更新者ユーザーID

		'content_updated',             // 内容最終更新日時

        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
	    'title',                       // タイトル
		'content',                     // コンテンツ
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
     * 次のアイテム
     * @param int $managementGroupId
     * @param int $chapterId
     * @return array
     */
    public function getNextOrderItem($managementGroupId, $chapterId, $currentOrder)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('chapter_id = ?', $chapterId);
    	$selectObj->where('content_order > ?', $currentOrder);
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetch();
    }

    /**
     * 前のアイテム
     * @param int $managementGroupId
     * @param int $chapterId
     * @return array
     */
    public function getPreOrderItem($managementGroupId, $chapterId, $currentOrder)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('chapter_id = ?', $chapterId);
    	$selectObj->where('content_order < ?', $currentOrder);
    	$selectObj->order('content_order DESC');
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 次の並び順
     * @param int $managementGroupId
     * @param int $chapterId
     * @return array
     */
    public function getNextContentOrder($managementGroupId, $chapterId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('chapter_id = ?', $chapterId);
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
     * @param int $chapterId
     * @return boolean
     */
    public function getListWithChapterId($managementGroupId, $chapterId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_user', 'frs_manual_item.last_update_user_id = frs_user.id', array($this->aesdecrypt('user_name', false) . 'AS user_name'));
    	
    	$selectObj->where('frs_manual_item.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_manual_item.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('frs_manual_item.chapter_id = ?', $chapterId);
    	$selectObj->order('frs_manual_item.content_order ASC');
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
    
    /**
     * updatePositionFrom
     * @param int $managementGroupId
     * @param int $chapterId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updatePositionFrom($managementGroupId, $chapterId, $position)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('chapter_id = ?', $chapterId);
    	$selectObj->where('content_order > ?', $position);
    	$items = $selectObj->query()->fetchAll();
    	
    	if (!empty($items)) {
	    	foreach ($items as $each) {
		    	$this->updateById($managementGroupId, $each['id'], array('content_order' => (int)$each['content_order'] + 1));
	    	}
    	}
    }

    /**
     * updateDownPositionFrom
     * @param int $managementGroupId
     * @param int $chapterId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateDownPositionFrom($managementGroupId, $chapterId, $position)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('chapter_id = ?', $chapterId);
    	$selectObj->where('content_order > ?', $position);
    	$items = $selectObj->query()->fetchAll();
    	
    	if (!empty($items)) {
	    	foreach ($items as $each) {
		    	$this->updateById($managementGroupId, $each['id'], array('content_order' => (int)$each['content_order'] - 1));
	    	}
    	}
    }   
}

