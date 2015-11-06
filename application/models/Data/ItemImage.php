<?php
/**
 * class Shared_Model_Data_ItemImage
 * アイテム画像
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemImage extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_image';

    protected $_fields = array(
        'id',                    // ID
        'management_group_id',   // 管理グループID
		'status',                // ステータス
		
		'item_id',               // アイテムID
        'file_name',             // ファイル名
        'summary',               // 概要
        
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'file_name',             // ファイル名
        'summary',               // 概要
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
    	$data = $selectObj->query()->fetch();
    	return $data;
    }

    /**
     * 更新
     * @param int   $managementGroupId
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

    /**
     * アイテム別リストを取得
     * @param  int  $managementGroupId
     * @param  int  $itemId
     * @return array
     */
    public function getListByItemId($managementGroupId, $itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->order('id ASC');
        return $selectObj->query()->fetchAll();
    }

}

