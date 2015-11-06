<?php
/**
 * class Shared_Model_Data_MessageTemplate
 * 明細書メッセージテンプレート
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_MessageTemplate extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_message_template';

    protected $_fields = array(
        'id',               // ID
        'warehouse_id',     // 倉庫ID
        'template_type',    // テンプレートタイプ
        'status',           // ステータス
        
        'title',            // テンプレート名
		'message',          // メッセージ

        'created',          // レコード作成日時
        'updated',          // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    	'title',            // テンプレート名
        'message',          // メッセージ
    );
    
    /**
     * リスト取得
     * @param int $warehouseId
     * @param int $templateType
     * @return boolean
     */
    public function getListByTemplateType($warehouseId, $templateType)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_id = ?', $warehouseId);
    	$selectObj->where('template_type = ?', $templateType);
    	$selectObj->order('id DESC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * IDで取得
     * @param int $warehouseId
     * @param int $id
     * @return boolean
     */
    public function getById($warehouseId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_id = ?', $warehouseId);
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 更新
     * @param int $warehouseId
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($warehouseId, $id, $columns)
    {
		return $this->update($columns, array('warehouse_id' => $warehouseId, 'id' => $id));
    }

}

