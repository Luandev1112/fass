<?php
/**
 * class Shared_Model_Data_SupplyFixtureProject
 * 備品資材
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_SupplyFixtureProject extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_supply_fixture_project';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
		'status',                              // ステータス
		
		'is_copied',
		
		'tag_id',                              // 一般名称タグ 
		'title',                               // 製造加工委託名
		'description',                         // 製造加工委託内容

		'uses',                                // 用途
		'use_memo',                            // 用途メモ
		
		'other_memo',                          // 調達方法・注意点等メモ
		
		'item_ids',                            // 対象商品ID
		
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
    	'title',                               // 製造加工委託名
		'description',                         // 製造加工委託内容
		
		'uses',                                // 用途
		'use_memo',                            // 用途メモ
		
		'other_memo',                          // 調達方法・注意点等メモ
		
		'item_ids',                            // 対象商品ID
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
    	$data['item_ids'] = unserialize($data['item_ids']);
    	$data['uses']     = unserialize($data['uses']);
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
     * 次の備品資材ID (BS＋西暦下二桁＋5桁)
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
					
					return 'BS' . $year . $nextAlphabet . '0001';
				} 
				return 'BS' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'BS' . $year . '0' . '0001';
    }
}

