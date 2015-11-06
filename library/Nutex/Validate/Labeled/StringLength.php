<?php
/**
 * class Nutex_Validate_Labeled_StringLength
 *
 * ラベル付き文字長バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_StringLength extends Nutex_Validate_StringLength implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "「%label%」が文字列ではありません",
        self::TOO_SHORT => "「%label%」は%min%文字以上で入力して下さい",
        self::TOO_LONG  => "「%label%」は%max%文字以下で入力して下さい",
    );

    /**
     * @var array
     */
    protected $_messageTemplatesAlternative = array(
        self::INVALID   => "「%label%」が文字列ではありません",
        self::TOO_SHORT => "「%label%」は%min%文字で入力して下さい",
        self::TOO_LONG  => "「%label%」は%max%文字で入力して下さい",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max',
        'label' => '_label',
    );

    /**
     * @var array
     */
    protected $_label = '';

    /**
     * setLabel
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->_label = (string)$label;
        return $this;
    }

    /**
     * getLabel
     *
     * @param void
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }
}
