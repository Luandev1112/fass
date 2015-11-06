<?php
/**
 * class Nutex_Client_FeaturePhone_Docomo
 *
 * クライアント フィーチャーフォン Docomo
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
class Nutex_Client_FeaturePhone_Docomo extends Nutex_Client_FeaturePhone
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
            'DoCoMo',
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
        return ($carrier && strtolower($carrier) === 'docomo');
    }
}
