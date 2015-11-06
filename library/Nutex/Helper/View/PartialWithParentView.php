<?php
/**
 * class Nutex_Helper_View_PartialWithParentView
 *
 * 親のスコープと全く同じスコープで実行するパーシャルビューヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_PartialWithParentView extends Zend_View_Helper_Partial
{
    /**
     * @var Zend_View
     */
    protected $_view;

    /**
     * delegator to partial()
     */
    public function partialWithParentView()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'partial'), $args);
    }

    /**
     * do not clone
     *
     * @see Zend_View_Helper_Partial::cloneView()
     * @return Zend_View_Interface
     */
    public function cloneView()
    {
        return $this->getView();
    }

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
