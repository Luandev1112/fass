<?php
/**
 * class Nutex_Response
 *
 * Zend_Controller_Response_Http拡張
 *
 * @package Nutex
 * @subpackage Nutex_Response
 */
class Nutex_Response extends Zend_Controller_Response_Http
{
    /**
     * @var callback
     */
    protected $_bodyConverter = null;

    /**
     * Echo the body segments
     *
     * @return void
     */
    public function outputBody()
    {
        $body = implode('', $this->_body);
        if ($this->getBodyConverter()) {
            echo call_user_func($this->getBodyConverter(), $body);
        } else {
            echo $body;
        }
    }

    /**
     * setBodyConverter
     *
     * @param callback $callback
     * @return $this
     */
    public function setBodyConverter($callback)
    {
        if (is_callable($callback)) {
            $this->_bodyConverter = $callback;
        } else {
            throw new Nutex_Exception_Error('invalid body converter callback');
        }
        return $this;
    }

    /**
     * getBodyConverter
     *
     * @param void
     * @return callback|null
     */
    public function getBodyConverter()
    {
        return $this->_bodyConverter;
    }
}
