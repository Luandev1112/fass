<?php
/**
 * class Shared_Model_Resource_Receivable
 *
 * 入金申請 参考資料ファイルアップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Receivable extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($receivableId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($receivableId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($receivableId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($receivableId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($receivableId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($receivableId, $id, $fileName);
                }
            }
            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($receivableId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($receivableId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($receivableId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($receivableId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($receivableId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($receivableId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($receivableId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $receivableId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($receivableId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$receivableId / 1000) * 1000;
        
        return self::_getResourcePath('receivable' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $receivableId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($receivableId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$receivableId / 1000) * 1000;
        
        return self::_getResourcePath('receivable' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $receivableId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($receivableId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($receivableId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($receivableId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($receivableId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($receivableId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $receivableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($receivableId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($receivableId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$receivableId / 1000) * 1000;   
        return '/rsrc/receivable/' . $receivableId . '/' . $id . '/' . urlencode($fileName);
    }
    

}
