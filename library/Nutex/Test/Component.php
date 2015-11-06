<?php
/**
 * class Nutex_Test_Component
 *
 * コンポーネントのテストの抽象クラス
 * 特に単一クラスの挙動をテストすることを想定しています
 *
 * @package Nutex
 * @subpackage Nutex_Test
 * @see Zend_Test_PHPUnit_ControllerTestCase
 * @see PHPUnit_Framework_TestCase
 */

//PHPUnit経由で呼ばれることがあるので読み込み
require_once('Nutex/Test/Abstract.php');

abstract class Nutex_Test_Component extends Nutex_Test_Abstract
{
    /**
     * @var object
     */
    protected $_component;

    /**
     * コンポーネント初期化
     * 継承先で $this->_component を初期化する処理を記述
     *
     * @param void
     * @return void
     */
    abstract protected function _initComponent();

    /**
     * getComponent
     *
     * @return object
     */
    public function getComponent()
    {
        return $this->_component;
    }

    /**
     * セットアップ処理
     * protectedですが命名規則に従っていないのは親のクラスがそうなっているからです
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_initComponent();
    }
}
