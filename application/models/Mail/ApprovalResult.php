<?php
/**
 * class Shared_Model_Mail_ApprovalResult
 *
 * 承認申請通知メール
 *
 * @package Shared
 * @subpackage Shared_Model_Mail
 */
class Shared_Model_Mail_ApprovalResult extends Shared_Model_Mail_Abstract
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
		$params['toMail']  = $input['to'];

		$this->_mailer->FromName = mb_encode_mimeheader($this->_config->get('from_name'), 'UTF-8'); // 差出人(From名)をセット
		
		$resultText = '★FASS★ 承認済み';
		
		if ((string)$input['approval_status'] === (string)Shared_Model_Code::APPROVAL_STATUS_MOD_REQUEST) {
			$resultText = '★FASS★ 修正依頼';
		} else if ((string)$input['approval_status'] === (string)Shared_Model_Code::APPROVAL_STATUS_REJECTED) {
			$resultText = '★FASS★ 却下';
		}
		
		$params['subject'] = $resultText . '：' . $input['type'];
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
[種別]=======================
{$input['type']}

[内容]=======================
{$input['content']}

[修正依頼コメント]===========
{$input['approval_comment']}

EOF;

	}
     
}


