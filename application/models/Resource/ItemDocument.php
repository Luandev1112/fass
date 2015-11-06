<?php
/**
 * class Shared_Model_Resource_ItemDocument
 *
 * 商品関連資料管理
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_ItemDocument extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($itemId, $documentId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($itemId, $documentId, $fileName))) {
            $backups[$fileName] = self::getBinary($itemId, $documentId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($itemId, $documentId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($itemId, $documentId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($itemId, $documentId, $fileName);
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
     * @param int    $documentId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($itemId, $documentId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($itemId, $documentId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($itemId, $documentId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($itemId, $documentId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($itemId, $documentId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($itemId, $documentId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($itemId, $documentId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $itemId
     * @param int    $documentId
     * @return string
     */
    public static function getResourceDirectoryPath($itemId, $documentId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$itemId / 1000) * 1000;
        
        return self::_getResourcePath('item_doc' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR . $documentId);
    }
    
    /**
     * リソースのパス
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($itemId, $documentId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$itemId / 1000) * 1000;
        
        return self::_getResourcePath('item_doc' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $itemId . DIRECTORY_SEPARATOR . $documentId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return string
     */
    public static function isExist($itemId, $documentId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($itemId, $documentId, $fileName))) {
        	return false;
        }
        
        return true;
    }
    
    /**
     * ファイルサイズ
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($itemId, $documentId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($itemId, $documentId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($itemId, $documentId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $itemId
     * @param int    $documentId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($itemId, $documentId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($itemId, $documentId, $fileName))) {
        	return '';
        }
        
        $base = ceil((int)$itemId / 1000) * 1000;   
        return '/resource/item-doc/' . '/' . $itemId . '/' . $documentId . '/' . urlencode($fileName);
    }
    

}
