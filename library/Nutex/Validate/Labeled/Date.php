<?php
/**
 * class Nutex_Validate_Labeled_Date
 *
 * ラベル付き日付バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_Date extends Nutex_Validate_Date implements Nutex_Validate_Labeled_Interface
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID        => "「%label%」は不正な値です",
        self::INVALID_DATE   => "「%label%」は日付の形式で入力して下さい",
        self::FALSEFORMAT    => "エラーが発生しました",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'format'  => '_format',
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
