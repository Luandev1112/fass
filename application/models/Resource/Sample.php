<?php
/**
 * class Shared_Model_Resource_Sample
 *
 * サンプル出荷/在庫破棄
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Sample extends Shared_Model_Resource_Abstract
{

//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $sampleId
     * @param string $fileName
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($sampleId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($sampleId, $fileName))) {
            $backups[$fileName] = self::getBinary($sampleId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($sampleId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($sampleId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($sampleId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $sampleId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($sampleId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($sampleId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $sampleId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($sampleId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($sampleId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $sampleId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($sampleId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($sampleId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($sampleId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $sampleId
     * @param int    $versionId
     * @return string
     */
    public static function getResourceDirectoryPath($sampleId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$sampleId / 1000) * 1000;
        
        return self::_getResourcePath('sample' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $sampleId);
    }
    
    /**
     * リソースのパス
     * @param int    $sampleId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($sampleId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$sampleId / 1000) * 1000;
        
        return self::_getResourcePath('sample' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $sampleId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $sampleId
     * @param string $fileName
     * @return string
     */
    public static function isExist($sampleId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($sampleId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $sampleId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($sampleId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($sampleId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($sampleId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $sampleId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($sampleId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($sampleId, $fileName))) {
        	return '';
        }

        $base = ceil((int)$sampleId / 1000) * 1000;   
        return '/rsrc/sample/' . $sampleId . '/' . urlencode($fileName);
    }
    

}
