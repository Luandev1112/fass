<?php
/**
 * class Shared_Helper_Action_History
 *
 * 遷移ヒストリ
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Shared_Helper_Action_History extends Zend_Controller_Action_Helper_Abstract
{
    const SESSION_KEY = 'Shared_Helper_Action_History';

    /**
     * @var array
     */
    protected $_stack = array();

    /**
     * init
     * @return void
     */
    public function init()
    {
        $this->_stack = Nutex_Session::get(self::SESSION_KEY);
        if (!is_array($this->_stack)) {
            $this->_stack = array();
        }
    }

    /**
     * postDispatch
     * @return void
     */
    public function postDispatch()
    {
        Nutex_Session::set(self::SESSION_KEY, $this->_stack);
    }

    /**
     * direct
     *
     * @param string $url
     * @param boolean $controllerGrouping
     * @return void
     */
    public function direct($url = null, $controllerGrouping = false)
    {
        return $this->push($url, $controllerGrouping);
    }

    /**
     * pop
     *
     * @param boolean $controllerGrouping
     * @return string
     */
    public function pop($controllerGrouping = false)
    {
        $current = $this->getRequest()->getServer('REQUEST_URI');
        $stack = array();
        foreach ($this->_stack as $url) {
            $stack[] = $url;
            if ($url == $current) {
                $url = array_pop($stack);
                $this->_stack = $stack;
                break;
            }
        }

        if ($controllerGrouping && $this->_stack != array()) {
            $count = count($this->_stack);
            $lasts = explode('/', $this->_stack[$count - 1]);
            $parts = explode('/', $this->getRequest()->getServer('REQUEST_URI'));
            if ($lasts >= 2 && $parts >= 2
                && $lasts[1] == $parts[1]) {
                array_pop($this->_stack);
            }
        }

        if ($this->_stack != array()) {
            return $this->_stack[count($this->_stack) - 1];
        } else {
            return null;
        }
    }

    /**
     * push
     *
     * @param string $url
     * @param boolean $controllerGrouping
     * @return $this
     */
    public function push($url = null, $controllerGrouping = false)
    {
        $request = $this->getRequest();
        if (!$url) {
            $url = $request->getServer('REQUEST_URI');
        }

        if ($controllerGrouping && $this->_stack != array()) {
            $count = count($this->_stack);
            $lasts = explode('/', $this->_stack[$count - 1]);
            $parts = explode('/', $url);
            if ($lasts >= 2 && $parts >= 2
                && $lasts[1] == $parts[1]) {
                $this->_stack[$count - 1] = $url;
                return $this;
            }
        }

        $this->_stack[] = $url;
        return $this;
    }

    /**
     * clear
     *
     * @param string $url
     * @return $this
     */
    public function clear($url = null)
    {
        $this->_stack = array();
        if ($url) {
            return $this->push($url);
        } else {
            return $this;
        }
    }

    /**
     * all
     *
     * @param void
     * @return array
     */
    public function all()
    {
        return $this->_stack;
    }
}