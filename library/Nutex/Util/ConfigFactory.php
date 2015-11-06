<?php
/**
 * class Nutex_Util_ConfigFactory
 *
 * Zend_Config factory
 *
 * @package Nutex
 * @subpackage Nutex_Util
 */
class Nutex_Util_ConfigFactory
{
    /**
     * @return Zend_Config $config
     */
    public static function createByPath($path, $section = null)
    {
        $ext = preg_replace('/^[^\.]+\./', '', basename($path));

        $config = null;
        switch ($ext) {

            case 'ini':
                $config = new Zend_Config_Ini($path, $section);
                break;

            case 'json':
            case 'js':
                $config = new Zend_Config_Json($path, $section);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($path, $section);
                break;

            case 'yml':
            case 'yaml':
                $config = new Zend_Config_Yaml($path, $section);
                break;

            case 'php':
            default:
                $config = new Zend_Config(require $path);
                if ($section && isset($config[$section])) {
                    $config = $config[$section];
                }
                break;

        }

        return $config;
    }
}