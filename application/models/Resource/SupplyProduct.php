<?php
/**
 * class Shared_Model_Resource_SupplyProduct
 *
 * 調達管理 原料・製品 入手見積書・補足資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_SupplyProduct extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($supplyProductId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($supplyProductId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($supplyProductId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($supplyProductId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($supplyProductId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($supplyProductId, $id, $fileName);
                }
            }
            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($supplyProductId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($supplyProductId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($supplyProductId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($supplyProductId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($supplyProductId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($supplyProductId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($supplyProductId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $supplyProductId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($supplyProductId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$supplyProductId / 1000) * 1000;
        
        return self::_getResourcePath('supply_product' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $supplyProductId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($supplyProductId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$supplyProductId / 1000) * 1000;
        
        return self::_getResourcePath('supply_product' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $supplyProductId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($supplyProductId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($supplyProductId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($supplyProductId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($supplyProductId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($supplyProductId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $supplyProductId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($supplyProductId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($supplyProductId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$supplyProductId / 1000) * 1000;   
        return '/rsrc/supply-product/' . $supplyProductId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
