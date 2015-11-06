<?php
/**
 * class Nutex_Validate_NotEmpty
 *
 * 空バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_NotEmpty extends Zend_Validate_NotEmpty
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::IS_EMPTY => "必須項目です",
        self::INVALID  => "不正な値です",
    );
}
