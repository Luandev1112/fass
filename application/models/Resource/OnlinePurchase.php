<?php
/**
 * class Shared_Model_Resource_DirectOrder
 *
 * ネット購入委託管理 添付資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_OnlinePurchase extends Shared_Model_Resource_Abstract
{

//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($onlinePurchaseId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($onlinePurchaseId, $fileName))) {
            $backups[$fileName] = self::getBinary($onlinePurchaseId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($onlinePurchaseId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($onlinePurchaseId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($onlinePurchaseId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($onlinePurchaseId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($onlinePurchaseId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($onlinePurchaseId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($onlinePurchaseId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($onlinePurchaseId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($onlinePurchaseId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($onlinePurchaseId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $onlinePurchaseId
     * @param int    $versionId
     * @return string
     */
    public static function getResourceDirectoryPath($onlinePurchaseId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$onlinePurchaseId / 1000) * 1000;
        
        return self::_getResourcePath('online_purchase' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $onlinePurchaseId);
    }
    
    /**
     * リソースのパス
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($onlinePurchaseId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$onlinePurchaseId / 1000) * 1000;
        
        return self::_getResourcePath('online_purchase' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $onlinePurchaseId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @return string
     */
    public static function isExist($onlinePurchaseId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($onlinePurchaseId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($onlinePurchaseId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($onlinePurchaseId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($onlinePurchaseId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $onlinePurchaseId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($onlinePurchaseId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($onlinePurchaseId, $fileName))) {
        	return '';
        }

        $base = ceil((int)$onlinePurchaseId / 1000) * 1000;   
        return '/rsrc/online-purchase/' . $onlinePurchaseId . '/' . urlencode($fileName);
    }
    

}
