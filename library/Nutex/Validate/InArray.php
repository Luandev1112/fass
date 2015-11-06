<?php
/**
 * class Nutex_Validate_InArray
 *
 * InArrayバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_InArray extends Zend_Validate_InArray
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_IN_ARRAY => "選択できない値です",
    );

    /**
     * Sets validator options
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            if (array_key_exists('codeClass', $options) && array_key_exists('codeName', $options) ) {
                $args = array($options['codeName']);
                if (array_key_exists('codeOmits', $options)) {
                    $args[] = $options['codeOmits'];
                }
                $codes = call_user_func_array(array($options['codeClass'], 'codes'), $args);
                if (is_array($codes)) {
                    $options['haystack'] = array_keys($codes);
                }
                unset($options['codeClass'], $options['codeName'], $options['codeOmits']);
            }
        }
        parent::__construct($options);
    }
}
