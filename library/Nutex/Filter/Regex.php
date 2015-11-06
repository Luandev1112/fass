<?php
/**
 * class Nutex_Filter_Regex
 *
 * 正規表現によるフィルタ
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Regex extends Nutex_Filter_Abstract
{
    /**
     * フィルタ用正規表現
     * @var string
     */
    protected $_pattern;

    /**
     * フィルタリング
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return mb_ereg_replace($this->getPattern(), '', (string)$value);
    }

    /**
     * getPattern
     *
     * @param void
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * setPattern
     *
     * @param string $pattern
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->_pattern = (string)$pattern;
        return $this;
    }
}
