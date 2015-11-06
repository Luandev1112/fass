<?php
/**
 * class Shared_Model_Data_EstimateVersion
 * 見積書バージョンデータ
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_EstimateVersion extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_estimate_version';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'estimate_id',                         // 見積書ID
        'version_id',                          // バージョンID
		'version_status',                      // バージョンステータス
		'is_copied',                           // コピー済みか
		
		'target_connection_id',                // 提出先取引ID
		'to_name',                             // 宛先
		'title',                               // タイトル
		'template_id',                         // 選択済みテンプレートID
		
		'labels',                              // テーブル項目ラベル
		'item_list',                           // テーブル中身
		
		'memo',                                // 備考
		'memo_private',                        // 社内メモ
		
		'approval_comment',                    // 承認コメント
		
		'file_name',                           // フリーアップロード 保存ファイル名
		
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
		'memo',                                // 備考
		'memo_private',                        // 社内メモ
		'approval_comment',                    // 承認コメント
		
		'file_name',                           // フリーアップロード 保存ファイル名
    );
    
    /**
     * バージョンリスト取得
     * @param int $estimateId
     * @return boolean
     */
    public function getListByEstimateId($estimateId)
    {
        $selectObj = $this->select();
    	$selectObj->where('estimate_id = ?', $estimateId);
    	$selectObj->order('version_id DESC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 作成中バージョン取得
     * @param int $estimateId
     * @return boolean
     */
    public function getMakingVersionByEstimateId($estimateId)
    {
        $selectObj = $this->select();
    	$selectObj->where('estimate_id = ?', $estimateId);
    	$selectObj->where('version_status = ?', Shared_Model_Code::ESTIMATE_VERSION_STATUS_MAKING);
    	$data = $selectObj->query()->fetch();
    	$data['labels'] = json_decode($data['labels'], true);
    	$data['item_list'] = json_decode($data['item_list'], true);
    	return $data;
    }

    /**
     * 提出済バージョン取得
     * @param int $estimateId
     * @return boolean
     */
    public function getSubmittedVersionByEstimateId($estimateId)
    {
        $selectObj = $this->select();
    	$selectObj->where('estimate_id = ?', $estimateId);
    	$selectObj->where('version_status = ?', Shared_Model_Code::ESTIMATE_VERSION_STATUS_SUBMITTED);
    	$data = $selectObj->query()->fetch();
    	$data['labels'] = json_decode($data['labels'], true);
    	$data['item_list'] = json_decode($data['item_list'], true);
    	return $data;
    }
     
    /**
     * 次のバージョンID
     * @param  int $estimateId
     * @return int $nextId
     */
    public function getNextVersionId($estimateId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('estimate_id = ?', $estimateId);
    	$selectObj->order('version_id DESC');
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return (int)$data['version_id'] + 1;
        }
        return 1;
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
    	$data['labels'] = json_decode($data['labels'], true);
    	$data['item_list'] = json_decode($data['item_list'], true);
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

