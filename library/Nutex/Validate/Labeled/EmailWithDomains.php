<?php
/**
 * class Nutex_Validate_Labeled_EmailWithDomains
 *
 * ラベル付きドメイン制限つきメールアドレスバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_EmailWithDomains extends Nutex_Validate_EmailWithDomains implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ALLOWED_DOMAIN => "「%label%」は使用できないメールアドレスです",
        self::INVALID            => "「%label%」はメールアドレスの形式で入力して下さい",
        self::INVALID_FORMAT     => "「%label%」はメールアドレスの形式で入力して下さい",
        self::INVALID_HOSTNAME   => "「%label%」は存在しないメールアドレスのようです",
        self::INVALID_MX_RECORD  => "「%label%」は存在しないメールアドレスのようです",
        self::INVALID_SEGMENT    => "「%label%」は存在しないメールアドレスのようです",
        self::DOT_ATOM           => "「%label%」はメールアドレスの形式で入力して下さい",
        self::QUOTED_STRING      => "「%label%」はメールアドレスの形式で入力して下さい",
        self::INVALID_LOCAL_PART => "「%label%」はメールアドレスの形式で入力して下さい",
        self::LENGTH_EXCEEDED    => "「%label%」はメールアドレスとして長すぎます",
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
