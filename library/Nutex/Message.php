<?php
/**
 * class Nutex_Message
 *
 * 静的メッセージの管理クラス
 * 静的遅延束縛 get_called_class() が入っているのでphp5.3以上のみ対応です
 *
 * @package Nutex
 * @subpackage Nutex_Message
 */
class Nutex_Message
{
    const STACK_NAME = '_messageStack';

    /**
     * @var array
     */
    protected static $_messageStack = array();

    /**
     * @var array
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    protected $_messages = array();

    /**
     * codes
     *
     * @param string $key
     * @param array $params
     * @return string
     */
    public static function get($key, array $params = array())
    {
        return self::getInstance()->getMessage($key, $params);
    }

    /**
     * getInstance
     *
     * @param void
     * @return Nutex_Code
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            $class = get_called_class();
            self::$_instance = new $class();
        }
        return self::$_instance;
    }

    /**
     * get
     *
     * @param string $key
     * @return string
     */
    public function getMessage($key, array $params)
    {
        $classes = array();
        $classes[] = get_class($this);
        $class = get_parent_class($this);
        while ($class !== false) {
            $classes[] = $class;
            $class = get_parent_class($class);
        }

        $message = '';
        foreach (array_unique($classes) as $class) {
            if (!array_key_exists($class, $this->_messages)) {
                $this->_messages[$class] = $this->_loadCodes($class);
            }
            if (array_key_exists($key, $this->_messages[$class])) {
                $message = $this->_messages[$class][$key];
                break;
            }
        }

        if ($message != '') {
            foreach ($params as $key => $value) {
                $message = str_replace('%' . $key . '%', $value, $message);
            }
        }

        return $message;
    }

    /**
     * _loadCodes
     *
     * @param string $class
     * @return array
     */
    protected function _loadCodes($class)
    {
        if (@class_exists($class)) {
            $stack = eval('return ' . $class . '::$' . self::STACK_NAME . ';');
            if (is_array($stack)) {
                return $stack;
            }
        }
        return array();
    }
}
