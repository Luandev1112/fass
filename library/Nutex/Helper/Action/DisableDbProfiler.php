<?php
/**
 * class Nutex_Helper_Action_DisableDbProfiler
 *
 * DBプロファイラを無効にするヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_DisableDbProfiler extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * direct
     *
     * @param mixed $input
     * @return void
     */
    public function direct($input = null)
    {
        $this->disable($input);
    }

    /**
     * disable
     *
     * @param mixed $input
     * @return void
     */
    public function disable($input)
    {
        if (!is_object($input)) {
            $input = Zend_Db_Table::getDefaultAdapter();
        }

        $adapter = null;
        if ($input instanceof Zend_Db_Adapter_Abstract) {
            $adapter = $input;
        }
        if (!$adapter instanceof Zend_Db_Adapter_Abstract && method_exists($input, 'getAdapter')) {
            $adapter = $input->getAdapter();
        }
        if (!$adapter instanceof Zend_Db_Adapter_Abstract && method_exists($input, 'getDbAdapter')) {
            $adapter = $input->getAdapter();
        }

        if ($adapter instanceof Zend_Db_Adapter_Abstract) {
            $adapter->getProfiler()->setEnabled(false);
        }
    }
}