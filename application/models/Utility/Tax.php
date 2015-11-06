<?php
/**
 * class Shared_Model_Utility_Tax
 *
 * 税率計算
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Utility_Tax
{

	public static function calcurateTax($basePrice, $taxPercent)
	{
		
		$priceWithTax = round($basePrice * (0.01 * $taxPercent));
	
		return $priceWithTax;
	}
	
	public static function calcuratePriceWithTax($basePrice, $taxPercent)
	{
		
		$priceWithTax = round($basePrice * (1 + 0.01 * $taxPercent));
	
		return $priceWithTax;
	}
    
}