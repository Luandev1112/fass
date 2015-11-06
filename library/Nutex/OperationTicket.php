<?php
/**
 * class Nutex_OperationTicket
 *
 * CSRF等対策の操作チケットを管理するクラス
 *
 * @package Nutex
 * @subpackage Nutex_OperationTicket
 */
class Nutex_OperationTicket
{
    /**
     * @var string
     */
    const HASH_SALT = 'Fa32_YowepdAsd9';

    /**
     * このクラスが使用するセッション名前空間
     * @var string
     */
    const SESSION_NAMESPACE = 'Nutex_OperationTicket';

    /**
     * @var int
     */
    const LIFETIME = 86400;

    /**
     * publish
     *
     * @param mixed
     * @return string
     */
    public static function publish()
    {
        if (!Nutex_Session::isSetup()) {
            return null;
        }

        $args = func_get_args();
        $namespace = call_user_func_array(array('Nutex_OperationTicket', 'getOperationNamespace'), $args);

        $base = self::HASH_SALT . mt_rand() . uniqid(null, true);
        $converter = new Nutex_BaseConvert_Convert(array(
            'baseChars' => 'URL',
            'baseCharsFrom' => 16,
        ));
        $array = array(
            $converter->convert(hash('sha256', $base)),
            Nutex_Date::getDiffer(self::LIFETIME, Zend_Date::TIMESTAMP),
        );

        Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
        Nutex_Session::set($namespace, $array, self::SESSION_NAMESPACE);
        Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);

        return array_shift($array);
    }

    /**
     * get
     *
     * @param mixed
     * @return string
     */
    public static function get()
    {
        if (!Nutex_Session::isSetup()) {
            return null;
        }

        $args = func_get_args();
        $namespace = call_user_func_array(array('Nutex_OperationTicket', 'getOperationNamespace'), $args);

        Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
        $array = Nutex_Session::get($namespace, self::SESSION_NAMESPACE);

        $ticket = null;
        $lifetime = null;
        if (is_array($array) && count($array) == 2) {
            $ticket = array_shift($array);
            $lifetime = array_shift($array);
        }

        //lifetime内の場合だけ値を返却
        if ($ticket && $lifetime && $lifetime >= Nutex_Date::get(Zend_Date::TIMESTAMP)) {
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            return $ticket;
        }

        Nutex_Session::remove($namespace, self::SESSION_NAMESPACE);
        Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);

        return null;
    }

    /**
     * remove
     *
     * @param mixed
     * @return void
     */
    public static function remove()
    {
        if (!Nutex_Session::isSetup()) {
            return;
        }

        $args = func_get_args();
        $namespace = call_user_func_array(array('Nutex_OperationTicket', 'getOperationNamespace'), $args);

        Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
        Nutex_Session::remove($namespace, self::SESSION_NAMESPACE);
        Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
    }

    /**
     * getOperationNamespace
     *
     * @param mixed
     * @return string
     */
    public static function getOperationNamespace()
    {
        $args = func_get_args();
        $names = array();
        $request = null;
        if (count($args) > 1) {
            $names = $args;
        } elseif (isset($args[0]) && is_string($args[0])) {
            $names = array($args[0]);
        } elseif (isset($args[0]) && is_array($args[0])) {
            $names = $args[0];
        } elseif (isset($args[0])) {
            if ($args[0] instanceof Zend_Controller_Action) {
                $request = $args[0]->getRequest();
            } elseif ($args[0] instanceof Zend_Controller_Request_Abstract) {
                $request = $args[0];
            }
            $names[] = $request->getModuleName();
            $names[] = $request->getControllerName();
            $names[] = $request->getActionName();
        }

        return implode('_', $names);
    }
}
