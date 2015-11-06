<?php
/**
 * class Nutex_Validate_Abstract_Filter
 *
 * フィルターを使用したバリデータの抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
abstract class Nutex_Validate_Abstract_Filter extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const NOT_MATCH = 'notMatch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "'%value%' に適切でない文字が含まれています",
    );

    /**
     * フィルターインスタンス
     * @var string|array|Zend_Filter_Interface
     */
    protected $_filter;

    /**
     * __construct
     *
     * @param array|Zend_Config $options
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            throw new Nutex_Exception_Error('invalid options');
        }
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * isValid
     * Defined by Zend_Validate_Interface
     *
     * @param void
     * @return string
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($value !== $this->getFilter()->filter($value)) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }

    /**
     * getFilter
     *
     * @param void
     * @return Zend_Filter_Interface
     */
    public function getFilter()
    {
        if (!$this->_filter instanceof Zend_Filter_Interface) {
            if (is_string($this->_filter)) {
                $className = $this->_filter;
                $this->_filter = new $className;
            } elseif (is_array($this->_filter) && count($this->_filter) == 2) {
                $className = array_shift($this->_filter);
                $option = array_shift($this->_filter);
                $this->_filter = new $className($option);
            }

            if (!$this->_filter instanceof Zend_Filter_Interface) {
                throw new Nutex_Exception_Error('invalid filter');
            }
        }

        return $this->_filter;
    }
}
