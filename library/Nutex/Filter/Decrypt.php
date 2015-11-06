<?php
/**
 * class Nutex_Filter_Decrypt
 *
 * 復号フィルタ 復号処理はNutex_Crypt依存
 * 暗号化用共通インスタンスとして Zend_Filter_Encrypt_Mcrypt を期待しています
 *
 * @package Nutex
 * @subpackage Nutex_Filter
 */
class Nutex_Filter_Decrypt extends Nutex_Filter_Abstract
{
    /**
     * 暗号化されたものをバイナリのまま扱うかどうか
     * @var boolean
     */
    protected $_binaryMode = false;

    /**
     * 復号
     *
     * @param  string $value
     * @return string $encrypted
     */
    public function filter($value)
    {
        if ($value === null) {
            return $value;
        }

        if ($this->getBinaryMode() == false) {
            $value = base64_decode($value);
        }

        //Nutex_Filter_Encryptでは暗号化時のnullバイトパディング対策で、base64_encodeしてから暗号化されているはずなので
        return base64_decode(Nutex_Crypt::getInstance()->decrypt($value));
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
