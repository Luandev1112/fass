<?php
/**
 * class Shared_Model_Data_SupplyCompetition
 * コンペ
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_SupplyCompetition extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_supply_competition';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
		'status',                              // ステータス
		
		'title',                               // コンペ・企画件名
		'description',                         // 本件概要
		
		'competiion_started_date',             // コンペ開始日
		
		'management_user_id',                  // 本件管理担当者
		'result_item_id',                      // 最終商品
		
		'material_list',                       // 主な使用前提原料・仕入品
		'condition_list',                      // コンペ依頼内容
		'competition_list',                    // コンペ・企画比較結果・進捗表
		'file_list',                           // 資料アップロード／資料名と資料概略
		
		'result_comment',                      // コンペ・企画の比較の総評と結論／商品化の方向
		'knowledge_comment',                   // 本案件で得た商品／業界の知見・一般情報・注意点
		'other_memo',                          // その他メモ
		'apploval_comment',                    // 承認者コメント
		
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
		'title',                               // コンペ・企画件名
		'description',                         // 本件概要
		'material_list',                       // 主な使用前提原料・仕入品
		'condition_list',                      // コンペ依頼内容
		'competition_list',                    // コンペ・企画比較結果・進捗表
		'file_list',                           // 資料アップロード／資料名と資料概略
		'result_comment',                      // コンペ・企画の比較の総評と結論／商品化の方向
		'knowledge_comment',                   // 本案件で得た商品／業界の知見・一般情報・注意点
		'other_memo',                          // その他メモ
		'apploval_comment',                    // 承認者コメント
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
    	$data['material_list']    = json_decode($data['material_list'], true);
		$data['condition_list']   = json_decode($data['condition_list'], true);
    	$data['competition_list'] = json_decode($data['competition_list'], true);
    	$data['file_list']        = json_decode($data['file_list'], true);
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
     * 次の備品資材ID (CP＋西暦下二桁＋5桁)
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
					
					return 'CP' . $year . $nextAlphabet . '0001';
				} 
				return 'CP' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'CP' . $year . '0' . '0001';
    }
}

