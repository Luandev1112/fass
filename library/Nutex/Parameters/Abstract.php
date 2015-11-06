<?php
/**
 * class Nutex_Parameters_Abstract
 *
 * パラメータ群に対して、まとめて何かするクラスの抽象クラス
 *
 * ＊設定配列の一例
 * array(
 *     self::PARAMETERS => array(
 *         'name' => array(
 *             self::PARAMETER_LABEL => 'パラメータ名',
 *             self::PARAMETER_BREAK_ON_ERROR => false,
 *             Nutex_Parameters_Validate::PARAMETER_AUTO_FILTER => 'pre',
 *             Nutex_Parameters_Validate::PARAMETER_VALIDATORS => array(
 *                 'notEmpty' => array(
 *                     self::COMPONENT_CLASS_NAME => 'notEmpty',
 *                     self::COMPONENT_OPTIONS => array(),
 *                     self::COMPONENT_SET_FROM_OTHER => array(),
 *                 ),
 *             ),
 *             Nutex_Parameters_Filter::PARAMETER_FILTERS => array(
 *                 'encrypt' => array(
 *                     self::COMPONENT_CLASS_NAME => 'encrypt',
 *                     self::COMPONENT_OPTIONS => array(),
 *                 ),
 *             ),
 *         ),
 *     ),
 *     self::BREAK_ON_ERRORS => false,
 * );
 *
 * @todo パラメータの多次元配列対応
 * @todo 外から各コンポーネントのインスタンスを参照できるようにしたい
 *
 * @package Nutex
 * @subpackage Nutex_Parameters
 */
abstract class Nutex_Parameters_Abstract
{
    /**
     * config keys
     * @var string
     */
    const PARAMETERS = 'parameters';
    const BREAK_ON_ERRORS = 'breakOnErrors';

    /**
     * parameter config keys
     * @var string
     */
    const PARAMETER_LABEL = 'label';
    const PARAMETER_BREAK_ON_ERROR = 'breakOnError';
    const PARAMETER_IGNORE_EMPTY = 'ignoreEmpty';
    const PARAMETER_IS_ARRAY = 'isArray';//未対応
    const PARAMETER_JOIN_FORMAT = 'joinFormat';//未対応

    /**
     * components config keys
     * @var string
     */
    const COMPONENT_CLASS_NAME = 'className';
    const COMPONENT_OPTIONS = 'options';
    const COMPONENT_SET_FROM_OTHER = 'setFromOther';
    const COMPONENT_BREAK_ON_ERROR = 'breakOnError';
    const COMPONENT_NAMESPACES = 'prefixes';

    /**
     * ignore empty param settings
     * @var string
     */
    const IGNORE_EMPTY_ALL = 'all';
    const IGNORE_EMPTY_NULL = 'null';
    const IGNORE_EMPTY_STRING = 'string';
    const IGNORE_EMPTY_NONE = 'none';

    /**
     * @var array
     */
    protected $_config = array();

    /**
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * @var array
     */
    protected $_instanceCache = array();

    /**
     * @var array
     */
    protected $_componentPrefixes = array();

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var boolean
     */
    protected $_partly = false;

    /**
     * execute
     *
     * @param array $params
     * @return mixed
     */
    abstract public function execute(array $params = array());

    /**
     * executeOne
     *
     * @param string $name
     * @param mixed $value
     * @param array $conf
     * @return mixed
     */
    abstract public function executeOne($name, $value, $conf);

