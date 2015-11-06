<?php
/**
 * class Shared_Model_Resource_Stamp
 *
 * 電子印鑑画像ファイルアップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Stamp extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $userId
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($userId, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($userId))) {
            $backup = self::getBinary($userId);
        }

        
        // 画像を保存
        if (self::saveResource($userId, $data) === false) {
        
            // バックアップから復帰
            if (isset($backup)) {
                self::saveResource($userId, $backup);
            } else {
                self::removeResource($userId);
            }
        
            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $userId
     * @param string $data
     * @return boolean
     */
    public static function saveResource($userId, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($userId));
    }

    /**
     * リソースを削除する
     * @param int    $userId
     * @return boolean
     */
    public static function removeResource($userId)
    {
        return self::_removeFile(self::getResourceObjectPath($userId));
    }

    /**
     * リソースを得る
     * @param int    $userId
     * @return string
     */
    public static function getBinary($userId)
    {
        if (file_exists(self::getResourceObjectPath($userId))) {
            return self::_getFile(self::getResourceObjectPath($userId)); 
        } else {
        	return self::_getFile(self::_getResourcePath('stamp' . DIRECTORY_SEPARATOR . 'stamp_default.png'));
        }
    }
    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースのパス
     * @param int    $userId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($userId)
    {        
        return self::_getResourcePath('stamp' . DIRECTORY_SEPARATOR . $userId . '.png');
    }

    /**
     * ファイルサイズ
     * @param int    $userId
     * @return string
     */
    public static function getFileSize($userId)
    {
        if (!file_exists(self::getResourceObjectPath($userId))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($userId));
    }
    

    /**
     * 画像のURL
     * @param string $contentKey
     * @return string
     */
    public static function getResourceUrl($userId)
    {
        return '/rsrc/stamp/' . $userId;
    }
    
}
