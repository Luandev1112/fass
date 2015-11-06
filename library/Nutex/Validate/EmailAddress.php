<?php
/**
 * class Nutex_Validate_EmailAddress
 *
 * メールアドレスバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_EmailAddress extends Zend_Validate_EmailAddress
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID            => "メールアドレスの形式で入力して下さい",
        self::INVALID_FORMAT     => "メールアドレスの形式で入力して下さい",
        self::INVALID_HOSTNAME   => "存在しないメールアドレスのようです",
        self::INVALID_MX_RECORD  => "存在しないメールアドレスのようです",
        self::INVALID_SEGMENT    => "存在しないメールアドレスのようです",
        self::DOT_ATOM           => "メールアドレスの形式で入力して下さい",
        self::QUOTED_STRING      => "メールアドレスの形式で入力して下さい",
        self::INVALID_LOCAL_PART => "メールアドレスの形式で入力して下さい",
        self::LENGTH_EXCEEDED    => "メールアドレスとして長すぎます",
    );

    /**
     * @param array|Zend_Config $options OPTIONAL
     * @return void
     */
    public function __construct($options = array())
    {
        if (array_key_exists('hostname', $this->_options) && $this->_options['hostname'] === null) {
            $this->_options['hostname'] = new Nutex_Validate_Hostname();
        }
        parent::__construct($options);
    }
}
