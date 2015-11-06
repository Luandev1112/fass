<?php
/**
 * class Shared_Model_Resource_Logo
 *
 * ロゴ画像管理
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Logo extends Shared_Model_Resource_Abstract
{
    /**
     * 各素材の拡張子
     * @var string
     */
    const EXTENTION_FOR_IMAGE = 'png';
    
//-------------------------------------------------------------------------------------
    
    /**
     * 画像を保存する
     * @param string   $contentKey
     * @param resource $data
     * @return boolean
     */
    public static function makeResource($contentKey, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($contentKey))) {
            $backups[$contentKey] = self::getBinary($contentKey);
        }

        // 画像を保存
        if (self::saveResource($contentKey, $data) === false) {
        
            // バックアップから復帰
            if (isset($backups[$contentKey])) {
                self::saveResource($contentKey, $backups[$contentKey]);
            }

            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * 画像を保存する
     * @param string $contentKey
     * @param string $data
     * @return boolean
     */
    public static function saveResource($contentKey, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($contentKey));
    }

    /**
     * 画像を削除する
     * @param string $contentKey
     * @return boolean
     */
    public static function removeResource($contentKey)
    {
        return self::_removeFile(self::getResourceObjectPath($contentKey));
    }

    /**
     * 画像を得る
     * @param string $contentKey
     * @return string
     */
    public static function getBinary($contentKey)
    {
        if (file_exists(self::getResourceObjectPath($contentKey))) {
            return self::_getFile(self::getResourceObjectPath($contentKey)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------

    /**
     * 保存ディレクトリのパス
     * @param  none
     * @return string
     */
    public static function getResourceDirectoryPath()
    {
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR);
    }
    
    /**
     * 画像のパス
     * @param string $contentKey
     * @return string
     */
    public static function getResourceObjectPath($contentKey)
    {
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'logo' . DIRECTORY_SEPARATOR . $contentKey . '.' . self::EXTENTION_FOR_IMAGE);
    }

    /**
     * 画像のURL
     * @param string $contentKey
     * @return string
     */
    public static function getResourceUrl($contentKey)
    {
        return '/resource/logo/' . $contentKey . '.' . self::EXTENTION_FOR_IMAGE;
    }

}
