<?php
/**
 * class Nutex_Helper_View_Abstract
 *
 * ビューヘルパー抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_Abstract extends Zend_View_Helper_Abstract
{
    /**
     * @var Zend_View
     */
    protected $_view;

    /**
     * viewオブジェクトを取得する
     *
     * @param void
     * @return Zend_View_Interface
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * viewオブジェクトをセットする
     *
     * @param Zend_View_Interface $view
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        if (!$this->_view instanceof Zend_View_Interface) {
            $this->_view = $view;
        }
    }
}
