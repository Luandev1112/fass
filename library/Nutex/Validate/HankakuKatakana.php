<?php
/**
 * class Nutex_Validate_HankakuKatakana
 *
 * 半角カタカナバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_HankakuKatakana extends Nutex_Validate_Abstract_Filter
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "全て半角カタカナで入力して下さい",
    );

    /**
     * フィルターインスタンス
     * @var string|array|Zend_Filter_Interface
     */
    protected $_filter = 'Nutex_Filter_HankakuKatakana';
}
