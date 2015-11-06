<?php
/**
 * class Shared_Model_Resource_Temporary
 *
 * 画像等の仮保存用リソースマネージャ
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Temporary extends Shared_Model_Resource_Abstract
{
    /**
     * 各素材の拡張子
     * @var string
     */
    const EXTENTION_FOR_IMAGE = 'jpg';

    /**
     * 素材分類
     * @var string
     */
    
    
    
//-------------------------------------------------------------------------------------
    
    /**
     * 画像を保存する
     * @return boolean
     */
    public static function makeResource($fileNameWithExtension, $data)
    {
        // 同じファイル名が存在する場合はいったんメモリ上に確保
        $backup = NULL;

        if (file_exists(self::getResourceObjectPath($fileNameWithExtension))) {
            $backup = self::getBinary($fileNameWithExtension);
        }

        
        // 画像を保存
        if (self::saveResource($fileNameWithExtension, $data) === false) {
        
            // バックアップから復帰
            if (isset($backup)) {
                self::saveResource($fileNameWithExtension, $backup);
            } else {
                self::removeResource($fileNameWithExtension);
            }

            return false;
        }

        return true;
    }
        
//-------------------------------------------------------------------------------------
 
    /**
     * 画像を保存する
     * @return boolean
     */
    public static function saveResource($fileNameWithExtension, $data)
    {
        return self::_saveFile($data, self::getResourceObjectPath($fileNameWithExtension));
    }

    /**
     * 画像を削除する
     * @return boolean
     */
    public static function removeResource($fileNameWithExtension)
    {
        return self::_removeFile(self::getResourceObjectPath($fileNameWithExtension));
    }

    /**
     * 画像を得る
     * @return string
     */
    public static function getBinary($fileNameWithExtension)
    {
        if (file_exists(self::getResourceObjectPath($fileNameWithExtension))) {
            return self::_getFile(self::getResourceObjectPath($fileNameWithExtension)); 
        }
        return NULL;
    }
    
//-------------------------------------------------------------------------------------

    /**
     * 画像のパスを得る
     * @param int $entryId
     * @return string
     */
    public static function getResourceObjectPath($fileNameWithExtension)
    {
        return self::_getPublicPath('resource' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $fileNameWithExtension);
    }
    
    /**
     * キービジュアル画像のダウンロードURLを得る
     * @return string
     */
    public static function getImageUrl($fileNameWithExtension)
    {
        return '/resource/temp/' . $fileNameWithExtension;
    }
    
//-------------------------------------------------------------------------------------

}
