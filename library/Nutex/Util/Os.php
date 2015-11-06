<?php
/**
 * class Nutex_Util_Os
 *
 * 実行環境OS判別
 *
 * @package Nutex
 * @subpackage Nutex_Util
 */
class Nutex_Util_Os
{
    /**
     * @return boolean
     */
    public static function isWin()
    {
        return strpos(PHP_OS, 'WIN') === 0;
    }
}