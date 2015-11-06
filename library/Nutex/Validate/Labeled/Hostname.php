<?php
/**
 * class Nutex_Validate_Labeled_Hostname
 *
 * ラベル付きホスト名バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Labeled_Hostname extends Nutex_Validate_Hostname implements Nutex_Validate_Labeled_Interface
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::CANNOT_DECODE_PUNYCODE  => "「%label%」が不正なホスト名のようです",
        self::INVALID                 => "「%label%」が不正なホスト名のようです",
        self::INVALID_DASH            => "「%label%」が不正なホスト名のようです",
        self::INVALID_HOSTNAME        => "「%label%」が不正なホスト名のようです",
        self::INVALID_HOSTNAME_SCHEMA => "「%label%」が不正なホスト名のようです",
        self::INVALID_LOCAL_NAME      => "「%label%」が不正なホスト名のようです",
        self::INVALID_URI             => "「%label%」が不正なホスト名のようです",
        self::IP_ADDRESS_NOT_ALLOWED  => "「%label%」が不正なホスト名のようです",
        self::LOCAL_NAME_NOT_ALLOWED  => "「%label%」が不正なホスト名のようです",
        self::UNDECIPHERABLE_TLD      => "「%label%」が不正なホスト名のようです",
        self::UNKNOWN_TLD             => "「%label%」が不正なホスト名のようです",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'tld' => '_tld',
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
