<?php
/**
 * class Shared_Model_Data_ConnectionBank
 * 取引先金融機関管理
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionBank extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_connection_bank';

    protected $_fields = array(
        'id',                                  // ID
        'connection_id',                       // 取引先ID
        'status',                              // ステータス
        'is_confirmed',                        // 最終確認済
        
        'bank_registered_type',                // 登録種別
        
        'target_id',                           // 対象supplier/buyer id
        'target_display_id',                   // 対象supplier/buyer 表示id
        
        'bank_code',                           // 金融機関コード
        'bank_name',                           // 金融機関名
        
        'branch_code',                         // 支店コード
        'branch_name',                         // 支店名
        
        'account_type',                        // 口座種別
        'account_no',                          // 口座番号
        
        'account_name',                        // 口座名義
        'account_name_kana',                   // 口座名義(カナ)
        'memo',                                // 備考
        
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'bank_code',                           // 金融機関番号
        'bank_name',                           // 金融機関名
        
        'branch_code',                         // 支店コード
        'branch_name',                         // 支店名

        'account_no',                          // 口座番号
        
        'account_name',                        // 口座名
        'account_name_kana',                   // 口座名義(カナ)
        'memo',                                // 備考
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
     * 取引先金融機関一覧
     * ＠param int $connectionId
     * @return boolean
     */
    public function getListByConnectionId($connectionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->order('id ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 登録種別種別ごと
     * ＠param int $connectionId
     * ＠param int $bankRegisteredType
     * @return boolean
     */
    public function getByRegisteredType($connectionId, $bankRegisteredType)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->where('bank_registered_type = ?', $bankRegisteredType);
    	
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 登録種別種別ごと
     * ＠param int $connectionId
     * ＠param int $bankRegisteredType
     * ＠param int $targetId
     * @return boolean
     */
    public function getByRegisteredTypeAndId($connectionId, $bankRegisteredType, $targetId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->where('bank_registered_type = ?', $bankRegisteredType);
    	$selectObj->where('target_id = ?', $targetId);
    	
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

}

