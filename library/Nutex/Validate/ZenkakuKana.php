<?php
/**
 * class Nutex_Validate_ZenkakuKana
 *
 * 全角かな・カナバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_ZenkakuKana extends Nutex_Validate_Abstract_Filter
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "全てひらがな又は全角カタカナで入力して下さい",
    );

    /**
     * フィルターインスタンス
     * @var string|array|Zend_Filter_Interface
     */
    protected $_filter = 'Nutex_Filter_ZenkakuKana';
}
