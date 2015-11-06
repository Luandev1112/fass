<?php
/**
 * class Nutex_Test_Abstract
 *
 * テストの抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Test
 * @see Zend_Test_PHPUnit_ControllerTestCase
 * @see PHPUnit_Framework_TestCase
 */

//PHPUnit経由で呼ばれることがあるので読み込み
require_once('Zend/Test/PHPUnit/ControllerTestCase.php');

abstract class Nutex_Test_Abstract extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * ブートストラップ用のファイル名 一番近いところにいる同名ファイルが読み込まれます
     * @var string
     */
    public $bootstrap = 'bootstrap.php';

    /**
     * セットアップ処理
     * protectedですが命名規則に従っていないのは親のクラスがそうなっているからです
     *
     * @return void
     */
    protected function setUp()
    {
        //bootstrapがphpファイル名指定のみだったらパスを探してあげる
        if (is_string($this->bootstrap) && preg_match('/\.php$/', $this->bootstrap) && basename($this->bootstrap) == $this->bootstrap) {
            $reflection = new ReflectionClass($this);
            $this->bootstrap = $this->_resolveFilePath($this->bootstrap, dirname($reflection->getFileName()));
        }

        parent::setUp();
    }

    /**
     * ファイル名からパスを解決する
     *
     * @param string $fileName
     * @param string $dirPath
     * @return string $path
     */
    protected function _resolveFilePath($fileName, $dirPath)
    {
        $path = realpath($dirPath . DIRECTORY_SEPARATOR . $fileName);

        if (!is_string($path) || !is_readable($path)) {
            $path = $this->_resolveFilePath($fileName, dirname($dirPath));
        }

        return $path;
    }
}
