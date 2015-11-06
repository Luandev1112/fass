<?php
/**
 * class Shared_Model_Resource_EstimateUpload
 *
 * 見積書アップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_EstimateUpload extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($estimateId, $versionId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($estimateId, $versionId, $fileName))) {
            $backups[$fileName] = self::getBinary($estimateId, $versionId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($estimateId, $versionId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($estimateId, $versionId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($estimateId, $versionId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($estimateId, $versionId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($estimateId, $versionId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($estimateId, $versionId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($estimateId, $versionId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($estimateId, $versionId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($estimateId, $versionId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($estimateId, $versionId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $estimateId
     * @param int    $versionId
     * @return string
     */
    public static function getResourceDirectoryPath($estimateId, $versionId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$estimateId / 1000) * 1000;
        
        return self::_getResourcePath('estimate_upload' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $estimateId . DIRECTORY_SEPARATOR . $versionId);
    }
    
    /**
     * リソースのパス
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($estimateId, $versionId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$estimateId / 1000) * 1000;
        
        return self::_getResourcePath('estimate_upload' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $estimateId . DIRECTORY_SEPARATOR . $versionId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return string
     */
    public static function isExist($estimateId, $versionId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($estimateId, $versionId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($estimateId, $versionId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($estimateId, $versionId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($estimateId, $versionId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $estimateId
     * @param int    $versionId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($estimateId, $versionId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($estimateId, $versionId, $fileName))) {
        	return '';
        }

        $base = ceil((int)$estimateId / 1000) * 1000;   
        return '/rsrc/estimate/' . $estimateId . '/' . $versionId . '/' . urlencode($fileName);;
    }
    

}
