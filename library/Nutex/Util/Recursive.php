<?php
/**
 * class Nutex_Util_Recursive
 *
 * ディレクトリ再帰処理ユーティリティ
 *
 * @package Nutex
 * @subpackage Nutex_Util
 */
class Nutex_Util_Recursive
{
    const FLAG_IGNORE_DOTS = 'ignoreDots';
    const FLAG_REVERSE = 'reverse';

    const NAME_FROM = 'nameFrom';
    const NAME_TO = 'nameTo';

    const TARGET_TYPE = 'targetType';
    const TARGET_FILENAME_PATTERN = 'targetFilenamePattern';

    const TYPE_FILE = 'file';
    const TYPE_DIR = 'dir';

    const PATHS = '__paths';

    protected static $_paths = array();

    /**
     * 再帰的にパス取得
     * @param string $src
     * @param string $type
     * @param string $filenamePattern
     * @return array
     */
    public static function getPaths($src, $type = null, $filenamePattern = null)
    {
        self::$_paths = array();

        if (is_dir($src) === false) {
            return self::$_paths;
        }

        $iterator = new DirectoryIterator($src);

        $params = array(
            self::FLAG_IGNORE_DOTS => true,
        );
        if (is_string($type)) {
            $params[self::TARGET_TYPE] = $type;
        }
        if (is_string($filenamePattern)) {
            $params[self::TARGET_FILENAME_PATTERN] = $filenamePattern;
        }

        self::_recursive($iterator, null, array('Nutex_Util_recursive', '_getPath'), $params);
        return self::$_paths;
    }

    /**
     * 再帰コピー
     * @param string $src
     * @param string $dst
     * @param string $type
     * @param string $filenamePattern
     * @return boolean
     */
    public static function copy($src, $dst, $type = null, $filenamePattern = null)
    {
        if (is_dir($src) === false) {
            return false;
        }

        $iterator = new DirectoryIterator($src);
        if (is_dir($dst) === false) {
            if (mkdir($dst, 0755, true) === false) {
                return false;
            }
        }

        $params = array(
            self::FLAG_IGNORE_DOTS => true,
        );
        if (is_string($type)) {
            $params[self::TARGET_TYPE] = $type;
        }
        if (is_string($filenamePattern)) {
            $params[self::TARGET_FILENAME_PATTERN] = $filenamePattern;
        }

        return self::_recursive($iterator, $dst, array('Nutex_Util_recursive', '_copy'), $params);
    }

    /**
     * 再帰移動
     * @param string $src
     * @param string $dst
     * @param boolean $preserve
     * @param string $type
     * @param string $filenamePattern
     * @return boolean
     */
    public static function move($src, $dst, $preserve = true, $type = null, $filenamePattern = null)
    {
        $result = self::copy($src, $dst, $preserve, $type, $filenamePattern) && self::remove($src, $type, $filenamePattern);
        if ($result && is_dir($src)) {
            return rmdir($src);
        } else {
            return $result;
        }
    }

    /**
     * 再帰リネーム
     * @param string $src
     * @param string $renameFrom
     * @param string $renameTo
     * @param string $type
     * @param string $filenamePattern
     * @return boolean
     */
    public static function rename($src, $renameFrom, $renameTo, $type = null, $filenamePattern = null)
    {
        if (is_dir($target) === false) {
            return false;
        }

        $params = array(
            self::FLAG_IGNORE_DOTS => true,
            self::NAME_FROM => $renameFrom,
            self::NAME_TO => $renameTo,
        );
        if (is_string($type)) {
            $params[self::TARGET_TYPE] = $type;
        }
        if (is_string($filenamePattern)) {
            $params[self::TARGET_FILENAME_PATTERN] = $filenamePattern;
        }

        return self::_recursive(new DirectoryIterator($target), $target, array('Nutex_Util_recursive', '_rename'), $params);
    }

    /**
     * 再帰置換
     * @param string $target
     * @param string $replaceFrom
     * @param string $replaceTo
     * @param string $type
     * @param string $filenamePattern
     * @return boolean
     */
    public static function rewrite($target, $replaceFrom, $replaceTo, $type = null, $filenamePattern = null)
    {
        if (is_dir($target) === false) {
            return false;
        }

        $params = array(
            self::FLAG_IGNORE_DOTS => true,
            self::NAME_FROM => $replaceFrom,
            self::NAME_TO => $replaceTo,
        );
        if (is_string($type)) {
            $params[self::TARGET_TYPE] = $type;
        }
        if (is_string($filenamePattern)) {
            $params[self::TARGET_FILENAME_PATTERN] = $filenamePattern;
        }

        return self::_recursive(new DirectoryIterator($target), $target, array('Nutex_Util_recursive', '_rewrite'), $params);
    }

