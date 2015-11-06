<?php
/**
 * class Nutex_Mail_Error
 *
 * エラーメールクラス
 *
 * @package Nutex
 * @subpackage Nutex_Mail
 */
class Nutex_Mail_Error extends Nutex_Mail_Abstract
{
    /**
     * @var string
     */
    protected static $_defaultSubject = '[ERROR] critical error ocurred';

    /**
     * @var string
     */
    protected static $_defaultFromAddress = null;

    /**
     * @var array
     */
    protected static $_defaultToAddresses = array();

    /**
     * @var array
     */
    protected static $_defaultCcAddresses = array();

    /**
     * setDefaultFromAddress
     * @param string $address
     */
    public static function setDefaultFromAddress($address)
    {
        self::$_defaultFromAddress = $address;
    }

    /**
     * addDefaultToAddress
     * @param string $address
     */
    public static function addDefaultToAddress($address)
    {
        self::$_defaultToAddresses[] = $address;
    }

    /**
     * addDefaultCcAddress
     * @param string $address
     */
    public static function addDefaultCcAddress($address)
    {
        self::$_defaultCcAddresses[] = $address;
    }

    /**
     * setDefaultSubject
     * @param string $subject
     */
    public static function setDefaultSubject($subject)
    {
        self::$_defaultSubject = $subject;
    }

    /**
     * isAble
     * @return boolean
     */
    public static function isAble()
    {
        return (self::$_defaultFromAddress && count(self::$_defaultToAddresses) > 0 && count(self::$_defaultCcAddresses) > 0);
    }

    /**
     * constructMail
     *
     * @param mixed $input
     * @return $this
     */
    public function constructMail($input = null)
    {
        $this->setFrom(self::$_defaultFromAddress);
        foreach (self::$_defaultToAddresses as $address) {
            $this->addTo($address);
        }
        foreach (self::$_defaultCcAddresses as $address) {
            $this->addCc($address);
        }
        $this->setSubject(self::$_defaultSubject);

        return $this;
    }
}
