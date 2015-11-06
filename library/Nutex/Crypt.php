<?php
/**
 * class Nutex_Crypt
 *
 * 暗号化用の共通インスタンスを抱えるだけのクラス
 *
 * @package Nutex
 * @subpackage Nutex_Crypt
 */
class Nutex_Crypt
{
    /**
     * @var object
     */
    protected static $_instance = null;

    /**
     * exists
     *
     * @param void
     * @return boolean
     */
    public static function exists()
    {
        return is_object(self::$_instance);
    }

    /**
     * getInstance
     *
     * @param void
     * @return object
     * @throws Nutex_Exception_Error
     */
    public static function getInstance()
    {
        if (self::exists() == false) {
            throw new Nutex_Exception_Error('please call Nutex_Crypt::setInstance()');
        }
        return self::$_instance;
    }

    /**
     * setInstance
     *
     * @param object $instance
     * @return void
     * @throws Nutex_Exception_Error
     */
    public static function setInstance($instance)
    {
        if (!is_object($instance) || !method_exists($instance, 'encrypt') || !method_exists($instance, 'decrypt')) {
            throw new Nutex_Exception_Error('not seem to be a crypt object');
        }
        self::$_instance = $instance;
    }
}
