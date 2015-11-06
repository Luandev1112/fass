<?php
/**
 * class Nutex_Validate_Labeled_InArray
 *
 * ラベル付きInArrayバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_InArray extends Nutex_Validate_InArray implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_IN_ARRAY => "「%label%」が選択できない値です",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
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
