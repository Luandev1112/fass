<?php
/**
 * class Nutex_Filter_ZenkakuHiragana
 *
 * 全角ひらがなフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_ZenkakuHiragana extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[^ぁ-んー]';
}
