<?php
/**
 * class Nutex_InstantMessage
 *
 * 一時メッセージを管理するクラス
 *
 * @package Nutex
 * @subpackage Nutex_InstantMessage
 * @see Zend_Controller_Action_Helper_FlashMessenger
 */
class Nutex_InstantMessage
{
    /**
     * このクラスが使用するセッション名前空間
     * @var string
     */
    const SESSION_NAMESPACE = 'Nutex_InstantMessage';

    /**
     * @var int
     */
    const LIFETIME = 86400;

    /**
     * @var string
     */
    const NAMESPACE_SIZE_MODULE = 'module';
    const NAMESPACE_SIZE_CONTROLLER = 'controller';
    const NAMESPACE_SIZE_ACTION = 'action';

    protected static $_namespaceSize = self::NAMESPACE_SIZE_CONTROLLER;

    /**
     * getMessages
     *
     * @param string $name
     * @param mixed
     * @return array
     */
    public static function getMessages()
    {
        if (!Nutex_Session::isSetup()) {
            return null;
        }

        $args = func_get_args();
        $namespace = call_user_func_array(array('Nutex_InstantMessage', 'getNamespace'), $args);

        Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
        $messages = Nutex_Session::get($namespace, self::SESSION_NAMESPACE);

        if (is_array($messages)) {
            Nutex_Session::set($namespace, array(), self::SESSION_NAMESPACE);
            Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
            return $messages;
        }

        Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
        return array();
    }

    /**
     * addMessage
     *
     * @param string $message
     * @param mixed
     * @return void
     */
    public static function addMessage($message)
    {
        if (!Nutex_Session::isSetup()) {
            return null;
        }

        $args = func_get_args();
        array_shift($args);
        $namespace = call_user_func_array(array('Nutex_InstantMessage', 'getNamespace'), $args);

        Nutex_Session::removeForbiddenNamespace(self::SESSION_NAMESPACE);
        $messages = Nutex_Session::get($namespace, self::SESSION_NAMESPACE);
        if (!is_array($messages)) {
            $messages = array();
        }
        $messages[] = $message;
        Nutex_Session::set($namespace, $messages, self::SESSION_NAMESPACE);
        Nutex_Session::addForbiddenNamespace(self::SESSION_NAMESPACE);
    }

    /**
     * getNamespace
     *
     * @param mixed
     * @return string
     */
    public static function getNamespace()
    {
        $args = func_get_args();
        $names = array();
        $request = null;
        if (count($args) > 1) {
            $names = $args;
        } elseif (isset($args[0]) && is_array($args[0])) {
            $names = $args[0];
        } elseif (isset($args[0])) {
            if ($args[0] instanceof Zend_Controller_Action) {
                $request = $args[0]->getRequest();
            } elseif ($args[0] instanceof Zend_Controller_Request_Abstract) {
                $request = $args[0];
            }

            switch (self::getNamespaceSize()) {

                case self::NAMESPACE_SIZE_MODULE:
                    $names[] = $request->getModuleName();
                    break;

                case self::NAMESPACE_SIZE_CONTROLLER:
                    $names[] = $request->getModuleName();
                    $names[] = $request->getControllerName();
                    break;

                case self::NAMESPACE_SIZE_ACTION:
                    $names[] = $request->getModuleName();
                    $names[] = $request->getControllerName();
                    $names[] = $request->getActionName();
                    break;

                default:
                    $names[] = self::getNamespaceSize();
                    break;

            }
        }

        return implode('_', $names);
    }

    /**
     * @return string
     */
    public static function getNamespaceSize()
    {
        return self::$_namespaceSize;
    }

    /**
     * @param string $namespaceSize
     */
    public static function setNamespaceSize($namespaceSize)
    {
        self::$_namespaceSize = (string) $namespaceSize;
    }
}
