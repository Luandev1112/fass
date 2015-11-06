<?php
/**
 * class Shared_Model_Data_DirectOrderForm
 * 発注管理(EC以外の直接取引)
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_DirectOrderForm extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_direct_order_form';

    protected $_fields = array(
        'id',                      // ID
        'management_group_id',     // 管理グループID
        'display_id',              // 表示ID XX＋西暦下二桁＋5桁
		'status',                  // ステータス
		
		'is_delivery_plan_date_unknown', // 納品予定日未定
		'delivery_plan_date',            // 納品予定日
		'deliveried_status',             // 入庫状況
		'deliveried_date',               // 納品日
		
		'language',                // 言語選択
		'including_tax',           // 内税表示
		'order_form_type',         // 形式
		
		'target_connection_id',    // 発注先 取引先ID
		'to_name',                 // 宛先
		
		'order_date',              // 発注日
		'title',                   // タイトル
		'labels',                  // テーブル項目ラベル
		'item_list',               // テーブル中身
		
		'warehouse_id',            // 納入希望先倉庫ID
		'conditions',              // 前提条件
		
		'memo',                    // 備考
		'memo_private',            // 社内メモ
		'approval_comment',        // 承認コメント

		'subtotal',                // 小計
		'tax_percentage',          // 消費税率
		'tax',                     // 消費税
		'total_with_tax',          // 合計
		'currency_mark',           // 通貨記号
  		'currency_id',             // 通貨ID
  		
  		'file_list',               // 添付資料リスト
  		'supply_list',             // 対象調達管理(発注書アップロード時用)
  		
		'created_user_id',         // 作成者ユーザーID
		'last_update_user_id',     // 最終更新者ユーザーID
		'approval_user_id',        // 承認者ユーザーID
		
		'order_form_payable_status',  // 発注支払申請実施ステータス
		'payable_ids',             // 支払い申請IDリスト
		
        'created',                 // レコード作成日時
        'updated',                 // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',                   // タイトル

		'labels',                  // テーブル項目ラベル
		'item_list',               // テーブル中身
		'conditions',              // 前提条件
		'memo',                    // 備考
		'memo_private',            // 社内メモ
		'approval_comment',        // 承認コメント
		
		'subtotal',                // 小計
		'tax_percentage',          // 消費税率
		'tax',                     // 消費税
		'total_with_tax',          // 合計
		
		'file_list',               // 添付資料リスト
		'payable_ids',             // 支払い申請IDリスト
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
	    	$data['labels']       = json_decode($data['labels'], true);
	    	$data['item_list']    = json_decode($data['item_list'], true);
	    	$data['file_list']    = json_decode($data['file_list'], true);
	    	$data['supply_list']  = json_decode($data['supply_list'], true);
	    	$data['payable_ids']  = unserialize($data['payable_ids']);
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
     * デフォルトラベル
     * @return array
     */
    public function getDefaultLabels($lang)
    {
    	if ($lang == Shared_Model_Code::LANGUAGE_EN) {
	    	return array(
	    	'label_1' => 'No.',
	    	'label_2' => 'ITEM NAME AND DESCRIPTION',
	    	'label_3' => 'Unit Price',
	    	'label_4' => 'QTY',
	    	'label_5' => 'Total',
	    	);
    	}
    
    	return array(
    	'label_1' => 'No.',
    	'label_2' => '項目',
    	'label_3' => '単価',
    	'label_4' => '数量',
    	'label_5' => '金額：円',
    	);
    }
    
    /**
     * 次の発注管理ID (XX＋西暦下二桁＋5桁)
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
					
					return 'HT' . $year . $nextAlphabet . '0001';
				} 
				return 'HT' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'HT' . $year . '0' . '0001';
    }
}

