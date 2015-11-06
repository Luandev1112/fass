<?php
/**
 * class Nutex_Validate_Zipcode
 *
 * 郵便番号バリデータ
 *
 * @todo 日本の形式のみ対応しています
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Zipcode extends Nutex_Validate_Abstract_Regex
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "郵便番号の形式で入力して下さい",
    );

    /**
     * 正規表現 preg系の定義で定義する
     * @var string
     */
    protected $_pattern = '/^[0-9]{3}\-?[0-9]{4}$/';

    /**
     * isValid
     * Defined by Zend_Validate_Interface
     *
     * @param void
     * @return string
     */
    public function isValid($value)
    {
        if (is_array($value)) {
            $value = implode('-', $value);
        }
        return parent::isValid($value);
    }
}
