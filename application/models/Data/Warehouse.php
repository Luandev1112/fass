<?php
/**
 * class Shared_Model_Data_Warehouse
 * 自社倉庫
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Warehouse extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_warehouse';

    protected $_fields = array(
        'id',               // ID
        'management_group_id',  // 管理グループID
        'status',           // ステータス
        
        'name',             // 倉庫名
		'company_name',     // 会社名
		'zipcode',          // 郵便番号
		'prefecture',       // 都道府県
		'address1',         // 住所
		'address2',         // 建物
		'tel',              // 電話番号
		'fax',              // FAX
		
		'person_in_charge', // 担当者名
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
        'name',             // 倉庫名
		'company_name',     // 会社名
		'zipcode',          // 郵便番号
		'prefecture',       // 都道府県
		'address1',         // 住所
		'address2',         // 建物
		'tel',              // 電話番号
		'fax',              // FAX
		
		'person_in_charge', // 担当者名
		'mail',             // 担当者メールアドレス
		'mobile',           // 担当者携帯
		'memo',             // メモ
    );
    
    /**
     * リスト取得
     * @param int  $managementGroupId
     * @param BOOL $isSelectObj
     * @return boolean
     */
    public function getActiveList($managementGroupId, $isSelectObj)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
		$selectObj->where('status = ?', Shared_Model_Code::WAREHOUSE_STATUS_ACTIVE);
		
    	if (!empty($isSelectObj)) {
    		$selectObj->order('id ASC');
    		return $selectObj;
    	}
    	
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
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 更新
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

}

