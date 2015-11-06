<?php
/**
 * class Nutex_Helper_View_StaticFile
 *
 * 静的ファイルのパスを取得
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_StaticFile extends Nutex_Helper_View_Abstract
{
    /**
     * @var string
     */
    protected $_staticFileDir = '/static';

    /**
     * 静的ファイルのパスを取得
     *
     * @param string $filePath
     * @param boolean $addModule
     * @return string $path
     */
    public function staticFile($filePath)
    {
        if ($filePath[0] === '/') {
            $filePath = substr($filePath, 1);
        }

        $client = $this->getView()->getClient();
        if ($client instanceof Nutex_Client_Abstract) {
            foreach (array_reverse($client->getClientNames('/')) as $name) {//配列を逆順にしてます
                $path = $this->_staticFileDir . '/' . $name;
                if (is_readable(PUBLIC_PATH . $path)) {
                    break;
                }
            }
        } else {
            $path = $this->_staticFileDir;
        }

        return $this->getView()->url($path . '/' . $filePath);
    }
}
