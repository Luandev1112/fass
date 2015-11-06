<?php
/**
 * class Shared_Model_Resource_Manual
 *
 * マニュアルコンテンツ
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Manual extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($manualId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($manualId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($manualId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($manualId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($manualId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($manualId, $id, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($manualId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($manualId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($manualId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($manualId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($manualId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($manualId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($manualId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $manualId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($manualId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$manualId / 1000) * 1000;
        
        return self::_getResourcePath('manual' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $manualId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($manualId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$manualId / 1000) * 1000;
        
        return self::_getResourcePath('manual' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $manualId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($manualId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($manualId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($manualId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($manualId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($manualId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $manualId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($manualId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($manualId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$manualId / 1000) * 1000;   
        return '/rsrc/manual/' . $manualId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
