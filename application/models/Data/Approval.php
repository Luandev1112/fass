<?php
/**
 * class Shared_Model_Data_Approval
 * 承認履歴
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Approval extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_approval';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
		'type',                                // 種別
		
		'authorizer_user_id',                  // 承認者ユーザーID
		'applicant_user_id',                   // 申請者ユーザーID
		
		'target_id',                           // 対象のID(種別に応じて)
		
		'title',                               // タイトル
		
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
     * 承認待ち件数
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function getPendingCount($userId) {
        $selectObj = $this->select(array(new Zend_Db_Expr('COUNT(`id`) as item_count')));
        $selectObj->where('authorizer_user_id = ?', $userId);
        $selectObj->where('status = ?', Shared_Model_Code::APPROVAL_STATUS_PENDDING);
        
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }

}

