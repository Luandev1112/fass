<?php
/**
 * class Nutex_Session_RequestParameter
 *
 * HTTPリクエストパラメータを利用したセッション
 *
 * @package Nutex
 * @subpackage Nutex_Session
 */
class Nutex_Session_RequestParameter extends Nutex_Session_Abstract
{
    /**
     * __construct
     * type hintingのみオーバーライドしています
     *
     * @param Nutex_Session_StorageAdapter_Abstract $dataAdapter
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return void
     */
    public function __construct(
        Nutex_Session_StorageAdapter_Abstract $dataAdapter,
        Zend_Controller_Request_Http $request,
        Zend_Controller_Response_Http $response,
        array $options = array()
    )
    {
        parent::__construct($dataAdapter, $request, $response, $options);
    }

    /**
     * getIdFromRequest
     *
     * @param void
     */
    public function getIdFromRequest()
    {
        return $this->getRequest()->getParam($this->getOption(self::OPTION_ID_NAME, self::DEFAULT_ID_NAME));
    }

    /**
     * publishNewId
     *
     * @param void
     * @return string
     */
    public function publishNewId()
    {
        $base = $this->getOption(self::OPTION_HASH_SALT, self::DEFAULT_HASH_SALT) . mt_rand() . $this->getRequest()->getServer('HTTP_USER_AGENT') . mt_rand() . uniqid(null, true);
        return $this->getConverter()->convert(hash($this->getOption(self::OPTION_HASH_FUNCTION, self::DEFAULT_HASH_FUNCTION), $base));
    }
}
