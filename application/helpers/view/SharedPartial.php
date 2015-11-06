<?php
/**
 * class Shared_Helper_View_SharedPartial
 *
 * 共通パーシャルビューをレンダリングするヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_SharedPartial extends Zend_View_Helper_Partial
{
    /**
     * @var string
     */
    const TARGET_MODULE = 'front';

    /**
     * delegator to partial()
     */
    public function sharedPartial($name = null, $model = null)
    {
        //モジュール決め打ちでパーシャルを呼ぶ
        return $this->partial($name, self::TARGET_MODULE, $model);
    }

    /**
     * Clone the current View
     *
     * @return Zend_View_Interface
     */
    public function cloneView()
    {
        $view = parent::cloneView();

        //クライアントオブジェクトが取れたら、パスの書き換えを行う
        $client = $this->view->getClient();
        if ($client) {
            $moduleDir = dirname(Zend_Controller_Front::getInstance()->getControllerDirectory(self::TARGET_MODULE));
            $client->rewriteViewPath($moduleDir . DIRECTORY_SEPARATOR . 'views', $view);
        }

        return $view;
    }
}
