<?php
/**
 * class Nutex_Filter_ConvertEncoding
 *
 * 文字コード変換
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_ConvertEncoding extends Nutex_Filter_Abstract
{
    /**
     * 変換前エンコーディング
     * @var string
     */
    protected $_from = null;

    /**
     * 変換後エンコーディング
     * @var string
     */
    protected $_to = 'SJIS-win';

    /**
     * フィルタリング
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!$this->getFrom()) {
            $this->setFrom(mb_detect_encoding($value, array('UTF-8', 'SJIS-win', 'eucJP-win', 'Windows-1252')));
        }
        return mb_convert_encoding($value, $this->getTo(), $this->getFrom());;
    }

    /**
     * getFrom
     *
     * @param void
     * @return string
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * setFrom
     *
     * @param string $encoding
     * @return $this
     */
    public function setFrom($encoding)
    {
        $this->_from = (string)$encoding;
        return $this;
    }

    /**
     * getTo
     *
     * @param void
     * @return string
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * setTo
     *
     * @param string $encoding
     * @return $this
     */
    public function setTo($encoding)
    {
        $this->_to = (string)$encoding;
        return $this;
    }
}
