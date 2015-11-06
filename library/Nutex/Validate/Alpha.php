<?php
/**
 * class Nutex_Validate_Alpha
 *
 * 半角英字バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Alpha extends Zend_Validate_Alpha
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID      => "不正な値です",
        self::NOT_ALPHA    => "半角英字で入力して下さい",
        self::STRING_EMPTY => "文字列が空です"
    );
}
