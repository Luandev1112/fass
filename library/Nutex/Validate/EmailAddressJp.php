<?php
/**
 * class Nutex_Validate_EmailAddressJp
 *
 * メールアドレス(日本ガラパゴス向け)バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_EmailAddressJp extends Zend_Validate_Abstract
{

    /**
     * @var string
     */
    const INVALID = 'invalid';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID            => "メールアドレスの形式で入力して下さい",
    );
	

    /**
     * Defined by Zend_Validate_Interface
     *
     * ===メールアドレスが不正でないか検証===
     * ユーザー名は記号が割と何でもあり
       @以降は半角英数 . 半角英数2文字以上
     * 
     * @param  string  $value
     * @return boolean
     */
    public function isValid($value)
    {
		if ($value === '') {
            return true;
        }
		
        $valueString = (string) $value;

        $this->_setValue($valueString);

        $regex = '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i';
        if (preg_match($regex, $value, $matches)) {
            return true;
        } else {
            $this->_error(self::INVALID);
            return false;
        }
    }
}
