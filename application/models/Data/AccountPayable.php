<?php
/**
 * class Shared_Model_Data_AccountPayable
 * 買掛管理
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountPayable extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_payable';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
        
        'template_id',                         // テンプレートID
        
        'status',                              // ステータス
		'payment_status',                      // 支払ステータス
		'is_attached',                         // 割当完了

        'relational_id',                       // 連携ID
        'relational_display_id',               // 連携表示ID
        
		'order_form_ids',                      // 発注管理IDリスト
		'online_purchase_id',                  // ネット購入委託管理ID
		
		'account_title_id',                    // 会計科目ID
		'account_totaling_group_id',           // 採算コード
		
		'target_connection_id',                // 支払先取引先
		
		'purchased_date',                      // クレジット利用日
		
		'paying_plan_date',                    // 支払予定日
		'total_amount',                        // 支払額
		'currency_id',                         // 通貨単位
		'tax_division',                        // 税区分
		'tax',                                 // 消費税
		
		'memo',                                // 摘要
		
		'paying_type',                         // 買掛支払種別(請求支払/カード支払/自動振替)
		'paying_method',                       // 支払方法
		'paying_method_memo',                  // 支払方法メモ
		
		'paying_bank_id',                      // 支払元銀行口座
		'paying_card_id',                      // 支払元クレジットカード
		
		'file_list',                           // 添付資料リスト
		
		'paid_user_id',                        // 支払処理担当者
		'paid_date',                           // 支払完了日
		
		'created_user_id',                     // 支払登録者
		'approval_user_id',                    // 承認者
		'approval_comment',                    // 修正依頼コメント
		
		'is_csv_target',
		
		'transfer_to_connection_bank_id',      // 振込先 取引先金融機関ID
		'bank_registered_type',                // 連携元 登録種別
		'target_id',                           // 連携元 サプライヤーID/BuyerID
		
		'transfer_to_bank_code',
		'transfer_to_bank_name',
		'transfer_to_branch_code',
		'transfer_to_branch_name',
		'transfer_to_account_type',
		'transfer_to_account_no',
		'transfer_to_account_name',
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
    	'order_form_ids',                      // 注文書IDリスト
		'memo',                                // メモ
		'paying_method_memo',                  // 支払方法メモ
		'file_list',                           // 添付資料リスト
		'approval_comment',                    // 修正依頼コメント
    );


    /**
     * 承認期限切れ一覧
     * @param none
     * @return array
     */
    public function getListExpired()
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_bank', 'frs_account_payable.paying_bank_id = frs_account_bank.id', array('short_name'));
    	$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($this->aesdecrypt('user_name', false) . 'AS user_name'));
    	$selectObj->where('frs_account_payable.payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PLANNED_EXPIRED);
    	return $selectObj->query()->fetchAll();
    }

    /**
     * IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($this->aesdecrypt('user_name', false) . 'AS user_name'));
    	$selectObj->where('frs_account_payable.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_payable.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
    		$data['order_form_ids']       = unserialize($data['order_form_ids']);
    		$data['file_list']            = json_decode($data['file_list'], true);
    	}
    	return $data;
    }

    /**
     * IDで取得(割当用/全てのグループで取得可能)
     * @param int $id
     * @return array
     */
    public function getByIdForAnyGroup($id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->joinLeft('frs_user', 'frs_account_payable.created_user_id = frs_user.id',array($this->aesdecrypt('user_name', false) . 'AS user_name'));
    	$selectObj->where('frs_account_payable.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
    		$data['order_form_ids']       = unserialize($data['order_form_ids']);
    		$data['file_list']            = json_decode($data['file_list'], true);
    	}
    	return $data;
    }

    /**
     * 毎月支払最新履歴
     * @param int $templateId
     * @return array
     */
    public function getLastestByTemplateId($templateId)
    {
    	$selectObj = $this->select();
		$selectObj->where('template_id = ?', $templateId);
		$selectObj->where('status != ?', Shared_Model_Code::PAYABLE_STATUS_DELETED);
		$selectObj->order('id DESC');
    	return $selectObj->query()->fetch();
    } 

    /**
     * 採算コードで取得
     * @param int $managementGroupId
     * @param int $totalingGroupId
     * @return array
     */
    public function getItemsWithTotalingGroupId($managementGroupId, $totalingGroupId, $type, $from = NULL, $to = NULL)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
		$selectObj->where('frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_APPROVED
			        . ' OR frs_account_payable.status = ' . Shared_Model_Code::PAYABLE_STATUS_ADDED_FROM_HISTORY);

    	$selectObj->where('frs_account_payable.payment_status != ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_PENDDING
    	           . ' AND frs_account_payable.payment_status != ' . Shared_Model_Code::PAYABLE_PAYMENT_STATUS_CANCELED);
			        
		$selectObj->where('account_totaling_group_id = ?', $totalingGroupId);
		
		if ($type === 'settlement') {
	    	if (!empty($from)) {
		    	$selectObj->where('frs_account_payable.paying_plan_date >= ?', $from);
	    	}
	
	    	if (!empty($to)) {
		    	$selectObj->where('frs_account_payable.paying_plan_date <= ?', $to);
	    	}
    	} else {
	    	if (!empty($from)) {
		    	$selectObj->where('frs_account_payable.purchased_date >= ?', $from);
	    	}
	
	    	if (!empty($to)) {
		    	$selectObj->where('frs_account_payable.purchased_date <= ?', $to);
	    	}
    	}
    	
    	return $selectObj->query()->fetchAll();
    }

	
    /**
     * 連携元 登録種別+サプライヤーID/BuyerID (bank_registered_type/target_id)で探す
     * @param int $connectionId
     * @param int $bankRegisteredType
     * @param int $targetId
     * @return array
     */
    public function getItemsByRegisteredType($connectionId, $bankRegisteredType, $targetId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('target_connection_id = ?', $connectionId);
    	$selectObj->where('bank_registered_type = ?', $bankRegisteredType);
    	$selectObj->where('target_id = ?', $targetId);
    	$selectObj->where('status = ?', Shared_Model_Code::PAYABLE_STATUS_APPROVED);
    	$selectObj->where('payment_status = ?', Shared_Model_Code::PAYABLE_PAYMENT_STATUS_UNPAID);
    	
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
    
    /**
     * 次の取引先ID (XX＋西暦下二桁＋5桁)
     * @param none
     * @return array
     */
     /*
    public function getNextDisplayId()
    {
    	$selectObj = $this->select();
    	$selectObj->order('id DESC');
    	$data = $selectObj->query()->fetch();
		
		$year = '' . date('y');
		
		if (!empty($data)) {
			$lastDate = substr($data['display_id'], 2, 2);
			
			if ($lastDate == $year) {
				$lastAlphabet = substr($data['display_id'], 4, 1);
				$lastCount = (int)substr($data['display_id'], 5, 4);

				if ($lastCount >= 9999) {
					$nextAlphabet = '';
					$isMatched = false;
					$alphabetCodeList = Shared_Model_Code::getIdAlpahabet();
					foreach ($alphabetCodeList as $each) {
						if ($isMatched === true) {
							$nextAlphabet = $each;
							break;
						} else if ($each === $lastAlphabet) {
							$isMatched = true;
						}
					}
					
					if ($nextAlphabet === '') {
						throw new Zend_Exception('Shared_Model_Data_AccountReceivable display_id id is over-flowed');
					}
					
					return 'AR' . $year . $nextAlphabet . '0001';
				} 
				return 'AR' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'AR' . $year . '0' . '0001';
    }
    */

}

