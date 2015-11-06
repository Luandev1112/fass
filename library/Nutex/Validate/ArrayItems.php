<?php
/**
 * class Nutex_Validate_ArrayItems
 *
 * 配列の値に不正なものがないかチェックするバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_ArrayItems extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const INVALID = 'invalid';
    const INVALID_ITEM = 'invalidItem';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "不正な値です",
        self::INVALID_ITEM => "選択できない値が含まれています",
    );

    protected $_validItems = array();

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

        if (is_array($options)) {
            if (array_key_exists('codeClass', $options) && array_key_exists('codeName', $options) ) {
                $args = array($options['codeName']);
                if (array_key_exists('codeOmits', $options)) {
                    $args[] = $options['codeOmits'];
                }
                $codes = call_user_func_array(array($options['codeClass'], 'codes'), $args);
                if (is_array($codes)) {
                    $options['validItems'] = array_keys($codes);
                }
                unset($options['codeClass'], $options['codeName'], $options['codeOmits']);
            }

            foreach ($options as $key => $value) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
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
        if ($value === array()) {
            return true;
        }

        $this->_setValue($value);

        if (is_array($value) === false) {
            $this->_error(self::INVALID);
            return false;
        }

        foreach ($value as $val) {
            if (in_array($val, $this->getValidItems()) === false) {
                $this->_error(self::INVALID_ITEM);
                return false;
            }
        }

        return true;
    }

    /**
     * getValidItems
     *
     * @param void
     * @return array
     */
    public function getValidItems()
    {
        return $this->_validItems;
    }

    /**
     * setValidItems
     *
     * @param array $values
     * @return $this
     */
    public function setValidItems(array $values)
    {
        $this->_validItems = $values;
        return $this;
    }
}
