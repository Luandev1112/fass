<?php
/**
 * class Shared_Model_Validate_DateSeparate
 *
 * フォームが分かれている場合のdateチェックを行う
 * 年にValidateをひっかけて使う
 *
 * @package Shared
 * @subpackage Shared_Model_Validate
 */
class Shared_Model_Validate_DateSeparate extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const INVALID_DATE = 'invalid_date';
    const REQUIRED     = 'required';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID_DATE   => '存在しない日付です',
        self::REQUIRED       => '必須項目です',
    );

    /**
     * monthの値
     * @var mixed
     */
    protected $_monthValue = null;

    /**
     * dateの値
     * @var mixed
     */
    protected $_dateValue = null;

    /**
     * 日付であることが必須かどうか
     * @var boolean
     */
    protected $_isRequired = false;

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
     * @param mixed $year
     * @return string
     */
    public function isValid($year)
    {
        $this->_setValue($year);
        // 必須チェック
        if ($this->_isRequired && !($year && $this->_monthValue && $this->_dateValue)) {
            $this->_error(self::REQUIRED);
            return false;
        }
        // 日付チェック
        if ($year && $this->_monthValue && $this->_dateValue) {
            $zendDate = new Zend_Date();
            $part = 'yyyy-MM-dd';
            if ($zendDate->isDate(sprintf('%04d-%02d-%02d', $year, $this->_monthValue, $this->_dateValue), $part)) {
                return true;
            } else {
                $this->_error(self::INVALID_DATE);
                return false;
            }
        }
        return true;
    }

    /**
     * getMonthValue
     * @return mixed
     */
    public function getMonthValue()
    {
        return $this->_monthValue;
    }

    /**
     * setMonthValue
     *
     * @param mixed $value
     * @return $this
     */
    public function setMonthValue($value)
    {
        $this->_monthValue = $value;
        return $this;
    }

    /**
     * getDateValue
     * @return mixed
     */
    public function getDateValue()
    {
        return $this->_dateValue;
    }

    /**
     * setDateValue
     *
     * @param mixed $value
     * @return $this
     */
    public function setDateValue($value)
    {
        $this->_dateValue = $value;
        return $this;
    }

    /**
     * setRequired
     * @param boolean $isRequired
     */
    public function setRequired($isRequired) {
        $this->_isRequired = $isRequired;
    }
}
