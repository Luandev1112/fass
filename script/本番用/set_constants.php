<?php
/*****************************
 * 定数定義  本番環境用
 ****************************/
 
/*
 * その他の定数
 */

define('HTTP_PROTOCOL', 'http://');
define('HTTPS_PROTOCOL', 'https://');

define('APPLICATION_DOMAIN', 'fass.fresco-co.net');

define('GMO_AOZORA_API_DOMAIN', 'https://api.gmo-aozora.com');

define('GS_DOMAIN', 'goosa.net');
define('GCS_DOMAIN', 'goosca.jp');

    
/*
 * パス定数のセット
 */
$dir = realpath(dirname(__FILE__) . '/../');

defined('APPLICATION_PATH')
    || define('PROJECT_ROOT_PATH', $dir);
    
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', $dir . DIRECTORY_SEPARATOR . 'application');

// Define path to public directory
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', $dir . DIRECTORY_SEPARATOR . 'public');

// Define path to log directory
defined('LOG_PATH')
    || define('LOG_PATH', $dir . DIRECTORY_SEPARATOR . 'log');

// Define path to library directory
defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', $dir . DIRECTORY_SEPARATOR . 'library');

// Define path to resource directory
defined('RESOURCE_PATH')
    || define('RESOURCE_PATH', $dir . DIRECTORY_SEPARATOR . 'resource');

// Define path to temporary directory
defined('TEMPORARY_PATH')
    || define('TEMPORARY_PATH', $dir . DIRECTORY_SEPARATOR . 'temporary');

// Define path to test directory
defined('TEST_PATH')
    || define('TEST_PATH', $dir . DIRECTORY_SEPARATOR . 'tests');


/*
 * 環境定数のセット
 */
$consts = array(
    'APPLICATION_ENV',
    'APPLICATION_LOCALE',
);
foreach ($consts as $const) {
    $value = @getenv($const);
    if ($value || defined($const)) {
        continue;
    }

    //環境定数ないし定数に居なかったら .htaccess を読み込んでみる
    $htaccess = PUBLIC_PATH . DIRECTORY_SEPARATOR . '.htaccess';
    if ($htaccess && $fp = @fopen($htaccess, 'r')) {
        while (feof($fp) == false) {
            $row = trim(fgets($fp));
            if (preg_match('/^SetEnv\s+' . $const . '/', $row)) {
                $value = trim(preg_replace('/(SetEnv|' . $const .')/', '', $row));
                break;
            }
        }
        fclose($fp);
    }

    //セット
    if ($value) {
        @putenv($const . '=' . $value);
        define($const, $value);
    }
}

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Define application locale
defined('APPLICATION_LOCALE')
    || define('APPLICATION_LOCALE', (getenv('APPLICATION_LOCALE') ? getenv('APPLICATION_LOCALE') : 'ja_JP'));


/*
 * その他の定数
 *
 * 追加する場合は、設定ファイルに書けないか、クラス定数やプロパティにできないか、などをよく考えた上で追加すること
 */
defined('MODULE_CLI')
    || define('MODULE_CLI', 'cli');






