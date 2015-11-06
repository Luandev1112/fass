<?php
/**
 * class Shared_Model_Resource_WarehouseItem
 *
 * 在庫管理資材 写真管理
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_WarehouseItem extends Shared_Model_Resource_Abstract
{
    /**
     * 各素材の拡張子
     * @var string
     */
    const EXTENTION_FOR_IMAGE = 'jpg';
    
//-------------------------------------------------------------------------------------
    
    /**
     * 画像を保存する
     * @param int    $id
     * @param string $contentKey
     * @return boolean
     */
    public static function makeResource($id, $contentKey, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($id, $contentKey))) {
            $backups[$contentKey] = self::getBinary($id, $contentKey);
        }

        
        // 画像を保存
        if (self::saveResource($id, $contentKey, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $contentKey) {
                if (isset($backups[$contentKey])) {
                    self::saveResource($id, $contentKey, $backups[$contentKey]);
                } else {
                    self::removeResource($id, $contentKey);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * 画像を保存する
     * @param int    $id
     * @param string $contentKey
     * @param string $data
     * @return boolean
     */
    public static function saveResource($id, $contentKey, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($id, $contentKey));
    }

    /**
     * 画像を削除する
     * @param int    $id
     * @param string $contentKey
     * @return boolean
     */
    public static function removeResource($id, $contentKey)
    {
        return self::_removeFile(self::getResourceObjectPath($id, $contentKey));
    }

    /**
     * 画像を得る
     * @param int    $id
     * @param string $contentKey
     * @return string
     */
    public static function getBinary($id, $contentKey)
    {
        if (file_exists(self::getResourceObjectPath($id, $contentKey))) {
            return self::_getFile(self::getResourceObjectPath($id, $contentKey)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$id / 1000) * 1000;
        
        return self::_getResourcePath('warehouse_item' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * 画像のパス
     * @param int    $id
     * @param string $contentKey
     * @return string
     */
    public static function getResourceObjectPath($id, $contentKey)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$id / 1000) * 1000;
        
        return self::_getResourcePath('warehouse_item' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $contentKey . '.' . self::EXTENTION_FOR_IMAGE);
    }

    /**
     * リソースが存在するか？
     * @param int    $id
     * @param int    $imageId
     * @param string $contentKey
     * @return string
     */
    public static function isExist($id, $contentKey)
    {
        if (!file_exists(self::getResourceObjectPath($id, $contentKey))) {
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
     * @param int    $id
     * @param string $contentKey
     * @return string
     */
    public static function getResourceUrl($id, $contentKey)
    {
        if (!file_exists(self::getResourceObjectPath($id, $contentKey))) {
        	return self::getDefaultImageUrl();
        }
        
        $base = ceil((int)$id / 1000) * 1000;   
        return '/rsrc/warehouse-item/' . $id . '/' . $contentKey . '.' . self::EXTENTION_FOR_IMAGE;
    }
    

}
