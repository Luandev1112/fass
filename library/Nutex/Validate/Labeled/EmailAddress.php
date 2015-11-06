<?php
/**
 * class Nutex_Validate_Labeled_EmailAddress
 *
 * ラベル付きメールアドレスバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_EmailAddress extends Nutex_Validate_EmailAddress implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID            => "「%label%」はメールアドレスの形式で入力して下さい",
        self::INVALID_FORMAT     => "「%label%」はメールアドレスの形式で入力して下さい",
        self::INVALID_HOSTNAME   => "「%label%」が存在しないメールアドレスのようです",
        self::INVALID_MX_RECORD  => "「%label%」が存在しないメールアドレスのようです",
        self::INVALID_SEGMENT    => "「%label%」が存在しないメールアドレスのようです",
        self::DOT_ATOM           => "「%label%」はメールアドレスの形式で入力して下さい",
        self::QUOTED_STRING      => "「%label%」はメールアドレスの形式で入力して下さい",
        self::INVALID_LOCAL_PART => "「%label%」はメールアドレスの形式で入力して下さい",
        self::LENGTH_EXCEEDED    => "「%label%」がメールアドレスとして長すぎます",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'hostname'  => '_hostname',
        'localPart' => '_localPart',
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
