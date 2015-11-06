<?php
/**
 * class Nutex_Parameters_Validate
 *
 * パラメータ群に対して、一括でバリデーション（とフィルタリング）を実行するクラス
 *
 * @package Nutex
 * @subpackage Nutex_Parameters
 */
class Nutex_Parameters_Validate extends Nutex_Parameters_Abstract
{
    /**
     * parameter config keys
     * @var string
     */
    const PARAMETER_VALIDATORS = 'validators';
    const PARAMETER_REQUIRED = 'required';
    const PARAMETER_AUTO_FILTER = 'autoFilter';

    /**
     * PARAMETER_AUTO_FILTER values
     * @var string
     */
    const AUTO_FILTER_PRE = 'pre';
    const AUTO_FILTER_POST = 'post';
    const AUTO_FILTER_NONE = 'none';

    /**
     * @var array
     */
    protected static $_additionalPrefixes = array();

    /**
     * バリデーション時の自動でフィルタリング設定
     * @var boolean
     */
    protected $_autoFilter = self::AUTO_FILTER_POST;

    /**
     * execute() でinvalidだった値を $_filtered に入れないかどうか
     * @var boolean
     */
    protected $_invalidParamRemove = false;

    /**
     * @var array
     */
    protected $_filtered = array();

    /**
     * @var array
     */
    protected $_componentPrefixes = array(
        'Zend_Validate_',
        'Nutex_Validate_',
    );

    /**
     * 必須チェック用バリデータ名
     * @var string
     */
    protected $_requiredValidatorName = 'notEmpty';

    /**
     * addPrefix
     *
     * @param string $prefix
     * @return void
     */
    public static function addPrefix($prefix)
    {
        self::$_additionalPrefixes[] = (string)$prefix;
    }

    /**
     * __construct
     *
     * @param array|Zend_Config $config
     * @return void
     */
    public function __construct($config)
    {
        foreach (self::$_additionalPrefixes as $prefix) {
            $this->addComponentPrefix($prefix);
        }

        parent::__construct($config);
    }

    /**
     * getAutoFilter
     *
     * @param void
     * @return string
     */
    public function getAutoFilter()
    {
        if (!is_string($this->_autoFilter) || $this->_autoFilter === self::AUTO_FILTER_NONE) {
            return false;
        } else {
            return $this->_autoFilter;
        }
    }

    /**
     * setAutoFilter
     *
     * @param string $flag
     * @return $this
     */
    public function setAutoFilter($flag)
    {
        $this->_autoFilter = (string)$flag;
        return $this;
    }

    /**
     * getInvalidParamRemove
     *
     * @param void
     * @return boolean
     */
    public function getInvalidParamRemove()
    {
        return $this->_invalidParamRemove;
    }

    /**
     * setInvalidParamRemove
     *
     * @param string $flag
     * @return $this
     */
    public function setInvalidParamRemove($flag = true)
    {
        $this->_invalidParamRemove = (boolean)$flag;
        return $this;
    }

    /**
     * getRequiredValidatorName
     *
     * @param void
     * @return string
     */
    public function getRequiredValidatorName()
    {
        return $this->_requiredValidatorName;
    }

    /**
     * setRequiredValidatorName
     *
     * @param string $name
     * @return $this
     */
    public function setRequiredValidatorName($name)
    {
        $this->_requiredValidatorName = (string)$name;
        return $this;
    }

