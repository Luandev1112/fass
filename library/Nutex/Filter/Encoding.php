<?php
/**
 * class Nutex_Filter_Encoding
 *
 * 文字コードフィルタ
 *
 * 指定されたエンコーディングに無い文字を全て落とします 文字コード変換はしません
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Encoding extends Nutex_Filter_Abstract
{
    /**
     * 変換前エンコーディング
     * @var string
     */
    protected $_current = null;

    /**
     * 変換後エンコーディング
     * @var string
     */
    protected $_target = 'SJIS-win';

    /**
     * フィルタリング
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (!$this->getCurrent()) {
            $this->setCurrent(mb_detect_encoding($value, array('UTF-8', 'SJIS-win', 'eucJP-win', 'Windows-1252')));
        }

        //変換して元に戻すことでフィルタリングする
        $value = mb_convert_encoding($value, $this->getTarget(), $this->getCurrent());
        $value = mb_convert_encoding($value, $this->getCurrent(), $this->getTarget());

        return $value;
    }

    /**
     * getCurrent
     *
     * @param void
     * @return string
     */
    public function getCurrent()
    {
        return $this->_current;
    }

    /**
     * setCurrent
     *
     * @param string $encoding
     * @return $this
     */
    public function setCurrent($encoding)
    {
        $this->_current = (string)$encoding;
        return $this;
    }

    /**
     * getTarget
     *
     * @param void
     * @return string
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * setTarget
     *
     * @param string $encoding
     * @return $this
     */
    public function setTarget($encoding)
    {
        $this->_target = (string)$encoding;
        return $this;
    }
}
