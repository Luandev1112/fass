<?php
/**
 * class Nutex_Helper_View_DateHistory
 *
 * スペーサーHTML
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_DateTimeHistory extends Nutex_Helper_View_Abstract
{
    /**
     * datetime型のものをTwitter風の文言で返す
     * @param datetime $dateTime
     * @param string $prefix
     */
    public function dateTimeHistory($dateTime, $prefix='約') {
        return ($dateTime) ? $prefix . Nutex_Date::changeHistoryDateTime($dateTime) : NULL;
    }

}
