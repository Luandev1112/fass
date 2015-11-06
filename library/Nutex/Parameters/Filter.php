<?php
/**
 * class Nutex_Parameters_Filter
 *
 * パラメータ群に対して、一括でフィルタリングを実行するクラス
 *
 * @package Nutex
 * @subpackage Nutex_Parameters
 */
class Nutex_Parameters_Filter extends Nutex_Parameters_Abstract
{
    /**
     * parameter config keys
     * @var string
     */
    const PARAMETER_FILTERS = 'filters';

    /**
     * @var array
     */
    protected static $_additionalPrefixes = array();

    /**
     * @var array
     */
    protected $_filtered = array();

    /**
     * @var array
     */
    protected $_componentPrefixes = array(
        'Zend_Filter_',
        'Nutex_Filter_',
    );

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
     * execute
     * 配列全てにフィルターをかける
     *
     * @todo 多次元配列への対応
     *
     * @param array $params
     * @return array
     */
    public function execute(array $params = array())
    {
        if ($params != array()) {
            $this->setParams($params);
            unset($param);
        }

        $this->_filtered = array();
        $this->_errorMessages = array();
        $result = true;

        $config = $this->getConfig(self::PARAMETERS);
        if (is_array($config)) {
            foreach ($config as $name => $conf) {
                if ($this->getPartly() && $this->paramExists($name) == false) {
                    continue;
                }

                $value = $this->retrieveParam($name);

                if ($this->_ignoreEmpty($conf, $value)) {
                    $this->_filtered[$name] = $value;
                    continue;
                }

                $this->_filtered[$name] = $this->executeOne($name, $value, $conf);
            }
        }

        $this->unsetParams();

        return $this->getFiltered();
    }

    /**
     * executeOne
     * 一つの値に対するフィルタリング処理
     *
     * @param string $name
     * @param mixed $value
     * @param array $conf
     * @return mixed $value
     */
    public function executeOne($name, $value, $conf)
    {
        if (isset($conf[self::PARAMETER_FILTERS]) && is_array($conf[self::PARAMETER_FILTERS])) {
            foreach ($conf[self::PARAMETER_FILTERS] as $key => $setting) {
                //インスタンス作成
                $filter = $this->_getComponent($key, $setting);
                if (!$filter || !$filter instanceof Zend_Filter_Interface) {
                    $this->_addErrorMessage($name, 'invalidFilter', "invalid filter on '$name => $key'");
                    continue;
                }

                //フィルタリング実行
                $value = $filter->filter($value);
            }
        }

        return $value;
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