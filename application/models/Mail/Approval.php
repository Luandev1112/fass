<?php
/**
 * class Shared_Model_Mail_Approval
 *
 * 承認申請通知メール
 *
 * @package Shared
 * @subpackage Shared_Model_Mail
 */
class Shared_Model_Mail_Approval extends Shared_Model_Mail_Abstract
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
		$params['subject'] = '★FASS★ 承認申請　申請者：' . $input['name'];
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

	$organization = '';
	if (!empty($input['managment_group_name'])) {
		$organization .= '[組織]=======================' . "\n";
		$organization .= $input['managment_group_name'];
	}


return <<< EOF
{$organization}

[種別]=======================
{$input['type']}

[内容]=======================
{$input['content']}

[申請者]=====================
{$input['name']}
{$input['user_id']}
EOF;

	}
     
}


