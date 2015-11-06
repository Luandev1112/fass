<?php
/**
 * class Shared_Model_Data_ManagementGroup
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ManagementGroup extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_management_group';

    protected $_fields = array(
        'id',                       // 管理グループID
		'display_id',               // 表示ID
		'status',                   // ステータス
		
		'group_header_color',       // グループヘッダーカラー
		
		'organization_name',        // 組織名
		'organization_name_en',     // 組織名EN
		
		'country',                  // 国(選択)
		'postal_code',              // 郵便番号
		'prefecture',               // 都道府県
		'city',                     // 市区町村
		'address',                  // 丁番地
		'building',                 // 建物名・階／号室
		
		'prefecture_en',               // 都道府県EN
		'city_en',                     // 市区町村EN
		'address_en',                  // 丁番地EN
		'building_en',                 // 建物名・階／号室EN
		
		'representative_name',      // 代表者名
		'representative_name_kana', // 代表者名カナ
		'representative_name_en',   // 代表者名EN
		
		'tel',                      // TEL
		'tel_int',                  // TEL(INTERNATIONAL)
		'fax',                      // FAX
		'fax_int',                  // FAX(INTERNATIONAL)
		
		'web_url',                  // WEB URL
		
		'memo',                     // メモ
		
		'gmo_account_id',           // GMOアカウントID
		
		'last_update_user_id',      // 最終更新者ユーザーID
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時  
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'organization_name',        // 組織名
		'organization_name_en',     // 組織名EN

		'postal_code',              // 郵便番号
		
		'prefecture',               // 都道府県
		'city',                     // 市区町村
		'address',                  // 丁番地
		'building',                 // 建物名・階／号室
		'prefecture_en',            // 都道府県
		'city_en',                  // 市区町村
		'address_en',               // 丁番地
		'building_en',              // 建物名・階／号室
		
		
		'representative_name',      // 代表者名
		'representative_name_kana', // 代表者名カナ
		'representative_name_en',   // 代表者名EN

		'tel',                      // TEL
		'tel_int',                  // TEL(INTERNATIONAL)
		'fax',                      // FAX
		'fax_int',                  // FAX(INTERNATIONAL)
		
		'web_url',                  // WEB URL
		
		'memo',                     // メモ
    );

    /**
     * 表示IDがすでに使用されているか
     * @param int $displayId
     * @param int $exceptId  除外ID
     * @return array
     */
    public function isUsedDisplayId($displayId, $exceptId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_management_group.display_id = ?', $displayId);
    	
    	if (!empty($exceptId)) {
    		$selectObj->where('frs_management_group.id != ?', $exceptId);
    	}
    	
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return true;
    	}
    	
    	return false;
    }

    /**
     * 表示IDがすでに使用されているか
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
    	$selectObj->where('frs_management_group.id = ?', $id);
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

