<?php
/**
 * class Nutex_Validate_ZenkakuKatakana
 *
 * 全角カタカナバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_ZenkakuKatakana extends Nutex_Validate_Abstract_Filter
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "全て全角カタカナで入力して下さい",
    );

    /**
     * フィルターインスタンス
     * @var string|array|Zend_Filter_Interface
     */
    protected $_filter = 'Nutex_Filter_ZenkakuKatakana';
}
