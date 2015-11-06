<?php
/**
 * class Shared_Helper_View_JapaneseCalendar
 *
 * カレンダーを生成するビューヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @see http://pear.php.net/package/Date_Holidays_Japan
 * @see http://pear.php.net/package/Calendar/
 */
require_once("Calendar/Month/Weekdays.php");
require_once("Date/Holidays.php");
class Shared_Helper_View_JapaneseCalendar extends Nutex_Helper_View_Abstract
{
    protected $_year;
    protected $_month;
    protected $_day;
    protected $_links;
    protected $_isDayAddClass= TRUE;

    /**
     * カレンダーを生成する
     * @param date $date date('Y-m-d')
     * @return string html
     */
    public function japaneseCalendar($date=NULL)
    {
        $zendDate = new Zend_Date();
        if ($date) {
            $part = 'yyyy-MM-dd';
            $zendDate->set($date, $part, 'ja_JP');
        }
        $this->_year  = $zendDate->get('yyyy');
        $this->_month = $zendDate->get('MM');
        $this->_day   = $zendDate->get('dd');
        return $this->render();
    }

    /**
     * カレンダーのhtmlを返す
     * @return string html
     */
    public function render()
    {
        if (empty($this->_year)) {
            $this->_year = date('Y');
        }
        if (empty($this->_month)) {
            $this->_month = date('n');
        }
        $current_year  = (int)$this->_year;
        $current_month = (int)$this->_month;
        $current_day   = (int)$this->_day;

        $calMonth = new Calendar_Month_Weekdays($current_year, $current_month, 0);//第3引数の0は週の最初を日曜に
        $calMonth->build();
        $ja = "data/Date_Holidays_Japan/lang/Japan/ja_JP.xml";
        $dh = &Date_Holidays::factory("Japan", $current_year, "ja_JP");
        $dh->addTranslationFile($ja, "ja_JP");
        $holidays = array();

        //祝日の月日をキーに祝日名を配列に格納
        foreach ($dh->getHolidays() as $value) {
            $holidays[$value->getDate()->format("%m%d")] = $value->getTitle();
        }

        $html = "<h2>".$current_year."年".$current_month."月"."</h2>\n";
        $html .= "<table class=\"calendar\">\n";
        $html .= "<thead>\n";
        $html .= "<tr><th class=\"sun\">Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th class=\"sat\">Sat</th></tr>\n";
        $html .= "</thead>\n";
        $html .= "<tbody>\n";

        while ($day = $calMonth->fetch()) {
            if ($day->isFirst()) {
                $html .= '<tr>';
            }
            if ($day->isEmpty()) {
                $html .= "<td>&nbsp;</td>";
            } else {
                $addDayClass = NULL;
                $date = sprintf("%02d",$day->thisMonth()).sprintf("%02d",$day->thisDay());
                if (array_key_exists($date, $holidays)) {
                    $addDayClass = 'holiday'; // 祝日のとき
                } else if ($day->isFirst()) {
                    $addDayClass = 'sun'; // 週の最初（日曜）のとき
                } else if ($day->isLast()) {
                    $addDayClass = 'sat'; // 週の最後（土曜）のとき
                }
                $isToday = FALSE;
                if ($this->_isDayAddClass && $current_day == $day->thisDay()) {
                    $isToday = TRUE;
                }
                $html.= "<td";
                // classを付与する
                if ($isToday || $addDayClass) {
                    $addClass = NULL;
                    if ($addDayClass) {
                        $addClass .= $addDayClass;
                    }
                    if ($isToday) {
                        if (!empty($addClass)) {
                            $addClass .= ' ';
                        }
                        $addClass .= 'targetDay';
                    }
                    $html .= " class=\"{$addClass}\"";
                }
                $html .= ">";
                $html .= $this->_getDayNumberHtml($day->thisDay());
                $html .= "</td>";
            }
            if ($day->isLast()) {
                $html .= "</tr>\n";
            }
        }
        $html .= "</tbody>\n";
        $html .= "</table>\n";
        return $html;
    }

    /**
     * 表示する年を設定する
     * @param int $year
     * @return Shared_Helper_View_JapaneseCalendar
     */
    public function setYear($year)
    {
        $this->_year = $year;
        return $this;
    }

    /**
     * 表示する月を設定する
     * @param int $month
     * @return Shared_Helper_View_JapaneseCalendar
     */
    public function setMonth($month)
    {
        $this->_month = $month;
        return $this;
    }

    /**
     * 表示する日を設定する
     * @param int $day
     * @return Shared_Helper_View_JapaneseCalendar
     */
    public function setDay($day)
    {
        $this->_day = $day;
        $this->setIsDayAddClass(TRUE);
        return $this;
    }

    /**
     * 日付を指定した場合その日付に特別なクラスを付与するかどうか
     * @param boolean $isDayAddClass
     * @return Shared_Helper_View_JapaneseCalendar
     */
    public function setIsDayAddClass($isDayAddClass)
    {
        $this->_isDayAddClass = $isDayAddClass;
        return $this;
    }

    /**
     * 日付とurlのkey=>valueを与えると該当の日付にリンクを貼ることができる
     * @param array $links
     * @return Shared_Helper_View_JapaneseCalendar
     */
    public function setLinks(array $links)
    {
        $this->_links = $links;
        return $this;
    }

    /**
     * 日付部分のhtml付与するものを返す
     * @param int 作成する日
     * @return string html
     */
    protected function _getDayNumberHtml($day)
    {
        $html = "<span class=\"day\">";
        if (!empty($this->_links) && key_exists($day, $this->_links)) {
            $html .= '<a href="' . $this->getView()->escape($this->_links[$day]) . '">' . $day . '</a>';
        } else {
            $html .= $day;
        }
        $html .= "</span>";
        return $html;
    }
}
