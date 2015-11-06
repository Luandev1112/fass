<?php
/**
 * webアプリケーションのフロントエンド
 * 初期化とアプリケーション起動を行う
 *
 */
 ini_set( 'display_errors', 1 );
//set constants and include paths
$scriptPath = realpath(dirname(__FILE__) . '/../script/');
require $scriptPath . DIRECTORY_SEPARATOR . 'set_constants.php';
require $scriptPath . DIRECTORY_SEPARATOR . 'set_include_path.php';

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()->run();

