<?php
/**
 * class Nutex_Client_Abstract
 *
 * クライアント抽象クラス
 * isMe()で自分かどうかを判定します
 *
 * @package Nutex
 * @subpackage Nutex_Client
 */
abstract class Nutex_Client_Abstract
{
    /**
     * ファクトリメソッドでisMeさせるクラスのリスト
     * @var array
     */
    protected static $_clientList = array(
        'Nutex_Client_FeaturePhone',
        'Nutex_Client_SmartPhone',
    );

    /**
     * デフォルトクライアント名
     * @var string
     */
    protected static $_defaultClientName = 'Nutex_Client_Default';

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var string
     */
    protected $_name;

    /**
     * 自分のクライアントに紐づくviewが無い場合、親クライアントのviewを使うかどうか
     * @var boolean
     */
    protected $_overrideViewsByParents = true;

    /**
     * 自分のクライアントに紐づくviewが無い場合、デフォルトクライアントのviewを使うかどうか
     * @var boolean
     */
    protected $_overrideViewsByDefault = true;

    /**
     * @var null|array
     */
    protected $_clientNames = null;

    /**
     * factory
     * ファクトリメソッド
     *
     * @param array $options
     * @return Nutex_Client_Abstract
     */
    public static function factory($options = array())
    {
        /*
         * 各クラスの isMe() に判定させる 優先順は getClientList() の配列の順番
         */
        foreach (self::getClientList() as $client) {
            if (call_user_func(array($client, 'isMe'), $options)) {
                return new $client($options);
            }
        }

        $client = self::getDefaultClientName();
        return new $client($options);
    }

    /**
     * isMe
     * このクライアントかどうか判定する
     *
     * @param array $options
     * @return boolean
     */
    public static function isMe($options = array())
    {
        return false;
    }

    /**
     * getClientList
     *
     * @param void
     * @return array
     */
    public static function getClientList()
    {
        return self::$_clientList;
    }

    /**
     * setClientList
     *
     * @param array $clients
     * @return void
     */
    public static function setClientList(array $clients)
    {
        self::$_clientList = $clients;
    }

    /**
     * getDefaultClientName
     *
     * @param void
     * @return string
     */
    public static function getDefaultClientName()
    {
        return self::$_defaultClientName;
    }

    /**
     * setDefaultClientName
     *
     * @param string $client
     * @return void
     */
    public static function setDefaultClientName($client)
    {
        self::$_defaultClientName = (string)$client;
    }

    /**
     * classNameToName
     *
     * @param string $className
     * @return string
     */
    public static function classNameToName($className)
    {
        return strtolower(preg_replace('/^.*Client_/', '', $className));
    }


