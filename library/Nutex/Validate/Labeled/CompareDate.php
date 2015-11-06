<?php
/**
 * class Nutex_Validate_Labeled_CompareDate
 *
 * 日付比較バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_CompareDate extends Nutex_Validate_CompareDate implements Nutex_Validate_Labeled_Interface
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID        => "「%label%」は不正な値です",
        self::ERROR        => "「%label%」でエラーが発生し、日付の比較ができませんでした",
        self::TOO_EARLY    => "「%label%」の %value% が %targetValue% よりも早い日付になっています",
        self::TOO_LATE    => "「%label%」の %value% が %targetValue% よりも遅い日付になっています",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'label' => '_label',
        'targetValue' => '_targetValue',
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
