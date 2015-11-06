<?php
/**
 * class Nutex_Filter_ZenkakuKatakana
 *
 * 全角カタカナフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_ZenkakuKatakana extends Nutex_Filter_Regex
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern = '[^ァ-ヴー]';
}
