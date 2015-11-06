<?php
/**
 * class Shared_Model_Resource_Item
 *
 * 商品写真管理
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Item extends Shared_Model_Resource_Abstract
{
    /**
     * 各素材の拡張子
     * @var string
     */
    const EXTENTION_FOR_IMAGE = 'jpg';
    
//-------------------------------------------------------------------------------------
    
    /**
     * 画像を保存する
     * @param int    $itemId
     * @param string $contentKey
     * @return boolean
     */
    public static function makeResource($itemId, $contentKey, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($itemId, $contentKey))) {
            $backups[$contentKey] = self::getBinary($itemId, $contentKey);
        }

        
        // 画像を保存
        if (self::saveResource($itemId, $contentKey, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $contentKey) {
                if (isset($backups[$contentKey])) {
                    self::saveResource($itemId, $contentKey, $backups[$contentKey]);
                } else {
                    self::removeResource($itemId, $contentKey);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * 画像を保存する
     * @param int    $itemId
     * @param string $contentKey
     * @param string $data
     * @return boolean
     */
    public static function saveResource($itemId, $contentKey, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($itemId, $contentKey));
    }

    /**
     * 画像を削除する
     * @param int    $itemId
     * @param string $contentKey
     * @return boolean
     */
    public static function removeResource($itemId, $contentKey)
    {
        return self::_removeFile(self::getResourceObjectPath($itemId, $contentKey));
    }

    /**
     * 画像を得る
     * @param int    $itemId
     * @param string $contentKey
     * @return string
     */
    public static function getBinary($itemId, $contentKey)
    {
        if (file_exists(self::getResourceObjectPath($itemId, $contentKey))) {
            return self::_getFile(self::getResourceObjectPath($itemId, $contentKey)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $itemId
     * @return string
     */
    public static function getResourceDirectoryPath($itemId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$itemId / 1000) * 1000;
        
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'item' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $itemId);
    }
    
    /**
     * 画像のパス
     * @param int    $itemId
     * @param string $contentKey
     * @return string
     */
    public static function getResourceObjectPath($itemId, $contentKey)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$itemId / 1000) * 1000;
        
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'item' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR . $contentKey . '.' . self::EXTENTION_FOR_IMAGE);
    }

    /**
     * リソースが存在するか？
     * @param int    $itemId
     * @param int    $imageId
     * @param string $contentKey
     * @return string
     */
    public static function isExist($itemId, $contentKey)
    {
        if (!file_exists(self::getResourceObjectPath($itemId, $contentKey))) {
        	return false;
        }
        
        return true;
    }
    
    public static function getDefaultImageUrl()
    {
    	return '/resource/item/default.png';
    }
    
    /**
     * 画像のURL
     * @param int    $itemId
     * @param string $contentKey
     * @return string
     */
    public static function getResourceUrl($itemId, $contentKey)
    {
        if (!file_exists(self::getResourceObjectPath($itemId, $contentKey))) {
        	return self::getDefaultImageUrl();
        }
        
        $base = ceil((int)$itemId / 1000) * 1000;   
        return '/resource/item/' . $base . '/' . $itemId . '/' . $contentKey . '.' . self::EXTENTION_FOR_IMAGE;
    }
    

}
