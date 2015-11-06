<?php
/**
 * class Nutex_Filter_Encrypt
 *
 * 暗号フィルタ 暗号処理はNutex_Crypt依存
 * 暗号化用共通インスタンスとして Zend_Filter_Encrypt_Mcrypt を期待しています
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Encrypt extends Nutex_Filter_Abstract
{
    /**
     * 暗号化されたものをバイナリのまま扱うかどうか
     * @var boolean
     */
    protected $_binaryMode = false;

    /**
     * 暗号
     *
     * @param  string $value
     * @return string $encrypted
     */
    public function filter($value)
    {
        if ($value === null) {
            return $value;
        }

        //暗号化時のnullバイトパディング対策で、base64_encodeしてから暗号化
        $encrypted = Nutex_Crypt::getInstance()->encrypt(base64_encode($value));

        if ($this->getBinaryMode() == false) {
            $encrypted = base64_encode($encrypted);
        }

        return $encrypted;
    }

    /**
     * getBinaryMode
     *
     * @param void
     * @return boolean
     */
    public function getBinaryMode()
    {
        return $this->_binaryMode;
    }

    /**
     * setBinaryMode
     *
     * @param boolean $flag
     * @return $this
     */
    public function setBinaryMode($flag)
    {
        $this->_binaryMode = (boolean)$flag;
        return $this;
    }
}
