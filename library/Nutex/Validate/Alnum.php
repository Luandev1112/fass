<?php
/**
 * class Nutex_Validate_Alnum
 *
 * 半角英数字バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Alnum extends Zend_Validate_Alnum
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID      => "不正な値です",
        self::NOT_ALNUM    => "半角英数字で入力して下さい",
        self::STRING_EMPTY => "文字列が空です"
    );
}
