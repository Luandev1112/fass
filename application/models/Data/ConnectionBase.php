<?php
/**
 * class Shared_Model_Data_ConnectionBase
 * 取引先 拠点
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionBase extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_connection_base';

    protected $_fields = array(
        'id',               // ID
        'management_group_id', // 管理グループID
        'connection_id',    // 取引先ID
        'status',           // ステータス
        'company_name',     // 法人名
        'base_name',        // 拠点名
		'zipcode',          // 郵便番号
		'prefecture',       // 都道府県
		'address1',         // 住所
		'address2',         // 建物
		'tel',              // 電話番号
		'fax',              // FAX
		
		'person_in_charge', // 担当者名
		'person_in_charge_kana', // 担当者名(カナ)
		'mail',             // 担当者メールアドレス
		'mobile',           // 担当者携帯
		'memo',             // メモ
		
        'created',          // レコード作成日時
        'updated',          // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'base_name',        // 拠点名
		'zipcode',          // 郵便番号
		'prefecture',       // 都道府県
		'address1',         // 住所
		'address2',         // 建物
		'tel',              // 電話番号
		'fax',              // FAX
		
		'person_in_charge', // 担当者名
		'person_in_charge_kana', // 担当者名(カナ)
		'mail',             // 担当者メールアドレス
		'mobile',           // 担当者携帯
		'memo',             // メモ
    );
    
    /**
     * 取引先IDで取得
     * @param int $managementGroupId
     * @param int $connectionId
     * @return array
     */
    public function getListByConnectionId($managementGroupId, $connectionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
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
    public function getById($managementGroupId, $id)
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

