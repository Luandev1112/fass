<?php
/**
 * class Nutex_Validate_Numeric
 *
 * 数字かどうか
 * 3桁区切りカンマと小数点も有効 1234 or 12.34 2,000.00 (Nutex_Filter_NumberCommaでフィルターをかけて保存すること)
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Numeric extends Zend_Validate_Abstract
{

    /**
     * @var string
     */
    const NOT_NUMERIC = 'notNumeric';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_NUMERIC            => "数字のみで入力してください",
    );
	

    /**
     * Defined by Zend_Validate_Interface
     *
     * 
     * @param  string  $value
     * @return boolean
     */
    public function isValid($value)
    {
		if ($value === '') {
            return true;
        }

        $valueString = str_replace(',', '', (string)$value);

        $this->_setValue($valueString);

        if (is_numeric($valueString)) {
            return true;
        } else {
            $this->_error(self::NOT_NUMERIC);
            return false;
        }
    }
}
