<?php
/**
 * class Nutex_Filter_NumberComma
 * 
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_NumberComma extends Nutex_Filter_Abstract
{
    /**
     * 数字のカンマを外す
     *
     * @param  string $value
     * @return string $replaced
     */
    public function filter($value)
    {
        if ($value === '') {
            return $value;
        }

        return str_replace(',', '', $value);
    }

}
