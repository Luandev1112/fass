<?php
/**
 * class Shared_Model_Resource_DirectOrder
 *
 * 受注管理 添付資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_DirectOrder extends Shared_Model_Resource_Abstract
{

//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $directOrderId
     * @param string $fileName
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($directOrderId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($directOrderId, $fileName))) {
            $backups[$fileName] = self::getBinary($directOrderId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($directOrderId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($directOrderId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($directOrderId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $directOrderId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($directOrderId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($directOrderId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $directOrderId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($directOrderId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($directOrderId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $directOrderId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($directOrderId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($directOrderId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($directOrderId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $directOrderId
     * @param int    $versionId
     * @return string
     */
    public static function getResourceDirectoryPath($directOrderId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$directOrderId / 1000) * 1000;
        
        return self::_getResourcePath('direct_order' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $directOrderId);
    }
    
    /**
     * リソースのパス
     * @param int    $directOrderId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($directOrderId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$directOrderId / 1000) * 1000;
        
        return self::_getResourcePath('direct_order' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $directOrderId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $directOrderId
     * @param string $fileName
     * @return string
     */
    public static function isExist($directOrderId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($directOrderId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $directOrderId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($directOrderId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($directOrderId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($directOrderId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $directOrderId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($directOrderId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($directOrderId, $fileName))) {
        	return '';
        }

        $base = ceil((int)$directOrderId / 1000) * 1000;   
        return '/rsrc/direct-order/' . $directOrderId . '/' . urlencode($fileName);
    }
    

}
