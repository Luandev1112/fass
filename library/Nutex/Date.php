<?php
/**
 * class Nutex_Date
 *
 * 時間をつかさどるクラス シングルトンパターン風
 *  - 静的メソッドを利用することでデフォルトインスタンスを利用できる
 *  - デフォルトインスタンスは内部的に時間を持っていて、それを変更することも可能
 *  - 普通にインスタンスを作って、それぞれに時間を持たせたインスタンス群をやりくりすることも可能
 *
 * @package Nutex
 * @subpackage Nutex_Date
 */
class Nutex_Date
{
    /**
     * シングルトンパターン的に使用するデフォルトインスタンス
     *
     * @var Nutex_Date
     */
    protected static $_defaultInstance = null;

    /**
     * デフォルトのタイムスタンプ
     *
     * この時間を前もって変更すると
     * デフォルトインスタンスなどこれを内部時間として持っているインスタンスの時間が変更される
     *
     * @var int
     */
    protected static $_defaultTimestamp = null;

    /**
     * timestamp
     *
     * @var int
     */
    protected $_timestamp = null;

    /**
     * @var Zend_Date
     */
    protected $_zendDate = null;

    /**
     * ロケールを固定するかどうか
     * @var boolean
     */
    protected $_fixLocale = true;

    /**
     * getDefaultTimestamp
     *
     * @param void
     * @return int
     */
    public static function getDefaultTimestamp()
    {
        if (!is_int(self::$_defaultTimestamp)) {
            self::setDefaultTimestamp(time());
        }
        return self::$_defaultTimestamp;;
    }

    /**
     * setDefaultTimestamp
     *
     * @param int
     * @return void
     * @throws Nutex_Exception_Error
     */
    public static function setDefaultTimestamp($timestamp)
    {
        if (self::$_defaultInstance) {
            throw new Nutex_Exception_Error('instance has been created');
        }
        self::$_defaultTimestamp = (int)$timestamp;
    }

    /**
     * getDefaultInstance
     *
     * @return Nutex_Date
     */
    public static function getDefaultInstance()
    {
        if (null === self::$_defaultInstance) {
            self::$_defaultInstance = new self(self::getDefaultTimestamp());
        }
        return self::$_defaultInstance;
    }

    /**
     * getDefaultInstanceClone
     *
     * @return Nutex_Date
     */
    public static function getDefaultInstanceClone()
    {
        $date = self::getDefaultInstance();
        $date = clone $date;
        return $date;
    }

    /**
     * getTimestamp
     *
     * @param void
     * @return int
     */
    public static function getTimestamp()
    {
        self::getDefaultInstance()->timestamp();
    }

    /**
     * get
     * Zend_Date::get()のアクセサ
     *
     * @param mixed
     * @return mixed
     */
    public static function get()
    {
        $args = func_get_args();
        return call_user_func_array(array(self::getDefaultInstance(), 'getFromZendDate'), $args);
    }

    /**
     * getDiffer
     * 内部時間からの差分で値を取得できるZend_Date::get()のアクセサ
     *
     * @param mixed $diffTime
     * @param mixed
     * @return mixed
     */
    public static function getDiffer($diffTime)
    {
        $args = func_get_args();
        return call_user_func_array(array(self::getDefaultInstance(), 'getFromDifferZendDate'), $args);
    }

    /**
     * getReplaced
     * 内部時間を置き換えつつ値を取得できるZend_Date::get()のアクセサ
     *
     * @param mixed $replaceTime
     * @param mixed
     * @return mixed
     */
    public static function getReplaced($replaceTime)
    {
        $args = func_get_args();
        return call_user_func_array(array(self::getDefaultInstance(), 'getFromReplacedZendDate'), $args);
    }

