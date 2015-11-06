<?php
/**
 * class Nutex_Mail_Abstract
 *
 * メールクラス
 *
 * @package Nutex
 * @subpackage Nutex_Mail
 */
abstract class Nutex_Mail_Abstract extends Zend_Mail
{
    /**
     * 日本語用7bitエンコーディング
     * 携帯に送る場合などに使用する
     * @var string
     */
    const JAPANESE_7BIT_ENCODING = 'ISO-2022-JP';

    /**
    * constructMail
    *
    * @param mixed $input
    * @return $this
    */
    abstract public function constructMail($input = null);

    /**
     * Public constructor
     *
     * @param  mixed $input
     * @param  string $charset
     * @return void
     */
    public function __construct($input = null, $charset = null)
    {
        if (is_null($charset)) {
            $charset = mb_internal_encoding();
        }

        parent::__construct($charset);
        if ($this->getCharset() === self::JAPANESE_7BIT_ENCODING) {
            $this->_headerEncoding = Zend_Mime::ENCODING_7BIT;
        }

        $this->constructMail($input);
    }

    /**
     * Sets the text body for the message.
     *
     * @param  string $txt
     * @param  string $charset
     * @param  string $encoding
     * @return Zend_Mail Provides fluent interface
    */
    public function setBodyText($txt, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($this->getCharset() === self::JAPANESE_7BIT_ENCODING) {
            $encoding = Zend_Mime::ENCODING_7BIT;
            $txt = $this->_convertEncoding($txt);
        }

        return parent::setBodyText($txt, $charset, $encoding);
    }

    /**
     * Sets the HTML body for the message
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return Zend_Mail Provides fluent interface
     */
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if ($this->getCharset() === self::JAPANESE_7BIT_ENCODING) {
            $encoding = Zend_Mime::ENCODING_7BIT;
            $html = $this->_convertEncoding($html);
        }

        return parent::setBodyHtml($html, $charset, $encoding);
    }

    /**
     * setCharset
     *
     * @param string $charset
     * @return $this
     * @see Zend_Mail
     */
    public function setCharset($charset)
    {
        $this->_charset = (string)$charset;
        return $this;
    }

    /**
     * Encode header fields
     *
     * Encodes header content according to RFC1522 if it contains non-printable
     * characters.
     *
     * @param  string $value
     * @return string
     */
    protected function _encodeHeader($value)
    {
        if ($this->getCharset() === self::JAPANESE_7BIT_ENCODING) {
            $value = $this->_convertEncoding($value);
            $value = Zend_Mime::encodeBase64Header($value, $this->getCharset(), Zend_Mime::LINELENGTH, Zend_Mime::LINEEND);
        } else {
            $value = parent::_encodeHeader($value);
        }
        return $value;
    }

    /**
     * _convertEncoding
     *
     * @param  string $string
     * @return string
     */
    protected function _convertEncoding($string)
    {
        return mb_convert_encoding($string, $this->getCharset());;
    }
}
