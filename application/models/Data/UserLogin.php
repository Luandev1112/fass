<?php
/**
 * class Shared_Model_Data_UserLogin
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_UserLogin extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_user_login';

    protected $_fields = array(
        'id',                    // ID
        
        'user_id',               // ユーザーID
        'login_result',          // ログイン結果
        'failed_mail',           // 失敗したメールアドレス
        'failed_password',       // 失敗したパスワード
        'ip_address',            // ログインIPアドレス
        'created',               // 作成日
        'updated',               // 更新日
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'failed_mail',
        'failed_password',
    );

    /**
     * 新規登録
     * @param boolean $result
     * @param int     $userId
     * @param string  $mail
     * @param string  $password
     * @param string  $ipAddress
     * @return boolean
     */
    public function addLog($result, $userId, $mail, $password, $ipAddress)
    {
    	if ($result === true) {
			// ログイン
			$mail = '';
			$password = '';	
    	}

    	$params = array(
    		'user_id'         => $userId,
    		'login_result'    => $result,
    		'failed_mail'     => $mail,
    		'failed_password' => $password,
    		'ip_address'      => $ipAddress,
    		'created'         => new Zend_Db_Expr('NOW()'),
    		'updated'         => new Zend_Db_Expr('NOW()'),
    	);
        
        return $this->create($params);
    }
    
    /**
     * ログイン履歴
     * @param int $userId
     * @return array
     */
    public function getLatestWithUserId($userId)
    {
   		$selectObj = $this->select();
		$selectObj->where('user_id = ?', $userId);
		$selectObj->order('id DESC');
		$selectObj->limit(6);
        return $selectObj->query()->fetchAll();
    }

}