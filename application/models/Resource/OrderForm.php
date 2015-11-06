<?php
/**
 * class Shared_Model_Resource_OrderForm
 *
 * 注文書アップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_OrderForm extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $orderFormId
     * @param string $fileName
     * @param binary $data
     * @return boolean
     */
    public static function makeResource($orderFormId, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($orderFormId, $fileName))) {
            $backups[$fileName] = self::getBinary($orderFormId, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($orderFormId, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($orderFormId, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($orderFormId, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $orderFormId
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($orderFormId, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($orderFormId, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $orderFormId
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($orderFormId, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($orderFormId, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $orderFormId
     * @param string $fileName
     * @return string
     */
    public static function getBinary($orderFormId, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($orderFormId, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($orderFormId, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $orderFormId
     * @param int    $versionId
     * @return string
     */
    public static function getResourceDirectoryPath($orderFormId)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$orderFormId / 1000) * 1000;
        
        return self::_getResourcePath('order_form' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $orderFormId);
    }
    
    /**
     * リソースのパス
     * @param int    $orderFormId
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($orderFormId, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$orderFormId / 1000) * 1000;
        
        return self::_getResourcePath('order_form' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $orderFormId . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $orderFormId
     * @param string $fileName
     * @return string
     */
    public static function isExist($orderFormId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($orderFormId, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $orderFormId
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($orderFormId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($orderFormId, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($orderFormId, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $orderFormId
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($orderFormId, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($orderFormId, $fileName))) {
        	return '';
        }

        $base = ceil((int)$orderFormId / 1000) * 1000;   
        return '/rsrc/order-form/' . $orderFormId . '/' . urlencode($fileName);
    }
    

}
