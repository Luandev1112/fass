<?php
/**
 * class Nutex_Helper_View_NumberFormat
 *
 * カンマかつ小数点対応
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_NumberFormat extends Nutex_Helper_View_Abstract
{
    /**
     * date型のものを指定したフォーマットで変換して返す
     * @param float $value
     */
    public function numberFormat($value)
    {
	    //$result = floatval($value);
        //$result = number_format($result, 6, '.', ',');
        $number = number_format($value, 6, '.', ','); // 1,200.50
		$number = preg_replace("/\.?0+$/","",$number); // 1,200.5
        
        return $number;
    }
}
