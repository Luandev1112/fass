<?php
/**
 * class Nutex_BaseConvert_Abstract
 *
 * N進数に基数変換して文字列をコンバートする抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_BaseConvert_Abstract
 */
abstract class Nutex_BaseConvert_Abstract
{
    /**
     * 基数変換に使う文字群定義
     * 左から十進数で言う所の 012345687.. という構成の文字列です
     * 下記だけでなく setBaseChars() で外部から文字群をセットできるのでマルチバイトを含め何でもいけます
     *  - ただし２の乗数分までしか使われません（２のN乗進数しか表現できません）
     *  - 重複文字があったりすると正しく動きません
     *
     * @var string
     */
    const BASE_CHARS_URL    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';//64chars
    const BASE_CHARS_BASE64 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/';//64chars
    const BASE_CHARS_KANA   = 'あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをんぁぃぅぇぉっゃゅょアイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲンァィゥェォヵッャュョ';//sample

    /**
     * エンコードに使う文字群
     *
     * @var string
     */
    protected $_baseChars = self::BASE_CHARS_URL;

    /**
     * @var array
     */
    protected $_isMultibyteBaseChars = array();

    /**
     * convert
     *
     * @param void
     * @return string
     */
    abstract public function convert($string);

    /**
     * revert
     *
     * @param void
     * @return string
     */
    abstract public function revert($string);

    /**
     * __construct
     *
     * @param array $options
     * @return void
     */
    public function __construct($options = array())
    {
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * getBaseChars
     *
     * @param void
     * @return string
     */
    public function getBaseChars()
    {
        return $this->_baseChars;
    }

    /**
     * setBaseChars
     *
     * @param string $string
     * @return void
     */
    public function setBaseChars($string)
    {
        $this->_baseChars = $this->fixBaseChars($string);
    }

    /**
     * fixBaseChars
     *
     * @param string $string
     * @return void
     */
    public function fixBaseChars($input)
    {
        if (is_string($input)) {
            $constName = get_class($this) . '::BASE_CHARS_' . strtoupper($input);
            if (defined($constName)) {
                return constant($constName);
            } else {
                return $input;
            }
        }

        if (is_array($input)) {
            return implode('', $input);
        }

        $basicBaseChars = array(
            16 => '0123456789abcdef',
            32 => '0123456789abcdefghijklmnopqrstuv',
            64 => self::BASE_CHARS_BASE64,
        );
        if (array_key_exists($input, $basicBaseChars)) {
            return $basicBaseChars[$input];
        }
        if ($input <= 10) {
            return implode('', range(0, $input - 1));
        }

        return (string)$input;
    }

    /**
     * getPartLength
     *
     * @param void
     * @return int
     */
    public function getPartLength($baseChars = null)
    {
        if (!is_string($baseChars)) {
            $baseChars = $this->getBaseChars();
        }
        if ($this->isMultibyteBaseChars($baseChars)) {
            $baseCharsLen = mb_strlen($baseChars);
        } else {
            $baseCharsLen = strlen($baseChars);
        }
        return strlen(decbin($baseCharsLen)) - 1;//１桁減らさないと全パターン表現できないので
    }

    /**
     * isMultibyteBaseChars
     *
     * @param void
     * @return int
     */
    public function isMultibyteBaseChars($baseChars = null)
    {
        if (!is_string($baseChars)) {
            $baseChars = $this->getBaseChars();
        }
        if (!array_key_exists($baseChars, $this->_isMultibyteBaseChars)) {
            if (function_exists('mb_strlen') && strlen($baseChars) != mb_strlen($baseChars)) {
                $this->_isMultibyteBaseChars[$baseChars] = true;
            } else {
                $this->_isMultibyteBaseChars[$baseChars] = false;
            }
        }

        return $this->_isMultibyteBaseChars[$baseChars];
    }

    /**
     * packBinaryStrings
     *
     * @param array|string $string
     * @return string $string
     */
    public function packBinaryStrings($strings)
    {
        $bytes = array();
        if (is_string($strings)) {
            $length = strlen($strings);
            for ($i = 0; $i < $length; $i += 8) {
                $bytes[] = substr($strings, $i, 8);
            }
        } elseif (is_array($strings)) {
            $bytes = $strings;
        }

        $string = '';
        foreach ($bytes as $byte) {
            $string .= pack('H*', str_pad(base_convert($byte, 2, 16), 2, '0', STR_PAD_LEFT));
        }

        return $string;
    }
}
