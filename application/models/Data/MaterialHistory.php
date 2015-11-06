<?php
/**
 * class Shared_Model_Data_MaterialHistory
 * 資料 履歴
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_MaterialHistory extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_material_history';

    protected $_fields = array(
        'id',                    // ID
        'material_id',           // サプライヤーID
        'version_id',            // バージョンID

		'file_type',             // ファイル種類
		'file_size',             // ファイルサイズ
		'file_name',             // 保存ファイル名
		'default_file_name',     // 初期ファイル名
		
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
     * 履歴を取得
     * @param int   $materialId
     * @return array
     */
    public function getHitoryListByMaterialId($materialId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('material_id = ?', $materialId);
    	$selectObj->order('version_id DESC');
    	return $selectObj->query()->fetchAll();
    }

	/**
     * 次のバージョン
     * @param int   $materialId
     * @return array
     */
    public function getNextVersionId($materialId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('material_id = ?', $materialId);
    	$selectObj->order('version_id DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (empty($data)) {
	    	return 1;
    	}
    	
    	return (int)$data['version_id'] + 1;
    }


    /**
     * IDで取得
     * @param int   $id
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
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

