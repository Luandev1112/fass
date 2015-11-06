<?php
/**
 * class Nutex_Validate_Labeled_NotEmptyArray
 *
 * ラベル付き配列用バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_NotEmptyArray extends Nutex_Validate_NotEmptyArray implements Nutex_Validate_Labeled_Interface
{
    /**
     * エラーメッセージ
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ENOUGH => '「%label%」に入力されていない項目があります',
        self::NOT_EMPTY => '「%label%」は必須項目です',
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