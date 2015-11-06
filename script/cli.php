<?php
/**
 * コマンドラインからの実行スクリプト
 *
 */
//念のためディレクトリ移動
chdir(dirname(__FILE__));

/*
 * ブートストラップまで
 */
//set constants and include paths
require realpath(dirname(__FILE__) . '/../script/set_constants.php');
require realpath(dirname(__FILE__) . '/../script/set_include_path.php');

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();


/*
 * コンソールオプションの解析
 */
$opts = null;
if (!isset($consoleOptions) || !is_array($consoleOptions)) {
    $consoleOptions = array();
}
$consoleOptions['help|h'] = 'Displays usage information.';
$consoleOptions['path|p=s'] = 'uri in ' . MODULE_CLI . ' module without the module name. (e.g. /cotroller/action';
$consoleOptions['controller|c=s'] = 'Controller name in ' . MODULE_CLI . ' module. If path was set, it is ignored';
$consoleOptions['action|a=s'] = 'Action name in ' . MODULE_CLI . ' module. If path was set, it is ignored';
try {
    $opts = new Zend_Console_Getopt($consoleOptions);
    $opts->parse();
    if (isset($optionValues) && is_array($optionValues)) {
        foreach ($optionValues as $key => $val) {
            $opts->$key = $val;
        }
    }
} catch (Zend_Console_Getopt_Exception $e) {
    exit($e->getMessage() ."\n\n". $e->getUsageMessage());
}
if(isset($opts->h)) {
    echo $opts->getUsageMessage();
    exit;
}

/*
 * パラメータを解析してコントローラ、アクションを決定
 */
$func = create_function(
    '$name',
    'if ($name === null) return $name;' .
    ' $target = lcfirst($name); $len = strlen($target); $name = "";' .
    ' for ($i = 0; $i < $len; $i++) {' .
    '    if (preg_match("/[A-Z]/", $target[$i])) $name .= ((isset($target[$i - 1]) && $target[$i - 1] === "-") ? "" : "-") . strtolower($target[$i]);' .
    '    else $name .= $target[$i];' .
    ' }' .
    ' return $name;'
);
$controller = null;
$action = null;
if ($opts->p) {
    $parts = explode('/', preg_replace('@^/@', '', $opts->p));
    if ($parts) {
        if (count($parts) >= 2) {
            $controller = call_user_func($func, $parts[0]);
            $action = call_user_func($func, $parts[1]);
        } else {
            $controller = call_user_func($func, $parts[0]);
        }
    }
} else {
    $controller = call_user_func($func, $opts->c);
    $action = call_user_func($func, $opts->a);
}

/*
 * リクエストオブジェクトの設定
 */
$request = new Zend_Controller_Request_Simple($action, $controller, MODULE_CLI);
if (!empty($opts)) {
    //コンソールオプションをリクエストへ追加
    foreach ($opts->getOptions() as $name) {
        $request->setParam($name, $opts->{$name});
    }

    //残りの引数
    foreach ($opts->getRemainingArgs() as $key => $val) {
        if (is_null($request->getParam($key))) {
            $request->setParam($key, $val);
        }
    }
}
$front = Zend_Controller_Front::getInstance();
$front->setRequest($request);

/*
 * ルーティング
 */
$front->setRouter(new Nutex_Router_Cli());
$front->setResponse(new Zend_Controller_Response_Cli());
$front->throwExceptions(true);

/*
 * 実行
 */
$application->run();

