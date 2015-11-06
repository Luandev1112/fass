<?php
/**
 * class Nutex_Validate_StringLength
 *
 * 文字長バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_StringLength extends Zend_Validate_StringLength
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "文字列ではありません",
        self::TOO_SHORT => "%min%文字以上で入力して下さい",
        self::TOO_LONG  => "%max%文字以下で入力して下さい",
    );

    /**
     * @var array
     */
    protected $_messageTemplatesAlternative = array(
        self::INVALID   => "文字列ではありません",
        self::TOO_SHORT => "%min%文字で入力して下さい",
        self::TOO_LONG  => "%max%文字で入力して下さい",
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ($this->getMax() === $this->getMin()) {
            $messageTemplates = $this->_messageTemplates;
            $this->_messageTemplates = $this->_messageTemplatesAlternative;
        }

        $result = parent::isValid($value);

        if ($this->getMax() === $this->getMin()) {
            $this->_messageTemplates = $messageTemplates;
        }

        return $result;
    }
}
