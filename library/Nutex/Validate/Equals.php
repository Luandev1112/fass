<?php
/**
 * class Nutex_Validate_Equals
 *
 * 完全一致バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Equals extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const NOT_EQUALS = 'notEquals';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EQUALS => "不正な値です",
    );

    /**
     * @var mixed
     */
    protected $_validValue = null;

    /**
     * @var boolean
     */
    protected $_strict = false;

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
        if (!$this->getValidValue()) {
            throw new Nutex_Exception_Error('valid value is required');
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

        if ($this->getStrict()) {
            if ($this->getValidValue() === $value) {
                return true;
            }
        } else {
            if ($this->getValidValue() == $value) {
                return true;
            }
        }

        if (is_object($value) && @class_exists($this->getValidValue()) && is_subclass_of($value, $this->getValidValue())) {
            return true;
        }

        $this->_error(self::NOT_EQUALS);
        return false;

    }

    /**
     * getValidValue
     *
     * @param void
     * @return mixed
     */
    public function getValidValue()
    {
        return $this->_validValue;
    }

    /**
     * setValidValue
     *
     * @param mixed $value
     * @return $this
     */
    public function setValidValue($value)
    {
        $this->_validValue = $value;
        return $this;
    }

    /**
     * getStrict
     *
     * @param void
     * @return boolean
     */
    public function getStrict()
    {
        return $this->_strict;
    }

    /**
     * setStrict
     *
     * @param boolean $flag
     * @return $this
     */
    public function setStrict($flag)
    {
        $this->_strict = $flag;
        return $this;
    }
}
