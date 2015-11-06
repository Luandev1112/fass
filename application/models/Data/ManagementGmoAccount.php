<?php
/**
 * class Shared_Model_Data_ManagementGmoAccount
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ManagementGmoAccount extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_management_gmo_account';

    protected $_fields = array(
        'id',                       // ID
		'status',                   // ステータス
		
		'name',                     // アカウント名
		
		'app_client_id',
		'app_client_secret',
		
		'gmo_access_token',                      // アクセストークン
		'gmo_access_token_expired_datetime',     // 

		'gmo_reflesh_token',                     // リフレッシュトークン
		'gmo_reflesh_token_expired_datetime',    // 
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時  
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'gmo_access_token',      // アクセストークン
		'gmo_reflesh_token',     // リフレッシュトークン
    );

    /**
     * リスト取得
     * @param none
     * @return array
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('id ASC');
    	$items = $selectObj->query()->fetchAll();
    	
    	$newItems = array();
    	
    	foreach ($items as $each) {
	    	$newItems[$each['id']] = $each;
    	}
    	
    	return $newItems;
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

