<?php
/**
 * class Shared_Model_Data_Setting
 * 共通設定
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Setting extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_setting';

    protected $_fields = array(
        'id',                    // ID

        'gmo_access_token_fr',                    // GMOあおぞら銀行(フレスコ)アクセストークン
        'gmo_access_token_fr_expired_datetime',   // GMOあおぞら銀行(フレスコ)アクセストークン有効期限
        
		'gmo_reflesh_token_fr',                   // GMOあおぞら銀行(フレスコ)リフレッシュトークン
		'gmo_reflesh_token_fr_expired_datetime',  // GMOあおぞら銀行(フレスコ)リフレッシュトークン有効期限
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時  
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'gmo_reflesh_token_fr',  // GMO(フレスコ)リフレッシュトークン
    );

    /**
     * IDで取得
     * @param none
     * @return array
     */
    public function get()
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', '1');
    	$data = $selectObj->query()->fetch();
    	return $data;
    }

    /**
     * 更新
     * @param array $columns
     * @return boolean
     */
    public function updateColumn($columns)
    {
	    $data = $this->get();
		if (empty($data)) {
			$this->create(array(
				'created'               => new Zend_Db_Expr('now()'),
				'updated'               => new Zend_Db_Expr('now()'),
			));
		}
		return $this->update($columns, array('id' => '1'));
    }

}
