<?php
/**
 * class Nutex_Log_Debug
 *
 * デバッグログクラス
 *
 * @package Nutex
 * @subpackage Nutex_Log
 */
class Nutex_Log_Debug extends Nutex_Log_Abstract
{
    /**
     * __construct
     *
     * @param void
     * @return void
     */
    public function __construct()
    {
	
        parent::__construct();
/*
        $writer = new Zend_Log_Writer_Firebug();
        $this->addWriter($writer);

        $writer = new Zend_Log_Writer_Stream($this->getLogFilePath());
        $formatter = new Zend_Log_Formatter_Simple('%timestamp% (%priorityName%): %message%' . PHP_EOL);
        $writer->setFormatter($formatter);
        $this->addWriter($writer);
*/
    }
}
