<?php
/**
 * class Nutex_Filter_Abstract
 *
 * フィルタ抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
abstract class Nutex_Filter_Abstract implements Zend_Filter_Interface
{
    /**
     * __construct
     *
     * @param array|Zend_Config $options
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            throw new Nutex_Exception_Error('invalid options');
        }
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }
}
