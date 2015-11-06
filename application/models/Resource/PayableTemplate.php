<?php
/**
 * class Shared_Model_Resource_PayableTemplate
 *
 * 毎月支払申請 参考資料ファイルアップロード
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_PayableTemplate extends Shared_Model_Resource_Abstract
{

    
//-------------------------------------------------------------------------------------
    
    /**
     * リソースを保存する
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function makeResource($templateId, $id, $fileName, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backups = array();

        if (file_exists(self::getResourceObjectPath($templateId, $id, $fileName))) {
            $backups[$fileName] = self::getBinary($templateId, $id, $fileName);
        }

        
        // 画像を保存
        if (self::saveResource($templateId, $id, $fileName, $data) === false) {
        
            // バックアップから復帰
            foreach ($types as $fileName) {
                if (isset($backups[$fileName])) {
                    self::saveResource($templateId, $id, $fileName, $backups[$fileName]);
                } else {
                    self::removeResource($templateId, $id, $fileName);
                }
            }
            return false;
        }

        return true;
    }
    

//-------------------------------------------------------------------------------------
 
    /**
     * リソースを保存する
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public static function saveResource($templateId, $id, $fileName, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($templateId, $id, $fileName));
    }

    /**
     * リソースを削除する
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return boolean
     */
    public static function removeResource($templateId, $id, $fileName)
    {
        return self::_removeFile(self::getResourceObjectPath($templateId, $id, $fileName));
    }

    /**
     * リソースを得る
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getBinary($templateId, $id, $fileName)
    {
        if (file_exists(self::getResourceObjectPath($templateId, $id, $fileName))) {
            return self::_getFile(self::getResourceObjectPath($templateId, $id, $fileName)); 
        } else {
            return NULL;
        }
    }
    
//-------------------------------------------------------------------------------------


    /**
     * 保存ディレクトリのパス
     * @param int    $templateId
     * @param int    $id
     * @return string
     */
    public static function getResourceDirectoryPath($templateId, $id)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$templateId / 1000) * 1000;
        
        return self::_getResourcePath('payable_template' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $templateId . DIRECTORY_SEPARATOR . $id);
    }
    
    /**
     * リソースのパス
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceObjectPath($templateId, $id, $fileName)
    {
        //     1 -  1000    1000
        //  1001 -  2000    2000
        // 10001 - 11000   11000
        // 11001 - 12000   12000
        
        $base = ceil((int)$templateId / 1000) * 1000;
        
        return self::_getResourcePath('payable_template' . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . $templateId . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * リソースが存在するか？
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function isExist($templateId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($templateId, $id, $fileName))) {
        	return false;
        }
        
        return true;
    }

    /**
     * ファイルサイズ
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getFileSize($templateId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($templateId, $id, $fileName))) {
        	return 0;
        }
        
        return filesize(self::getResourceObjectPath($templateId, $id, $fileName));
    }
    
    /**
     * リソースURL
     * @param int    $templateId
     * @param int    $id
     * @param string $fileName
     * @return string
     */
    public static function getResourceUrl($templateId, $id, $fileName)
    {
        if (!file_exists(self::getResourceObjectPath($templateId, $id, $fileName))) {
        	return '';
        }

        $base = ceil((int)$templateId / 1000) * 1000;   
        return '/rsrc/payable-template/' . $templateId . '/' . $id . '/' . urlencode($fileName);;
    }
    

}
