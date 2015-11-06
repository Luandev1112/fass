<?php
/**
 * class Shared_Model_Data_AccountBankHistoryItem
 * 金融機関口座 取込CSV行
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountBankHistoryItem extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_bank_history_item';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'bank_history_id',                     // 銀行取込CSVID
        'status',                              // ステータス
        
        'row_count',                           // 行番号
        'target_date',                         // 対象日
        'jnb_time',                            // JNB操作時間
        'gmo_item_key',                        // GMO明細キー
        
        'name',                                // 項目名
         
        'currency_id',                         // 通貨ID
        'paid_amount',                         // 出金額
        'received_amount',                     // 預かり額(入金額)
        'balance_amount',                      // 残高
        
        'payable_id',                          // 買掛ID  (廃止予定)  
		'receivable_id',                       // 売掛ID  (廃止予定)  
		
		'payable_ids',                         // 買掛ID（複数） 
		'receivable_ids',                      // 売掛ID（複数）
		
		'apply_nos',                           // GMO　総合振込IDs
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'name',                                // 項目名
        
        'paid_amount',                         // 今月支払額
        'received_amount',                     // 支払開始月
        'balance_amount',                      // 支払回数
    );
    
    /**
     * IDで取得
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_currency', 'frs_account_bank_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	$selectObj->where('frs_account_bank_history_item.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	$data['payable_ids']    = unserialize($data['payable_ids']);
	    	$data['receivable_ids'] = unserialize($data['receivable_ids']);
	    	$data['apply_nos']      = unserialize($data['apply_nos']);
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
    	$selectObj->joinLeft('frs_currency', 'frs_account_bank_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	$selectObj->where('frs_account_bank_history_item.bank_history_id = ?', $historyId);
    	$selectObj->where('frs_account_bank_history_item.status != ?', Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_DELETED);
    	$selectObj->order('frs_account_bank_history_item.row_count ASC');
    	return $selectObj->query()->fetchAll();
    }


    /**
     * 同じデータがあるか(JNB専用)
     * @param int $name
     * @param int $operationDate
     * @return boolean
     */
    public function findJNBSameData($name, $operationDate)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history_item.bank_history_id = frs_account_bank_history.id', 'status AS history_status');
    	//$selectObj->where('frs_account_bank_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$selectObj->where('frs_account_bank_history_item.status != ?', Shared_Model_Code::BANK_HISTORY_ITEM_STATUS_DELETED);
    	$selectObj->where('jnb_time = ?', $operationDate);
    	$selectObj->where($this->aesdecrypt('name', false) . ' = ?', $name);

    	return $selectObj->query()->fetch();
	}

    /**
     * 最終行取得
     * @param int $historyId
     * @return array
     */
    public function lastRowOfHistory($historyId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('bank_history_id = ?', $historyId);
    	$selectObj->order('row_count DESC');
    	return $selectObj->query()->fetch();
    }

    /**
     * GMO item_key存在確認
     * @param string $itemKey
     * @return array
     */
    public function itemKeyExist($itemKey)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history_item.bank_history_id = frs_account_bank_history.id', 'status AS history_status');
    	$selectObj->where('gmo_item_key = ?', $itemKey);
    	$selectObj->where('frs_account_bank_history.status = ?', Shared_Model_Code::CONTENT_STATUS_ACTIVE);
    	$data = $selectObj->query()->fetch();

    	if (!empty($data)) {
    	    return true;
    	}
    	
    	return false;
    } 
    
    /**
     * 割当完了でないものがあるか(取り込み履歴別)
     * @param int $historyId
     * @return boolean
     */
    public function haveNoneAttach($historyId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_account_bank_history_item.bank_history_id = ?', $historyId);
    	$selectObj->where('frs_account_bank_history_item.status = ?', Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return true;
    	}
    	
    	return false;
    }


    
    /**
     * 割当完了でないものがあるか(期間別)
     * @param int $bankId
     * @param string $form
     * @param string $to
     * 
     * @return boolean
     */
    public function haveNoneAttachWithTerm($bankId, $form, $to)
    {
    	$selectObj = $this->select(array(new Zend_Db_Expr("COUNT(`frs_account_bank_history_item`.`id`) AS item_count")));
    	$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history_item.bank_history_id = frs_account_bank_history.id');
    	$selectObj->where('frs_account_bank_history.bank_id = ?', $bankId);
    	$selectObj->where('frs_account_bank_history_item.status = ?', Shared_Model_Code::CARD_HISTORY_ITEM_STATUS_NONE);
    	
    	$selectObj->where('frs_account_bank_history_item.target_date >= ?', $form);
    	$selectObj->where('frs_account_bank_history_item.target_date <= ?', $to);
    	
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return $data['item_count'];
    	}
    	
    	return 0;
    }
    
    
    /**
     * リスト期間開始日取得
     * @param int $historyId
     * @return string firstDate (YYYY-mm-dd)
     */
    public function getFirstDateByHistoryId($historyId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('bank_history_id = ?', $historyId);
    	$selectObj->order('target_date ASC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return $data['target_date'];	
    	}
    	
    	return NULL;
    }
    
    /**
     * リスト期間開終了日取得
     * @param int $historyId
     * @return string $lastDate (YYYY-mm-dd)
     */
    public function getLastDateByHistoryId($historyId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('bank_history_id = ?', $historyId);
    	$selectObj->order('target_date DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
	    	return $data['target_date'];	
    	}
    	
    	return NULL;
    }
    

    /**
     * 入金予定 割当済みリスト取得
     * @param int $receivableId
     * @return 
     */
    public function getListByReceivableId($receivableId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history_item.bank_history_id = frs_account_bank_history.id', 'import_key');
    	$selectObj->joinLeft('frs_currency', 'frs_account_bank_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	
        $receivableIdString = $this->getAdapter()->quoteInto('`receivable_ids`  LIKE ?', '%"' . $receivableId .'"%');
        $selectObj->where($receivableIdString);

    	
    	$selectObj->order('frs_account_bank_history_item.id ASC');
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
    	$selectObj->joinLeft('frs_account_bank_history', 'frs_account_bank_history_item.bank_history_id = frs_account_bank_history.id', 'import_key');
    	$selectObj->joinLeft('frs_currency', 'frs_account_bank_history_item.currency_id = frs_currency.id', 'name AS currency_name');
    	
        $payableIdString = $this->getAdapter()->quoteInto('`payable_ids`  LIKE ?', '%"' . $payableId .'"%');
        $selectObj->where($payableIdString);
    	
    	$selectObj->order('frs_account_bank_history_item.id ASC');
    	return $selectObj->query()->fetchAll();
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

