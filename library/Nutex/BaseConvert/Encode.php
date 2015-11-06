<?php
/**
 * class Nutex_BaseConvert_Encode
 *
 * バイナリをN進数でエンコードするbase64encodeみたいな感じ
 * 独自アルゴリズムでbase64encodeみたいなことがしたいときに
 *
 * @package Nutex
 * @subpackage Nutex_BaseConvert_Encode
 */
class Nutex_BaseConvert_Encode extends Nutex_BaseConvert_Abstract
{
    /**
     * convert
     *
     * @param string $string
     * @return string $string
     * @throws Nutex_Exception_Error
     */
    public function convert($string)
    {
        $baseChars = $this->getBaseChars();
        $string = (string)$string;

        $partLen = $this->getPartLength();
        if ($partLen <= 0) {
            throw new Nutex_Exception_Error('base chars too short');
        }

        $binaryString = implode('', $this->_extractToBinaryStrings($string));
        $binaryStringLen = strlen($binaryString);
        $points = array();
        for ($i = 0; $i < $binaryStringLen; $i += $partLen) {
            $part = substr($binaryString, $i, $partLen);
            $points[] = bindec($part);
            if ($i + $partLen >= $binaryStringLen) {
                //一番最後の一文字は最後のpartの長さ
                $points[] = strlen($part);
            }
        }

        $converted = '';
        foreach ($points as $point) {
            if ($this->isMultibyteBaseChars()) {
                $converted .= mb_substr($baseChars, $point, 1);
            } else {
                $converted .= $baseChars[$point];
            }
        }

        return $converted;
    }

    /**
     * revert
     *
     * @param string $string
     * @return string $string
     * @throws Nutex_Exception_Error
     */
    public function revert($string)
    {
        $baseChars = $this->getBaseChars();

        $partLen = $this->getPartLength();
        if ($partLen <= 0) {
            throw new Nutex_Exception_Error('base chars too short');
        }

        if ($this->isMultibyteBaseChars()) {
            $length = mb_strlen($string);
        } else {
            $length = strlen($string);
        }

        $points = array();
        for ($i = 0; $i < $length; $i++) {
            if ($this->isMultibyteBaseChars()) {
                $point = mb_strpos($baseChars, mb_substr($string, $i, 1));
            } else {
                $point = strpos($baseChars, $string[$i]);
            }
            if ($point === false) {
                throw new Nutex_Exception_Error('invalid input');
            }
            $points[] = $point;
        }

        $lastLength = array_pop($points);//一番最後の一文字は最後のpartの長さ
        $length = count($points);
        $binaryString = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i < $length - 1) {
                $binaryString .= str_pad(decbin($points[$i]), $partLen, '0', STR_PAD_LEFT);
            } else {
                $binaryString .= str_pad(decbin($points[$i]), $lastLength, '0', STR_PAD_LEFT);
            }
        }
        $reverted = $this->packBinaryStrings($binaryString);

        return $reverted;
    }

    /**
     * _extractToBinaryStrings
     *
     * @param string $string
     * @return array $bytes
     */
    protected function _extractToBinaryStrings($string)
    {
        $bytes = array();
        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            $bytes[] = str_pad(base_convert((bin2hex($string[$i])), 16, 2), 8, '0', STR_PAD_LEFT);
        }

        return $bytes;
    }
}
