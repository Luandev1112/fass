<?php
/**
 * class Nutex_Validate_Encoding
 *
 * 文字コードバリデータ
 *
 * 指定された文字コードで使えない文字が含まれているかどうかチェックします
 * デフォルトのチェック文字コードはcp932(SJIS-win)
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_Encoding extends Nutex_Validate_Abstract_Filter
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MATCH => "使用できない文字が含まれています => [%chars%]",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'chars' => '_chars',
    );

    /**
     * フィルターインスタンス
     * @var string|array|Zend_Filter_Interface
     */
    protected $_filter = array(
        'Nutex_Filter_Encoding',
        array('target' => 'SJIS-win'),
    );

    /**
     * @var string
     */
    protected $_chars = '';

    /**
     * isValid
     * Defined by Zend_Validate_Interface
     *
     * @param void
     * @return string
     */
    public function isValid($value)
    {
        $value = (string)$value;
        $this->_setValue($value);

        $filtered = $this->getFilter()->filter($value);
        if ($value !== $filtered) {
            $this->_chars = implode('', $this->_getStringDiff($value, $filtered));
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }

    /**
     * setEncoding
     *
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        if ($this->_filter instanceof Nutex_Filter_Encoding) {
            $this->_filter->setEncoding($encoding);
        } else {
            $this->_filter = array(
                'Nutex_Filter_Encoding',
                array('target' => $encoding),
            );
        }
        return $this;
    }

    /**
     * _getStringDiff
     *
     * @param string $source
     * @param string $compare
     * @param boolean $unique
     * @return array $diff
     */
    protected function _getStringDiff($source, $compare, $unique = true)
    {
        $length = mb_strlen($source);
        $diff = array();
        for ($i = 0; $i < $length; $i++) {
            $src = mb_substr($source, $i, 1);
            if ($unique && in_array($src, $diff)) {
                continue;
            }
            if (mb_strpos($compare, $src) === false) {
                $diff[] = $src;
            }
        }
        return $diff;
    }
}
