<?php
/**
 * class Nutex_Filter_Abstract
 *
 * mb_convert_kanaフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
abstract class Nutex_Filter_MbConvertKana_Abstract extends Nutex_Filter_Abstract
{
    /**
     * mb_convert_kana用のoption
     * @var string
     */
    protected $_mbConvertOption;

    /**
     * フィルタリング
     *
     * @param  string $value
     * @return string $value
     */
    public function filter($value)
    {
        $value = (string)$value;
        $value = mb_convert_kana($value, $this->_mbConvertOption);
        return $value;
    }
}
