<?php
/**
 * class Nutex_Filter_Hankaku
 *
 * 半角フィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Hankaku extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[^ -~｡-ﾟ]';
}
