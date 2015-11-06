<?php
/**
 * class Bootstrap
 *
 * ブートストラップ
 *
 * @package Demos
 * @subpackage Demos
 */
class Bootstrap extends Nutex_Bootstrap_Bootstrap
{
    /**
     * _initClient
     *
     * @param void
     * @return void
     */
    protected function _initClient()
    {
        Nutex_Client_Abstract::setClientList(array(
            'Shared_Model_Client_SmartPhone',
        ));
        parent::_initClient();

    }

    /**
     * _initMail
     *
     * @param void
     * @return void
     */
    protected function _initMail()
    {
    	/*
        $contact = Nutex_Util_ConfigFactory::createByPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'contact.ini', APPLICATION_ENV);
        $host = $contact->get('transport')->get('host');
        $config = $contact->get('transport')->get('config');
        $transport = (is_array($config)) ? new Zend_Mail_Transport_Smtp($host, $config) : new Zend_Mail_Transport_Smtp($host);
        Zend_Mail::setDefaultTransport($transport);
        */
    }
}
