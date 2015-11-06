<?php
/**
 * class Shared_Model_Resource_ItemImage
 *
 * 商品画像管理
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_ItemImage extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($itemId, $imageId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($itemId, $imageId, $fileName))) {
            $backups[$fileName] = self::getBinary($itemId, $imageId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($itemId, $imageId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($itemId, $imageId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($itemId, $imageId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($itemId, $imageId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($itemId, $imageId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($itemId, $imageId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($itemId, $imageId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($itemId, $imageId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($itemId, $imageId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($itemId, $imageId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $itemId
     * @param int    $imageId
     * @return string
     */
    public static function getResourceDirectoryPath($itemId, $imageId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$itemId / 1000) * 1000;
        
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'item' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR . $imageId);
    }
    
    /**
     * リソースのパス
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($itemId, $imageId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$itemId / 1000) * 1000;
        
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'item' .  DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR . $imageId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @return string
     */
    public static function isExist($itemId, $imageId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($itemId, $imageId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    public static function getDefaultImageUrl()
    {
    	return '/resource/item/default.png';
    }
     
    /**
     * リソースURL
     * @param int    $itemId
     * @param int    $imageId
     * @param string $fileName
     * @return string
     */
    public static function getImageUrl($itemId, $imageId, $fileName)
    {
        if (!self::isExist($itemId, $imageId, $fileName)) {
        	return self::getDefaultImageUrl();
        }
        
        $base = ceil((int)$itemId / 1000) * 1000;   
        return '/resource/item/' . $base . '/' . $itemId . '/' . $imageId . '/' . $fileName;
    }
    
}
