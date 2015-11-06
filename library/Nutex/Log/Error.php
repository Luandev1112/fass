<?php
/**
 * class Nutex_Log_Error
 *
 * エラーログクラス
 *
 * @package Nutex
 * @subpackage Nutex_Log
 */
class Nutex_Log_Error extends Nutex_Log_Abstract
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

        $writer = new Zend_Log_Writer_Stream($this->getLogFilePath());
        $formatter = new Zend_Log_Formatter_Simple('%timestamp% (%priorityName%): %message% %info%' . PHP_EOL);
        $writer->setFormatter($formatter);
        $this->addWriter($writer);

        if (Nutex_Mail_Error::isAble()) {
            $mail = new Nutex_Mail_Error();
            $writer = new Zend_Log_Writer_Mail($mail);
            $formatter = new Zend_Log_Formatter_Simple('%timestamp% (%priorityName%): %message%' . PHP_EOL);
            $writer->setFormatter($formatter);
            $filter = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
            $writer->addFilter($filter);
            $this->addWriter($writer);
        }
    }
}
