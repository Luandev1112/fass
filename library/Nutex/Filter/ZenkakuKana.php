<?php
/**
 * class Nutex_Filter_ZenkakuKana
 *
 * 全角かな・カナフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_ZenkakuKana extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[^ぁ-ヴー]';
}
