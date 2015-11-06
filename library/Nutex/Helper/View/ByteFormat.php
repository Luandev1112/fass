<?php
/**
 * class Nutex_Helper_View_ByteFormat
 *
 * Byte表示
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 * @cf. https://php-archive.net/php/filesize_unit/
 */
class Nutex_Helper_View_ByteFormat extends Nutex_Helper_View_Abstract
{
    /**
     * スペーサーHTML
     *
     * @param int $size
     * @return string $html
     */
    public function byteFormat($size, $dec=-1, $separate=false)
    {
	    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	    $digits = ($size == 0) ? 0 : floor( log($size, 1024) );
	     
	    $over = false;
	    $max_digit = count($units) -1 ;
	 
	    if($digits == 0){
	        $num = $size;
	    } else if(!isset($units[$digits])) {
	        $num = $size / (pow(1024, $max_digit));
	        $over = true;
	    } else {
	        $num = $size / (pow(1024, $digits));
	    }
	     
	    if($dec > -1 && $digits > 0) $num = sprintf("%.{$dec}f", $num);
	    if($separate && $digits > 0) $num = number_format($num, $dec);
	     
	    return ($over) ? $num . $units[$max_digit] : $num . $units[$digits];
    }
}