    /**
     * __construct
     *
     * @param array|Zend_Config $config
     * @param array $options
     * @return void
     */
    public function __construct($config)
    {
        $this->setConfig($config);

        $ignoreKeys = array(
            self::PARAMETERS,
            self::BREAK_ON_ERRORS,
            'config',
            'Config',
        );

        foreach ($this->getConfig() as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * getConfig
     *
     * @param string|null $name
     * @return array
     */
    public function getConfig($name = null)
    {
        if (is_null($name)) {
            return $this->_config;
        } elseif (array_key_exists($name, $this->_config)) {
            return $this->_config[$name];
        }
        return null;
    }

    /**
     * setConfig
     *
     * @param array|Zend_Config $config
     * @return $this
     * @throws Nutex_Exception_Error
     */
    public function setConfig($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        if (!is_array($config)) {
            throw new Nutex_Exception_Error('invalid config');
        }
        $this->_config = $config;
        $this->_errorMessages = array();
        $this->_filtered = array();
        return $this;
    }

    /**
     * retrieveParam
     *
     * retrieveParam([配列キー値1], [配列キー値2], [配列キー値3] ...
     * と可変引数で配列の値を抽出できます
     *
     * @param mixed
     * @return mixed
     */
    public function retrieveParam()
    {
        $args = func_get_args();
        $retrieved = $this->_params;
        foreach ($args as $key) {
            if (is_array($retrieved) && array_key_exists($key, $retrieved)) {
                $retrieved = $retrieved[$key];
            } else {
                $retrieved = null;
                break;
            }
        }
        return $retrieved;
    }

    /**
     * paramExists
     *
     * retrieveParam([配列キー値1], [配列キー値2], [配列キー値3] ...
     * と可変引数で配列の値を抽出できます
     *
     * @param mixed
     * @return boolean
     */
    public function paramExists()
    {
        $args = func_get_args();
        $retrieved = $this->_params;
        foreach ($args as $key) {
            if (is_array($retrieved) && array_key_exists($key, $retrieved)) {
                $retrieved = $retrieved[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * setParams
     *
     * @param array
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * unsetParams
     *
     * @param void
     * @return void
     */
    public function unsetParams()
    {
        $this->_paramsBuff = array();
    }

    /**
     * getErrorMessage
     *
     * @param string $name
     * @return array|null
     */
    public function getErrorMessage($name = null)
    {
        if (is_null($name)) {
            return $this->_errorMessages;
        } elseif (array_key_exists($name, $this->_errorMessages)) {
            return $this->_errorMessages[$name];
        }
        return null;
    }

    /**
     * isErrored
     *
     * @param string $name
     * @return boolean
     */
    public function isErrored($name = null)
    {
        if (is_null($name)) {
            return (count($this->getErrorMessage()) > 0) ? true : false;
        } else {
            return ($this->getErrorMessage($name)) ? true : false;
        }
    }

    /**
     * getComponentPrefixes
     *
     * @param void
     * @return array
     */
    public function getComponentPrefixes()
    {
        return array_reverse($this->_componentPrefixes);
    }

    /**
     * setComponentPrefixes
     *
     * @param array $prefixes
     * @return $this
     */
    public function setComponentPrefixes(array $prefixes)
    {
        $this->_componentPrefixes = $prefixes;
        return $this;
    }

    /**
     * addComponentPrefix
     *
     * @param string $prefix
     * @return $this
     */
    public function addComponentPrefix($prefix)
    {
        $this->_componentPrefixes[] = $prefix;
        return $this;
    }

    /**
     * setPartly
     *
     * @param boolean $flag
     * @return $this
     */
    public function setPartly($flag = true)
    {
        $this->_partly = (bool)$flag;
        return $this;
    }

    /**
     * getPartly
     *
     * @return boolean
     */
    public function getPartly()
    {
        return $this->_partly;
    }

    /**
     * _addErrorMessage
     * エラーメッセージ登録
     *
     * @param string $name
     * @param string $type
     * @param string $message
     * @return void
     */
    protected function _addErrorMessage($name, $type, $message)
    {
        if (array_key_exists($name, $this->_errorMessages)) {
            $this->_errorMessages[$name] = array();
        }
        $this->_errorMessages[$name][$type] = $message;
    }

    /**
     * _isBreakByParameter
     *
     * @param array $conf
     * @return boolean
     */
    protected function _isBreakByParameter($conf)
    {
        if (isset($conf[self::PARAMETER_BREAK_ON_ERROR]) && $conf[self::PARAMETER_BREAK_ON_ERROR]) {
            return true;
        }

        $breakOnErrors = $this->getConfig(self::BREAK_ON_ERRORS);
        if (is_string($breakOnErrors) && $breakOnErrors) {
            $breakOnErrors = array($breakOnErrors);
        }
        if (is_array($breakOnErrors)) {
            foreach ($breakOnErrors as $name) {
                if ($this->isErrored($name) == false) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }


    /**
     * _isBreakByComponent
     *
     * @param array $conf
     * @return boolean
     */
    protected function _isBreakByComponent($conf)
    {
        if (isset($conf[self::COMPONENT_BREAK_ON_ERROR]) && $conf[self::COMPONENT_BREAK_ON_ERROR]) {
            return true;
        }

        return false;
    }

    /**
     * _ignoreEmpty
     *
     * @param array $conf
     * @return boolean
     */
    protected function _ignoreEmpty($conf, $value)
    {
        if (!isset($conf[self::PARAMETER_IGNORE_EMPTY])) {
            $conf[self::PARAMETER_IGNORE_EMPTY] = self::IGNORE_EMPTY_ALL;
        }

        if ($conf[self::PARAMETER_IGNORE_EMPTY] == self::IGNORE_EMPTY_ALL) {
            if ($value === null || $value === '') {
                return true;
            }
        }

        if ($conf[self::PARAMETER_IGNORE_EMPTY] == self::IGNORE_EMPTY_NULL) {
            if ($value === null) {
                return true;
            }
        }

        if ($conf[self::PARAMETER_IGNORE_EMPTY] == self::IGNORE_EMPTY_STRING) {
            if ($value === '') {
                return true;
            }
        }

        return false;
    }

    /**
     * _getComponent
     * 各コンポーネントのインスタンス取得
     * 同じクラスのインスタンスは、flyweightパターンに対応していることを期待して使いまわします
     *
     * @param string $key
     * @param array $setting
     * @param string $label
     * @return object|false
     */
    protected function _getComponent($key, $setting, $label = null)
    {
        if (isset($setting[self::COMPONENT_NAMESPACES]) && is_array($setting[self::COMPONENT_NAMESPACES])) {
            $prefixesPre = $this->getComponentPrefixes();
            $this->setComponentPrefixes($setting[self::COMPONENT_NAMESPACES]);
        }

        //クラス名を決定
        $className = null;
        if (isset($setting[self::COMPONENT_CLASS_NAME]) && is_string($setting[self::COMPONENT_CLASS_NAME])) {
            $className = $this->_findClassName($setting[self::COMPONENT_CLASS_NAME], $this->getComponentPrefixes());
        }
        if (!is_string($className)) {
            $className = $this->_findClassName($key, $this->getComponentPrefixes());
        }
        if (!is_string($className)) {
            return false;
        }

        //他のパラメータの値からセットする項目をまとめる
        $params = array();
        if (isset($setting[self::COMPONENT_SET_FROM_OTHER]) && is_array($setting[self::COMPONENT_SET_FROM_OTHER])) {
            $params = $setting[self::COMPONENT_SET_FROM_OTHER];
            foreach ($params as $name => $value) {
                $retrieved = $this->retrieveParam($value);
                if (!is_null($retrieved)) {
                    $params[$name] = $retrieved;
                } else {
                    unset($params[$name]);
                }
            }
        }

        //インスタンスの作成と初期化
        $options = array();
        if (isset($setting[self::COMPONENT_OPTIONS]) && is_array($setting[self::COMPONENT_OPTIONS])) {
            $options = $setting[self::COMPONENT_OPTIONS];
        }
        if (array_key_exists($className, $this->_instanceCache)) {
            //インスタンスがキャッシュされていれば使いまわす
            $component = $this->_instanceCache[$className];
            $this->_setToInstance($component, array_merge($options, $params));
        } else {
            //インスタンス作成
            $component = new $className(array_merge($options, $params));
        }

        //ラベルをセット
        if (is_string($label) && $component instanceof Nutex_Validate_Labeled_Interface) {
            $component->setLabel($label);
        }

        if (isset($setting[self::COMPONENT_NAMESPACES]) && is_array($setting[self::COMPONENT_NAMESPACES])) {
            $this->setComponentPrefixes($prefixesPre);
        }

        return $component;
    }

    /**
     * _setToInstance
     * インスタンスに対して、設定用配列を元にsetterを悉く実行する
     *
     * @param object $instance
     * @param array $setting
     * @return void
     */
    protected function _setToInstance($instance, array $setting)
    {
        foreach ($setting as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($instance, $setter)) {
                $instance->$setter($value);
            } else {
                $this->_addErrorMessage(get_class($instance), 'setterNotExists', 'set \'' . $name . '\' method does not exists');
            }
        }
    }

    /**
     * _findClassName
     * prefix群を参考にクラス名を探し出す
     *
     * @param string $name
     * @param array $prefixes
     * @return string|false
     */
    protected function _findClassName($name, array $prefixes)
    {
        array_unshift($prefixes, '');//prefixなしを先頭に追加
        foreach ($prefixes as $prefix) {
            $found = $prefix . ucfirst($name);
            if (class_exists($found)) {
                return $found;
            }
        }
        return false;
    }
}