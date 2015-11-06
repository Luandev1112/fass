<?php
/**
 * class Nutex_BaseConvert_Convert
 *
 * N進数文字列への基数変換を行うクラス
 * 16進文字列になっているハッシュを64進数に変換(圧縮)して短縮URLっぽいの作ったりできる
 *
 * @package Nutex
 * @subpackage Nutex_Encode
 */
class Nutex_BaseConvert_Convert extends Nutex_BaseConvert_Abstract
{
    /**
     * 変換元の基数定義文字群
     *
     * @var string
     */
    protected $_baseCharsFrom = null;

    /**
     * __construct
     *
     * @param array $options
     * @return void
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            $options = array();
        }
        if (!array_key_exists('baseCharsFrom', $options)) {
            $options['baseCharsFrom'] = 16;//デフォルト値として16進数を指定
        }
        parent::__construct($options);
    }

    /**
     * convert
     *
     * @param string $string
     * @return string $string
     * @throws Nutex_Exception_Error
     *
     * FIXME!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 一番後ろのpartが色々おかしい 文字落ちたり 変わったり
     *
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
     */
    public function revert($string)
    {
        $baseChars = $this->getBaseChars();
        $baseCharsFrom = $this->getBaseCharsFrom();

        $this->setBaseChars($baseCharsFrom);
        $this->setBaseCharsFrom($baseChars);

        $reverted = $this->convert($string);

        $this->setBaseChars($baseChars);
        $this->setBaseCharsFrom($baseCharsFrom);

        return $reverted;
    }

    /**
     * getBaseCharsFrom
     *
     * @param void
     * @return string
     */
    public function getBaseCharsFrom()
    {
        return $this->_baseCharsFrom;
    }

    /**
     * setBaseChars
     *
     * @param string $string
     * @return void
     */
    public function setBaseCharsFrom($string)
    {
        $this->_baseCharsFrom = $this->fixBaseChars($string);
    }

    /**
     * _extractToBinaryStrings
     *
     * @param string $string
     * @return array $bytes
     */
    protected function _extractToBinaryStrings($string)
    {
        $fromBase = $this->getBaseCharsFrom();
        if ($this->isMultibyteBaseChars($fromBase)) {
            $length = mb_strlen($string);
        } else {
            $length = strlen($string);
        }
        $bitLen = $this->getPartLength($fromBase);

        $bytes = array();
        for ($i = 0; $i < $length; $i++) {
            if ($this->isMultibyteBaseChars($fromBase)) {
                $char = mb_substr($string, $i, 1);
                $base10 = mb_strpos($fromBase, $char);
            } else {
                $char = $string[$i];
                $base10 = strpos($fromBase, $char);
            }
            if ($i < $length - 1) {
                $part = str_pad(decbin($base10), $bitLen, '0', STR_PAD_LEFT);
            } else {
                $part = decbin($base10);
            }
            $bytes[] = $part;
        }

        return $bytes;
    }
}
