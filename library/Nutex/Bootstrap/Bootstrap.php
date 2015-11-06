<?php
/**
 * class Nutex_Bootstrap_Root
 *
 * 共通ブートストラップの基底クラス
 *
 * @package Nutex
 * @subpackage Nutex_Bootstrap
 */
class Nutex_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * application.ini上の拡張用名前空間
     * @var string
     */
    const EXTRA_OPTION_NAMESPACE = 'nutex';

    /**
     * 共通設定値
     * @var array
     */
    protected $_setting = null;

    /**
     * クライアントオブジェクト
     * @var array
     */
    protected $_client = null;

    /**
     * 拡張用のオプションを取得
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getExtraOption($key = null, $default = null)
    {
        $options = $this->getOption(self::EXTRA_OPTION_NAMESPACE);
        if (is_null($key)) {
            return $options;
        } elseif (is_array($options) && array_key_exists($key, $options)) {
            return $options[$key];
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
        if (!is_array($this->_setting)) {
            $filePath = $this->getExtraOption('settingFile');
            if (is_readable($filePath)) {
                $config = Nutex_LoadConfig::load($filePath)->toArray();
                $this->_setting = (array_key_exists(APPLICATION_ENV, $config)) ? $config[APPLICATION_ENV] : $config;
            } else {
                $this->_setting = array();
            }
        }

        if (is_null($name)) {
            return $this->_setting;
        } elseif (array_key_exists($name, $this->_setting)) {
            return $this->_setting[$name];
        }

        return $default;
    }

    /**
     * getClient
     *
     * @param void
     * @return Nutex_Client_Abstract|null
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * getDefaultModule
     * デフォルトモジュール設定は _initRequest() で変更することがあるので
     * 参照したい場合はこのメソッドを使用する
     *
     * @param void
     * @return string
     */
    public function getDefaultModuleReally()
    {
        $module = $this->getOption('defaultModuleReally');
        if ($module) {
            return $module;
        } else {
            return Zend_Controller_Front::getInstance()->getDefaultModule();
        }
    }

    /**
     * _initRoute
     *
     * @param void
     * @return void
     */
    protected function _initRoute()
    {
        $filePath = $this->getExtraOption('routeSettingFile');
        if ($filePath && is_readable($filePath)) {
            $config = new Zend_Config_Ini($filePath);
            Zend_Controller_Front::getInstance()->getRouter()->addConfig($config, 'routes');
        }
    }

    /**
     * _initRequest
     *
     * @param void
     * @return void
     */
    protected function _initRequest()
    {
        $options = $this->getExtraOption('request');
        $className = 'Nutex_Request';
        if (isset($options['class'])) {
            $className = $options['class'];
        }
        $request = new $className();
        $options = $this->getOptions();

        //デフォルトルートのセット
        if (isset($options['resources']['frontController'])) {
            $this->_setDefaultRoutes(
                (isset($options['resources']['frontController']['defaultModule'])) ? $options['resources']['frontController']['defaultModule'] : null,
                (isset($options['resources']['frontController']['defaultControllerName'])) ? $options['resources']['frontController']['defaultControllerName'] : null,
                (isset($options['resources']['frontController']['defaultAction'])) ? $options['resources']['frontController']['defaultAction'] : null
            );
        }

        Zend_Controller_Front::getInstance()->setRequest($request)
                                            ->getRouter()->removeDefaultRoutes()->route($request);

        //後で参照できるようにデフォルトモジュールを保存する
        $this->setOptions(array('defaultModuleReally' => Zend_Controller_Front::getInstance()->getDefaultModule()));

        //デフォルトモジュール以外の場合 デフォルトを変更する
        if (Zend_Controller_Front::getInstance()->getDefaultModule() !== $request->getModuleName()) {
            $this->_setDefaultRoutes($request->getModuleName(), null, null);
            Zend_Controller_Front::getInstance()->getDispatcher()->setParam('prefixDefaultModule', $request->getModuleName());
        }
    }

    /**
     * _initResponse
     *
     * @param void
     * @return void
     */
    protected function _initResponse()
    {
        $options = $this->getExtraOption('response');
        $className = 'Nutex_Response';
        if (isset($options['class'])) {
            $className = $options['class'];
        }
        $response = new $className();
        Zend_Controller_Front::getInstance()->setResponse($response);
    }

    /**
     * _initClient
     *
     * @param void
     * @return void
     */
    protected function _initClient()
    {
        $options = $this->getExtraOption('client');

        //clientの初期化
        if (isset($options['enabled']) && $options['enabled']) {

            //携帯ipデータの初期化
            $filePath = $this->getExtraOption('mobileIpFile');
            if ($filePath && is_readable($filePath)) {
                Nutex_Client_FeaturePhone::setMobileIps(Nutex_Util_ConfigFactory::createByPath($filePath));
                Nutex_Client_FeaturePhone::setCheckIp(true);
            }

            if (isset($options['class'])) {
                $className = $options['class'];
            } else {
                $className = 'Nutex_Client_Abstract';
            }

            $client = null;
            if (method_exists($className, 'factory')) {
                $client = call_user_func(array($className, 'factory'), $options);
            } else {
                $client = new $className($options);
            }
            if (!$client instanceof Nutex_Client_Abstract) {
                throw new Nutex_Exception_Error('invalid client class');
            }

            $this->_client = $client;
        }
    }

    /**
     * _initCrypt - 鍵ファイルから暗号化インスタンスを作成し、共通インスタンスとしてセットする
     *
     * @param void
     * @return void
     */
    protected function _initCrypt()
    {
	    /*
        $filePath = $this->getExtraOption('cryptKeyFile');
        if ($filePath && is_readable($filePath)) {
            $content = file($filePath);
            if (isset($content[0]) && isset($content[1])) {
                $instance = new Zend_Filter_Encrypt_Mcrypt(array(
                    'key' => $content[0],//１行目に秘密鍵
                    'vector' => $content[1],//２行目に暗号化ベクトル - バイト数固定なので注意 mcryptは8byte
                ));
                Nutex_Crypt::setInstance($instance);
            }
        }
        */
    }

    /**
     * _initParametersManager
     *
     * @param void
     * @return void
     */
    protected function _initParametersManager()
    {
        Nutex_Parameters_Filter::addPrefix($this->getAppNamespace() . 'Model_Filter_');
        Nutex_Parameters_Validate::addPrefix($this->getAppNamespace() . 'Model_Validate_');
    }

    /**
     * _initParametersManager
     *
     * @param void
     * @return void
     */
    protected function _setDefaultRoutes($module, $controller, $action)
    {
        $options = $this->getOptions();
        $resOptions = ($this->getPluginResource('Frontcontroller')) ? $this->getPluginResource('Frontcontroller')->getOptions() : null;

        if ($module) {
            Zend_Controller_Front::getInstance()->setDefaultModule($module);
            if (isset($options['resources']['frontController']['defaultModule'])) {
                $options['resources']['frontController']['defaultModule'] = $module;
            }
            if (isset($resOptions['defaultModule'])) {
                $resOptions['defaultModule'] = $module;
            }
        }

        if ($controller) {
            Zend_Controller_Front::getInstance()->setDefaultControllerName($controller);
            if (isset($options['resources']['frontController']['defaultControllerName'])) {
                $options['resources']['frontController']['defaultControllerName'] = $controller;
            }
            if (isset($resOptions['defaultControllerName'])) {
                $resOptions['defaultControllerName'] = $controller;
            }
        }

        if ($action) {
            Zend_Controller_Front::getInstance()->setDefaultAction($action);
            if (isset($options['resources']['frontController']['defaultAction'])) {
                $options['resources']['frontController']['defaultAction'] = $action;
            }
            if (isset($resOptions['defaultAction'])) {
                $resOptions['defaultAction'] = $action;
            }
        }

        $this->setOptions($options);
        if ($resOptions) {
            $this->getPluginResource('Frontcontroller')->setOptions($resOptions)->init();
        }
    }
}
