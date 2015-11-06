<?php
/**
 * class Nutex_Log_Abstract
 *
 * ログクラス
 * 静的遅延束縛 get_called_class() が入っているのでphp5.3以上のみ対応です
 *
 * @package Nutex
 * @subpackage Nutex_Log
 */
abstract class Nutex_Log_Abstract extends Zend_Log
{
    /**
     * @var array
     */
    protected static $_instances = array();

    /**
     * getInstance
     *
     * @param void
     * @return Nutex_Log_Abstract
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$_instances)) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
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
            $priority = Zend_Log::NOTICE;
        }
        if (is_array($extras)) {
            $extras = print_r($extras, true);
        } elseif ($extras instanceof Exception) {
            $extras = $extras->getTraceAsString();
        }
        self::getInstance()->log($message, $priority, $extras);
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
        if (defined('LOG_PATH')) {
            $dir = LOG_PATH;
        } else {
            $dir = APPLICATION_PATH . '/../logs';
        }
        $dir = realpath($dir);
        
        $oldmask = umask(0);

        if (!$dir || !is_dir($dir)) {
            umask($oldmask);
            throw new Nutex_Exception_Error('log file dir is not found');
        }
        umask($oldmask);
        
        $dir .= DIRECTORY_SEPARATOR . $this->getLogId() . DIRECTORY_SEPARATOR . date('Y-m');
        
        $oldmask = umask(0);
        
        if (is_dir($dir)) {
            return $dir;
            
        } else if (mkdir($dir, 0777)) {
            chgrp($dir, "apache");
            chown($dir,"apache");
            chmod($dir, 0777);
            return $dir;
            
        } else {
            throw new Nutex_Exception_Error('log file dir is not writable');
        }
    }

    /**
     * ログファイルパス取得
     *
     * @param  void
     * @return string $path
     * @throws Nutex_Exception_Error
     */
    public function getLogFilePath()
    {
        $path = $this->getLogFileDir() . DIRECTORY_SEPARATOR . $this->getLogFileName();
        if (!file_exists($path) || is_writable($path)) {
            return $path;
        } else {
            throw new Nutex_Exception_Error('log file is not writable');
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
        return $this->getLogId() . '_' . date('Ymd') . '.log';
    }

    /**
     * ログ種別取得
     *
     * @param  void
     * @return string
     */
    public function getLogId()
    {
        $id = 'unknown';
        if (preg_match('/[^_]+$/', get_class($this), $match)) {
            $id = strtolower($match[0]);
        }
        return $id;
    }
}
