<?php
/**
 * class Nutex_Filter_AesEncrypt
 *
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_AesEncrypt extends Nutex_Filter_Abstract
{
    /**
     * 暗号
     *
     * @param  string $value
     * @return string $encrypted
     */
    public function filter($value)
    {
        if ($value === null) {
            return $value;
        }

        $cryptKey = 'BSNDhfkfus';
		
		// クォートつきの値を埋め込むように変更 modified miyano 2012/10/19
        $value = "AES_ENCRYPT('{$value}', '{$cryptKey}')";
		
		// modified miyano 2012/10/19
        //$encrypted = new Zend_Db_Expr($value);
		$encrypted = new Zend_Db_Expr($value);
		
        return $encrypted;
    }

}
