<?php
/**
 * class Nutex_Validate_Ascii
 *
 * アスキーコードバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Ascii extends Nutex_Validate_Abstract_Filter
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "全て半角英数字記号で入力して下さい",
    );

    /**
     * フィルターインスタンス
     * @var string|array|Zend_Filter_Interface
     */
    protected $_filter = 'Nutex_Filter_Ascii';
}
