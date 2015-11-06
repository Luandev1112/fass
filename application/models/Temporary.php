<?php
/**
 * class Shared_Model_Temporary
 *
 * 一時ディレクトリマネージャ
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Temporary
{
    /**
     * 一時ファイルを保存する
     * @param string $data
     * @param string $ext
     * @return string|null $key 一時ファイルを示すキー
     */
    public static function save($data, $ext = null)
    {
        $key = self::publishKey($ext);
        $path = self::getPath($key);
        if ($path && file_put_contents($path, $data)) {
            return $key;
        } else {
            return null;
        }
    }

    /**
     * 一時ファイルからデータを取得する
     * @param string $key 一時ファイルを示すキー
     * @return string|null
     */
    public static function load($key)
    {
        $path = self::getPath($key);
        if ($path && is_readable($path)) {
            return file_get_contents($path);
        } else {
            return null;
        }
    }

    /**
     * 一時ファイルからデータを取得する
     * @param string $key 一時ファイルを示すキー
     * @return string|null
     */
    public static function remove($key)
    {
        $path = self::getPath($key);
        if ($path) {
            return unlink($path);
        } else {
            return null;
        }
    }

    /**
     * 一時ファイルのキーを発行
     * @param string $ext
     * @return string|null
     */
    public static function publishKey($ext = null)
    {
        return date('Ymd')  . '_' . hash('sha256', mt_rand() . '_' . uniqid()) . '.' . $ext;
    }

    /**
     * 一時ファイルパスを取得
     * @param string $key 一時ファイルを示すキー
     * @param string $ext
     * @return string|null
     */
    public static function getPath($key = null, $ext = null)
    {
        if ($key === null) {
            $key = self::publishKey($ext);
        }

        $path = TEMPORARY_PATH . DIRECTORY_SEPARATOR . self::_retrieveDirnameFromKey($key) . DIRECTORY_SEPARATOR . $key;
        if (is_dir(dirname($path)) || mkdir(dirname($path), 0755, true)) {
            return $path;
        } else {
            return null;
        }
    }

    /**
     * 過去のファイルを消す
     * @param int $days
     */
    public static function garbageCollection($days = 7)
    {
        $target = (int) date('Ymd', mktime((int) date('H'), (int) date('i'), (int) date('s'), (int) date('m'), ((int) date('d') - $days), (int) date('Y')));
        foreach (new DirectoryIterator(TEMPORARY_PATH) as $file) {
            if ((int) $file->getFilename() <= $target) {
                Nutex_Util_Recursive::remove($file->getPathname());
                rmdir($file->getPathname());
            }
        }
    }

    /**
     * キーからディレクトリ名を得る
     * @param string $key
     */
    protected static function _retrieveDirnameFromKey($key)
    {
        return preg_replace('/_.*$/', '', $key);
    }
}