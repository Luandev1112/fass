<?php
/**
 * class Shared_Helper_View_DiffNum
 *
 * 差分の数字を表示する
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_DiffNum extends Zend_View_Helper_Partial
{
    /**
     * diffNum
     * @param int $num
     * @return string
     */
    public function diffNum($num)
    {
        if ($num > 0) {
            return '＋' . number_format($num);
        } else if ($num == 0) {
            return '±' . number_format($num);
        } else {
            return '－' . number_format(abs($num));
        }
    }
}
