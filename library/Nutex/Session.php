<?php
/**
 * class Nutex_Session
 *
 * セッションの静的マネージャクラス
 *
 * @package Nutex
 * @subpackage Nutex_Session
 */
class Nutex_Session
{
    /**
     * @var string
     */
    const NAMESPACE_DELIMITER = '/';

    /**
     * @var string
     */
    const NAMESPACE_META_INFO = 'META_INFO';
    const META_INFO_LAST_ACCESSED = 'lastAccessed';
    const META_INFO_LAST_REGENERATED = 'lastRegenerated';
    const META_INFO_CURRENT_CLIENT_IP = 'currentClientIp';
    const META_INFO_PREV_CLIENT_IP = 'prevClientIp';

    /**
     * @var string
     */
    const DEFAULT_SESSION_CLASS = 'Nutex_Session_Cookie';

    /**
     * @var Nutex_Session_Abstract
     */
    protected static $_sessionInstance = null;

    /**
     * @var string
     */
    protected static $_defaultNamespace = null;

    /**
     * 使用禁止の名前空間
     * @var array
     */
    protected static $_forbiddenNamespaces = array(
        Nutex_Login::SESSION_NAMESPACE,
        Nutex_OperationTicket::SESSION_NAMESPACE,
        Nutex_OperationTicket::SESSION_NAMESPACE,
        self::NAMESPACE_META_INFO,
    );

    /**
     * setup
     *
     * @param Nutex_Controller_Abstract $controller
     * @param array $options
     * @return void
     * @throws Nutex_Exception_Error
     */
    public static function setup(Nutex_Controller_Abstract $controller, array $options = array())
    {
        //クラスの指定によるクラス名特定
        $className = (isset($options['className'])) ? $options['className'] : null;
        if (!is_null($className) && @class_exists($className) == false) {
            $prefixes = array(
                'Nutex_Session_'
            );
            foreach ($prefixes as $prefix) {
                $className = $prefix . ucfirst($options['className']);
                if (@class_exists($className)) {
                    break;
                } else {
                    $className = null;
                }
            }
        }

        //クラス名の指定が無ければクライアントオブジェクトをもとにセッションクラスの決定
        if (is_null($className)) {
            if ($controller->getClient() instanceof Nutex_Client_Abstract) {
                switch (get_class($controller->getClient())) {

                    default:
                        $className = self::DEFAULT_SESSION_CLASS;
                        break;

                }
            } else {
                $className = self::DEFAULT_SESSION_CLASS;
            }
        }

        //ストレージアダプタの作成
        if (isset($options['storageAdapter'])) {
            $storageAdapter = self::storageAdapterFactory($options['storageAdapter']);
        } else {
            throw new Nutex_Exception_Error('session settings \'storageAdapter\' is required');
        }
        self::$_sessionInstance = new $className($storageAdapter, $controller->getRequest(), $controller->getResponse(), $options);

        //セッション名前空間の設定
        self::setDefaultNamespace($controller);
    }

    /**
     * shutdown
     *
     * 放っておいても Nutex_Session_Abstract::__destruct() で shutdown() されるので明示的にシャットダウンしたいとき用
     * シャットダウンするともう一度 Nutex_Session_Abstract::setup() するまでセッションデータにアクセスできないので注意
     *
     * @param void
     * @return void
     */
    public static function shutdown()
    {
        if (self::$_sessionInstance instanceof Nutex_Session_Abstract) {
            return self::$_sessionInstance->shutdown();
        }
    }

