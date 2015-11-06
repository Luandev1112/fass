<?php
/**
 * class Nutex_Debug_ReflectObjects
 *
 * オブジェクト群から情報をまるっと取得する静的クラス
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Debug_ReflectObjects extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * params
     * パラメータ（定数とプロパティ）の情報をまるっと取得する
     *
     * @param array|object $objects
     * @param boolean $objectToName
     * @return array
     */
    public static function params($objects, $objectToName = true)
    {
        $result = array();

        if (!is_array($objects)) {
            $objects = array($objects);
        }
        foreach ($objects as $name => $object) {
            if (!is_object($object)) {
                $result[$name] = $object;
                continue;
            }
            $result[$name] = array();
            $reflection = new ReflectionClass($object);
            $result[$name]['constants'] = array();
            foreach ($reflection->getConstants() as $param) {
                $result[$name]['constants'][$param->getName()] = ($objectToName) ? self::objectToName($param->getValue($object)) : $param->getValue($object);
            }
            $result[$name]['properties'] = array();
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $param) {
                $result[$name]['properties'][$param->getName()] = ($objectToName) ? self::objectToName($param->getValue($object)) : $param->getValue($object);
            }
        }

        return $result;
    }

    /**
     * getters
     * ゲッターの情報をまるっと取得する
     *
     * @param array|object $objects
     * @param boolean $objectToName
     * @return array
     */
    public static function getters($objects, $objectToName = true)
    {
        $result = array();

        if (!is_array($objects)) {
            $objects = array($objects);
        }
        foreach ($objects as $name => $object) {
            if (!is_object($object)) {
                $result[$name] = $object;
                continue;
            }
            $result[$name] = array();
            $reflection = new ReflectionClass($object);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (strpos($method->getName(), 'get') === 0 && count($method->getParameters()) == 0) {
                    $result[$name][$method->getName()] = ($objectToName) ? self::objectToName($method->invoke($object)) : $method->invoke($object);
                }
            }
        }

        return $result;
    }

    /**
     * objectToName
     * オブジェクトをクラス名に変換する
     *
     * @param mixed $input
     * @return mixed $input
     */
    public static function objectToName($input)
    {
        if (is_array($input)) {
            foreach ($input as &$col) {
                $col = self::objectToName($col);
            }
        } elseif (is_object($input)) {
            $input = get_class($input);
        }

        return $input;
    }
}