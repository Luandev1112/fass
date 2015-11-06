<?php
/**
 * class Nutex_Helper_View_VersionNumber
 *
 * バージョン番号埋め込みヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_VersionNumber extends Nutex_Helper_View_Abstract
{
    protected $_version;

    /**
     * チケットをhiddenで表示する
     *
     * @param string $version
     * @return string
     */
    public function versionNumber($versionNo = null)
    {
        if ($versionNo) {
            $this->setVersion($version);
            return;
        } elseif ($this->getVersion()) {
            return $this->getView()->formHidden('__version__', $this->getVersion());
        }

        return '';
    }
    /**
     * チケットをセットする
     *
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
       $this->_version = $version;
    }

    /**
     * チケットを取得する
     *
     * @param void
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }
}
