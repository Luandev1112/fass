<?php
/**
 * class Nutex_Filter_AlpahbetNumber
 * 
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_AlpahbetNumber extends Nutex_Filter_MbConvertKana_Abstract
{
    /**
     * 数字のカンマを外す
     *
     * @param  string $value
     * @return string $replaced
     */
    public function filter($value)
    {
        $value = (string)$value;
        $value = mb_convert_kana($value, $this->_mbConvertOption);
        return $value;
    }

}
