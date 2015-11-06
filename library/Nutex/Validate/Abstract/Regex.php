<?php
/**
 * class Nutex_Validate_Abstract_Regex
 *
 * 正規表現バリデータの抽象クラス
 * Zend_Validate_Regexとは別の実装です 外側から正規表現を弄れないようにしてあります
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
abstract class Nutex_Validate_Abstract_Regex extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const NOT_MATCH = 'notMatch';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "'%value%' に適切でない文字が含まれています",
    );

    /**
     * 正規表現 preg系の定義で定義する
     * @var string
     */
    protected $_pattern;

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

    /**
     * isValid
     * Defined by Zend_Validate_Interface
     *
     * @param void
     * @return string
     */
    public function isValid($value)
    {
        $value = (string)$value;
        $this->_setValue($value);

        if (!@preg_match($this->_pattern, $value)) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}
