<?php
/**
 * class Nutex_Validate_Regex
 *
 * 正規表現バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Regex extends Zend_Validate_Regex
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID      => "Invalid type given. String, integer or float expected",
        self::NOT_MATCH    => "形式が一致しません",
        self::ERROROUS     => "パターンが不正です"
    );

}