    /**
     * 再帰削除
     * @param string $target
     * @param string $type
     * @param string $filenamePattern
     * @return boolean
     */
    public static function remove($target, $type = null, $filenamePattern = null)
    {
        if (is_dir($target) === false) {
            return false;
        }

        $params = array(
            self::FLAG_REVERSE => true,
        );
        if (is_string($type)) {
            $params[self::TARGET_TYPE] = $type;
        }
        if (is_string($filenamePattern)) {
            $params[self::TARGET_FILENAME_PATTERN] = $filenamePattern;
        }

        if (isset($params[self::TARGET_TYPE])) {
            return self::_recursive(new DirectoryIterator($target), $target, array('Nutex_Util_recursive', '_remove'), $params);
        } else {
            $iterator = new DirectoryIterator($target);
            $params4file = $params;
            $params4dir = $params;
            $params4file[self::TARGET_TYPE] = self::TYPE_FILE;
            $params4dir[self::TARGET_TYPE] = self::TYPE_DIR;
            return self::_recursive($iterator, $target, array('Nutex_Util_recursive', '_remove'), $params4file) && self::_recursive($iterator, $target, array('Nutex_Util_recursive', '_remove'), $params4dir);
        }
    }

    /**
     * 再帰処理
     * @param DirectoryIterator|string $src
     * @param string $dst
     * @param callback $callback
     * @param array $params
     * @return boolean
     */
    protected static function _recursive($src, $dst, $callback, &$params)
    {
        if ($src instanceof DirectoryIterator) {
            $iterator = $src;
        } elseif (is_dir($src)) {
            $iterator = new DirectoryIterator($src);
        } else {
            return false;
        }

        foreach ($iterator as $file) {
            if ($file->isDot()) {
                continue;
            }

            if (isset($params[self::FLAG_IGNORE_DOTS]) && $params[self::FLAG_IGNORE_DOTS]) {
                if (strpos($file->getFilename(), '.') === 0) {
                    continue;
                }
            }

            $execution = true;
            if (isset($params[self::TARGET_TYPE]) && $params[self::TARGET_TYPE] === self::TYPE_DIR && $file->isFile()) {
                $execution = false;
            }
            if (isset($params[self::TARGET_TYPE]) && $params[self::TARGET_TYPE] === self::TYPE_FILE && $file->isDir()) {
                $execution = false;
            }
            if (isset($params[self::TARGET_FILENAME_PATTERN]) && mb_ereg_match($params[self::TARGET_FILENAME_PATTERN], $file->getFilename()) === false) {
                $execution = false;
            }

            $dstPath = ($dst) ? $dst . DIRECTORY_SEPARATOR . $file->getFilename() : null;
            $args = ($dstPath) ? array($file, $dstPath, $params) : array($file, $params);

            if (isset($params[self::FLAG_REVERSE]) && $params[self::FLAG_REVERSE]) {
                if ($file->isDir()) {
                    if (self::_recursive(new DirectoryIterator($file->getPathname()), $dstPath, $callback, $params) === false) {
                        return false;
                    }
                }

                if ($execution && call_user_func_array($callback, $args) === false) {
                    return false;
                }
            } else {
                if ($execution && call_user_func_array($callback, $args) === false) {
                    return false;
                }

                if ($file->isDir()) {
                    if (self::_recursive(new DirectoryIterator($file->getPathname()), $dstPath, $callback, $params) === false) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * パス取得
     * @param DirectoryIterator $iterator
     * @param array $params
     * @return boolean
     */
    protected static function _getPath(DirectoryIterator $file, $params)
    {
        self::$_paths[] = $file->getPathname();
    }

    /**
     * コピー
     * @param DirectoryIterator $iterator
     * @param string $dstPath
     * @param array $params
     * @return boolean
     */
    protected static function _copy(DirectoryIterator $file, $dstPath, $params)
    {
        if ($file->isDir()) {
            if (mkdir($dstPath) === false) {
                return false;
            }
        } else {
            if (copy($file->getPathname(), $dstPath) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * リネーム
     * @param DirectoryIterator $iterator
     * @param string $dstPath
     * @param array $params
     * @return boolean
     */
    protected static function _rename(DirectoryIterator $file, $dstPath, $params)
    {
        $dstPath = $file->getDirname() . DIRECTORY_SEPARATOR .  mb_ereg_replace($params[self::NAME_FROM], $params[self::NAME_TO], $file->getFilename());
        return rename($file->getPathname(), $dstPath);
    }

    /**
     * 置換処理 正規表現にはmb系を使用
     * @param DirectoryIterator $iterator
     * @param string $dstPath
     * @param array $params
     * @return boolean
     */
    protected static function _rewrite(DirectoryIterator $file, $dstPath, $params)
    {
        if ($file->isFile()) {
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                return false;
            }

            if (file_put_contents($dstPath, mb_ereg_replace($params[self::NAME_FROM], $params[self::NAME_TO], $content)) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 削除
     * @param DirectoryIterator $iterator
     * @param string $dstPath
     * @param array $params
     * @return boolean
     */
    protected static function _remove(DirectoryIterator $file, $dstPath, $params)
    {
        if ($file->isFile()) {
            return unlink($file->getPathname());
        }

        if ($file->isDir()) {
            return rmdir($file->getPathname());
        }

        return true;
    }
}
