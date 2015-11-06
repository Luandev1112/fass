<?php
/**
 * class Nutex_Filter_HankakuKatakana
 *
 * 半角カタカナフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_HankakuKatakana extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[^ｦ-ﾟ]';
}
