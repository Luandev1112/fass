<?php
/**
 * class Nutex_Validate_Digits
 *
 * 半角数字バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Digits extends Zend_Validate_Digits
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID      => "不正な値です",
        self::NOT_DIGITS    => "半角数字で入力して下さい",
        self::STRING_EMPTY => "文字列が空です"
    );
}
