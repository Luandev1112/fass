<?php
/**
 * class Nutex_Client_Ajax
 *
 * クライアント Ajax
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
class Nutex_Client_Ajax extends Nutex_Client_Abstract
{
    /**
     * 自分のクライアントに紐づくviewが無い場合、親クライアントのviewを使うかどうか
     * @var boolean
     */
    protected $_overrideViewsByParents = false;

    /**
     * 自分のクライアントに紐づくviewが無い場合、デフォルトクライアントのviewを使うかどうか
     * @var boolean
     */
    protected $_overrideViewsByDefault = true;

    /**
     * isMe
     * このクライアントかどうか判定する
     * @see Zend_Controller_Request_Http::isXmlHttpRequest()
     *
     * @param array $options
     * @return boolean
     */
    public static function isMe($options = array())
    {
        /**
         * @todo jQueryには対応してないかも 未検証
         */
        if (self::getHttpHeader('X_REQUESTED_WITH') === 'XMLHttpRequest') {
            return true;
        } else {
            return false;
        }
    }
}
