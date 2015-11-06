<?php
/**
 * class Nutex_Login
 *
 * ログイン状態管理の静的マネージャクラス
 * Nutex_Session依存
 *
 * @package Nutex
 * @subpackage Nutex_Login
 * @see Nutex_Session
 */
class Nutex_Login
{
    /**
     * このクラスが使用するセッション名前空間
     * @var string
     */
    const SESSION_NAMESPACE = 'Nutex_Login';

    /**
     * 現在のログイン領域
     * @var string
     */
    protected static $_currentDivision = 'default';

    /**
     * getCurrentDivision
     *
     * @param void
     * @return string
     */
    public static function getCurrentDivision()
    {
        return self::$_currentDivision;
    }

    /**
     * setCurrentDivision
     *
     * @param mixed
     * @return string
     */
    public static function setCurrentDivision($division)
    {
        self::$_currentDivision = self::fixDivision($division);
    }

    /**
     * login
     *
     * @param array $data
     * @param mixed $division
     * @return boolean
     */
    public static function login(array $data = array(), $division = null)
    {
        $division = self::fixDivision($division);
        if ($division && Nutex_Session::isSetup() && !self::isLogined($division)) {
            Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
            Nutex_Session::set($division, $data, self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            return true;
        }
        return false;
    }

    /**
     * logout
     *
     * @param mixed $division
     * @return boolean
     */
    public static function logout($division = null)
    {
        $division = self::fixDivision($division);
        if ($division && Nutex_Session::isSetup() && self::isLogined($division)) {
            Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
            Nutex_Session::remove($division, self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            return true;
        }
        return false;
    }

    /**
     * isLogined
     *
     * @param mixed $division
     * @return boolean
     */
    public static function isLogined($division = null)
    {
        if (!Nutex_Session::isSetup()) {
            return false;
        }

        Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);

        $division = self::fixDivision($division);
        $result = false;
        if ($division && Nutex_Session::get($division, self::SESSION_NAMESPACE) !== null) {
            $result = true;
        }

        Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);

        return $result;
    }

    /**
     * getData
     *
     * @param string $name
     * @param mixed $division
     * @return mixed
     */
    public static function getData($name = null, $division = null)
    {
        $division = self::fixDivision($division);
        if ($division && self::isLogined($division)) {
            Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
            $data = Nutex_Session::get($division, self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            if (is_null($name)) {
                return $data;
            } elseif (array_key_exists($name, $data)) {
                return $data[$name];
            }
            return null;
        }
        return false;
    }

    /**
     * setData
     *
     * @param string $name
     * @param mixed $value
     * @param mixed $division
     * @return boolean
     */
    public static function setData($name, $value, $division = null)
    {
        $division = self::fixDivision($division);
        if ($division && self::isLogined($division)) {
            Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
            $data = Nutex_Session::get($division, self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            $data[$name] = $value;
            self::refleshData($data);
            return true;
        }
        return false;
    }

    /**
     * unsetData
     *
     * @param string $name
     * @param mixed $division
     * @return boolean
     */
    public static function unsetData($name, $division = null)
    {
        $division = self::fixDivision($division);
        if ($division && self::isLogined($division)) {
            Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
            $data = Nutex_Session::get($division, self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            unset($data[$name]);
            self::refleshData($data);
            return true;
        }
        return false;
    }

    /**
     * refleshData
     *
     * @param array $data
     * @param mixed $division
     * @return boolean
     */
    public static function refleshData(array $data, $division = null)
    {
        $division = self::fixDivision($division);
        if ($division && self::isLogined($division)) {
            Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
            Nutex_Session::set($division, $data, self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            return true;
        }
        return false;
    }

    /**
     * fixDivision
     *
     * @param mixed $input
     * @return string
     */
    public static function fixDivision($input = null)
    {
        if (is_null($input)) {
            return self::getCurrentDivision();
        }

        if (is_string($input)) {
            return $input;
        }

        if ($input instanceof Zend_Controller_Action) {
            $input = $input->getRequest();
        }
        if ($input instanceof Zend_Controller_Request_Abstract) {
            $input = $input->getModuleName();
        }

        return (string)$input;
    }
}
