<?php
/**
 * class Shared_Model_Resource_Material
 *
 * 資料管理
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Material extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($managementGroupId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($managementGroupId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($managementGroupId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($managementGroupId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($managementGroupId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($managementGroupId, $id, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($managementGroupId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($managementGroupId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($managementGroupId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($managementGroupId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($managementGroupId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($managementGroupId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($managementGroupId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $managementGroupId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($managementGroupId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$managementGroupId / 1000) * 1000;
        
        return self::_getResourcePath('material' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $managementGroupId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($managementGroupId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$managementGroupId / 1000) * 1000;

        return self::_getResourcePath('material' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $managementGroupId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($managementGroupId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($managementGroupId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }
    
    /**
     * ファイルサイズ
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($managementGroupId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($managementGroupId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($managementGroupId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $managementGroupId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrlForBuyer($managementGroupId, $id, $saveFileName, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($managementGroupId, $id, $saveFileName))) {
        	return '';
        }
         
        return '/rsrc/material/' . $managementGroupId . '/' . $id . '/' . urlencode($fileName);
    }

}
