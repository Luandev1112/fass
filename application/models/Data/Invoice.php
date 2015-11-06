<?php
/**
 * class Shared_Model_Data_Invoice
 * 請求書
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Invoice extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_invoice';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
		'status',                              // ステータス
		'language',                            // 言語選択
		'including_tax',                       // 内税表示
		
		'invoice_type',                        // 請求書タイプ
		
		'target_connection_id',                // 提出先取引ID
		
		'direct_order_id',                     // 受注管理ID(廃止予定)
		'direct_order_ids',                    // 受注管理ID(複数)
		
		'document_id',                         // 契約書ID
		
		'invoice_date',                        // 請求書日付
		'to_name',                             // 宛先	
		'title',                               // タイトル

		'labels',                              // テーブル項目ラベル
		'item_list',                           // テーブル中身

		'subtotal',                            // 小計
		'tax_percentage',                      // 消費税率
		'tax',                                 // 消費税
		'total_with_tax',                      // 合計
		'currency_mark',                       // 通貨記号
		'currency_id',                         // 通貨ID

  		'file_list',                           // 添付資料リスト
  		
		'memo',                                // 備考
		'memo_private',                        // 社内メモ
		'approval_comment',                    // 承認コメント
		
		'created_user_id',                     // 作成者ユーザーID
		'last_update_user_id',                 // 最終更新者ユーザーID
		'approval_user_id',                    // 承認者ユーザーID
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'to_name',                             // 宛先	
		'title',                               // タイトル

		'labels',                              // テーブル項目ラベル
		'item_list',                           // テーブル中身

		'subtotal',                            // 小計
		'tax_percentage',                      // 消費税率
		'tax',                                 // 消費税
		'total_with_tax',                      // 合計
		
		'file_list',                           // 添付資料リスト
		
		'memo',                                // 備考
		'memo_private',                        // 社内メモ
		'approval_comment',                    // 承認コメント
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
    	$selectObj->where('frs_invoice.management_group_id = ?', $managementGroupId);
    	$selectObj->joinLeft('frs_connection', 'frs_invoice.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->where('frs_invoice.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
	    	$data['labels']           = json_decode($data['labels'], true);
			$data['item_list']        = json_decode($data['item_list'], true);
			$data['file_list']        = json_decode($data['file_list'], true);
			$data['direct_order_ids'] = unserialize($data['direct_order_ids']);
			
    	}
    	return $data;
    }


    /**
     * IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getCountByDirectOrderId($managementGroupId, $directOrderId)
    {
        $selectObj = $this->select(array(new Zend_Db_Expr('COUNT(`id`) as item_count')));
        $selectObj->where('management_group_id = ?', $managementGroupId);

        $selectObj->where('`direct_order_ids` LIKE ?', '%"' . $directOrderId .'"%');
        //$selectObj->where('direct_order_id = ?', $directOrderId);
        
        $selectObj->where('status != ?', Shared_Model_Code::INVOICE_STATUS_DELETED);
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0; 
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
	    	'label_2' => 'ITEM',
	    	'label_4' => 'Unit Price',
	    	'label_5' => 'QTY',
	    	'label_6' => 'Total',
	    	);
    	}
    
    	return array(
    	'label_1' => 'No.',
    	'label_2' => '項目',
    	'label_4' => '単価',
    	'label_5' => '数量',
    	'label_6' => '金額：円',
    	);
    }
        
    /**
     * 次の見積書ID (XX＋西暦下二桁＋5桁)
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
					
					return 'SK' . $year . $nextAlphabet . '0001';
				} 
				return 'SK' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'SK' . $year . '0' . '0001';
    }
}

