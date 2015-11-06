<?php
/**
 * class Shared_Model_Data_ItemDocumentKind
 * ドキュメント種別
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemDocumentKind extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_item_document_kind';
    
    protected $_fields = array(
        'id',                    // ID
        'name',                  // 種類名
		'content_order',         // ステータス

        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    );
    
    /**
     * IDで取得
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	return $data;
    }

    /**
     * 更新
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }

    /**
     * リスト取得
     * @param  int $managementGroupId
     * @param  int $itemId
     * @param  int $docType
     * @return array
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order ASC');
        $kindList = $selectObj->query()->fetchAll();
        
        $items = array();
        foreach ($kindList as $each) {
	        $items[$each['id']] = $each;
        }
        
        return $items;
    }
}

