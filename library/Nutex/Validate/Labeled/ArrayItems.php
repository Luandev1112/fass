<?php
/**
 * class Nutex_Validate_Labeled_ArrayItems
 *
 * ラベル付きArrayItemsバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_ArrayItems extends Nutex_Validate_ArrayItems implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID_ITEM => "「%label%」に選択できない値が含まれています",
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
