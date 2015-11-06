<?php
/**
 * class Nutex_Filter_MbConvertKana_HankakuKatakana
 *
 * 半角カタカナ変換フィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_MbConvertKana_HankakuKatakana extends Nutex_Filter_MbConvertKana_Abstract
{
    /**
     * mb_convert_option
     * @var string
     */
    protected $_mbConvertOption = 'skh';
}
