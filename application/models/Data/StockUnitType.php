<?php
/**
 * class Shared_Model_Data_StockUnitType
 * 棚卸数量単位
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_StockUnitType extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_stock_unit_type';

    protected $_fields = array(
        'id',                    // ID
        'symbol',                // 単位表記
        'name',                  // 名称
		'content_order',         // 並び順
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
     * @return boolean
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
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

