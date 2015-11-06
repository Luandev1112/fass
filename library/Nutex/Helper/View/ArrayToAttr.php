<?php
/**
 * class Nutex_Helper_View_ArrayToAttr
 *
 * 配列からhtml要素の属性文字列を作り出すヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_ArrayToAttr extends Nutex_Helper_View_Abstract
{
    /**
      * arrayToAttr
      *
      * @param array $attrs
      * @return string
      */
    public function arrayToAttr(array $attrs)
    {
        $parts = array();
        foreach ($attrs as $key => $value) {
            $parts[] = $this->getView()->escape($key) . '="' . $this->getView()->escape($value) . '"';
        }
        return implode(' ', $parts);
    }
}
