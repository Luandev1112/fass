<?php
/**
 * class Shared_Model_Resource_Payable
 *
 * 請求支払申請 請求書ファイルアップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Payable extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($payableId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($payableId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($payableId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($payableId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($payableId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($payableId, $id, $fileName);
                }
            }
            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($payableId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($payableId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($payableId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($payableId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($payableId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($payableId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($payableId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $payableId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($payableId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$payableId / 1000) * 1000;
        
        return self::_getResourcePath('payable' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $payableId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($payableId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$payableId / 1000) * 1000;
        
        return self::_getResourcePath('payable' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $payableId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($payableId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($payableId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($payableId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($payableId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($payableId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $payableId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($payableId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($payableId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$payableId / 1000) * 1000;   
        return '/rsrc/payable/' . $payableId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
