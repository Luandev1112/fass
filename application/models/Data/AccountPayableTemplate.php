<?php
/**
 * class Shared_Model_Data_AccountPayableTemplate
 * 買掛管理テンプレート
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountPayableTemplate extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_payable_template';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
		'template_type',                       // テンプレート種別
		
		'account_title_id',                    // 会計科目ID
		'account_totaling_group_id',           // 採算コード
		
		'target_connection_id',                // 支払先取引先
		
		'paying_plan_monthly_day',             // 毎月支払時期
		'total_amount',                        // 支払額
		'currency_id',                         // 通貨単位
		'tax_division',                        // 税区分
		'tax',                                 // 消費税
		
		'description',                         // 内容
		'other_memo',                          // 備考
		
		'paying_method',                       // 支払方法
		'paying_method_memo',                  // 支払方法メモ
		
		'paying_bank_id',                      // 支払元銀行口座
		'paying_card_id',                      // 支払元クレジットカード

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
		'paying_plan_monthly_day',             // 摘要
		'description',                         // 内容
		'paying_method_memo',                  // 支払方法メモ
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
    	$selectObj->joinLeft('frs_connection', 'frs_account_payable_template.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->where('frs_account_payable_template.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_payable_template.id = ?', $id);
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

