<?php
/**
 * class Shared_Model_Resource_SupplyCompetition
 *
 * 調達管理 コンペ 入手見積書・補足資料
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_SupplyCompetition extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($competitionId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($competitionId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($competitionId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($competitionId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($competitionId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($competitionId, $id, $fileName);
                }
            }
            return false;
        }


        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($competitionId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($competitionId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($competitionId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($competitionId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($competitionId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($competitionId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($competitionId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $competitionId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($competitionId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$competitionId / 1000) * 1000;
        
        return self::_getResourcePath('supply_competition' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $competitionId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($competitionId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$competitionId / 1000) * 1000;
        
        return self::_getResourcePath('supply_competition' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $competitionId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($competitionId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($competitionId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($competitionId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($competitionId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($competitionId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $competitionId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($competitionId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($competitionId, $id, $fileName))) {
        	return '';
        }
        
        $base = ceil((int)$competitionId / 1000) * 1000;   
        return '/rsrc/supply-competition/' . $competitionId . '/' . $id . '/' . urlencode($fileName);
    }
    

}
