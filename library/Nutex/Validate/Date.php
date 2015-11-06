<?php
/**
 * class Nutex_Validate_Date
 *
 * 日付バリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Date extends Zend_Validate_Date
{
    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID        => "不正な値です",
        self::INVALID_DATE   => "日付の形式で入力して下さい",
        self::FALSEFORMAT    => "エラーが発生しました",
    );

    /**
     * Sets validator options
     *
     * @param  string|Zend_Config $options OPTIONAL
     * @return void
     */
    public function __construct($options = array())
    {
        if (!array_key_exists('format', $options)) {
            $options['format'] = 'yyyy-MM-dd HH:mm:ss';
        }
        parent::__construct($options);
    }
}
