<?php
/**
 * Nutex_Session_StorageAdapter_File
 *
 * セッション ストレージアダプタ ファイル
 *
 * @package Nutex
 * @subpackage Nutex_Session_StorageAdapter
 */
class Nutex_Session_StorageAdapter_File extends Nutex_Session_StorageAdapter_Abstract
{
    /**
     * セッションファイルディレクトリ
     * @var string
     */
    protected $_dir;

    /**
     * __construct
     *
     * @param string $dir
     * @throws Nutex_Exception_Error
     */
    public function __construct($dir)
    {
        $dir = preg_replace('@' . preg_quote(DIRECTORY_SEPARATOR) . '$@', '', $dir);

        if (!is_dir($dir)) {
            throw new Nutex_Exception_Error("session dir '$dir' is not directory");
        }
        if (!is_readable($dir) || !is_writable($dir)) {
            throw new Nutex_Exception_Error("session dir '$dir' is not readable or not writable");
        }

        $this->_dir = $dir;
    }

    /**
     * read
     * ファイルからのデータ読み込み
     *
     * @todo ロックしなくて大丈夫？
     *
     * @param void
     * @return array
     * @throws Nutex_Exception_Error
     */
    public function read()
    {
        if (!is_readable($this->getFilePath())) {
            throw new Nutex_Exception_Error('cannot read session file');
        }
        return unserialize(base64_decode(file_get_contents($this->getFilePath())));
    }

    /**
     * write
     * ファイルへのデータ書き込み
     *
     * @todo ロックのやりかた大丈夫？
     *
     * @param array $data
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function write(array $data)
    {
        if (file_exists($this->getFilePath()) && !is_writable($this->getFilePath())) {
            throw new Nutex_Exception_Error('cannot write session file');
        }
        file_put_contents($this->getFilePath(), base64_encode(serialize($data)), LOCK_EX);
    }

    /**
     * destroy
     *
     * @param string $id
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function destroy($id = null)
    {
        $path = $this->getFilePath($id);
        if (file_exists($path) && is_writable($path)) {
            unlink($path);
        }
    }

    /**
     * gc : garbageCollection
     *
     * @param void
     * @return void
     */
    public function gc()
    {
        $maxTimestamp = Nutex_Date::getDefaultTimestamp() - $this->getLifetime();
        $iterator = new DirectoryIterator($this->getDir());
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getATime() < $maxTimestamp) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * alreadyExists
     *
     * @param string|null $id
     * @return boolean
     */
    public function alreadyExists($id = null)
    {
        return file_exists($this->getFilePath($id));
    }

    /**
     * getDir
     *
     * @param void
     * @param string
     */
    public function getDir()
    {
        return $this->_dir;
    }

    /**
     * getFilePath
     *
     * @param string $id
     * @param string
     */
    public function getFilePath($id = null)
    {
        if (!is_string($id)) {
            $id = $this->getId();
        }

        //windowsはファイル名の大文字小文字を区別しないためハッシュにする
        //return $this->getDir() . DIRECTORY_SEPARATOR . hash('sha256', $id);
        return $this->getDir() . DIRECTORY_SEPARATOR . $id;
    }
}
