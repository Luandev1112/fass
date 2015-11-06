<?php
/**
 * class Nutex_Data_WebApi_Abstract
 *
 * データ WebApi 抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Data
 */
abstract class Nutex_Data_WebApi_Abstract extends Nutex_Data_Abstract
{
    /**
     * @var Zend_Http_Client
     */
    protected $_http;

    /**
     * APIのあるURI
     * @var string
     */
    protected $_uri;

    /**
     * __construct
     *
     * @param Zend_Db_Adapter_Abstract|null $adapter
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * create
     *
     * @param array $input
     * @throws Nutex_Exception_Error
     * @return boolean
     */
    public function create($input)
    {
        throw new Nutex_Exception_Error('this method is not supported');
        return false;
    }

    /**
     * read
     *
     * @param mixed $condition
     * @param array $options
     * @throws Nutex_Exception_Error
     * @return mixed
     */
    public function read($condition = null, array $options = array())
    {
        throw new Nutex_Exception_Error('this method is not supported');
        return false;
    }

    /**
     * find
     *
     * @param mixed $condition
     * @throws Nutex_Exception_Error
     * @return mixed
     */
    public function find($condition)
    {
        throw new Nutex_Exception_Error('this method is not supported');
        return false;
    }

    /**
     * update
     *
     * @param array $input
     * @param mixed $condition
     * @throws Nutex_Exception_Error
     * @return boolean
     */
    public function update($input, $condition)
    {
        throw new Nutex_Exception_Error('this method is not supported');
        return false;
    }

    /**
     * delete
     *
     * @param mixed $condition
     * @throws Nutex_Exception_Error
     * @return boolean
     */
    public function delete($condition)
    {
        throw new Nutex_Exception_Error('this method is not supported');
        return false;
    }

    /**
     * getHttp
     *
     * @param void
     * @return Zend_Http_Client
     */
    public function getHttp()
    {
        if ($this->_http === null) {
            $this->_http = new Zend_Http_Client($this->getUri());
        }
        return $this->_http;
    }

    /**
     * getUri
     *
     * @param void
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * getUri
     *
     * @param void
     * @return string
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;

        if ($this->_http instanceof Zend_Http_Client) {
            $this->_http->setUri($uri);
        }
    }
}
