<?php
/**
 * class Shared_Model_Data_AccountCreditCardHistory
 * クレジットカード取込CSV
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountCreditCardHistory extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_credit_card_history';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
        'import_key',                          // 取り込みキー
        
        'paying_plan_date',                    // 支払予定日
        
        'card_id',                             // カードID
        
        'created_user_id',                     // 取込実施者

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
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
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_credit_card', 'frs_account_credit_card_history.card_id = frs_account_credit_card.id', array($this->aesdecrypt('card_name', false) . 'AS card_name'));
    	$selectObj->where('frs_account_credit_card_history.id = ?', $id);
    	return $selectObj->query()->fetch();
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

