<?php
/**
 * class Nutex_Helper_View_HtmlSpacer
 *
 * スペーサーHTML
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_HtmlSpacer extends Nutex_Helper_View_Abstract
{
    /**
     * スペーサーHTML
     *
     * @param int $size
     * @return string $html
     */
    public function htmlSpacer($size)
    {
        if (is_int($size) || is_numeric($size)) {
            $size = (string)$size . 'px';
        }
        $html = '<div style="margin: 0; padding: 0; width: 100%; height: ' . $size . '; background-color: transparent; border: none;"></div>';
        return $html;
    }
}
