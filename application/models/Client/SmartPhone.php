<?php
/**
 * class Shared_Model_Client_SmartPhone
 *
 * クライアント スマートフォン
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Client_SmartPhone extends Nutex_Client_SmartPhone
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
        //iPadをスマートフォンと判定させない
        $patterns = array(
            'Android',
            'iPhone',
            'iPod',
			'iPad',
            'BlackBerry',
            'Windows CE',
            'Vame',
        );
        if (preg_match('/(' . implode('|', $patterns) . ')/', self::getUserAgent())) {
            return true;
        }
        return false;
    }

    /**
     * アプリからのアクセスかどうか判定する
     * @return boolean
     */
    public static function isApp()
    {
        return (self::isMe() && preg_match('/app/', self::getUserAgent()));
    }

    /**
     * Androidかどうか判定する
     * @return boolean
     */
    public static function android()
    {
        return (preg_match('/Android/', self::getUserAgent()));
    }

    /**
     * iOSかどうか判定する
     * @return boolean
     */
    public static function iOS()
    {
        return (preg_match('/(iPhone|iPad)/', self::getUserAgent()));
    }
}
