<?php
/**
 * class Nutex_Validate_CompareDate
 *
 * 日付比較バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_CompareDate extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const INVALID = 'invalid';
    const ERROR = 'error';
    const TOO_EARLY = 'tooEarly';
    const TOO_LATE = 'tooLate';

    /**
     * @var string
     */
    const METHOD_IS_EARLIER = 'isEarlier';
    const METHOD_IS_LATER = 'isLater';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID        => "不正な値です",
        self::ERROR        => "エラーが発生し、日付の比較ができませんでした",
        self::TOO_EARLY    => "%value% が %targetValue% よりも早い日付になっています",
        self::TOO_LATE    => "%value% が %targetValue% よりも遅い日付になっています",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'targetValue' => '_targetValue',
    );

    /**
     * 日付フォーマット
     * @var string
     */
    protected $_format = 'yyyy-MM-dd HH:mm:ss';

    /**
     * 比較に使うZend_Dateのメソッド
     * @var string
     */
    protected $_compareMethod = self::METHOD_IS_EARLIER;

    /**
     * 比較対象となる値
     * @var mixed
     */
    protected $_targetValue = '';

    /**
     * 同値を許可するかどうか
     * @var boolean
     */
    protected $_allowEqual = true;

    /**
     * Sets validator options
     *
     * @param  string|Zend_Config $options OPTIONAL
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $setter = 'set' . ucfirst($key);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ($this->getAllowEqual() && $value === $this->getTargetValue()) {
            return true;
        }

        $this->_setValue($value);
        $date = new Zend_Date($value, $this->getFormat());

        if (is_callable(array($date, $this->getCompareMethod()))) {
            if (call_user_func_array(array($date, $this->getCompareMethod()), array($this->getTargetValue(), $this->getFormat())) === false) {
                switch ($this->getCompareMethod()) {

                    case self::METHOD_IS_EARLIER:
                        $this->_error(self::TOO_LATE);
                        break;

                    case self::METHOD_IS_LATER:
                        $this->_error(self::TOO_EARLY);
                        break;

                    default:
                        $this->_error(self::INVALID);
                        break;

                }

                return false;
            }
        } else {
            $this->_error(self::ERROR);
            return false;
        }

        return true;
    }

    /**
     * getFormat
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * setFormat
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->_format = $format;
        return $this;
    }

    /**
     * getCompareMethod
     * @return string
     */
    public function getCompareMethod()
    {
        return $this->_compareMethod;
    }

    /**
     * setCompareMethod
     * @param string $method
     * @return $this
     */
    public function setCompareMethod($method)
    {
        $this->_compareMethod = $method;
        return $this;
    }

    /**
     * getTargetValue
     * @return string
     */
    public function getTargetValue()
    {
        return $this->_targetValue;
    }

    /**
     * setTargetValue
     * @param string $value
     * @return $this
     */
    public function setTargetValue($value)
    {
        $this->_targetValue = $value;
        return $this;
    }

    /**
     * getAllowEqual
     * @return string
     */
    public function getAllowEqual()
    {
        return $this->_allowEqual;
    }

    /**
     * setAllowEqual
     * @param boolean $value
     * @return $this
     */
    public function setAllowEqual($value)
    {
        $this->_allowEqual = (boolean) $value;
        return $this;
    }
}
