<?php
/**
 * class Nutex_Validate_Labeled_Encoding
 *
 * ラベル付き文字コードバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_Encoding extends Nutex_Validate_Encoding implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "「%label%」に使用できない文字が含まれています => [%chars%]",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'chars' => '_chars',
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
