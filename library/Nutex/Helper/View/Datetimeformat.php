<?php
/**
 * class Nutex_Helper_View_Datetimeformat
 *
 * スペーサーHTML
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_Datetimeformat extends Nutex_Helper_View_Abstract
{
    /**
     * datetime型のものを指定したフォーマットで変換して返す
     * @param datetime $value
     * @param string sprintfのformat
     */
    public function datetimeformat($value, $format='%04d年%d月%d日%d時%d分%d秒')
    {
        $result = "";
        try {
            $date = new Zend_Date();
            $date->set($value, 'yyyy-MM-dd HH:mm:ss');
            $result = sprintf($format, $date->get('yyyy'), $date->get('MM'), $date->get('dd'), $date->get('HH'), $date->get('mm'), $date->get('ss'));
        } catch (Exception $exc) {
        }
        return $result;
    }
}
