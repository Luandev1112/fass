<?php
/**
 * class Shared_Model_Data_ConnectionRecord
 * 議事録
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionRecord extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection_record';

    protected $_fields = array(
        'id',                                  // ID
        'display_id',                          // 表示ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
		'connection_id',                       // 取引先ID
		'progress_item_id',                    // 営業進捗ID
		
		'record_type',
		
		'target_date',                         // 対象日
        'title',                               // タイトル
		'content',                             // 内容

		'file_list',                           // 添付資料リスト
			
		'created_user_id',                     // 初期登録者ユーザーID
		'last_update_user_id',                 // 最終更新者ユーザーID
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時

    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'title',                               // タイトル
		'content',                             // 内容
		
		'file_list',                           // 添付資料リスト
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
	    	$data['file_list']  = json_decode($data['file_list'], true);
    	}
    	
    	
    	return $data;
    }

    /**
     * 営業案件ID関連最新一件取得
     * @param int $managementGroupId
     * @param int $progressItemId
     * @return array
     */
    public function getLatestByProgressItemId($managementGroupId, $progressItemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('progress_item_id = ?', $progressItemId);
    	$selectObj->order('target_date DESC');
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 更新
     * @param int $managementGroupId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }

    /**
     * 次の議事録ID (GJ＋西暦下二桁＋5桁)
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
						throw new Zend_Exception('Shared_Model_Data_ConnectionRecord display_id id is over-flowed');
					}
					
					return 'GJ' . $year . $nextAlphabet . '0001';
				} 
				return 'GJ' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'GJ' . $year . '0' . '0001';
    }

}

