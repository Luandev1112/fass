<?php
/**
 * class Nutex_Validate_Url
 *
 * URLバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Url extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const INVALID_URL = 'invalidUrl';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID_URL => "URLの形式で入力して下さい",
    );

    /**
     * スキーマが必須かどうか
     * @var boolean
     */
    protected $_requireScheme = true;

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

        if (!$this->getRequireScheme() && !preg_match('@^[A-Za-z]+:@', $value)) {
            $value = 'http://' . $value;
        }

        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }

        return true;
    }

    /**
     * getRequireScheme
     *
     * @param void
     * @return boolean
     */
    public function getRequireScheme()
    {
        return $this->_requireScheme;
    }

    /**
     * setRequireScheme
     *
     * @param boolean $flag
     * @return $this
     */
    public function setRequireScheme($flag)
    {
        $this->_requireScheme = (boolean)$flag;
        return $this;
    }
}
