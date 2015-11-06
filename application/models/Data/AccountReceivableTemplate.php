<?php
/**
 * class Shared_Model_Data_AccountReceivableTemplate
 * 売掛管理テンプレート
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountReceivableTemplate extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_receivable_template';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
		'template_type',                       // テンプレート種別
		
		'account_title_id',                    // 会計科目ID
		'account_totaling_group_id',           // 採算コード
		
		'target_connection_id',                // 支払元取引先
		
		'recieve_plan_monthly_day',            // 毎月入金時期
		'total_amount',                        // 入金額
		'currency_id',                         // 通貨単位
		
		
		'description',                         // 内容
		'other_memo',                          // 備考
		
		'bank_id',                             // 入金口座

		'file_list',                           // 添付資料リスト
		
		'created_user_id',                     // 支払登録者
		'approval_user_id',                    // 承認者
		'approval_comment',                    // 修正依頼コメント
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'recieve_plan_monthly_day',            // 摘要
		'description',                         // 内容
		'other_memo',                          // 備考
		'file_list',                           // 添付資料リスト
		'approval_comment',                    // 修正依頼コメント
    );
    
    /**
     * IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_connection', 'frs_account_receivable_template.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->where('frs_account_receivable_template.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_receivable_template.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	$data['file_list']       = json_decode($data['file_list'], true);
    	return $data;
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

