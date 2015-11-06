<?php
/**
 * class Nutex_Util_Serialize
 *
 * base64をかませながらシリアライズする
 *
 * @package Nutex
 * @subpackage Nutex_Util
 */
class Nutex_Util_Serialize
{
    /**
     * serializeする
     * @param string $input
     * @return string $input
     */
    public static function serialize($input)
    {
        return (string) @base64_encode(@serialize($input));
    }

    /**
     * unserializeする
     * @param string $input
     * @param boolean $returnArray
     * @return array|mixed $input
     */
    public static function unserialize($input, $returnArray = true)
    {
        $input = @unserialize(@base64_decode((string) $input));
        if (!$returnArray || is_array($input)) {
            return $input;
        } else {
            return array();
        }
    }
}
