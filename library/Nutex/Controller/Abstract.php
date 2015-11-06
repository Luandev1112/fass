<?php
/**
 * class Nutex_Controller_Abstract
 *
 * 基底コントローラ抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Controller
 */
abstract class Nutex_Controller_Abstract extends Zend_Controller_Action
{
    /**
     * @var boolean
     */
    protected $_sessionActive = true;

    /**
     * @var Nutex_Client_Abstract
     */
    protected $_client = null;

    /**
     * init
     *
     * @param void
     * @return void
     */
    public function init()
    {
        parent::init();

        if ($this->getClient()) {
            $this->getClient()->onStartOfMVC($this);
        }

        if ($this->_sessionActive) {
            $this->_setupSession($this->getExtraConfig('session'));
            Nutex_Session::updateMetaInfo();
        }

        $this->_setupErrorHandler();
    }

    /**
     * preDispatch
     *
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();
    }

    /**
     * postDispatch
     *
     * @param void
     * @return void
     */
    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->getHelper('DisableView')->isDisabled() === false) {
            if ($this->getClient()) {
                $this->getClient()->rewriteScriptPath($this);
            } else if ($this->getHelper('Layout') && $this->getHelper('Layout')->isEnabled()) {
                $moduleDir = $this->getModuleBootstrap()->getModuleDirectory();
                $this->getHelper('Layout')->setLayoutPath($moduleDir . DIRECTORY_SEPARATOR . 'layouts');
            }
        }

        if ($this->getClient()) {
            $this->getClient()->onEndOfMVC($this);
        }
    }

    /**
     * getClient
     *
     * @param void
     * @return Nutex_Client_Abstract|null
     */
    public function getClient()
    {
        if ($this->_client === null && $this->getBootstrap() instanceof Nutex_Bootstrap_Bootstrap) {
            $this->_client = $this->getBootstrap()->getClient();
        }
        return $this->_client;
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
     * getBootstrap
     *
     * @param void
     * @return Zend_Application_Bootstrap_Bootstrap
     */
    public function getBootstrap()
    {
        return $this->getInvokeArg('bootstrap');
    }

    /**
     * getModuleBootstrap
     *
     * @param void
     * @return Zend_Application_Module_Bootstrap
     */
    public function getModuleBootstrap()
    {
        return $this->getInvokeArg('bootstrap')->getResource('modules')->{$this->getRequest()->getModuleName()};
    }

    /**
     * getConfig
     * application.iniの設定を取得する
     *
     * @param string|null $name
     * @return mixed
     */
    public function getConfig($name = null)
    {
        return $this->getBootstrap()->getOption($name);
    }

    /**
     * getExtraConfig
     * application.ini拡張用設定項目を取得する
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getExtraConfig($name = null, $default = null)
    {
        if ($this->getBootstrap() instanceof Nutex_Bootstrap_Bootstrap) {
            return $this->getBootstrap()->getExtraOption($name, $default);
        }
        return $default;
    }

    /**
     * getSetting
     * 共通設定値取得
     * application.iniで定義されているはずの追加設定ファイルを読み込もうとします
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getSetting($name = null, $default = null)
    {
        if ($this->getBootstrap() instanceof Nutex_Bootstrap_Bootstrap) {
            return $this->getBootstrap()->getSetting($name, $default);
        }
        return $default;
    }

    /**
     * getModuleConfig
     * モジュール固有の設定値を取得
     * [moduleDirectory]/configs/ 以下のものを読みこもうとします
     *
     * @param string|null $name
     * @param string|null $fileName
     * @return mixed
     */
    public function getModuleConfig($name = null, $fileName = null)
    {
        if ($this->getModuleBootstrap() instanceof Nutex_Bootstrap_Module) {
            return $this->getModuleBootstrap()->getModuleOption($name, $fileName);
        }
        return null;
    }

    /**
     * getControllerConfig
     * コントローラー固有の設定値を取得
     * [moduleDirectory]/configs/[コントローラ名].[(共通)拡張子]
     *
     * @param string|null $name
     * @param string|null $controller
     * @return mixed
     */
    public function getControllerConfig($name = null, $controller = null)
    {
        $fileName = (is_string($controller)) ? $controller : $this->getRequest()->getControllerName();
        return $this->getModuleConfig($name, $fileName);
    }

    /**
     * getActionConfig
     * アクション固有の設定値を取得
     * [moduleDirectory]/configs/[コントローラ名]/[アクション名].[(共通)拡張子]
     *
     * @param string|null $name
     * @param string|null $action
     * @param string|null $controller
     * @return mixed
     */
    public function getActionConfig($name = null, $action = null, $controller = null)
    {
        $action = (is_string($action)) ? $action : $this->getRequest()->getActionName();
        $controller = (is_string($controller)) ? $controller : $this->getRequest()->getControllerName();
        $fileName = $controller . DIRECTORY_SEPARATOR . $action;
        return $this->getModuleConfig($name, $fileName);
    }

    /**
     * httpならhttpsにリダイレクトする
     * @param string $url
     * @return boolean
     */
    protected function _redirectIfHttp($url = null)
    {
        if ($this->getRequest()->isSecure() == false) {
            $this->_redirect($this->_urlWithProtocol('https'));
            return true;
        }
        return false;
    }

    /**
     * httpsならhttpにリダイレクトする
     * @param string $url
     * @return boolean
     */
    protected function _redirectIfHttps($url = null)
    {
        if ($this->getRequest()->isSecure()) {
            $this->_redirect($this->_urlWithProtocol('http'));
            return true;
        }
        return false;
    }

    /**
     * @param string $protocol
     * @param string|null $url
     */
    protected function _urlWithProtocol($protocol, $url = null)
    {
        $request = $this->getRequest();
        if (!$url) {
            $url = $request->getServer('HTTP_HOST') . $request->getServer('REQUEST_URI');
        }
        return $protocol . '://' . $url;
    }

    /**
     * httpなら404にする
     */
    protected function _rejectIfHttp()
    {
        if ($this->getRequest()->isSecure() == false) {
            throw new Zend_Controller_Dispatcher_Exception();
        }
    }

    /**
     * httpsなら404にする
     */
    protected function _rejectIfHttps()
    {
        if ($this->getRequest()->isSecure()) {
            throw new Zend_Controller_Dispatcher_Exception();
        }
    }

    /**
     * _setupSession
     * セッションのセットアップ
     *
     * @param array|null $options
     * @return void
     */
    protected function _setupSession($options = null)
    {
        if (!is_array($options)) {
            $options = array();
        }

        if (!isset($options['storageAdapter'])) {
            //デフォルトのストレージアダプタ
            $options['storageAdapter'] = array(
                'className' => 'file',
                'dir' => realpath(APPLICATION_PATH . '/../session'),
            );
        }

        Nutex_Session::setup($this, $options);
        Nutex_Login::setCurrentDivision($this);
    }

    /**
     * _setupErrorHandler
     *
     * @param void
     * @return void
     */
    protected function _setupErrorHandler()
    {
        //ErrorHandlerにモジュール名を教えてあげる
        $errorHandler = Zend_Controller_Front::getInstance()->getPlugin('Zend_Controller_Plugin_ErrorHandler');
        $request = $this->getRequest();
        if ($errorHandler) {
            $errorHandler->setErrorHandlerModule($request->getModuleName());
        }
    }
}
