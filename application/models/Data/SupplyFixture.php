<?php
/**
 * class Shared_Model_Data_SupplyFixture
 * 備品資材 仕入・調達先
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_SupplyFixture extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_supply_fixture';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'project_id',                          // プロジェクトID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
		'status',                              // ステータス
		'history_memo',                        // 変更履歴等メモ
		
		'title',                               // 備品資材名
		'description',                         // 備品資材内容
		
		'individual_name',                     // 仕入先毎呼称
		'target_connection_id',                // 取引先ID
		
		'base_name',                           // 取引拠点名
		
		'uses',                                // 用途
		'use_memo',                            // 用途メモ
		
		'methods',                             // 委託方法
		'method_memo',                         // 委託方法メモ
		
		'condition_list',                      // 購入条件(ロット別)
		
		'file_list',                           // 入手見積書

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
		'use_memo',                            // 目的メモ
		'method_memo',                         // 委託方法メモ
		'condition_list',                      // 購入条件(ロット別)
		'file_list',                           // 入手見積書
    );

    /**
     * 仕入・調達先リスト取得
     * @param int $managementGroupId
     * @param int $projectId
     * @return array
     */
    public function getListByProjectId($managementGroupId, $projectId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_connection', 'frs_supply_fixture.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name', 'display_id AS connection_display_id'));
    	$selectObj->where('frs_supply_fixture.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_supply_fixture.project_id = ?', $projectId);
    	$selectObj->order('frs_supply_fixture.id ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 仕入・調達先リスト(検討中・採用中)取得
     * @param int $managementGroupId
     * @param int $projectId
     * @return array
     */
    public function getActiveListByProjectId($managementGroupId, $projectId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_connection', 'frs_supply_fixture.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name', 'display_id AS connection_display_id'));
    	$selectObj->where('frs_supply_fixture.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_supply_fixture.project_id = ?', $projectId);
    	$selectObj->where('frs_supply_fixture.status IN (?)', array(Shared_Model_Code::SUPPLY_STATUS_CONSIDERING, Shared_Model_Code::SUPPLY_STATUS_USING));
    	$selectObj->order('frs_supply_fixture.id ASC');
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
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	$data['methods']        = unserialize($data['methods']);
    	$data['condition_list'] = json_decode($data['condition_list'], true);
    	$data['file_list']      = json_decode($data['file_list'], true);
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


}

