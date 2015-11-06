<?php
/**
 * class Nutex_Helper_View_InstantMessage
 *
 * 一時メッセージを表示するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_InstantMessage extends Nutex_Helper_View_Abstract
{
    /**
     * 一時メッセージを表示する
     *
     * @param mixed
     * @return string
     */
    public function instantMessage()
    {
        $args = func_get_args();
        if (count($args) == 0) {
            $names = array();
            switch (Nutex_InstantMessage::getNamespaceSize()) {

                case Nutex_InstantMessage::NAMESPACE_SIZE_MODULE:
                    $names[] = $this->getView()->moduleName();
                    break;

                case Nutex_InstantMessage::NAMESPACE_SIZE_CONTROLLER:
                    $names[] = $this->getView()->moduleName();
                    $names[] = $this->getView()->controllerName();
                    break;

                case Nutex_InstantMessage::NAMESPACE_SIZE_ACTION:
                    $names[] = $this->getView()->moduleName();
                    $names[] = $this->getView()->controllerName();
                    $names[] = $this->getView()->actionName();
                    break;

                default:
                    $names[] = Nutex_InstantMessage::getNamespaceSize();
                    break;
            }
            $args = array($names);
        }

        $htmls = array();
        foreach (call_user_func_array(array('Nutex_InstantMessage', 'getMessages'), $args) as $message) {
            $htmls[] = $this->_renderMessage($message);
        }
        $this->_wrapHtmls($htmls);
        return implode("\r\n", $htmls);
    }

    /**
     * 一時メッセージをレンダリングする
     *
     * @param string $message
     * @return string
     */
    protected function _renderMessage($message)
    {
        return '<p class="message">' . $this->getView()->escape($message) . '</p>';
    }

    /**
     * メッセージ群をラッピングする
     *
     * @param array htmls
     * @return array
     */
    protected function _wrapHtmls(array &$htmls)
    {
        if (count($htmls) > 0) {
            array_unshift($htmls, '<div class="messageBlock">');
            $htmls[] = '</div>';
        }
    }
}
