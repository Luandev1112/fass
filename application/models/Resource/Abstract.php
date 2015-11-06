<?php
/**
 * class Shared_Model_Resource_Abstract
 *
 * リソース管理クラス base
 *
 * @package Shared
 * @subpackage Shared_Model_Resource
 */
class Shared_Model_Resource_Abstract
{

    /**
     * ダミー画像を得る
     * @return string
     */
    public static function getDummyImage()
    {
        return self::_getFile(self::_getResourcePath('dummy.gif'));
    }
    
    
    /**
     * ファイルの内容を得る
     * @param string $path
     * @param string|null
     */
    protected static function _getFile($path)
    {
        /*
         * @todo サーバ複数台対応
         */
        if (APPLICATION_ENV === 'production') {
		    if (is_readable($path)) {
            	return file_get_contents($path);
            } else {
                return null;
            }
			
		} else {
            if (is_readable($path)) {
                return file_get_contents($path);
            } else {
                return null;
            }
        }
    }
    
    
    /**
     * ファイルを保存する
     * @param string $data
     * @param string $path
     */
    protected static function _saveFile($data, $path)
    {
        /*
         * @todo サーバ複数台対応
         */
        if (APPLICATION_ENV === 'production') {
            if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0755, true)) {
                return null;
            }
            return file_put_contents($path, $data);
        } else {
            if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0755, true)) {
                return null;
            }
            return file_put_contents($path, $data);
        }
    }


    /**
     * ファイルを削除する
     * @param string $path
     */
    protected static function _removeFile($path)
    {
        /*
         * @todo サーバ複数台対応
         */
        if (APPLICATION_ENV === 'production') {
            return unlink($path);
        } else {
            return unlink($path);
        }
    }


    /**
     * @param string $userId
     * @param array $entryIds
     */
    public static function removeAll($userId, $entryIds = array())
    {
        /*
         * @todo サーバ複数台対応
        */
        if (APPLICATION_ENV === 'production') {
            $paths = array();
            $paths[] = self::_getBloggerImagePath($userId);
            $paths[] = self::_getUserImagePath($userId);
            foreach ($entryIds as $entryId) {
                $paths[] = self::_getMovieThumbPath((isset($entryId['id'])) ? $entryId['id'] : $entryId);
            }
            foreach ($paths as $path) {
                @rmdir($path);
            }
        } else {
            $paths = array();
            $paths[] = self::_getBloggerImagePath($userId);
            $paths[] = self::_getUserImagePath($userId);
            foreach ($entryIds as $entryId) {
                $paths[] = self::_getMovieThumbPath((isset($entryId['id'])) ? $entryId['id'] : $entryId);
            }
            foreach ($paths as $path) {
                @rmdir($path);
            }
        }
    }

    /**
     * リソースディレクトリのフルパスを得る
     * @param string $location
     * @return string
     */
    protected static function _getResourcePath($location)
    {
        return RESOURCE_PATH . DIRECTORY_SEPARATOR . $location;
    }
    
    /**
     * 公開ディレクトリディレクトリのフルパスを得る
     * @param string $location
     * @return string
     */
    protected static function _getPublicPath($location)
    {
        return PUBLIC_PATH . DIRECTORY_SEPARATOR . $location;
    }
    	
    

}
