<?php
/**
 * class Nutex_Helper_Action_DisableView
 *
 * view全般を無効にするヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_DisableView extends Zend_Controller_Action_Helper_Abstract
{
    protected $_isDisabled = false;

    /**
     * direct
     *
     * @param void
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->_isDisabled = false;
    }

    /**
     * direct
     *
     * @param void
     * @return void
     */
    public function direct()
    {
        $this->disable();
    }

    /**
     * disable
     *
     * @param void
     * @return void
     */
    public function disable()
    {
        $controller = $this->getActionController();
        if ($controller->getHelper('Layout')) {
            //レイアウトを使ってるっぽかったらレイアウトも無効にする
            $controller->getHelper('Layout')->disableLayout();
        }
        $controller->getHelper('ViewRenderer')->setNoRender();
        $this->_isDisabled = true;
    }

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->_isDisabled;
    }
}