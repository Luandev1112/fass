<?php
/**
 * class Shared_Model_Mail_LoginPassword
 *
 * ログイン情報通知
 *
 * @package Shared
 * @subpackage Shared_Model_Mail
 */
class Shared_Model_Mail_LoginPassword extends Shared_Model_Mail_Abstract
{
   /**
    * constructMail
    *
    * @param mixed $input
    * @return $this
    */
    public function sendMail($input = null)
    {
        if ($input == null) {
            return $this;
        }
		
		$params = array();
		
		// メール送信先
		$params['toMail']  = $input['mail'];

		$this->_mailer->FromName = mb_encode_mimeheader($this->_config->get('from_name'), 'ISO-2022-JP'); // 差出人(From名)をセット
		$params['subject'] = '★FASS★ パスワード';
		$params["body"]    = $this->createBody($input);

        $this->send($params);
    }
    
    
   /**
    * createBody
    *
    * @param mixed $input
    * @return $this
    */
    public function createBody($input)
    {

return <<< EOF
[ログインパスワード]======================
{$input['password']}
EOF;
   


	}
     
}


