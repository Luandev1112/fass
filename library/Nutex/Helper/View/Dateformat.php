<?php
/**
 * class Nutex_Helper_View_Dateformat
 *
 * スペーサーHTML
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_Dateformat extends Nutex_Helper_View_Abstract
{
    /**
     * date型のものを指定したフォーマットで変換して返す
     * @param date $value
     * @param string sprintfのformat
     */
    public function dateformat($value, $format='%04d年%d月%d日')
    {
        $result = "";
        try {
            $date = new Zend_Date();
            $date->set($value, 'yyyy-MM-dd');
            $result = sprintf($format, $date->get('yyyy'), $date->get('MM'), $date->get('dd'));
        } catch (Exception $exc) {
        }
        return $result;
    }
}
