<?php
/**
 * class Shared_Model_Data_AccountReceivable
 * 売掛管理
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountReceivable extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_receivable';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        
        'template_id',                         // テンプレートID
        
        'status',                              // ステータス
        'payment_status',                      // 入金ステータス
        'is_attached',                         // 割当完了
        
        'relational_id',                       // 連携ID
        'relational_display_id',               // 連携表示ID
        
        'accrual_date',                        // 発生日
        
		'type',                                // 売掛管理種別
		'invoice_id',                          // 請求書ID
		
		'account_title_id',                    // 会計科目ID
		'account_totaling_group_id',           // 採算コード
		
		'target_connection_id',                // 支払元取引先ID
		'bank_sender_name',                    // 振込人名義(全角カタカナ)
		
		'currency_id',                         // 通貨ID
		'total_amount',                        // 入金予定額
		
		'bank_id',                             // 入金予定口座
		'card_id',                             // 入金予定カード
		'receive_plan_date',                   // 入金予定日
		'received_date',                       // 入金受取日
		
		'file_list',                           // 添付資料リスト
		
		'created_user_id',                     // 登録者ユーザーID
		'approval_user_id',                    // 承認者
		'approval_comment',                    // 修正依頼コメント
		
		'confirm_user_id',                     // 入金確認者ユーザーID
		'confirm_datetime',                    // 入金確認日
		
		'memo',                                // メモ
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'memo',                                // メモ
		
		'bank_sender_name',                    // 振込人名義(全角カタカナ)
		
		'file_list',                           // 添付資料リスト
		'approval_comment',                    // 修正依頼コメント
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
    	$selectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
    	$selectObj->where('frs_account_receivable.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_receivable.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
    		$data['file_list'] = json_decode($data['file_list'], true);
    	}
    	return $data;
    }

    /**
     * 請求書IDで取得
     * @param int $managementGroupId
     * @param int $invoiceId
     * @return array
     */
    public function getListByInvoiceId($managementGroupId, $invoiceId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_account_receivable.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_receivable.invoice_id = ?', $invoiceId);
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * IDで取得(割当用/全てのグループで取得可能)
     * @param int $id
     * @return array
     */
    public function getByIdForAnyGroup($id)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
    	$selectObj->where('frs_account_receivable.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
    		$data['file_list'] = json_decode($data['file_list'], true);
    	}
    	return $data;
    }
    
    /**
     * 毎月入金最新履歴
     * @param int $templateId
     * @return array
     */
    public function getLastestByTemplateId($templateId)
    {
    	$selectObj = $this->select();
		$selectObj->where('template_id = ?', $templateId);
		$selectObj->where('status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
		$selectObj->order('id DESC');
    	return $selectObj->query()->fetch();
    } 
    
    
    /**
     * 連携IDで取得
     * @param int $managementGroupId
     * @param int $relationalId
     * @return array
     */
    public function getByRelationalId($managementGroupId, $relationalId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_invoice', 'frs_account_receivable.invoice_id = frs_invoice.id', array('display_id AS invoice_display_id'));
    	$selectObj->where('frs_account_receivable.management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_account_receivable.relational_id = ?', $relationalId);
    	$selectObj->where('frs_account_receivable.status != ?', Shared_Model_Code::RECEIVABLE_STATUS_DELETED);
    	$data = $selectObj->query()->fetch();
    	if (!empty($data)) {
    		$data['file_list'] = json_decode($data['file_list'], true);
    	}
    	return $data;
    }
    
    
    /**
     * 採算コードで取得
     * @param int $managementGroupId
     * @param int $totalingGroupId
     * @return array
     */
    public function getItemsWithTotalingGroupId($managementGroupId, $totalingGroupId, $type, $from = NULL, $to = NULL)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('account_totaling_group_id = ?', $totalingGroupId);

		if ($type === 'settlement') {
	    	if (!empty($from)) {
		    	$selectObj->where('frs_account_receivable.receive_plan_date >= ?', $from);
	    	}
	
	    	if (!empty($to)) {
		    	$selectObj->where('frs_account_receivable.receive_plan_date <= ?', $to);
	    	}
    	} else {
	    	if (!empty($from)) {
		    	$selectObj->where('frs_account_receivable.accrual_date >= ?', $from);
	    	}
	
	    	if (!empty($to)) {
		    	$selectObj->where('frs_account_receivable.accrual_date <= ?', $to);
	    	}
    	}


    	return $selectObj->query()->fetchAll();
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

