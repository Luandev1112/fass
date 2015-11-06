<?php
/**
 * class Nutex_Controller_Base
 *
 * 基底コントローラ
 *
 * @package Nutex
 * @subpackage Nutex_Controller
 */
class Nutex_Controller_Base extends Nutex_Controller_Abstract
{
    /**
     * errorAction
     *
     * @param void
     * @return void
     */
    public function errorAction()
    {
        //errorhandler経由で来なければ404
        $errors = $this->_getParam('error_handler');
        if (!$errors || !$errors instanceof ArrayObject) {
            throw new Zend_Controller_Dispatcher_Exception();
        }

        $title = '';
        $messages = array();
        $priority = Zend_Log::WARN;
        $statusCode = 500;

        //エラー解析
        switch ($errors->type) {

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $statusCode = 404;
                $priority = Zend_Log::NOTICE;
                $title = 'ページが見つかりません';
                $messages[] = '大変お手数ですが、URLをご確認いただくか、もう一度トップページからやり直して下さい。';
                break;

            default:
                $statusCode = 500;
                $priority = Zend_Log::CRIT;
                $title = 'エラーが発生しました';
                $messages[] = 'エラーが発生しました。大変恐れ入りますが、しばらく後でやり直していただくか、管理者までお問い合わせ下さい。';
                break;

        }
        $this->getResponse()->setHttpResponseCode($statusCode);

        //ログを取る
        try {
            if (Nutex_Log_Error::getInstance()) {
                Nutex_Log_Error::write($errors->exception->getMessage(), $priority, ($priority <= Zend_Log::CRIT) ? $errors->exception : null);
                Nutex_Log_Error::write('Request Parameters', $priority, $errors->request->getParams());
            }
        } catch (Exception $excp) {
            if ($this->getInvokeArg('displayExceptions') == true) {
                $messages[] = $excp->getMessage();
            }
        }

        //viewにセット
        $this->view->title = $title;
        $this->view->messages = $messages;
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
            $this->_helper->viewRenderer->setScriptAction('error-display-exceptions');//詳細表示用view
        }
        $this->view->request = $errors->request;
    }
}
