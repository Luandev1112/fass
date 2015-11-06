<?php
/**
 * class Shared_Model_Resource_Invoice
 *
 * 請求書アップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Invoice extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $invoiceId
     * @param string $fileName
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($invoiceId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($invoiceId, $fileName))) {
            $backups[$fileName] = self::getBinary($invoiceId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($invoiceId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($invoiceId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($invoiceId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $invoiceId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($invoiceId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($invoiceId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $invoiceId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($invoiceId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($invoiceId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $invoiceId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($invoiceId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($invoiceId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($invoiceId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $invoiceId
     * @param int    $versionId
     * @return string
     */
    public static function getResourceDirectoryPath($invoiceId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$invoiceId / 1000) * 1000;
        
        return self::_getResourcePath('invoice' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $invoiceId);
    }
    
    /**
     * リソースのパス
     * @param int    $invoiceId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($invoiceId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$invoiceId / 1000) * 1000;
        
        return self::_getResourcePath('invoice' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $invoiceId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $invoiceId
     * @param string $fileName
     * @return string
     */
    public static function isExist($invoiceId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($invoiceId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $invoiceId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($invoiceId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($invoiceId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($invoiceId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $invoiceId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($invoiceId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($invoiceId, $fileName))) {
        	return '';
        }

        $base = ceil((int)$invoiceId / 1000) * 1000;   
        return '/rsrc/invoice/' . $invoiceId . '/' . urlencode($fileName);
    }
    

}
