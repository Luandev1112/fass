<?php
/**
 * class Nutex_Log_Cli
 *
 * CLIログクラス
 *
 * @package Nutex
 * @subpackage Nutex_Log
 */
class Nutex_Log_Cli extends Nutex_Log_Abstract
{
    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected static $_request;

    /**
     * @var array
     */
    protected $_logInfo = array();

    /**
     * @var array
     */
    protected $_startedAt;

    /**
     * getRequest
     *
     * @param  void
     * @return Zend_Controller_Request_Abstract
     */
    public static function getRequest()
    {
        return self::$_request;
    }

    /**
     * setRequest
     *
     * @param  Zend_Controller_Request_Abstract $request
     */
    public static function setRequest(Zend_Controller_Request_Abstract $request)
    {
        self::$_request = $request;
    }

    /**
     * getInstance
     *
     * @param void
     * @return Nutex_Log_Cli
     */
    public static function getInstance()
    {
        if (!function_exists('get_called_class')) {
            self::setInstanceName(__CLASS__);
        }
        return parent::getInstance();
    }

    /**
     * __construct
     *
     * @param void
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function __construct()
    {
        parent::__construct();

        if (php_sapi_name() != 'cli') {
            throw new Nutex_Exception_Error('not cli');
        }

        $writer = new Zend_Log_Writer_Stream($this->getLogFilePath());
        $formatter = new Zend_Log_Formatter_Simple('%timestamp% (%priorityName%): %message%' . PHP_EOL);
        $writer->setFormatter($formatter);
        $this->addWriter($writer);
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @param  mixed    $extras    Extra information to log in event
     * @return void
     * @see Zend_Log::log()
     */
    public static function write($message, $priority = null, $extras = null)
    {
        if (is_null($priority)) {
            $priority = Zend_Log::INFO;
        }

        parent::write('[' . getmypid() . ']' . $message, $priority, $extras);
    }

    /**
     * 動作開始ログを書き込む
     *
     * @param void
     * @return void
     */
    public static function processStart()
    {
        self::getInstance()->startLog();
    }

    /**
     * 経過時間を書き込む
     *
     * @param void
     * @return void
     */
    public static function processTime()
    {
        self::getInstance()->timeLog();
    }


    /**
     * 動作終了ログを書き込む
     *
     * @param void
     * @return void
     */
    public static function processEnd()
    {
        self::getInstance()->endLog();
    }

    /**
     * 動作開始ログを書き込む
     *
     * @param void
     * @return $this
     */
    public function startLog()
    {
        $this->_startedAt = microtime(true);

        $messages = array();
        $messages[] = '---------------------------------- start process ----------------------------------';
        $messages[] = '[----- process informations -----]';
        foreach ($this->getLogInfo() as $name => $value) {
            $messages[] = $name . ' => ' . $value;
        }
        $messages[] = '[-------------------------------]';

        foreach ($messages as $message) {
            $this->log($message, Zend_Log::INFO);
        }

        return $this;
    }

    /**
     * 経過時間を書き込む
     *
     * @param void
     * @return $this
     */
    public function timeLog()
    {
        $this->log('suceeeded: ' . $this->getSucceededTime() . ' sec', Zend_Log::INFO);
        return $this;
    }

    /**
     * 動作終了ログを書き込む
     *
     * @param void
     * @return $this
     */
    public function endLog()
    {
        $messages = array();
        $messages[] = 'total time: ' . $this->getSucceededTime() . ' sec';
        $messages[] = '----------------------------------  end process  ----------------------------------';

        foreach ($messages as $message) {
            $this->log($message, Zend_Log::INFO);
        }

        return $this;
    }

    /**
     * 経過時間を取得
     *
     * @param void
     * @return float
     */
    public function getSucceededTime()
    {
        return microtime(true) - $this->_startedAt;
    }

    /**
     * ログ情報取得
     *
     * @param  void
     * @return array
     */
    public function getLogInfo()
    {
        return $this->_logInfo;
    }

    /**
     * ログ情報セット
     *
     * @param  string $name
     * @param  string $value
     * @return $this
     */
    public function setLogInfo($name, $value)
    {
        $this->_logInfo[$name] = $value;
        return $this;
    }

    /**
     * ログファイルディレクトリ取得
     *
     * @param  void
     * @return string $dir
     * @throws Nutex_Exception_Error
     */
    public function getLogFileDir()
    {
        $dir = parent::getLogFileDir();
        $dir .= DIRECTORY_SEPARATOR . self::getRequest()->getControllerName() . DIRECTORY_SEPARATOR . self::getRequest()->getActionName();

        if ((is_dir($dir) && is_writable($dir)) || mkdir($dir, 0777, true)) {
            return $dir;
        } else {
            throw new Nutex_Exception_Error('log file dir is not writable');
        }
    }

    /**
     * ログファイル名取得
     *
     * @param  void
     * @return string
     */
    public function getLogFileName()
    {
        return ((self::getRequest()->getControllerName()) ? self::getRequest()->getControllerName() . '_' : '')
            . ((self::getRequest()->getActionName()) ? self::getRequest()->getActionName() . '_' : '')
            . date('Ymd') . '.log';
    }
}
