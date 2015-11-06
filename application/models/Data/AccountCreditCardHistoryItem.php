<?php
/**
 * class Shared_Model_Data_AccountCreditCardHistoryItem
 * クレジットカード取込CSV行
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountCreditCardHistoryItem extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_credit_card_history_item';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'card_history_id',                     // クレジットカード取込CSVID
        'status',                              // ステータス
        
        'row_count',                           // 行番号
        'purchased_date',                      // 利用日(購入日)
        'name',                                // 項目名
        
        'start_month',                         // 支払開始月
        'times',                               // 支払回数
        'time_count',                          // 支払今回回数
        'charge',                              // 手数料
        'balance',                             // 残り残高
         
        'currency_id',                         // 通貨ID
        'amount',                              // 今月支払額
        
        'payable_id',                          // 買掛ID   （廃止予定） 
        'payable_ids',                         // 買掛ID（複数） 
        'receivable_ids',

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'name',                                // 項目名
        'amount',                              // 今月支払額
        'start_month',                         // 支払開始月
        'times',                               // 支払回数
        'time_count',                          // 支払今回回数
        'charge',                              // 手数料
        'balance',                             // 残り残高
    );
    
    /**
     * IDで取得
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_currency', 'frs_account_credit_card_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	$selectObj->where('frs_account_credit_card_history_item.id = ?', $id);
		$data = $selectObj->query()->fetch();
		
    	if (!empty($data)) {
	    	$data['payable_ids']    = unserialize($data['payable_ids']);
	    	$data['receivable_ids'] = unserialize($data['receivable_ids']);
    	}

    	return $data;
    }

    /**
     * リスト取得
     * @param int $historyId
     * @return array
     */
    public function getList($historyId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_currency', 'frs_account_credit_card_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	$selectObj->where('frs_account_credit_card_history_item.card_history_id = ?', $historyId);
    	$selectObj->order('frs_account_credit_card_history_item.row_count ASC');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 割当完了でないものがあるか
     * @param int $historyId
     * @return boolean
     */
    public function haveNoneAttach($historyId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_account_credit_card_history_item.card_history_id = ?', $historyId);
    	$selectObj->where('frs_account_credit_card_history_item.status = ?', Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	
    	return false;
    }
    

    /**
     * 入金予定 割当済みリスト取得
     * @param int $receivableId
     * @return 
     */
    public function getListByReceivableId($receivableId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_credit_card_history', 'frs_account_credit_card_history_item.card_history_id = frs_account_credit_card_history.id', 'import_key');
    	$selectObj->joinLeft('frs_currency', 'frs_account_credit_card_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	
        $receivableIdString = $this->getAdapter()->quoteInto('`receivable_ids`  LIKE ?', '%"' . $receivableId .'"%');
        $selectObj->where($receivableIdString);

    	
    	$selectObj->order('frs_account_credit_card_history_item.id ASC');
    	return $selectObj->query()->fetchAll();
    }
        
    /**
     * 支払予定 割当済みリスト取得
     * @param int $payableId
     * @return 
     */
    public function getListByPayableId($payableId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_credit_card_history', 'frs_account_credit_card_history_item.card_history_id = frs_account_credit_card_history.id', 'import_key');
    	$selectObj->joinLeft('frs_currency', 'frs_account_credit_card_history_item.currency_id = frs_currency.id', 'name AS currency_name');

        $payableIdString = $this->getAdapter()->quoteInto('`payable_ids`  LIKE ?', '%"' . $payableId .'"%');
        $selectObj->where($payableIdString);

    	$selectObj->order('frs_account_credit_card_history_item.id ASC');
    	//var_dump($selectObj->__toString());
    	
    	return $selectObj->query()->fetchAll();
    }


    
    /**
     * 更新
     * @param int $managementGroupId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }

}