    /**
     * storageAdapterFactory
     * ストレージアダプタ用のファクトリーメソッド
     *
     * @param array $settings
     * @return void
     * @throws Nutex_Exception_Error
     */
    public static function storageAdapterFactory(array $options)
    {
        if (!isset($options['className'])) {
            throw new Nutex_Exception_Error('session settings \'storageAdapter.className\' is required');
        }

        //クラス名の特定
        $className = $options['className'];
        if (@class_exists($className) == false || is_subclass_of($className, 'Nutex_Session_StorageAdapter_Abstract') == false) {
            $prefixes = array(
                'Nutex_Session_StorageAdapter_'
            );
            foreach ($prefixes as $prefix) {
                $className = $prefix . ucfirst($options['className']);
                if (@class_exists($className)) {
                    break;
                }
            }
        }

        //インスタンスの作成
        $requires = array();
        switch ($className) {

            case 'Nutex_Session_StorageAdapter_File':
                if (!isset($options['dir'])) {
                    throw new Nutex_Exception_Error('session settings \'storageAdapter.dir\' is required');
                }
                return new Nutex_Session_StorageAdapter_File($options['dir']);

            case 'Nutex_Session_StorageAdapter_Memcached':
                if (!isset($options['memcached'])) {
                    throw new Nutex_Exception_Error('session settings \'storageAdapter.memcached\' is required');
                }
                return new Nutex_Session_StorageAdapter_Memcached($options['memcached']);

            case 'Nutex_Session_StorageAdapter_Memcache':
                if (!isset($options['memcache'])) {
                    throw new Nutex_Exception_Error('session settings \'storageAdapter.memcache\' is required');
                }
                return new Nutex_Session_StorageAdapter_Memcache($options['memcache']);

            default:
                throw new Nutex_Exception_Error('session settings \'storageAdapter\' is invalid');

        }
    }

    /**
     * getInstance
     *
     * @param void
     * @return Nutex_Session_Abstract|null
     */
    public static function getInstance()
    {
        return self::$_sessionInstance;
    }

    /**
     * getStorageAdapter
     *
     * @param void
     * @return Nutex_Session_StorageAdapter_Abstract|null
     */
    public static function getStorageAdapter()
    {
        self::_setupCheck();

        return self::$_sessionInstance->getStorageAdapter();
    }

