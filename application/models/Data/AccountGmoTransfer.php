<?php
/**
 * class Shared_Model_Data_AccountGmoTransfer
 * GMO振込予約履歴
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountGmoTransfer extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_gmo_transfer';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'payable_id',
        'status',                              // ステータス
        
        'account_id',                          // 口座ID
        
        'transfer_designated_date',            // 振込指定日
        'apply_no',                            // 受付番号（振込申請番号）
        'item_id',                             // 明細番号
        
        'result_code',                         // 結果コード     1:完了　2：未完了
        'apply_end_datetime',                  // 振込依頼完了日時
        
        'transfer_status',                     // 振込ステータス
        
        'unable_detail_info',                  // 不能明細情報
        
        
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'unable_detail_info',                  //  不能明細情報
    );
    
    /**
     * IDで取得
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }
    
    
    /**
     * ステータス確認リスト取得
     * @param int none
     * @return array
     */
    public function getConfirmList()
    {
    	$selectObj = $this->select();
    	$selectObj->where('result_code = ?', Shared_Model_Code::GMO_API_TRANSFER_RESULT_CODE_UNAPPLOVED);
    	$selectObj->group('apply_no');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * ステータス確認リスト取得(口座別)
     * @param int $gmoBankAccountid
     * @return array
     */
    public function getConfirmListByGmoBankAccountId($gmoBankAccountId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('account_id = ?', $gmoBankAccountId);
    	$selectObj->where('result_code = ?', Shared_Model_Code::GMO_API_TRANSFER_RESULT_CODE_UNAPPLOVED);
    	$selectObj->group('apply_no');
    	//var_dump($selectObj->__toString());exit;
    	
    	return $selectObj->query()->fetchAll();
    }
    
    
    /**
     * 申請番号でリスト取得
     * @param int $applyNo
     * @return array
     */
    public function getListByApplyNo($applyNo)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_account_payable', 'frs_account_gmo_transfer.payable_id = frs_account_payable.id', array('total_amount'));
    	$selectObj->joinLeft('frs_connection', 'frs_account_payable.target_connection_id = frs_connection.id', array($this->aesdecrypt('company_name', false) . 'AS company_name'));
    	$selectObj->where('apply_no = ?', $applyNo);
    	//var_dump($selectObj->__toString());exit;
    	
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 更新
     * @param int $applyNo
     * @param array $columns
     * @return boolean
     */
    public function updateByApplyNo($applyNo, $columns)
    {
		return $this->update($columns, array('apply_no' => $applyNo));
    }
    
    /**
     * 更新
     * @param int $applyNo
     * @param int $itemId
     * @param array $columns
     * @return boolean
     */
    public function updateByApplyNoAndItemId($applyNo, $itemId, $columns)
    {
		return $this->update($columns, array('apply_no' => $applyNo, 'item_id' => $itemId));
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

