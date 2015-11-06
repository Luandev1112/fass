<?php
/**
 * class ErrorController
 *
 * @version
 */
class ErrorController extends Front_Model_Controller
{

    /******************************************************************************
    |  init                                                                       |
    ******************************************************************************/
    public function init()
    {
		$this->_requireAuth = false;
        parent::init();

    }
}

