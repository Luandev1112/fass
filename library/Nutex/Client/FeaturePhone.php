<?php
/** include Net_IPv4  */
if (defined('LIBRARY_PATH')) {
    $path = LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Net_IPv4' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'IPv4.php';
    if (is_readable($path)) {
        require_once $path;
    }
}
/**
 * class Nutex_Client_FeaturePhone
 *
 * クライアント フィーチャーフォン
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
class Nutex_Client_FeaturePhone extends Nutex_Client_Abstract
{
    /**
     * @var string
     */
    const CAIRRIER_DOCOMO = 'docomo';
    const CAIRRIER_AU = 'au';
    const CAIRRIER_SOFTBANK = 'softbank';

    /**
     * 携帯ip設定
     * @var array
     */
    protected static $_mobileIps = array();
    protected static $_carrierByIp = null;

    /**
     * @var boolean
     */
    protected static $_checkIp = false;

    /**
     * isMe
     * このクライアントかどうか判定する
     *
     * @param array $options
     * @return boolean
     */
    public static function isMe($options = array())
    {
        $patterns = array(
            'DoCoMo',
            'KDDI-',
            'SoftBank',
            'Vodafone',
            'J-PHONE',
        );
        if (preg_match('/(' . implode('|', $patterns) . ')/', self::getUserAgent())) {
            if (self::getCheckIp()) {
                return self::isValidIp();
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * isValidIp
     * 携帯として正しいIPかどうか判定する
     *
     * @param array $options
     * @return boolean
     */
    public static function isValidIp()
    {
        return (self::getCarrierByIp() !== false);
    }

    /**
     * getCarrierByIp
     * IPからキャリアを取得する
     *
     * @param void
     * @return string|false $carrier
     */
    public static function getCarrierByIp()
    {
        if (self::$_carrierByIp === null) {
            self::$_carrierByIp = self::findCarrierByIp();
        }
        return self::$_carrierByIp;
    }

    /**
     * findCarrierByIp
     * IPからキャリアを判別する
     *
     * @param void
     * @return string|false $carrier
     */
    public static function findCarrierByIp()
    {
        $carrier = false;

        if (self::$_mobileIps == array()) {
            return $carrier;
        }

        if (class_exists('Net_IPv4') === false) {
            return $carrier;
        }

        $clientIp = self::getIp();
        $net = new Net_IPv4();
        foreach (self::$_mobileIps as $car => $ips) {
            foreach ($ips as $ip) {
                $network = (strstr($ip, '/')) ? $ip : $ip . '/32';
                if ($net->ipInNetwork($clientIp, $network)) {
                    $carrier = $car;
                    break 2;
                }
            }
        }

        return $carrier;
    }

    /**
     * setMobileIps
     *
     * @param array|Zend_Config $ips
     * @return void
     */
    public static function setMobileIps($ips)
    {
        if ($ips instanceof Zend_Config) {
            $ips = $ips->toArray();
        }
        if (is_array($ips)) {
            self::$_mobileIps = $ips;
        }
    }

    /**
     * setCheckIp
     *
     * @param boolean $flag
     * @return void
     */
    public static function setCheckIp($flag)
    {
        self::$_checkIp = (boolean) $flag;
    }

    /**
     * getCheckIp
     *
     * @return boolean
     */
    public static function getCheckIp()
    {
        return self::$_checkIp;
    }

    /**
     * @return string
     */
    public static function getUID($carrier = null)
    {
        if ($carrier === null) {
            $carrier = self::getCarrierByIp();
        }

        $uid = null;
        switch (strtolower($carrier)) {

            case self::CAIRRIER_DOCOMO:
                $uid = (isset($_SERVER['HTTP_X_DCMGUID'])) ? $_SERVER['HTTP_X_DCMGUID'] : null;
                break;

            case self::CAIRRIER_AU:
                $uid = (isset($_SERVER['HTTP_X_UP_SUBNO'])) ? $_SERVER['HTTP_X_UP_SUBNO'] : null;
                break;

            case self::CAIRRIER_SOFTBANK:
                $uid = (isset($_SERVER['HTTP_X_JPHONE_UID'])) ? $_SERVER['HTTP_X_JPHONE_UID'] : null;
                break;

        }

        return $uid;
    }

    /**
     * onStartMVC
     */
    public function onStartOfMVC()
    {
        //DoCoMoでguid=ONがついていなかったらつけてリダイレクト
        if (self::getCarrierByIp() === self::CAIRRIER_DOCOMO && self::getUID() === null) {
            $url = $_SERVER['REQUEST_URI'] . (($_SERVER['QUERY_STRING']) ? '&' : '?') . 'guid=ON';
            header('location: ' . $url);
            exit;
        }
    }
}
