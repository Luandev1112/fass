<?php
/**
 * class Nutex_LoadConfig
 *
 * factory methodっぽくZend_Config用設定ファイルを読み込むクラス
 *
 * @package Nutex
 * @subpackage Nutex_LoadConfig
 */
class Nutex_LoadConfig
{
    /**
     * load
     *
     * @param mixed $config
     * @param string $environment
     * @return Zend_Config
     * @throws Nutex_Exception_Error
     */
    public static function load($config, $environment = null)
    {
        $className = null;

        if (is_string($config)) {
            //文字列の場合はファイルパスとみなして拡張子で判断してみる
            switch (strtolower(preg_replace('/^[^\.]+\./', '', basename($config)))) {

                case 'ini':
                    $className = 'Zend_Config_Ini';
                    break;

                case 'php':
                case 'inc':
                    $config = require($config);
                    $className = 'Zend_Config';
                    break;

                case 'json':
                    $className = 'Zend_Config_Json';
                    break;

                case 'xml':
                    $className = 'Zend_Config_Xml';
                    break;

                case 'yml':
                case 'yaml':
                    $className = 'Zend_Config_Yaml';
                    break;

                default:
                    throw new Nutex_Exception_Error('unknown config file extention');

            }
        } elseif (is_array($config)) {
            $className = 'Zend_Config';
        }

        if (is_string($className)) {
            if (is_string($environment)) {
                return new $className($config, $environment);
            } else {
                return new $className($config);
            }
        }

        throw new Nutex_Exception_Error('invalid config param');
    }
}