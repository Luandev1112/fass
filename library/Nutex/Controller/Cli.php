<?php
/**
 * class Nutex_Controller_Cli
 *
 * コマンドライン実行用 基底コントローラ
 *
 * @package Nutex
 * @subpackage Nutex_Controller
 */
class Nutex_Controller_Cli extends Nutex_Controller_Abstract
{
    /**
     * @var boolean
     */
    protected $_sessionActive = false;

    /**
     * @var array
     */
    protected $_additionalLogInfo = array();

    /**
     * init
     *
     * @param void
     * @return void
     */
    public function init()
    {
        //コマンドライン経由実行でなければ問答無用で例外を投げる
        if (php_sapi_name() != 'cli') {
            throw new Zend_Controller_Dispatcher_Exception();
        }

        parent::init();

        //viewを全部オフにする
        $this->_helper->disableView();

        //プロファイラをオフにする
        $this->_helper->disableDbProfiler();

        //メモリ制限解放
        ini_set('memory_limit', -1);
    }

    /**
     * preDispatch
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        //ログの設定
        Nutex_Log_Cli::setRequest($this->getRequest());
        Nutex_Log_Cli::getInstance()->setLogInfo('APPLICATION_PATH', APPLICATION_PATH)
                                    ->setLogInfo('APPLICATION_ENV', APPLICATION_ENV);

        foreach ($this->_additionalLogInfo as $name => $value) {
            Nutex_Log_Cli::getInstance()->setLogInfo($name, $value);
        }

        parent::preDispatch();
        Nutex_Log_Cli::processStart();
    }

    /**
     * postDispatch
     * @param void
     * @return void
     */
    public function postDispatch()
    {
        parent::postDispatch();
        Nutex_Log_Cli::processEnd();
    }

    /**
     * _log
     * @param string $message
     */
    protected function _log($message)
    {
        Nutex_Log_Cli::write($message);
    }
}
