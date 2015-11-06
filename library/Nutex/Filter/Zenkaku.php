<?php
/**
 * class Nutex_Filter_Zenkaku
 *
 * 全角フィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Zenkaku extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[ -~｡-ﾟ]';
}
