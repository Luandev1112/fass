<?php
/**
 * class Nutex_Filter_MbConvertKana_ZenkakuKana
 *
 * 全角カナ変換フィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_MbConvertKana_ZenkakuKana extends Nutex_Filter_MbConvertKana_Abstract
{
    /**
     * mb_convert_option
     * @var string
     */
    protected $_mbConvertOption = 'KV';
}
