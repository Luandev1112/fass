<?php
/**
 * class Nutex_Filter_AesDecrypt
 * 
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_AesDecrypt extends Nutex_Filter_Abstract
{
    /**
     * 復号
     *
     * @param  string $value
     * @param  bool $as
     * @param  bool $expr
     * @return string $encrypted
     */
    public function filter($value)
    {
        if ($value === null) {
            return $value;
        }

        $cryptKey = 'BSNDhfkfus';
        $value = "AES_DECRYPT({$value}, '{$cryptKey}')";
        
        return $value;
    }

}
