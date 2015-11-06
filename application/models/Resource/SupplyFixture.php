<?php
/**
 * class Shared_Model_Resource_SupplyFixture
 *
 * 調達管理 備品資材 入手見積書・補足資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_SupplyFixture extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($fixtureId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($fixtureId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($fixtureId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($fixtureId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($fixtureId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($fixtureId, $id, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($fixtureId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($fixtureId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($fixtureId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($fixtureId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($fixtureId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($fixtureId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($fixtureId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $fixtureId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($fixtureId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$fixtureId / 1000) * 1000;
        
        return self::_getResourcePath('supply_fixture' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $fixtureId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($fixtureId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$fixtureId / 1000) * 1000;
        
        return self::_getResourcePath('supply_fixture' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $fixtureId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($fixtureId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($fixtureId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($fixtureId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($fixtureId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($fixtureId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $fixtureId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($fixtureId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($fixtureId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$fixtureId / 1000) * 1000;   
        return '/rsrc/supply-fixture/' . $fixtureId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