    /**
     * execute
     * 配列全てをバリデーションする
     *
     * @todo 多次元配列への対応
     *
     * @param array $params
     * @return boolean $result
     */
    public function execute(array $params = array())
    {
        if ($params != array()) {
            $this->setParams($params);
            unset($param);
        }

        if ($this->getAutoFilter()) {
            $this->_filtered = array();
            $filter = new Nutex_Parameters_Filter($this->getConfig());
        }

        $this->_errorMessages = array();
        $result = true;

        $config = $this->getConfig(self::PARAMETERS);
        $break = false;
        if (is_array($config)) {
            foreach ($config as $name => $conf) {
                if ($this->getPartly() && $this->paramExists($name) == false) {
                    continue;
                }

                $value = $this->retrieveParam($name);

                $autoFilter = (isset($conf[self::PARAMETER_AUTO_FILTER])) ? $conf[self::PARAMETER_AUTO_FILTER] : null;
                if ($autoFilter === self::AUTO_FILTER_PRE || $this->getAutoFilter() === self::AUTO_FILTER_PRE) {
                    $value = $filter->executeOne($name, $value, $conf);
                }

                if ($break == false && $this->executeOne($name, $value, $conf)) {
                    if ($autoFilter === self::AUTO_FILTER_POST || $this->getAutoFilter()  === self::AUTO_FILTER_POST) {
                        $this->_filtered[$name] = $filter->executeOne($name, $value, $conf);
                    } else {
                        $this->_filtered[$name] = $value;
                    }
                } else {
                    $result = false;
                    if ($this->getInvalidParamRemove() == false) {
                        if ($autoFilter === self::AUTO_FILTER_POST || $this->getAutoFilter()  === self::AUTO_FILTER_POST) {
                            $this->_filtered[$name] = $filter->executeOne($name, $value, $conf);
                        } else {
                            $this->_filtered[$name] = $value;
                        }
                    }
                    if ($this->_isBreakByParameter($conf)) {
                        $break = true;
                    }
                }
            }
        }

        $this->unsetParams();

        return $result;
    }

    /**
     * executeOne
     * 一つの値に対するバリデーション処理
     *
     * @param string $name
     * @param mixed $value
     * @param array $conf
     * @return boolean
     */
    public function executeOne($name, $value, $conf)
    {
        //必須チェックを追加
        if (isset($conf[self::PARAMETER_REQUIRED]) && $conf[self::PARAMETER_REQUIRED]) {
            if (!isset($conf[self::PARAMETER_VALIDATORS]) || !is_array($conf[self::PARAMETER_VALIDATORS])) {
                $conf[self::PARAMETER_VALIDATORS] = array();
            }
            $conf[self::PARAMETER_VALIDATORS][self::PARAMETER_REQUIRED] = array(
                self::COMPONENT_CLASS_NAME => $this->getRequiredValidatorName(),
                self::COMPONENT_BREAK_ON_ERROR => true,
            );
        }

        if (!isset($conf[self::PARAMETER_VALIDATORS])) {
            return true;
        }

        if (!is_array($conf[self::PARAMETER_VALIDATORS])) {
            $this->_addErrorMessage($name, 'invalidValidatorSetting', "invalid validator settings on '$name'");
            return false;
        }

        $label = null;
        if (isset($conf[self::PARAMETER_LABEL])) {
            $label = $conf[self::PARAMETER_LABEL];
        }

        $result = true;
        foreach ($conf[self::PARAMETER_VALIDATORS] as $key => $setting) {
            //空値無視チェック
            if ($key != self::PARAMETER_REQUIRED && $this->_ignoreEmpty($conf, $value)) {
                continue;
            }

            //インスタンス作成
            $validator = $this->_getComponent($key, $setting, $label);
            if (!$validator || !$validator instanceof Zend_Validate_Abstract) {
                $this->_addErrorMessage($name, 'invalidValidator', "invalid validator on '$name => $key'");
                $result = false;
                if ($this->_isBreakByComponent($setting)) {
                    break;
                } else {
                    continue;
                }
            }

            //バリデーション実行
            if (!$validator->isValid($value)) {
                foreach ($validator->getMessages() as $type => $message) {
                    $this->_addErrorMessage($name, $type, $message);
                }
                $result = false;
                if ($this->_isBreakByComponent($setting)) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * getFiltered
     *
     * @param string $name
     * @return array|mixed
     */
    public function getFiltered($name = null)
    {
        if (is_null($name)) {
            return $this->_filtered;
        } elseif (array_key_exists($name, $this->_filtered)) {
            return $this->_filtered[$name];
        }
        return null;
    }
}