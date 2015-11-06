<?php
/**
 * class Nutex_Validate_Labeled_ZenkakuKana
 *
 * ラベル付き全角かな・カナバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_ZenkakuKana extends Nutex_Validate_ZenkakuKana implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "「%label%」は全てひらがな又は全角カタカナで入力して下さい",
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
