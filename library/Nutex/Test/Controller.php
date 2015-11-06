<?php
/**
 * class Nutex_Test_Controller
 *
 * コントローラのテストの抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Test
 * @see Zend_Test_PHPUnit_ControllerTestCase
 * @see PHPUnit_Framework_TestCase
 */

//PHPUnit経由で呼ばれることがあるので読み込み
require_once('Nutex/Test/Abstract.php');

abstract class Nutex_Test_Controller extends Nutex_Test_Abstract
{
    /*
     * 何かあれば処理を追加したい
     * @todo
     */
}
