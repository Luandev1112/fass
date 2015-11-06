<?php
/**
 * class Shared_Helper_View_Parameters
 *
 * Shared_Helper_View_Parameters用のデータを受け取って何かするヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_Parameters extends Nutex_Helper_View_Parameters
{
    /**
     * テキストボックスのwidthの値を計算する
     *
     * @param int $max
     * @return int $max
     */
    protected function _caluculateFormTextWidth($max)
    {
        if ($this->getView()->client() instanceof Nutex_Client_Default) {
            return parent::_caluculateFormTextWidth($max);
        }

        $max = (int)$max;
        $max = ceil($max * 1.1);
        if ($max > 16.9) {
            $max = 16.9;
        }
        return (string)$max . 'em';
    }
}
