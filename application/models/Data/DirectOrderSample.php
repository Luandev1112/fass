<?php
/**
 * class Shared_Model_Data_DirectOrderSample
 * サンプル出荷先
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_DirectOrderSample extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_direct_order_sample';

    protected $_fields = array(
        'id',                            // ID
        'management_group_id',           // 管理グループID
        'display_id',                    // 表示ID XX＋西暦下二桁＋5桁
        
        'type',                          // 種別
		'status',                        // ステータス
		
		'is_delivery_plan_date_unknown', // 納品予定日未定
		'delivery_plan_date',            // 納品予定日
		'shipment_status',               // 出荷状況
		'deliveried_date',               // 納品日

		'target_connection_id',          // 発注元取引先ID	
		'reason',                        // 破棄理由
		'memo',                          // 備考
		'warehouse_id',                  // 出荷元倉庫ID
		
		'items',                         // 対象商品資材
		'file_list',                     // 添付ファイルリスト
		
		'base_id',                       // 納入先拠点
		'shipment_request_date',         // 出荷希望日
		'shipped_date',                  // 出荷完了日
		'delivery_method',               // 配送方法指示
		'shipment_memo',                 // 伝達事項
		
		'delivery_agent',                // 配送業者
		'delivery_company',              // その他配送業者名
		'delivery_code',                 // 配送伝票番号
		'shipped_memo',                  // 出荷管理メモ
		
		'approval_comment',              // 承認コメント
		
		'created_user_id',               // 作成者ユーザーID
		'last_update_user_id',           // 最終更新者ユーザーID
		'approval_user_id',              // 承認者ユーザーID
		
        'created',                       // レコード作成日時
        'updated',                       // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
	    'reason',                        // 破棄理由
		'memo',                          // 備考
		
		'items',                         // 受注内容(商品リスト)
		'file_list',                     // 添付ファイルリスト
		
		'delivery_method',               // 配送方法指示
		'shipment_memo',                 // 伝達事項

		'delivery_company',              // 配送業者
		'delivery_code',                 // 配送伝票番号
		
		'approval_comment',              // 承認コメント
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
    		$data['items']       = json_decode($data['items'], true);
    		$data['file_list']   = json_decode($data['file_list'], true);
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
     * 次の受注管理ID (SW＋西暦下二桁＋5桁)
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
					
					return 'SW' . $year . $nextAlphabet . '0001';
				} 
				return 'SW' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'SW' . $year . '0' . '0001';
    }
}

