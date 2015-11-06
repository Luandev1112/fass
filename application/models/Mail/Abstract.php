<?php

/**
 * Abstaractメールクラス
 * 
 * @package Shared
 */
abstract class Shared_Model_Mail_Abstract
{
	protected $_config = null;
	protected $_mailer;
	
    /**
     * getConfig
     *
     * @param void
     * @return Zend_Config
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = Nutex_Util_ConfigFactory::createByPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'contact.ini', APPLICATION_ENV);
        }
        return $this->_config;
    }
	
	function __construct()
	{
        require_once('PHPMailer_5.2.1/class.phpmailer.php');
        
        mb_language("japanese");           // 言語(日本語)
        mb_internal_encoding("UTF-8");     // 内部エンコーディング(UTF-8)
        
        $this->getConfig();
        
        $this->_mailer = new PHPMailer();                            // PHPMailerのインスタンス生成
        $this->_mailer->CharSet  = "iso-2022-jp";                    // 文字コード設定
        $this->_mailer->Encoding = "7bit";                           // エンコーディング
        
        $this->_mailer->IsSMTP();                                    //「SMTPサーバーを使う」設定
        $this->_mailer->SMTPAuth = true;                             //「SMTP認証を使う」設定
        
        
        $this->_mailer->Host     = $this->_config->get('smtp_host') . ':' . $this->_config->get('smtp_port');  // SMTPサーバーアドレス:ポート番号
        $this->_mailer->Username = $this->_config->get('smtp_user'); // SMTP認証用のユーザーID
        $this->_mailer->Password = $this->_config->get('smtp_pass'); // SMTP認証用のパスワード
		
		$this->_mailer->From     = $this->_config->get('from_mail'); // 差出人(From)をセット
		
	}

    /**
     * 管理者を送信先に設定
     * @return void
     */
    public function setToAdmin()
    {
        $admins = $this->_config->get('admin_mail')->toArray();
		
        foreach ($admins as $each) {
            if ($each != '') {
                $this->_mailer->AddAddress($each);
            }
        }
    }

    /**
     * メール送信
     * @return void
     */
	public function send($params)
	{		
		if (!empty($params['toMail'])) {
			// 宛先(To)をセット
			$this->_mailer->AddAddress($params['toMail']);
		}
		                               
		$this->_mailer->Subject  = mb_encode_mimeheader($params['subject'],'ISO-2022-JP');  // 件名(Subject)をセット
		$this->_mailer->Body     = mb_convert_encoding($params["body"], "JIS", "UTF-8");    // 本文(Body)をセット
		
		// メールを送信
		if (!$this->_mailer->Send()){
            $log_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'mail' . DIRECTORY_SEPARATOR . date('Ymd') . '.log';
            
            // ファイル存在チェック
            $chmod_flag = false;
            if (!file_exists($log_path)) {
                $chmod_flag = true;
            }
			$writer = new Zend_Log_Writer_Stream($log_path);
            if ($chmod_flag) {
                chmod($log_path, 0666);
            }
			$logger = new Zend_Log($writer);
			
			if (!empty($this->_mailer->ErrorInfo)) {
				$logger->log(get_class($this) . $this->_mailer->ErrorInfo, Zend_Log::INFO);
			}

		}
	}


    /**
     * @return string
     */
    protected function _getFooter()
    {

return <<<EOF

=============================
販売管理システム自動送信メール
=============================
EOF;

    }

}

