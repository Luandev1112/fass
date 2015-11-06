<?php
/**
 * class Shared_Model_Data_UserDepartment
 * 所属
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_UserDepartment extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_user_department';

    protected $_fields = array(
        'id',                    // ID
        'management_group_id',   // 管理グループID
        'status',                // ステータス
        
        'department_name',       // 部署名
        'department_name_en',    // 部署名英語
        'is_accountants_office', // 会計事務所
        'mailing_list_address',  // 通知用メールアドレス
		
		'mail_cost',             // 通知 原価計算
		'mail_estimate',         // 通知 提出見積
		
		'mail_supply',           // 通知 調達管理
		
		'mail_order',            // 通知 受注管理
		'mail_order_form',       // 通知 発注管理
		
		'mail_payable',          // 通知 支払申請
		'mail_payable_monthly',  // 通知 毎月支払申請
		
		'mail_invoice',          // 通知 請求書発行
		'mail_receivable_monthly', // 通知 毎月入金管理

		'content_order',         // 並び順
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'department_name',       // 部署名
		'department_name_en',    // 部署名英語
		'mailing_list_address',  // 通知用メールアドレス
    );
    
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
     * 一覧
     * @param int $managementGroupId
     * @return boolean
     */
    public function getList($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 一覧
     * @param int $managementGroupId
     * @return boolean
     */
    public function getListExceptAccountOffice($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('is_accountants_office = 0');
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 次の並び順
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getNextContentOrder($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
    }
     
    /**
     * 更新
     * @param int $managementGroupId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

}

