<?php
/**
 * class Nutex_Validate_NumericInteger
 *
 * 数字かどうか
 * 3桁区切りカンマはOK,小数点不可(整数のみ) 1234 or 1,234 (Nutex_Filter_NumberCommaでフィルターをかけて保存すること)
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_NumericInteger extends Zend_Validate_Abstract
{

    /**
     * @var string
     */
    const NOT_NUMERIC = 'notNumericInteger';

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
		
        if (is_numeric($valueString) && strpos($valueString, '.') === false) {
            return true;
        } else {
            $this->_error(self::NOT_NUMERIC);
            return false;
        }
    }
}
