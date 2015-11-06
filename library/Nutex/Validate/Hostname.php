<?php
/**
 * class Nutex_Validate_Hostname
 *
 * ホスト名バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Hostname extends Zend_Validate_Hostname
{
    /**
     * FIXME もう少しマシにしたい
     * @var array
     */
    protected $_messageTemplates = array(
        self::CANNOT_DECODE_PUNYCODE  => "不正なホスト名のようです",
        self::INVALID                 => "不正なホスト名のようです",
        self::INVALID_DASH            => "不正なホスト名のようです",
        self::INVALID_HOSTNAME        => "不正なホスト名のようです",
        self::INVALID_HOSTNAME_SCHEMA => "不正なホスト名のようです",
        self::INVALID_LOCAL_NAME      => "不正なホスト名のようです",
        self::INVALID_URI             => "不正なホスト名のようです",
        self::IP_ADDRESS_NOT_ALLOWED  => "不正なホスト名のようです",
        self::LOCAL_NAME_NOT_ALLOWED  => "不正なホスト名のようです",
        self::UNDECIPHERABLE_TLD      => "不正なホスト名のようです",
        self::UNKNOWN_TLD             => "不正なホスト名のようです",
    );
}
