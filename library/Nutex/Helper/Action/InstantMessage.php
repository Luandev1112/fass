<?php
/**
 * class Nutex_Helper_Action_InstantMessage
 *
 * 一時メッセージを管理するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 * @see Zend_Controller_Action_Helper_FlashMessenger
 */
class Nutex_Helper_Action_InstantMessage extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * direct
     *
     * @param string $message
     * @return void
     */
    public function direct($message)
    {
        return $this->add($message);
    }

    /**
     * メッセージを追加
     *
     * @param string $message
     * @param mixed
     * @return $this
     */
    public function add($message)
    {
        $args = func_get_args();
        if (count($args) == 1) {
            $args[] = $this->getActionController();
        }
        call_user_func_array(array('Nutex_InstantMessage', 'addMessage'), $args);
        return $this;
    }

    /**
     * メッセージを取得
     *
     * @param mixed
     * @return array
     */
    public function get()
    {
        $args = func_get_args();
        if (count($args) == 0) {
            $args[] = $this->getActionController();
        }
        return call_user_func_array(array('Nutex_InstantMessage', 'getMessages'), $args);
    }
}
