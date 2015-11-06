<?php
/**
 * class Api_ErrorController
 */
class Api_ErrorController extends Api_Model_Controller
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

