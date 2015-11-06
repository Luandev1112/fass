<?php
/**
 * class Shared_Model_Data_AccountCreditCard
 * 金融機関定義
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountCreditCard extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_credit_card';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
        'card_name',                           // カード名
        'card_company',                        // カード会社名
        
        'card_max',                            // カード限度額
        'card_no_last4',                       // カード番号下4桁
        
        'closing_day',                         // 締め日
        'payment_day',                         // 支払日
        
        'content_order',                       // 並び順     

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'card_name',                           // カード名
        'card_company',                        // カード会社名
        
        'card_max',                            // カード限度額
        'card_no_last4',                       // カード番号下4桁
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
     * 一覧
     * @return boolean
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 次の並び順
     * @param int $id
     * @return array
     */
    public function getNextContentOrder()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
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

