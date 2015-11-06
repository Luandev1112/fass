<?php
/**
 * class Nutex_Client_Default
 *
 * デフォルトクライアント
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
class Nutex_Client_Default extends Nutex_Client_Abstract
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
        //常にtrue
        return true;
    }
}