    /**
     * getHttpHeader
     * @see Zend_Controller_Request_Http::getHeader()
     *
     * @param string $header
     * @return string|array|null
     */
    public static function getHttpHeader($header = null)
    {
        if ($header === null) {
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') !== 0) {
                    continue;
                }
                $headers[$key] = $value;
            }
            return $headers;
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (isset($_SERVER[$header])) {
            return $_SERVER[$header];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * getUserAgent
     *
     * @param void
     * @return string|null
     */
    public static function getUserAgent()
    {
        return self::getHttpHeader('USER_AGENT');
    }

    /**
     * getIp
     *
     * @param void
     * @return string|null
     */
    public static function getIp()
    {
        return (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * getCookie
     *
     * @param string $key
     * @return mixed|null
     */
    public static function getCookie($key = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : null;
    }

    /**
     * __construct
     *
     * @param array $options
     * @return void
     */
    public function __construct($options = array())
    {
        $this->setOption($options);
    }

    /**
     * onStartMVC
     */
    public function onStartOfMVC()
    {
        //for override
    }

    /**
     * onEndMVC
     */
    public function onEndOfMVC()
    {
        //for override
    }

    /**
     * getOption
     *
     * @param string $key
     * @return mixed
     */
    public function getOption($key = null)
    {
        if (is_null($key)) {
            return $this->_options;
        }

        if (array_key_exists($key, $this->_options)) {
            return $this->_options[$key];
        }

        return null;
    }

    /**
     * setOption
     *
     * @param mixed
     * @return $this
     */
    public function setOption()
    {
        $args = func_get_args();
        $value = array_pop($args);
        $key = array_shift($args);

        if ($key) {
            $this->_options[$key] = $value;
        } else {
            $this->_options = (array)$value;
        }

        return $this;
    }

    /**
     * getName
     *
     * @param void
     * @return string
     */
    public function getName()
    {
        if (!$this->_name) {
            $this->_name = self::classNameToName(get_class($this));
        }
        return $this->_name;
    }

    /**
     * getOverrideViewsByParents
     *
     * @param void
     * @return string
     */
    public function getOverrideViewsByParents()
    {
        return $this->_overrideViewsByParents;
    }

    /**
     * setOverrideViewsByParents
     *
     * @param boolean $flag
     * @return $this
     */
    public function setOverrideViewsByParents($flag)
    {
        $this->_overrideViewsByParents = (boolean)$flag;
        $this->_clientNames = null;
        return $this;
    }

    /**
     * getOverrideViewsByDefault
     *
     * @param void
     * @return string
     */
    public function getOverrideViewsByDefault()
    {
        return $this->_overrideViewsByDefault;
    }

    /**
     * setOverrideViewsByDefault
     *
     * @param boolean $flag
     * @return $this
     */
    public function setOverrideViewsByDefault($flag)
    {
        $this->_overrideViewsByDefault = (boolean)$flag;
        $this->_clientNames = null;
        return $this;
    }

    /**
     * getClientNames
     * 親子関係を加味したクライアント名の配列を取得する
     *
     * @return array
     */
    public function getClientNames($namesDelimiter = '_')
    {
        if (is_array($this->_clientNames)) {
            return $this->_clientNames;
        }

        $this->_clientNames = array();

        if ($this->getOverrideViewsByParents()) {
            $parts = array();
            foreach (explode('_', $this->getName()) as $part) {
                $parts[] = $part;
                $this->_clientNames[] = implode($namesDelimiter, $parts);
            }
        } else {
            $this->_clientNames[] = str_replace('_', $namesDelimiter, $this->getName());
        }

        if ($this->getOverrideViewsByDefault()) {
            $default = str_replace('_', $namesDelimiter, self::classNameToName(self::getDefaultClientName()));
            if (!in_array($default, $this->_clientNames)) {
                array_unshift($this->_clientNames, $default);
            }
        }

        return $this->_clientNames;
    }

    /**
     * layoutとviewのパスを書き換える
     * @param Nutex_Controller_Abstract $controller
     */
    public function rewriteScriptPath(Nutex_Controller_Abstract $controller)
    {
        $moduleDir = $controller->getModuleBootstrap()->getModuleDirectory();
        if ($controller->getHelper('Layout') && $controller->getHelper('Layout')->isEnabled()) {
            $this->rewriteLayoutPath($moduleDir . DIRECTORY_SEPARATOR . 'layouts', $controller->getHelper('Layout')->getLayoutInstance());
        }

        $this->rewriteViewPath($moduleDir . DIRECTORY_SEPARATOR . 'views', $controller->view);
    }

    /**
     * layoutのパスを書き換える
     * @param string $baseDir
     * @param Nutex_Client $client
     * @param Zend_Layout $layout
     */
    public function rewriteLayoutPath($baseDir, Zend_Layout $layout)
    {
        foreach (array_reverse($this->getClientNames(DIRECTORY_SEPARATOR)) as $name) {//配列を逆順にしてます
            $path = $baseDir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $layout->getLayout() . '.' . $layout->getViewSuffix();
            if (is_readable($path)) {
                $layout->setLayoutPath(dirname($path));
                return;
            }
        }
        throw new Nutex_Exception_Error('no layouts for "' . $this->getName() . '" client');
    }

    /**
     * viewのパスを書き換える
     * @param string $baseDir
     * @param Nutex_Client $client
     * @param Zend_View_Interface $view
     */
    public function rewriteViewPath($baseDir, Zend_View_Interface $view)
    {
        foreach ($this->getClientNames(DIRECTORY_SEPARATOR) as $name) {
            $view->addBasePath($baseDir . DIRECTORY_SEPARATOR . $name);
        }
    }
}
