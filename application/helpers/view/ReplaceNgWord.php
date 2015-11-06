<?php
/**
 * class Shared_Helper_View_ReplaceNgWord
 *
 * NGワードを伏字にする
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_ReplaceNgWord extends Nutex_Helper_View_Abstract
{
    const HUSEJI = '＊＊＊＊';

    /**
     * @var array
     */
    protected $_ngWords = array();

    /**
     * replaceNgWord
     * @param string|array $input
     * @return string|void
     */
    public function replaceNgWord($input)
    {
        if (is_array($input)) {
            return $this->setNgWords($input);
        } else {
            return $this->replace($input);
        }
    }

    /**
     * replace
     * @param string $comment
     * @return string $comment
     */
    public function replace($comment)
    {
        foreach ($this->getNgWords() as $ngWord) {
            $comment = str_replace($ngWord, self::HUSEJI, $comment);
        }
        return $comment;
    }

    /**
     * setNgWords
     * @param array $ngWords
     * @return $this
     */
    public function setNgWords(array $ngWords)
    {
        $this->_ngWords = $ngWords;
        return $this;
    }

    /**
     * getNgWords
     * @return array
     */
    public function getNgWords()
    {
        return $this->_ngWords;
    }
}
