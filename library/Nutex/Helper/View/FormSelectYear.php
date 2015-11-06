<?php
/**
 * class Nutex_Helper_View_FormSelectYear
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_FormSelectYear extends Nutex_Helper_View_Abstract
{
    const DEFAULT_MIN_YEAR = 1950;

    /**
     * 年のプルダウン作成
     * ○○○○年のプルダウンを作成する
     *
     * @param string $name フォームのname
     * @param mixed $value フォームのvalue
     * @param string $attribs
     * @param string $prefix
     * @param string $noSelectString '「選択なし」とかを入れたい場合はつける'
     * @param int $minYear 最低年数
     * @param int $maxYear 最高年数
     */
    public function formSelectYear($name, $value=null, $attribs=null, $prefix=null, $noSelectString=null, $minYear=self::DEFAULT_MIN_YEAR, $maxYear=null) {
        $options = array();
        if ($minYear === null) {
            $minYear = self::DEFAULT_MIN_YEAR;
        }
        if ($maxYear === null) {
            $maxYear = date('Y');
        }
        for ($year=$minYear; $year<=$maxYear; $year++) {
            $options[$year] = $year;
        }
        if ($noSelectString) {
            $options = array("" => $noSelectString) + $options;
        }
        return $this->getView()->formSelect($name, $value, $attribs, $options) . $prefix;
    }
}
