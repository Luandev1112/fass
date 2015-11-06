<?php
/**
 * class Cli_Model_Controller
 *
 * cliモジュール用 基底コントローラ
 *
 * @package Cli
 * @subpackage Cli_Model
 */
class Cli_Model_Controller extends Nutex_Controller_Cli
{
    protected function _print($str, $newLine = false)
    {
        if (is_array($str)) {
            foreach ($str as $row) {
                self::_print($row, $newLine);
            }
        } else {
            if (preg_match('/WIN/i', PHP_OS)) {
                $newStr = mb_convert_encoding($str, 'SJIS');
            } else {
                $newStr = $str;
            }
            echo $newStr;
            if ($newLine != false) {
                echo "\n";
            }
        }
    }

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
