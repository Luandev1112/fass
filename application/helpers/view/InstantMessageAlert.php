<?php
/**
 * class Shared_Helper_View_InstantMessageAlert
 *
 * 一時メッセージをアラート表示するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_InstantMessageAlert extends Nutex_Helper_View_InstantMessage
{
    /**
     * 一時メッセージを表示する
     *
     * @param mixed
     * @return string
     */
    public function instantMessageAlert()
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'instantMessage'), $args);
    }

    /**
     * 一時メッセージをレンダリングする
     *
     * @param string $message
     * @return string
     */
    protected function _renderMessage($message)
    {
        if (is_array($message) && count($message) == 2) {
            return "setTimeout(function(){util.alert('" . $this->getView()->escape($message[1]) . "', '" . $this->getView()->escape($message[0]) . "');}, 0);\r\n";
        } else {
            return "setTimeout(function(){util.alert('" . $this->getView()->escape($message) . "');}, 0);\r\n";
        }
    }

    /**
     * メッセージ群をラッピングする
     *
     * @param array htmls
     * @return array
     */
    protected function _wrapHtmls(array &$htmls)
    {
        //nothing to do
    }
}
