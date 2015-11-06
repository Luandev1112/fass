<?php
/**
 * class Nutex_Helper_Action_VersionNumber
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_VersionNumber extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * direct
     *
     * @param void
     * @return void
     */
    public function direct($version)
    {
        return $this->set($version);
    }

    /**
     * set
     *
     * @param void
     * @return int $version
     */
    public function set($version)
    {
        $this->getActionController()->view->getHelper('VersionNumber')->setVersion($version);
    }

    /**
     * get
     *
     * @param void
     * @return string
     */
    public function get()
    {
        return $this->_retrieveFromRequest();
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
        return $request->$method('__version__');
    }
}
