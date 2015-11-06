<?php
/**
 * class Nutex_View
 *
 * Zend_View拡張
 *
 * @package Nutex
 * @subpackage Nutex_View
 */
class Nutex_View extends Zend_View
{
    /**
     * フォームデータなどのデータブロック
     * @var array
     */
    protected $_data = array();

    /**
     * フォームデータなどのデータブロック デフォルト値用
     * @var array
     */
    protected $_dataDefault = array();

    /**
     * クライアントオブジェクト
     * @var array
     */
    protected $_client = null;

    /**
     * パラメータをエスケープして取得
     *
     * @param string $key
     * @return void
     */
    public function escaped($key)
    {
        return $this->escape($this->$key);
    }

    /**
     * データブロックのデータをエスケープして取得
     *
     * @param string $key
     * @return void
     */
    public function data($key)
    {
        return $this->escape($this->getData($key));
    }

    /**
     * urlをエスケープして取得
     *
     * @param string $url
     * @return void
     */
    public function url($url = null)
    {
        if ($url === null) {
            $url = $this->currentUrl();
        } else {
            $url = (string)$url;
            if ($url[0] !== '/') {
                $url = $this->$url;
            }
        }
        return $this->escape($this->baseUrl($url));
    }

    /**
     * getData
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->_data;
        }
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }
        return $this->getDataDefault($key);
    }

    /**
     * setData
     *
     * @param mixed
     * @return $this
     */
    public function setData()
    {
        $args = func_get_args();
        switch (count($args)) {

            case 1:
                $this->_data = (array)$args[0];
                break;

            case 2:
                $this->_data[$args[0]] = $args[1];
                break;

        }
        return $this;
    }

    /**
     * issetData
     *
     * @param string $key
     * @return boolean
     */
    public function issetData($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * unsetData
     *
     * @param string $key
     * @return $this
     */
    public function unsetData($key)
    {
        unset($this->_data[$key]);
        return $this;
    }

    /**
     * getDataDefault
     *
     * @param string $key
     * @return mixed
     */
    public function getDataDefault($key = null)
    {
        if (is_null($key)) {
            return $this->_dataDefault;
        }
        if (array_key_exists($key, $this->_dataDefault)) {
            return $this->_dataDefault[$key];
        }
        return '';
    }

    /**
     * setDataDefault
     *
     * @param mixed
     * @return $this
     */
    public function setDataDefault()
    {
        $args = func_get_args();
        switch (count($args)) {

            case 1:
                $this->_dataDefault = (array)$args[0];
                break;

            case 2:
                $this->_dataDefault[$args[0]] = $args[1];
                break;

        }
        return $this;
    }

    /**
     * currentUrl
     *
     * @param void
     * @return string
     */
    public function currentUrl()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request instanceof Zend_Controller_Request_Http) {
            return $request->getServer('REQUEST_URI');
        } else {
            return null;
        }
    }

    /**
     * moduleName
     *
     * @param void
     * @return string
     */
    public function moduleName()
    {
        return Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
    }

    /**
     * controllerName
     *
     * @param void
     * @return string
     */
    public function controllerName()
    {
        return Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    }

    /**
     * actionName
     *
     * @param void
     * @return string
     */
    public function actionName()
    {
        return Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    }

    /**
     * client
     *
     * @param void
     * @return string
     */
    public function client()
    {
        if ($this->getClient()) {
            return $this->getClient()->getName();
        } else {
            return Nutex_Client_Abstract::getDefaultClientName();
        }
    }

    /**
     * setClient
     *
     * @param Nutex_Client_Abstract $client
     * @return $this
     */
    public function setClient(Nutex_Client_Abstract $client)
    {
        $this->_client = $client;
        return $this;
    }

    /**
     * getClient
     *
     * @param void
     * @return Nutex_Client_Abstract
     */
    public function getClient()
    {
        return $this->_client;
    }
}
