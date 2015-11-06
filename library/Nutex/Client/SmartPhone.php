<?php
/**
 * class Nutex_Client_SmartPhone
 *
 * クライアント スマートフォン
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
class Nutex_Client_SmartPhone extends Nutex_Client_Abstract
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
            'Android',
            'iPhone',
            'iPod',
            'iPad',
            'BlackBerry',
            'Windows CE',
        );
        if (preg_match('/(' . implode('|', $patterns) . ')/', self::getUserAgent())) {
            return true;
        }
        return false;
    }
}
