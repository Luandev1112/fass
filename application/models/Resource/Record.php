<?php
/**
 * class Shared_Model_Resource_Record
 *
 * 議事録ファイルアップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Record extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($recordId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($recordId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($recordId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($recordId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($recordId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($recordId, $id, $fileName);
                }
            }
            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($recordId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($recordId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($recordId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($recordId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($recordId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($recordId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($recordId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $recordId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($recordId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$recordId / 1000) * 1000;
        
        return self::_getResourcePath('record' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $recordId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($recordId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$recordId / 1000) * 1000;
        
        return self::_getResourcePath('record' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $recordId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($recordId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($recordId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($recordId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($recordId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($recordId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $recordId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($recordId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($recordId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$recordId / 1000) * 1000;   
        return '/rsrc/record/' . $recordId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
