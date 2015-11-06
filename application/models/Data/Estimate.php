<?php
/**
 * class Shared_Model_Data_Estimate
 * 見積書
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Estimate extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_estimate';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
		'status',                              // ステータス
		
		'target_connection_id',                // 提出先取引ID
		
		'estimate_date',                       // 見積書発行日
		'title',                               // タイトル
		'template_id',                         // 選択済みテンプレートID
		
		
		'created_user_id',                     // 作成者ユーザーID
		'last_update_user_id',                 // 最終更新者ユーザーID
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',
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


    /**
     * 次の見積書ID (XX＋西暦下二桁＋5桁)
     * @param none
     * @return array
     */
    public function getNextDisplayId()
    {
    	$selectObj = $this->select();
    	$selectObj->where('status != ?', Shared_Model_Code::CONNECTION_STATUS_REMOVE);
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
					
					return 'MM' . $year . $nextAlphabet . '0001';
				} 
				return 'MM' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'MM' . $year . '0' . '0001';
    }
}

