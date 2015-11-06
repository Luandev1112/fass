<?php
/**
 * class Shared_Helper_View_RoundText
 *
 * 長いテキストを丸めるヘルパー
 *
 */
class Shared_Helper_View_RoundText extends Nutex_Helper_View_Abstract
{
    const ASCII_COUNT = 0.6;

    /**
     * @var int
     */
    protected $_default = null;

    /**
     * 長いテキストを丸める
     *
     * @param string $text
     * @param int $limit
     * @param string $continueSign
     * @return string
     */
    public function roundText($text, $limit = null, $continueSign = '…')
    {
        if ($limit === null) {
            $limit = $this->getDefault();
        }

        //半角の文字数カウント調整
        $len = mb_strlen($text);
        
        $numChars = 0;
        $charLimit = 0;
        for ($i = 0; $i < $len; $i++) {
            $numChars += (strlen(mb_substr($text, $i, 1)) > 1) ? 1 : self::ASCII_COUNT;
            if ($numChars > $limit) {
                break;
            }
            $charLimit++;
        }
        
        //return $numChars . ' ' . ' ' . $limit;

        if ($numChars > $limit) {
            return mb_substr($text, 0, $charLimit) . $continueSign;
        } else {
            return $text;
        }
    }

    /**
     * @return int
     */
    public function getDefault()
    {
        if ($this->_default === null) {
            $this->_default = ($this->getView()->getClient() instanceof Nutex_Client_SmartPhone) ? 12 : 20;
        }
        return $this->_default;
    }

    /**
     * @param int $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->_default = $default;
        return $this;
    }
}
