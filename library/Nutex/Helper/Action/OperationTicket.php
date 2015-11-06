<?php
/**
 * class Nutex_Helper_Action_OperationTicket
 *
 * CSRF等対策の操作チケットヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_OperationTicket extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * direct
     *
     * @param mixed $namespace
     * @return void
     */
    public function direct($namespace = null)
    {
        return $this->publish($namespace);
    }

    /**
     * publish
     * チケット発行
     *
     * @param mixed $namespace
     * @return string $ticket
     */
    public function publish($namespace = null)
    {
        $ticket = Nutex_OperationTicket::publish($this->_fixNamespace($namespace));
        $this->getActionController()->view->getHelper('OperationTicket')->setTicket($ticket);
        return $ticket;
    }

    /**
     * sustain
     * リクエストに含まれるチケットを維持させる
     *
     * @param void
     * @return string $ticket
     */
    public function sustain()
    {
        $ticket = $this->_retrieveFromRequest();
        $this->getActionController()->view->getHelper('OperationTicket')->setTicket($ticket);
        return $ticket;
    }

    /**
     * check
     * チケットチェック
     *
     * @param mixed $namespace
     * @return boolean
     */
    public function check($namespace = null)
    {
        if ($this->_retrieveFromRequest() && $this->_retrieveFromRequest() === Nutex_OperationTicket::get($this->_fixNamespace($namespace))) {
            return true;
        } else {
            return false;

        }
    }

    /**
     * checkAndThrow
     * チケットをチェックしつつエラーならそのまま例外を投げる
     *
     * @param mixed $namespace
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function checkAndThrow($namespace = null)
    {
        if ($this->check($namespace) == false) {
            throw new Nutex_Exception_Error('ticket invalid');
        }
        Nutex_OperationTicket::remove($this->_fixNamespace($namespace));
    }

    /**
     * _retrieveFromRequest
     *
     * @param void
     * @return string
     */
    protected function _retrieveFromRequest()
    {
        $request = $this->getActionController()->getRequest();
        if ($request->isGet()) {
            $method = 'getQuery';
        } else {
            $method = 'getPost';
        }
        return $request->$method('__operation_ticket__');
    }

    /**
     * _fixNamespace
     * @param mixed $namespace
     * @return mixed $namespace
     */
    protected function _fixNamespace($namespace = null)
    {
        if (!$namespace) {
            $namespace = $this->getActionController();
        }
        return $namespace;
    }
}
