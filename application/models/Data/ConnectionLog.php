<?php
/**
 * class Shared_Model_Data_ConnectionLog
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionLog extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection_log';

    protected $_fields = array(
        'id',                                  // ID
        'excutor_user_id',                     // 実行者ユーザーID
        'connection_id',                       // 取引先ID
		'type',                                // ログ種別
		
		'import_key',                          // 取込キー
		'result',                              // 取込結果
		'message',                             // メッセージ

        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時

    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'message',                             // メッセージ
    );
    
    
    /**
     * 基本情報
     * @param array $oldData
     * @return string $message
     */
    public function createDifferenceMessageBasic($oldData, $newData)
    {
    	$message = '';
    	
    	if ($oldData['company_name'] != $newData['company_name']) {
    		$message .= '<b>企業・機関名</b>';
    		$message .= '<hr>';
    		$message .= $oldData['company_name'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['company_name'] . "\n";
    		$message .= '<hr>';
    	}
    	
    	if ($oldData['company_name_kana'] != $newData['company_name_kana']) {
    		$message .= '<b>企業・機関名カナ</b>';
    		$message .= '<hr>';
    		$message .= $oldData['company_name_kana'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['company_name_kana'] . "\n";
    		$message .= '<hr>';
    	}

    	if ($oldData['description'] != $newData['description']) {
    		$message .= '<b>事業内容</b>';
    		$message .= '<hr>';
    		$message .= $oldData['description'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['description'] . "\n";
    		$message .= '<hr>';
    	}
    	
    	return $message;
    
    }
    
    
    /**
     * 担当者
     * @param array $oldData
     * @return string $message
     */
    public function createDifferenceMessageStaff($oldData, $newData)
    {
    	$message = '';
    	
    	if ($oldData['department_name'] != $newData['department_name']) {
    		$message .= '<b>所属</b>';
    		$message .= '<hr>';
    		$message .= $oldData['department_name'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['department_name'] . "\n";
    		$message .= '<hr>';
    	}

    	if ($oldData['staff_position'] != $newData['staff_position']) {
    		$message .= '<b>役職</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_position'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_position'] . "\n";
    		$message .= '<hr>';
    	}

    	if ($oldData['staff_tel'] != $newData['staff_tel']) {
    		$message .= '<b>電話番号</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_tel'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_tel'] . "\n";
    		$message .= '<hr>';
    	}

    	if ($oldData['staff_fax'] != $newData['staff_fax']) {
    		$message .= '<b>FAX番号</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_fax'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_fax'] . "\n";
    		$message .= '<hr>';
    	}	  	

    	if ($oldData['staff_mobile'] != $newData['staff_mobile']) {
    		$message .= '<b>携帯</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_mobile'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_mobile'] . "\n";
    		$message .= '<hr>';
    	}	

    	if ($oldData['staff_mail'] != $newData['staff_mail']) {
    		$message .= '<b>メールアドレス</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_mail'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_mail'] . "\n";
    		$message .= '<hr>';
    	}	

    	if ($oldData['staff_postal_code'] != $newData['staff_postal_code']) {
    		$message .= '<b>郵便番号</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_mail'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_mail'] . "\n";
    		$message .= '<hr>';
    	}
    	
    	if ($oldData['staff_address'] != $newData['staff_address']) {
    		$message .= '<b>住所</b>';
    		$message .= '<hr>';
    		$message .= $oldData['staff_address'] . "\n";
    		$message .= '<i class="icon-down-circled-1"></i>' . "\n";
    		$message .= $newData['staff_address'] . "\n";
    		$message .= '<hr>';
    	}
    	
    	if (!empty($message)) {
    		$message = "担当者：" . $newData['staff_name'] . "\n\n" . $message;
    	
    	}
    	
    	
    	return $message;
    
    }
    
}

