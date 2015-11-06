<?php
/**
 * class Nutex_Helper_View_PassedTime
 *
 * 経過時間取得
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_PassedTime extends Nutex_Helper_View_Abstract
{
    /**
     * @var int
     */
    protected $_thresholdDays = 3;

    /**
     * 経過時間
     *
     * @param mixed $time : Zend_Date型
     *                      MYSQLのDATETIME形式 yyyy-MM-dd HH:mm:ss
     *                      シリアル秒
     * @return string $output : #時間前、#分前、#秒前、#秒後、#分後、#時間後
     *                          24時間以上離れたら ####年##月##日
     *                          引数が時間として判断できない場合 false
     */
    public function passedTime($time)
    {
        $zendDate = 'Zend_Date';
        $nowDate = Nutex_Date::getDefaultInstance()->getZendDate()->toValue();

        if ($time instanceof $zendDate) {
            $timestamp = $time;
        } else if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $time)) {
            $timestamp = new Zend_Date($time, 'yyyy-MM-dd HH:mm:ss');
        } elseif ($time > 0) {
            $timestamp = new Zend_Date($time);
        }

        if (isset($timestamp)) {
            $designateDate = $timestamp->toValue();

            if ($nowDate >= $designateDate) {
                $span = $nowDate - $designateDate;
                $str = '前';
            } else {
                $span = $designateDate - $nowDate;
                $str = '後';
            }

            if ($span > 86400) {
                if ($span > 86400 * $this->getThresholdDays()) {
                    return $timestamp->toString('yyyy年M月d日');
                } else {
                    return (int)($span / 86400) . '日' . $str;
                }
            } elseif ($span > 3600) {
                return (int)($span / 3600) . '時間' . $str;
            } elseif ($span > 60) {
                return (int)($span / 60) . '分' . $str;
            } else {
                return $span . '秒' . $str;
            }
        }

        return false;
    }

    /**
     * この日数以上前だと日付を表示する日数を取得
     * @return int
     */
    public function getThresholdDays()
    {
        return $this->_thresholdDays;
    }

    /**
     * この日数以上前だと日付を表示する日数を取得
     * @param int $days
     */
    public function setThresholdDays($days)
    {
        $this->_thresholdDays = (int) $days;
        return $this;
    }
}
