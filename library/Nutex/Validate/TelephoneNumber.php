<?php
/**
 * class Nutex_Validate_TelephoneNumber
 *
 * 電話番号バリデータ
 *
 * @todo 日本の形式のみ対応しています 割と簡易的なチェック
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_TelephoneNumber extends Nutex_Validate_Abstract_Regex
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "電話番号の形式で入力して下さい",
    );

    /**
     * 正規表現 preg系の定義で定義する
     * @var string
     */
    protected $_pattern = '[0-9]{1,5}\-[0-9]{1,5}\-[0-9]{1,5}';

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
