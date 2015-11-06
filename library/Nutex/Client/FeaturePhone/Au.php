<?php
/**
 * class Nutex_Client_FeaturePhone_Au
 *
 * クライアント フィーチャーフォン Au
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
class Nutex_Client_FeaturePhone_Au extends Nutex_Client_FeaturePhone
{
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
            'KDDI-',
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
        $carrier = self::getCarrierByIp();
        return ($carrier && strtolower($carrier) === 'au');
    }
}