    /**
     * __construct
     *
     * @param int $currentTimestamp
     * @return void
     */
    public function __construct($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = self::getDefaultTimestamp();
        }
        $this->_timestamp = $timestamp;
    }

    /**
     * timestamp
     *
     * @param void
     * @return int
     */
    public function timestamp()
    {
        return $this->_timestamp;
    }

    /**
     * getFromZendDate
     * Zend_Date::get()のアクセサ
     *
     * @param void
     * @return mixed
     */
    public function getFromZendDate()
    {
        $args = func_get_args();
        return call_user_func_array(array($this->getZendDate(), 'get'), $args);
    }

    /**
     * getFromDifferZendDate
     * 内部時間からの差分で値を取得できるZend_Date::get()のアクセサ
     *
     * @param int $diffTime
     * @param mixed
     * @return mixed
     */
    public function getFromDifferZendDate($diffTime)
    {
        $args = func_get_args();
        array_shift($args);

        $date = $this->getZendDate();
        $current = $date->getTimestamp();
        $date->setTimestamp($current + $diffTime);
        $result = call_user_func_array(array($date, 'get'), $args);
        $date->setTimestamp($current);

        return $result;
    }

    /**
     * getFromReplacedZendDate
     * 内部時間を置き換えつつ値を取得できるZend_Date::get()のアクセサ
     *
     * @param int $replaceTime
     * @param mixed
     * @return mixed
     */
    public function getFromReplacedZendDate($replaceTime)
    {
        $args = func_get_args();
        array_shift($args);

        $date = $this->getZendDate();
        $current = $date->getTimestamp();
        $date->setTimestamp($replaceTime);
        $result = call_user_func_array(array($date, 'get'), $args);
        $date->setTimestamp($current);

        return $result;
    }

    /**
     * getZendDate
     *
     * @param void
     * @return Zend_Date
     */
    public function getZendDate()
    {
        if (!$this->_zendDate instanceof Zend_Date) {
            if ($this->getFixLocale()) {
                $this->_zendDate = new Zend_Date($this->timestamp(), Zend_Date::TIMESTAMP, new Zend_Locale(APPLICATION_LOCALE));
            } else {
                $this->_zendDate = new Zend_Date($this->timestamp(), Zend_Date::TIMESTAMP);
            }
        }
        return $this->_zendDate;
    }

    /**
     * getFixLocale
     *
     * @param void
     * @return boolean
     */
    public function getFixLocale()
    {
        return $this->_fixLocale;;
    }

    /**
     * setFixLocale
     *
     * @param boolean $flag
     * @return void
     */
    public function setFixLocale($flag)
    {
        $this->_fixLocale = (boolean) $flag;
        $this->_zendDate = null;
    }
    
    
    /**
     * 時間の表示をTwitter風にする
     *
     * @param datetime $dateTime
     * @return string Twitter風にした時間
     * @author Y.Kawano
     */
    public static function changeHistoryDateTime($dateTime) {
        $dt = strtotime(date('Y-m-d H:i:s')) - strtotime($dateTime);

        $yy = (int)($dt / (60 * 60 * 24 * 365) );
        $dt -= $yy * (60 * 60 * 24 * 365);
        $mm = (int)($dt / (60 * 60 * 24 * 30) );
        $dt -= $mm * (60 * 60 * 24 * 30);
        $dd = (int)($dt / (60 * 60 * 24) );
        $dt -= $dd * (60 * 60 * 24);
        $hh = (int)($dt / (60 * 60) );
        $dt -= $hh * (60 * 60);
        $nn = (int)($dt / 60);
        $dt -= $nn * 60;

        if($yy != 0){
            $aboutHistoryString = $yy . '年前';
        }elseif ($mm != 0){
            $aboutHistoryString = $mm . '月前';
        }elseif ($dd != 0){
            $aboutHistoryString = $dd . '日前';
        }elseif($hh != 0){
            $aboutHistoryString = $hh . '時間前';
        }elseif($nn != 0){
            $aboutHistoryString = $nn . '分前';
        }elseif($dt != 0){
            $aboutHistoryString = $dt . '秒以内';
        }else{
            $aboutHistoryString = FALSE;
        }
        return $aboutHistoryString;
    }
    
    
        /**
     * 指定月の末日を求める
     *
     * @param unknown_type $year
     * @param unknown_type $month
     * @return unknown
     */
    public function getMonthEndDay($year, $month) {
        //mktime関数で日付を0にすると前月の末日を指定したことになります
        //$month + 1 をしていますが、結果13月のような値になっても自動で補正されます
        $dt = mktime(0, 0, 0, $month + 1, 0, $year);
        return date("d", $dt);
    }
}
