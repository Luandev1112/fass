<?php
/**
 * class Shared_Model_Data_AccountBankHistory
 * 金融機関口座 取込CSV
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountBankHistory extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_bank_history';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
        'import_key',                          // 取り込みキー
        
        'bank_id',                             // 金融機関ID
        
        'created_user_id',                     // 取込実施者
		
		'term_form',                           // 期間開始日
		'term_to',                             // 期間終了日
		
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
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }


    /**
     * 銀行IDで最新の取込履歴取得
     * @param int $bankId
     * @return array
     */
    public function latestHistoryOfBank($bankId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('bank_id = ?', $bankId);
    	$selectObj->where('frs_account_bank_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->order('id DESC');
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

