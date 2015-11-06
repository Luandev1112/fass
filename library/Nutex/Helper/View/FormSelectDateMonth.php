<?php
/**
 * class Nutex_Helper_View_FormSelectDateMonth
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_FormSelectDateMonth extends Nutex_Helper_View_Abstract
{
    /**
     * dateのプルダウン生成
     * ○○○○年○○月のプルダウンを作成する
     *
     * @param array $name フォームのname year, monthの順番
     * @param mixed $value date('Y-m')の形式
     * @param string $attribs
     * @param string $noSelectString '「選択なし」とかを入れたい場合はつける'
     * @param int $minYear 最低年数
     * @param int $maxYear 最高年数
     */
    public function formSelectDateMonth(array $name, $value=null, $attribs=null, $noSelectString=null, $minYear=null, $maxYear=null)
    {
        $year  = null;
        $month = null;
        if ($value) {
            $date = new Zend_Date();
            $part = 'yyyy-MM';
            $date->set($value, $part);
            $year  = $date->get('yyyy');
            $month = $date->get('MM');
        }
        return $this->getView()->formSelectYear($name[0], $year, $attribs, '年', $noSelectString, $minYear, $maxYear)
            . $this->getView()->formSelectMonth($name[1], $month, $attribs, '月', $noSelectString, TRUE);
    }
}
