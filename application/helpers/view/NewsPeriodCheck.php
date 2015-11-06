<?php
/**
 * class Shared_Helper_View_NewsPeriodCheck
 *
 *
 *
 */
class Shared_Helper_View_NewsPeriodCheck extends Nutex_Helper_View_Abstract
{
    /**
     * 期間内チェック
     *
     * @param string $startDate
     *        string $endDate
     *        boolean $mode : true:年月日時分秒を使用 / false:年月日を使用
     * @return boolean
     */
    public function newsPeriodCheck($startDate, $endDate, $mode)
    {
        $timeZoneDiff = 54000;
        $nowDate = Nutex_Date::getDefaultInstance()->getZendDate()->toValue() - $timeZoneDiff;

        $start = $this->_toZendDate($startDate);
        $end = $this->_toZendDate($endDate);

        if ($start != false) {
            $tmpTime = $start->toValue() - $timeZoneDiff;
            if ($mode == false) {
                $tmpTime = $tmpTime - ($tmpTime % 86400);
            }
            if ($nowDate < $tmpTime) {
                return false;
            }
        }
        if ($end != false) {
            $tmpTime = $end->toValue() - $timeZoneDiff;
            if ($mode == false) {
                $tmpTime = $tmpTime + 86400 - ($tmpTime % 86400);
            }
            if ($nowDate > $tmpTime) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * 入力をZend_Date型に変換
     *
     * @param Zend_Date $time
     */
    private function _toZendDate($time)
    {
        $zendDate = 'Zend_Date';

        if ($time instanceof $zendDate) {
            $timestamp = $time;
        } else if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $time)) {
            if ($time == '0000-00-00 00:00:00') {
                return false;
            }
            $timestamp = new Zend_Date($time, 'yyyy-MM-dd HH:mm:ss');
        } elseif ($time > 0) {
            $timestamp = new Zend_Date($time);
        } else {
            return false;
        }
        return $timestamp;
    }
}
