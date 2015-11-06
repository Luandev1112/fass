<?php
/**
 * class Nutex_Helper_View_OperationTicket
 *
 * CSRF等対策の操作チケットのビューヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_OperationTicket extends Nutex_Helper_View_Abstract
{
    /**
     * @var string
     */
    protected $_ticket = null;

    /**
      * チケットをhiddenで表示する
      *
      * @param string $ticket
      * @return string
      */
    public function operationTicket($ticket = null)
    {
        if ($ticket) {
            $this->setTicket($ticket);
            return;
        } elseif ($this->getTicket()) {
            return $this->getView()->formHidden('__operation_ticket__', $this->getTicket());
        }

        return '';
    }
    /**
      * チケットをセットする
      *
      * @param string $ticket
      * @return $this
      */
    public function setTicket($ticket)
    {
       $this->_ticket = (string)$ticket;
    }

    /**
      * チケットを取得する
      *
      * @param void
      * @return string
      */
    public function getTicket()
    {
        return $this->_ticket;
    }
}
