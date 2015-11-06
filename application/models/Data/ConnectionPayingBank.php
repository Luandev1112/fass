<?php
/**
 * class Shared_Model_Data_ConnectionPayingBank
 * 取引先支払元口座名
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionPayingBank extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_connection_paying_bank';

    protected $_fields = array(
        'id',                          // ID
        'connection_id',               // 取引先ID
        
        'resource_history_item_id',    // 取引先ID
        'bank_account_name',           // 取引先支払元口座名
        'status',                      // ステータス

        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'bank_account_name',           // 取引先支払元口座名
    );
    
    /**
     * 取引先IDで取得
     * @param int $connectionId
     * @return array
     */
    public function getListByConnectionId($connectionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->order('id ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * IDで取得
     * @param int $managementGroupId
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

