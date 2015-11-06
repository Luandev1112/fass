<?php
/**
 * class Shared_Model_Data_MaterialKind
 * アイテム 資料種別
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_MaterialKind extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_material_kind';

    protected $_fields = array(
        'id',                    // ID
        'name',                  // 種類名
        'status',                              // ステータス
        
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
     * 次の並び順
     * @return array
     */
    public function getNextContentOrder()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
    }

    /**
     * リスト取得
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

    /**
     * アクティブ一覧
     * @return boolean
     */
    public function getActiveList()
    {
    	$selectObj = $this->select();
    	$selectObj->where('status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }
     

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

}

