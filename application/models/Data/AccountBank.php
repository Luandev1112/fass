<?php
/**
 * class Shared_Model_Data_AccountBank
 * 金融機関定義
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_AccountBank extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_account_bank';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'status',                              // ステータス
        
        'bank_code',                           // 金融機関コード
        'bank_name',                           // 金融機関名
        
        'branch_code',                         // 支店コード
        'branch_name',                         // 支店名
        
        'account_type',                        // 口座種別
        'account_no',                          // 口座番号
        
        'account_name',                        // 口座名義
        'account_name_kana',                   // 口座名義(カナ)
        'short_name',                          // 略名
        
        'gmo_account_id',                      // GMO契約アカウントID(FASS上のGMOアカウントID)
        'gmo_bank_account_id',                 // GMO口座アカウントID (APIから取得して保存)
        
        'content_order',                       // 並び順     

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
     * 一覧
     * @return boolean
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('management_group_id ASC');
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * GMO口座一覧
     * @return boolean
     */
    public function getGMOBankList()
    {
    	$selectObj = $this->select();
    	$selectObj->where($this->aesdecrypt('bank_code', false) . ' = 0310');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * GMO契約アカウント別口座リスト
     * @return array
     */
    public function getGMOBankListWithGmoId($gmoAccountId)
    {
    	$selectObj = $this->select();
    	$selectObj->where($this->aesdecrypt('bank_code', false) . ' = 0310');
    	$selectObj->where('gmo_account_id = ?', $gmoAccountId);
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * GMO口座番号から取得
     * @return array
     */
    public function getGMOBank($accountNo)
    {
    	$selectObj = $this->select();
    	$selectObj->where($this->aesdecrypt('bank_code', false) . ' = 0310');
    	$selectObj->where($this->aesdecrypt('account_no', false) . ' = ?', $accountNo);
    	return $selectObj->query()->fetch();
    }

    /**
     * GMO口座IDから取得
     * @return array
     */
    public function getGMOBankBankAccountId($gmoBankAccountId)
    {
    	$selectObj = $this->select();
    	$selectObj->where($this->aesdecrypt('bank_code', false) . ' = 0310');
    	$selectObj->where('gmo_bank_account_id = ?', $gmoBankAccountId);
    	return $selectObj->query()->fetch();
    }
    /**
     * 次の並び順
     * @param none
     * @return array
     */
    public function getNextContentOrder()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
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

