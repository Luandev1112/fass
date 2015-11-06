<?php
/**
 * class Nutex_Filter_MbConvertKana_Hankaku
 *
 * 半角変換フィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_MbConvertKana_Hankaku extends Nutex_Filter_MbConvertKana_Abstract
{
    /**
     * mb_convert_option
     * @var string
     */
    protected $_mbConvertOption = 'ask';
}
