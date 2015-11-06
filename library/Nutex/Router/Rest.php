<?php
/**
 * class Nutex_Router_Rest
 *
 * Zend_Restパッケージ用ルータ
 * Zend_Rest_Routeの具合の悪い部分をオーバーライドしたもの
 *
 * @package Nutex
 * @subpackage Nutex_Router
 */
class Nutex_Router_Rest extends Zend_Rest_Route {

    /**
     * @param Zend_Config $config
     * @return Nutex_Router_Rest
     *
     * @todo
     * 親クラスのgetInstance()内で、決め打ちexplode()をしている箇所(r24013 L106)があり、
     * iniからZend_Controller_Router_Rewrite::_getRouteFromConfig経由でインスタンスを作らせた場合、
     * モジュール全てをrestfulモジュールとして扱わせる設定ができない
     * 本家が対応するまでは拡張して対応しておく
     */
    public static function getInstance(Zend_Config $config)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $defaultsArray = array();
        $restfulConfigArray = array();
        foreach ($config as $key => $values) {
            if ($key == 'type') {
                // do nothing
            } elseif ($key == 'defaults') {
                $defaultsArray = $values->toArray();
            } else {
                if (strstr(',', $values) !== false) {
                    $restfulConfigArray[$key] = explode(',', $values);
                } else {
                    $restfulConfigArray[$key] = $values;
                }
            }
        }

        $instance = new self($frontController, $defaultsArray, $restfulConfigArray);
        return $instance;
    }

    /**
     * @todo
     * 親クラスの処理では、$pathが空だと問答無用でマッチしたことになってしまう
     * $pathが空の場合も、ある程度判定するようにした
     *
     * @param Zend_Controller_Request_Http $request Request used to match against this routing ruleset
     * @return array An array of assigned values or a false on a mismatch
     */
    public function match($request, $partial = false)
    {
        if (!$request instanceof Zend_Controller_Request_Http) {
            $request = $this->_front->getRequest();
        }
        $this->_request = $request;
        $this->_setRequestKeys();

        $path   = $request->getPathInfo();
        $params = $request->getParams();
        $values = array();
        $path   = trim($path, self::URI_DELIMITER);

        if ($path == '' && !$this->_allRestful()&& !$this->_checkRestfulModule($request->getModuleName())) {
            return null;
        } else {
            return parent::match($request, $partial);
        }
    }

}