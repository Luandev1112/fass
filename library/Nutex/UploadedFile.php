<?php
/**
 * class Nutex_UploadedFile
 *
 * アップロードされたファイルのユーティリティ
 *
 * @todo 配列でファイルが送られた場合
 *
 * @package Nutex
 * @subpackage Nutex_UploadedFile
 */
class Nutex_UploadedFile
{
    /**
     * @var array
     */
    protected static $_src = null;

    /**
     * @var array
     */
    protected static $_files = null;

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var string
     */
    protected $_mimeType = null;

    /**
     * @var string
     */
    protected $_tmpPath = null;

    /**
     * @var int
     */
    protected $_error = null;

    /**
     * @var int
     */
    protected $_size = null;

    /**
     * setSrc
     *
     * @param array $src
     */
    public static function setSrc($src = null)
    {
        if (is_array($src)) {
            self::$_src = $src;
        } else {
            self::$_src = $_FILES;
        }
        self::$_files = null;
    }

    /**
     * getList
     *
     * @return array
     */
    public static function getList()
    {
        if (self::$_files === null) {
            if (self::$_src === null) {
                self::setSrc();
            }
            self::$_files = array();
            if (isset(self::$_src) && is_array(self::$_src)) {
                foreach (self::$_src as $name => $attr) {
                    self::$_files[$name] = new Nutex_UploadedFile($attr);
                }
            }
        }
        return self::$_files;
    }

    /**
     * get
     *
     * @param string $name
     * @return Nutex_UploadedFile
     */
    public static function get($name)
    {
        $files = self::getList();
        if (is_array($files) && array_key_exists($name, $files)) {
            return $files[$name];
        }
        return null;
    }

    /**
     * isUploaded
     *
     * @param string $name
     * @return boolean
     */
    public static function isUploaded($name)
    {
        return array_key_exists($name, self::getList());
    }

    /**
     * constructor
     *
     * @param array $attr
     */
    public function __construct(array $attr)
    {
        if (array_key_exists('name', $attr)) {
            $this->_name = $attr['name'];
        }

        if (array_key_exists('type', $attr)) {
            $this->_mimeType = $attr['type'];
        }

        if (array_key_exists('tmp_name', $attr)) {
            $this->_tmpPath = $attr['tmp_name'];
        }

        if (array_key_exists('error', $attr)) {
            $this->_error = $attr['error'];
        }

        if (array_key_exists('size', $attr)) {
            $this->_size = $attr['size'];
        }
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * getMimeType
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }

    /**
     * getTmpPath
     *
     * @return string
     */
    public function getTmpPath()
    {
        return $this->_tmpPath;
    }

    /**
     * getError
     *
     * @return int
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * getSize
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * getExt
     *
     * @return boolean
     */
    public function getExt()
    {
        return preg_replace('/^[^\.]+\./', '', $this->getName());
    }

    /**
     * isValid
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->exists() && $this->isEmpty() == false && $this->isError() == false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * isEmpty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !($this->_size > 0);
    }

    /**
     * isError
     *
     * @return boolean
     */
    public function isError()
    {
        return ($this->getError() !== UPLOAD_ERR_OK);
    }

    /**
     * isImage
     *
     * @return boolean
     */
    public function isImage()
    {
        if (@getimagesize($this->getTmpPath())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * exists
     *
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->getTmpPath());
    }

    /**
     * extIs
     *
     * @param string|array $directive
     * @param boolean $ignoreCase
     * @return boolean
     */
    public function extIs($directive, $ignoreCase = true)
    {
        if (is_array($directive) == false) {
            $directive = array($directive);
        }

        $ext = $this->getExt();
        if ($ignoreCase) {
            $cp = $directive;
            $directive = array();
            foreach ($cp as $ext) {
                $directive[] = strtolower($ext);
            }
            $ext = strtolower($ext);
        }

        return in_array($ext, $directive);
    }

    /**
     * moveTo
     * ディレクトリのみを指定すると、元のファイル名で移動します
     *
     * @param string $dst
     * @return boolean
     */
    public function moveTo($dst)
    {
        if (is_dir($dst)) {
            $dst = preg_replace('@' . preg_quote(DIRECTORY_SEPARATOR) . '$@', '', $dst) . DIRECTORY_SEPARATOR . $this->getName();
        }

        if ($this->isValid() && copy($this->getTmpPath(), $dst)) {
            return true;
        } else {
            return false;
        }
    }
}
