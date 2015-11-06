<?php
/**
 * class Nutex_Helper_Action_MvcInfo
 *
 * デバッグ用にMVC関連（というかViewとController）のオブジェクトから情報をまるっと取得するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_MvcInfo extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * direct
     *
     * @param void
     * @return array
     */
    public function direct()
    {
        return $this->info();
    }

    /**
     * info
     *
     * @param void
     * @return array
     */
    public function info()
    {
        $objects = array(
            'layout' => Zend_Layout::getMvcInstance(),
            'view' => $this->getActionController()->view,
            'controller' => $this->getActionController(),
            'action helper' => Zend_Controller_Action_HelperBroker::getPluginLoader()->getPaths(),
            'ViewRenderer' => $this->getActionController()->getHelper('ViewRenderer'),
        );
        return Nutex_Debug_ReflectObjects::getters($objects);
    }
}