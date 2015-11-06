<?php
/**
 * class Shared_Model_Data_OrderImportFormat
 * 注文取込フォーマット
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_OrderImportFormat extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_order_import_format';

    protected $_fields = array(
        'id',                    // ID
		'status',                // ステータス
		'name',                  // フォーマット名
		'column_setting',        // カラム定義
		'convert_setting',       // 変換定義
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
     * 一覧取得
     * @param boolean $isSelectObj
     * @return array
     */
    public function getActiveList($isSelectObj = false)
    {
    	$selectObj = $this->select();
    	$selectObj->where('status = ?', Shared_Model_Code::ORDER_IMPORT_FORMAT_STATUS_ACTIVE);

    	if ($isSelectObj) {
    		return $selectObj;
    	}
    	
    	$selectObj->order('id ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * データ一件取得
     * @param int $id
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
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }
}

