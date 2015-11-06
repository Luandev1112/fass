<?php
/**
 * class Nutex_Filter_Ascii
 *
 * アスキーコードフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Ascii extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[^ -~]';
}
