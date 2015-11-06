<?php
/**
 * class Shared_Model_Data_User
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_User extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_user';

    protected $_fields = array(
        'id',                    // ID
        
        'management_group_id',   // 管理グループID
        'parent_user_id',        // 親アカウントユーザーID
        
        'user_department_id',    // 所属部署ID
        'display_id',            // 表示ID
        'user_type',             // ユーザー種別
        'is_master',             // マスター権限ユーザー(複数組織管理)
        
        'limit_shipping',        // ページ制限：出荷担当
        'limit_production',      // ページ制限：製造担当
        
        'allow_editing_accounting_title',    // 会計科目編集権限
        'allow_editing_search_tag',          // 検索用タグ編集権限
		'allow_delete_row_data',             // データ削除権限
		'allow_connection_progress_master',  // 営業案件管理項目定義
		'allow_connection_progress_tag',     // 営業案件管理発足名称定義
		'allow_cancel_finish_attach',         // 会計割当完了解除権限
		
        'mail',                  // メールアドレス
        'mail_hash',             // メールアドレスハッシュ値
        'password',              // パスワード
        
        //'gmo_reflesh_token',     // GMOあおぞら用リフレッシュトークン
        
        'app_passcode',          // 在庫管理アプリ用パスコード
        
        'status',                // ステータス
        
        'user_name',             // 担当者名
        'user_name_en',          // 担当者名英語
        'position_name',         // 役職名
        'employment_condition',  // 雇用形態
        'remarks',               // 備考
        
        'approver_1_user_id',     // 承認者1
        'approver_2_user_id',     // 承認者2
        'approver_3_user_id',     // 承認者3
        
        'approver_a_user_id',     // 経営管理（A）
        'approver_b1_user_id',    // 経理管理者（B1）
        'approver_b2_user_id',    // 経理担当（B2）
        'approver_c1_user_id',    // 営業管理者（C1）
        'approver_c2_user_id',    // 営業担当者（C2）
        'approver_d_user_id',     // 閲覧者権限（D）
        
        'last_logined',           // 最終ログイン

        'created',                // アカウント作成日
        'updated',                // 更新日
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'mail',                  // メールアドレス
        'password',              // パスワード
        'gmo_reflesh_token',     // GMOあおぞら用リフレッシュトークン
        'app_passcode',          // 在庫管理アプリ用パスコード
    	'user_name',             // 担当者名
    	'user_name_en',          // 担当者名英語
    	'position_name',         // 役職名
		'remarks',               // 備考
    );

    /**
     * 表示IDがすでに使用されているか
     * @param int $displayId
     * @param int $exceptId  除外ID
     * @return array
     */
    public function isUsedDisplayId($managementGroupId, $displayId, $exceptId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('display_id = ?', $displayId);
    	
    	if (!empty($exceptId)) {
    		$selectObj->where('id != ?', $exceptId);
    	}
    	
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return true;
    	}
    	
    	return false;
    }
    
    /**
     * 新規登録
     * @param array  $params
     * @return array
     */
    public function register($params)
    {
    	$params['status']       = Shared_Model_Code::USER_STATUS_ACTIVE; 
    	$params['mail_hash']    = $this->mailHash($params['mail']);
    	$params['last_logined'] = new Zend_Db_Expr('NOW()');
    	$params['created']      = new Zend_Db_Expr('NOW()');
        $params['updated']      = new Zend_Db_Expr('NOW()');
        
        $this->getAdapter()->beginTransaction();
        
        try {
            // ★userデータ登録 ---------------------------------------
            $insertResult = $this->create($data);
            if (!$insertResult) {
                throw new Zend_Exception();
            }
            
            // 作成したユーザーIDを取得
			$userId = $this->getLastInsertedId('id');

		                
            // commit
            $this->getAdapter()->commit();

        } catch (Exception $e) {
            $this->getAdapter()->rollBack();
            throw new Zend_Exception('register failed' . $e);
        }

        return $this->getByUserId($userId);
    }
    
    /**
     * 更新
     * @param int   $userId
     * @param array $columns
     * @return boolean
     */
    public function updateByUserId($userId, $columns)
    {
		return $this->update($columns, array('id' => $userId));
    }

    /**
     * メール重複チェック
     * メールアドレス自体は暗号化され、mail_hashにユニーク制約がついている
     *
     * @param string $mail
     * @param int    $escapeUserId
     * @return boolean
     */
     /*
    public function mailIsDuplicated($mail, $escapeUserId = null)
    {
        $condition = array(
            array('mail_hash = ?', $this->mailHash($mail)),
        );
        
        if (!empty($id)) {
            $condition[] = array('id != ?', $escapeUserId, Zend_Db::BIGINT_TYPE);
        }

        if ($this->find($condition)) {
            return true;
        } else {
            return false;
        }

        return false;
    }
    */
    
    /**
     * アプリパスコード重複チェック
     *
     * @param string $managementGroupId
     * @param string $passCode
     * @param int    $escapeUserId
     * @return boolean
     */
     /*
    public function passcodeIsDuplicated($managementGroupId, $passCode, $escapeUserId = null)
    {
        $selectObj = $this->select();
        $selectObj->where('management_group_id = ?', $managementGroupId);
        
        $dbAdapter = $this->getAdapter();			
        $whereString = $dbAdapter->quote($this->aesdecrypt('app_passcode', false) . ' = ' . $passCode);
        $selectObj->where($whereString);
        
        if (!empty($escapeUserId)) {
	        $selectObj->where('id != ?', $escapeUserId);
	    }
        
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
	        return true;
        }
        
        return false;
    }
    */
    
    /**
     * 会員認証処理
     * @param string $mail
     * @param string $password
     * @return array | boolean
     */

    public function authorize($mail, $password)
    {
        $data = $this->find(array(
            array('mail_hash = ?', $this->mailHash($mail)),
            array('status = ?', Shared_Model_Code::USER_STATUS_ACTIVE),
            array('user_type = ?', Shared_Model_Code::USER_TYPE_MEMBER),
        ));
		
		if ($data) {
	        if ($data['password'] === $password) {
	            // ログイン成功
	            $result = $data;
	            unset($result['password']);
	            $result['login_result'] = true;
	            return $result;   
	        } else {
	        	// パスワード不一致
				return array(
					'login_result'  => false,
					'id'            => $data['id'],
				);
	        }
        }
        
		return array(
			'login_result'  => false,
			'id'            => 0,
		);
    }
    
    /**
     * パスコード認証処理
     * @param string $id
     * @param string $passCode
     * @return array | boolean
     */

    public function authorizePassCode($id, $passCode)
    {
        $selectObj = $this->select();
        $selectObj->where('id = ?', $id);
        
        $dbAdapter = $this->getAdapter();
        
        $selectObj->where('app_passcode IS NOT NULL');
        $whereString1 = $this->aesdecrypt('app_passcode', false) . " != ''";
        $selectObj->where($whereString1);
        
        		
        $whereString2 = $this->aesdecrypt('app_passcode', false) . ' = ' . $dbAdapter->quote($passCode);
        $selectObj->where($whereString2);
        
        
        $data = $selectObj->query()->fetch();
        
		if (!empty($data)) {
            // ログイン成功
            $result = $data;
            $result['login_result'] = true;
            return $result;   
        } else {
        	// パスワード不一致
			return array(
				'login_result'  => false,
				'id'            => $data['id'],
				'password'      => '',
			);
        }
        
		return array(
			'login_result'  => false,
			'id'            => 0,
			'password'      => '',
		);
    }
    
    
    
    

    /**
     * 管理者認証処理
     * @param string $mail
     * @param string $password
     * @return array | boolean
     */

    public function authorizeAdmin($mail, $password)
    {
	    $selectObj = $this->select();
    	$selectObj->where('mail_hash = ?', $this->mailHash($mail));
    	$selectObj->where('status IN (?)', array(Shared_Model_Code::USER_STATUS_ACTIVE, Shared_Model_Code::USER_STATUS_COOPERATIVE));
    	$selectObj->where('user_type = ?', Shared_Model_Code::USER_TYPE_ADMIN);
    	$selectObj->order('frs_user.id ASC');
        $data = $selectObj->query()->fetch();
        

		if ($data) {
	        if ($data['password'] === $password) {
	            // ログイン成功
	            $result = $data;
	            unset($result['password']);
	            $result['login_result'] = true;
	            return $result;
	        } else {
	        	// パスワード不一致
				return array(
					'login_result'  => false,
					'id'            => $data['id'],
				);
	        }
        }
        
		return array(
			'login_result'  => false,
			'id'            => 0,
		);
    }

    /**
     * リセットトークン発行
     * @param string $mail
     * @return string | boolean
     */

    public function issueResetToken($mail)
    {
        $result = $this->find(array(
            array('mail_hash = ?', $this->mailHash($mail)),
            array('status = ?', Shared_Model_Code::USER_STATUS_ACTIVE),
        ));

        if (!empty($result)) {
        	$token = md5(uniqid(rand(), true));
            $this->updateByUserId($result['id'], array('reset_token' => $token));
            return $result['id'];
            
        } else {
            return false;
        }
    }
    
    /**
     * リセットトークン発行
     * @param string $mail
     * @return string | boolean
     */

    public function geByResetToken($token)
    {
        return $this->find(array(
            array('reset_token = ?', $token),
            array('status = ?', Shared_Model_Code::USER_STATUS_ACTIVE),
        ));
    }
    
    /**
     * 最終ログイン更新
     *
     * @param int $id
     * @param int $version
     * @return string
     */
     /*
    public function updateLastLogin($id, $version)
    {
        $date = Nutex_Date::getDefaultInstance()->getZendDate();
        if ($this->updateWithVersion(array('last_logined' => $date), array('id' => $id), $version)) {
            return $date->get('yyyy-MM-dd HH:mm:ss');
        }
        return null;
    }
    */
        
    /**
     * mailHash
     *
     * @param string $mail
     * @return string
     */
    public function mailHash($mail)
    {
        return hash('sha512', $mail);
    }

    /**
     * ユーザー情報取得
     * @param int  $userId
     * @return array
     */
    public function getById($userId)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_user_department', 'frs_user_department.id = frs_user.user_department_id', array(
    		$this->aesdecrypt('department_name', false) . 'AS department_name',
			$this->aesdecrypt('department_name_en', false) . 'AS department_name_en',
			'is_accountants_office',
    	));
    	
    	$selectObj->where('frs_user.id = ?', $userId);
        $data = $selectObj->query()->fetch();
        if ($data) {
            unset($data['password']);
            return $data;       
        }
        
        return NULL;
    }
    
    /**
     * ユーザー情報取得
     * @param int  $userId
     * @return array
     */
    public function getByUserId($userId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $userId);
    	$selectObj->where('status = ?', Shared_Model_Code::USER_STATUS_ACTIVE);
        $data = $selectObj->query()->fetch();
        if ($data) {
            unset($data['password']);
            return $data;       
        }
        
        return NULL;
    }
    
    /**
     * ユーザー一覧取得
     * @param int  $userId
     * @param bool $isSelectObj
     * @return array
     */
    public function getListByUserType($userType, $isSelectObj = false)
    {
        $selectObj = $this->select();
		$selectObj->where('user_type = ?', $userType);
		
		if ($isSelectObj) {
			return $selectObj;
		}
		
		$selectObj->order('id ASC');
        return $selectObj->query()->fetchAll();
    }

    /**
     * 会員登録数(期間)
     * @param string $from
     * @param string  $to
     * @return int $count
     */
    public function getRegisteredCountWithTerm($from, $to)
    {
   		$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
		$selectObj->where('user_type = ?', Shared_Model_Code::USER_TYPE_MEMBER);
		$selectObj->where('registered_date >= ?', $from);
		$selectObj->where('registered_date <= ?', $to);
		
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    /**
     * 会員登録数(トータル)
     * @param none
     * @return int $count
     */
    public function getRegisteredCountTotal()
    {
   		$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
		$selectObj->where('user_type = ?', Shared_Model_Code::USER_TYPE_MEMBER);
		
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }

}