<?php
/**
 * class Shared_Model_Resource_SupplySubcontracting
 *
 * 調達管理 業務委託 入手見積書・補足資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_SupplySubcontracting extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($subcontractingId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($subcontractingId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($subcontractingId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($subcontractingId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($subcontractingId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($subcontractingId, $id, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($subcontractingId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($subcontractingId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($subcontractingId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($subcontractingId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($subcontractingId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($subcontractingId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($subcontractingId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $subcontractingId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($subcontractingId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$subcontractingId / 1000) * 1000;
        
        return self::_getResourcePath('supply_subcontracting' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $subcontractingId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($subcontractingId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$subcontractingId / 1000) * 1000;
        
        return self::_getResourcePath('supply_subcontracting' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $subcontractingId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($subcontractingId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($subcontractingId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($subcontractingId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($subcontractingId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($subcontractingId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $subcontractingId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($subcontractingId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($subcontractingId, $id, $fileName))) {
        	return '';
        }
        
        $base = ceil((int)$subcontractingId / 1000) * 1000;   
        return '/rsrc/supply-subcontracting/' . $subcontractingId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
