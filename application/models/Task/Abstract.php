<?php
/**
 * class Shared_Model_Task_Abstract
 *
 * タスク
 *
 * @package Shared
 * @subpackage Shared_Model
 */

abstract class Shared_Model_Task_Abstract
{
    /**
     * phpコマンド
     * @return string
     */
    protected static function _php()
    {
        return 'php';
    }

    /**
     * cliモジュールのエントリポイントのスクリプトパスを取得
     * @return string
     */
    protected static function _cliPhp()
    {
        return realpath(APPLICATION_PATH . '/../script/cli.php');
    }

    /**
     * バックグラウンドでコマンドを実行する
     * @param void
     */
    protected static function _execOnBackground($cmd)
    {
        if (preg_match('/WIN/i', PHP_OS)) {
            $fp = popen('start ' . $cmd, 'r');
            pclose($fp);
        } else {
            shell_exec('nohup ' . $cmd . ' > ' . LOG_PATH . DIRECTORY_SEPARATOR . 'nohup.out  &');
        }
    }
}