    /**
     * get
     *
     * @param string $name
     * @param string $namespace
     * @return mixed
     */
    public static function get($name = null, $namespace = null)
    {
        self::_setupCheck();

        $namespace = self::fixNamespace($namespace);
        self::_namespaceCheck($namespace);

        $values = self::getInstance()->get($namespace, array());
        if (is_null($name)) {
            return $values;
        } elseif (array_key_exists($name, $values)) {
            return $values[$name];
        }
        return null;
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed $value
     * @param mixed $namespace
     * @return void
     */
    public static function set($name, $value, $namespace = null)
    {
        self::_setupCheck();

        $namespace = self::fixNamespace($namespace);
        self::_namespaceCheck($namespace);

        $values = self::get(null, $namespace);
        $values[$name] = $value;
        self::getInstance()->$namespace = $values;
    }

    /**
     * remove
     *
     * @param string $name
     * @param mixed $namespace
     * @return void
     */
    public static function remove($name, $namespace = null)
    {
        self::_setupCheck();

        $namespace = self::fixNamespace($namespace);
        self::_namespaceCheck($namespace);

        $values = self::get(null, $namespace);
        unset($values[$name]);
        self::getInstance()->$namespace = $values;
    }

    /**
     * unsetNamespace
     *
     * @param mixed $namespace
     * @return void
     */
    public static function unsetNamespace($namespace = null)
    {
        self::_setupCheck();

        $namespace = self::fixNamespace($namespace);
        self::_namespaceCheck($namespace);

        unset(self::getInstance()->$namespace);
    }

    /**
     * getDefaultNamespace
     *
     * @param void
     * @return string
     */
    public static function getDefaultNamespace()
    {
        return self::$_defaultNamespace;
    }

    /**
     * setDefaultNamespace
     *
     * @param mixed $input
     * @return void
     */
    public static function setDefaultNamespace($input)
    {
        $namespace = self::fixNamespace($input);
        self::_namespaceCheck($namespace);

        self::$_defaultNamespace = $namespace;
    }

    /**
     * fixNamespace
     *
     * @param mixed $input
     * @return string
     */
    public static function fixNamespace($input = null)
    {
        if (is_null($input)) {
            return self::getDefaultNamespace();
        }

        if (is_string($input)) {
            return $input;
        }

        if ($input instanceof Zend_Controller_Action) {
            $input = $input->getRequest();
        }
        if ($input instanceof Zend_Controller_Request_Abstract) {
            $input = array(
                $input->getModuleName(),
            );
        }

        if (is_array($input)) {
            return implode(self::NAMESPACE_DELIMITER, $input);
        }

        return (string)$input;
    }

    /**
     * isSetup
     *
     * @param void
     * @return boolean
     */
    public static function isSetup()
    {
        if (self::$_sessionInstance instanceof Nutex_Session_Abstract && self::getInstance()->isOpened()) {
            return true;
        }
        return false;
    }

    /**
     * isShutdowned
     *
     * @param void
     * @return boolean
     */
    public static function isShutdowned()
    {
        if (self::$_sessionInstance instanceof Nutex_Session_Abstract && self::getInstance()->isShutdowned()) {
            return true;
        }
        return false;
    }

    /**
     * getId
     *
     * @param void
     * @return string
     */
    public static function getId()
    {
        self::_setupCheck();

        return self::getInstance()->getId();
    }

    /**
     * regenerateId
     *
     * @param void
     * @return void
     */
    public static function regenerateId()
    {
        self::_setupCheck();

        if (self::getInstance()->alreadyExists()) {
            self::getInstance()->regenerateId();
        }
    }

    /**
     * getMetaInfo
     *
     * @param void
     * @return void
     */
    public static function getMetaInfo()
    {
        $metaInfo = self::get(self::NAMESPACE_META_INFO);
        if (!is_array($metaInfo)
            || !array_key_exists(self::META_INFO_LAST_ACCESSED, $metaInfo)
            || !array_key_exists(self::META_INFO_LAST_REGENERATED, $metaInfo)
            || !array_key_exists(self::META_INFO_CURRENT_CLIENT_IP, $metaInfo)
            || !array_key_exists(self::META_INFO_PREV_CLIENT_IP, $metaInfo)
        ) {
            $metaInfo = array(
                self::META_INFO_LAST_ACCESSED => time(),
                self::META_INFO_LAST_REGENERATED => time(),
                self::META_INFO_CURRENT_CLIENT_IP => (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null,
                self::META_INFO_PREV_CLIENT_IP => null,
            );
        }
        return $metaInfo;
    }

    /**
     * updateMetaInfo
     *
     * @param void
     * @return void
     */
    public static function updateMetaInfo()
    {
        $metaInfo = self::getMetaInfo();
        $metaInfo[self::META_INFO_LAST_ACCESSED] = time();
        $metaInfo[self::META_INFO_PREV_CLIENT_IP] = $metaInfo[self::META_INFO_CURRENT_CLIENT_IP];
        $metaInfo[self::META_INFO_CURRENT_CLIENT_IP] = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
        self::set(self::NAMESPACE_META_INFO, $metaInfo);
    }

    /**
     * regenerateIfIntervalPassed
     *
     * @param int $interval
     * @return void
     */
    public static function regenerateIfIntervalPassed($interval)
    {
        $metaInfo = self::getMetaInfo();
        if ($interval <= time() - $metaInfo[self::META_INFO_LAST_REGENERATED]) {
            self::regenerateId();
            $metaInfo[self::META_INFO_LAST_REGENERATED] = time();
        }
        self::set(self::NAMESPACE_META_INFO, $metaInfo);
    }

    /**
     * addForbiddenNamespace
     *
     * @param mixed $namespace
     * @return void
     */
    public static function addForbiddenNamespace($namespace)
    {
        $namespace = self::fixNamespace($namespace);
        if (!in_array($namespace, self::$_forbiddenNamespaces)) {
            self::$_forbiddenNamespaces[] = $namespace;
        }
    }

    /**
     * removeForbiddenNamespace
     *
     * @param mixed $namespace
     * @return void
     */
    public static function removeForbiddenNamespace($namespace)
    {
        $namespace = self::fixNamespace($namespace);
        foreach (array_keys(self::$_forbiddenNamespaces) as $key) {
            if (self::$_forbiddenNamespaces[$key] == $namespace) {
                unset(self::$_forbiddenNamespaces[$key]);
            }
        }
        self::$_forbiddenNamespaces = array_values(self::$_forbiddenNamespaces);//キー値整理
    }

    /**
     * _setupCheck
     *
     * @param void
     * @return void
     * @throws Nutex_Exception_Error
     */
    protected static function _setupCheck()
    {
        if (self::isSetup() == false) {
            throw new Nutex_Exception_Error('please call Nutex_Session::setup()');
        }
    }

    /**
     * _namespaceCheck
     *
     * @param string $namespace
     * @return void
     * @throws Nutex_Exception_Error
     */
    protected static function _namespaceCheck($namespace)
    {
        if (in_array($namespace, self::$_forbiddenNamespaces)) {
            throw new Nutex_Exception_Error('invalid namespace');
        }
    }
}
