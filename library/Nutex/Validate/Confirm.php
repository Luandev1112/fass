<?php
/**
 * class Nutex_Validate_Confirm
 *
 * 値の確認バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Confirm extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const NOT_MATCH = 'notMatch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "確認用の値と異なっています",
    );

    /**
     * 確認用の値
     * @var mixed
     */
    protected $_confirmValue = null;

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

        if ($value !== $this->getConfirmValue()) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }

    /**
     * getConfirmValue
     *
     * @param void
     * @return mixed
     */
    public function getConfirmValue()
    {
        return $this->_confirmValue;
    }

    /**
     * setConfirmValue
     *
     * @param mixed $value
     * @return $this
     */
    public function setConfirmValue($value)
    {
        $this->_confirmValue = $value;
        return $this;
    }
}
