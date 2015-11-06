<?php
/**
 * class Shared_Model_Resource_SupplyProduction
 *
 * 調達管理 製造管理 入手見積書・補足資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_SupplyProduction extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($productionId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($productionId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($productionId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($productionId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($productionId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($productionId, $id, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($productionId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($productionId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($productionId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($productionId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($productionId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($productionId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($productionId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $productionId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($productionId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$productionId / 1000) * 1000;
        
        return self::_getResourcePath('supply_production' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $productionId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($productionId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$productionId / 1000) * 1000;
        
        return self::_getResourcePath('supply_production' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $productionId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($productionId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($productionId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($productionId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($productionId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($productionId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $productionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($productionId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($productionId, $id, $fileName))) {
        	return '';
        }
        
        $base = ceil((int)$productionId / 1000) * 1000;   
        return '/rsrc/supply-production/' . $productionId . '/' . $id . '/' . urlencode($fileName);
    }
    

}
