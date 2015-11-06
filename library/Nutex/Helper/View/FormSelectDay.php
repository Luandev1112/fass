<?php
/**
 * class Nutex_Helper_View_FormSelectDay
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_FormSelectDay extends Nutex_Helper_View_Abstract
{
    /**
     * 日のプルダウン作成
     * ○○日のプルダウンを作成する
     *
     * @param string $name フォームのname
     * @param mixed $value フォームのvalue
     * @param string $attribs
     * @param string $prefix
     * @param string $noSelectString '「選択なし」とかを入れたい場合はつける'
     * @param boolean $isAddZeroKeyValue 値と表示に0をつけるかどうか
     */
    public function formSelectDay($name, $value=null, $attribs=null, $prefix=null, $noSelectString=null, $isAddZeroKeyValue=FALSE) {
        $options = array();
        for ($i=1; $i<=31; $i++) {
            if ($isAddZeroKeyValue) {
                $options[sprintf("%02d", $i)] = sprintf("%02d", $i);
            } else {
                $options[$i] = $i;
            }
        }
        if ($noSelectString) {
            $options = array("" => $noSelectString) + $options;
        }
        return $this->getView()->formSelect($name, $value, $attribs, $options) . $prefix;
    }
}
