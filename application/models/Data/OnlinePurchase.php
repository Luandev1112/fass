<?php
/**
 * class Shared_Model_Data_OnlinePurchase
 * ネット購入委託管理
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_OnlinePurchase extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_online_purchase';

    protected $_fields = array(
        'id',                            // ID
        'management_group_id',           // 管理グループID
        'display_id',                    // 表示ID XX＋西暦下二桁＋5桁
		'status',                        // ステータス
		
		'is_delivery_plan_date_unknown', // 納品予定日未定
		'delivery_plan_date',            // 納品予定日
		'deliveried_status',             // 入庫状況
		'deliveried_date',               // 納品日
		
		'target_connection_id',          // 注文先 取引先ID
		'order_plan_date',               // 注文予定日
		'purchased_date',                // 注文日

		'title',                         // タイトル
		'item_list',                     // 注文内容

		'memo',                          // 備考
		'memo_for_payable',              // 摘要
		'memo_private',                  // 社内メモ
		'approval_comment',              // 承認コメント

		'subtotal',                      // 小計
		'tax_percentage',                // 消費税率
		'tax',                           // 消費税
		'total_with_tax',                // 合計
  		'currency_id',                   // 通貨ID
  		
  		'file_list',                     // 添付資料リスト
  		
		'created_user_id',               // 作成者ユーザーID
		'last_update_user_id',           // 最終更新者ユーザーID
		'approval_user_id',              // 承認者ユーザーID

		'paying_plan_date',              // 支払予定日
		
		'account_title_id',              // 会計科目ID
		'account_totaling_group_id',     // 採算コードID
		'payable_id',                    // 支払申請ID
		'paying_method',                 // 支払方法
		'paying_method_memo',            // 支払方法メモ
		
		'paying_bank_id',                // 支払元銀行口座
		'paying_card_id',                // 支払元クレジットカード
		
        'created',                       // レコード作成日時
        'updated',                       // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',                   // タイトル
		'item_list',               // テーブル中身

		'memo',                    // 備考
		'memo_for_payable',        // 摘要
		'memo_private',            // 社内メモ
		'approval_comment',        // 承認コメント
		
		'subtotal',                // 小計
		'tax_percentage',          // 消費税率
		'tax',                     // 消費税
		'total_with_tax',          // 合計
		
		'file_list',               // 添付資料リスト
		'paying_method_memo',      // 支払方法メモ
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
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
	    	$data['item_list']    = json_decode($data['item_list'], true);
	    	$data['file_list']    = json_decode($data['file_list'], true);
    	}
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
    
    /**
     * 次の発注管理ID (TK＋西暦下二桁＋5桁)
     * @param none
     * @return array
     */
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
						throw new Zend_Exception('display_id id is over-flowed');
					}
					
					return 'TK' . $year . $nextAlphabet . '0001';
				} 
				return 'TK' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'TK' . $year . '0' . '0001';
    }
}

