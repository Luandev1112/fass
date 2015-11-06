<?php
/**
 * class Shared_Model_Data_ConnectionProgressItem
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionProgressItem extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection_progress_item';

    protected $_fields = array(
        'id',                                  // ID
        'display_id',                          // 表示ID XX＋西暦下二桁＋4桁
        'management_group_id',                 // 管理グループID
		'status',                              // ステータス
		
		'sheet_id',                            // シートID
		
		'user_id',                             // 自社担当ユーザーID
		
        'connection_id',                       // 取引先ID
		'staff_id',                            // 取引先担当者ID
		
		'area_id',                             // 営業エリア		

		'start_type',                          // 発足区分
		'start_tag_id',                        // 発足名称タグID

		'proposition_id',                      // 案件ID
		'proposition_category',                // 案件分野

		'item_ids',                            // 案件対象商品

		'importance',                          // 重要度
		'possibility',                         // 可能性
		
		'progress',                            // 実績
		'after',                               // 見積後ヒア
		
		'has_no_task',                         // 宿題なし
		'task',                                // 宿題
		'details',                             // 詳細
		
		'other_memo',                          // その他展望等
	
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
		'task',                                 // 宿題
		'details',                              // 詳細
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
    	$selectObj->joinLeft('frs_connection', 'frs_connection_progress_item.connection_id = frs_connection.id', array('display_id AS connection_display_id', $this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->joinLeft('frs_connection_progress_start_tag', 'frs_connection_progress_item.start_tag_id = frs_connection_progress_start_tag.id', array('tag_name'));

    	$selectObj->where('frs_connection_progress_item.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_connection_progress_item.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {   	
			$data['progress']  = unserialize($data['progress']);
			$data['after']     = unserialize($data['after']);
			$data['item_ids']  = unserialize($data['item_ids']);
    	} 
    	return $data;
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
     * 次の案件ID (XX＋西暦下二桁＋4桁)
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

				if ($lastCount >= 999) {
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
						throw new Zend_Exception('Shared_Model_Data_ConnectionProgressItem display_id id is over-flowed');
					}
					
					return 'AN' . $year . $nextAlphabet . '001';
				} 
				return 'AN' . $year . $lastAlphabet . sprintf('%03d', $lastCount + 1);
			}
		}
		
		return 'AN' . $year . '0' . '001';
    } 
}

