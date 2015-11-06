<?php
/**
 * interface Nutex_Session_StorageAdapter_Abstract
 *
 * セッション ストレージアダプタ抽象クラス
 *
 * @todo データのロックに関しては具象クラスに丸投げしています。
 *
 * @package Nutex
 * @subpackage Nutex_Session_StorageAdapter
 *
 * inspired
 * @see Zend_Session_SavaHandler_Interface
 */
abstract class Nutex_Session_StorageAdapter_Abstract implements IteratorAggregate, Countable
{
    /**
     * セッションを一意に識別するID
     * @var string
     */
    protected $_id;

    /**
     * データバッファ - openしてからcloseするまでのデータのやりとりは全てここのみで行う
     * @var array|null
     */
    protected $_data = null;

    /**
     * @var boolean
     */
    protected $_opened = false;

    /**
     * @var int
     */
    protected $_lifetime;

    /**
     * open
     *
     * @param string $id セッションを一意に識別するID
     * @return void
     */
    public function open($id)
    {
        $this->setId($id);
        if ($this->alreadyExists($id)) {
            $this->_data = $this->read();
        } else {
            $this->_data = array();
        }
        $this->_opened = true;
    }

    /**
     * close
     *
     * @param void
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function close()
    {
        if (!$this->isOpened()) {
            throw new Nutex_Exception_Error('session is not opened');
        }
        $this->write($this->_data);
        $this->_data = null;
        $this->_opened = false;
    }

    /**
     * read
     * 何かしらのリソースからのデータ読み込み
     *
     * @param void
     * @return array
     */
    abstract public function read();

    /**
     * write
     * 何かしらのリソースへのデータ書き込み
     *
     * @param array $data
     * @return void
     */
    abstract public function write(array $data);

    /**
     * destroy
     *
     * @param string $id セッションを一意に識別するID
     * @return void
     */
    abstract public function destroy($id = null);

    /**
     * gc : garbageCollection
     *
     * @param void
     * @return void
     */
    abstract public function gc();

    /**
     * alreadyExists
     *
     * @param string|null $id
     * @return boolean
     */
    abstract public function alreadyExists($id = null);

    /**
     * swapId
     *
     * @param string $newId
     * @return void
     */
    public function swapId($newId)
    {
        $data = $this->get();
        $this->destroy();
        $this->open($newId);
        $this->setAll($data);
    }

    /**
     * get
     *
     * @param string $name
     * @param mixed $dafault
     * @return mixed
     * @throws Nutex_Exception_Error
     */
    public function get($name = null, $dafault = null)
    {
        if (!$this->isOpened()) {
            throw new Nutex_Exception_Error('session is not opened');
        }

        if (is_null($name)) {
            return $this->_data;
        } elseif (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }

        return $dafault;
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function set($name, $value)
    {
        if (!$this->isOpened()) {
            throw new Nutex_Exception_Error('session is not opened');
        }
        $this->_data[$name] = $value;
    }

    /**
     * setAll
     *
     * @param string $name
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function setAll(array $data)
    {
        if (!$this->isOpened()) {
            throw new Nutex_Exception_Error('session is not opened');
        }
        $this->_data = $data;
    }

    /**
     * getId
     *
     * @param void
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * setId
     *
     * @param string
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = (string)$id;
        return $this;
    }

    /**
     * getLifetime
     *
     * @param void
     * @return int
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }

    /**
     * setLifetime
     *
     * @param int
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->_lifetime = (int) $lifetime;
        return $this;
    }

    /**
     * isOpened
     *
     * @param void
     * @return string
     */
    public function isOpened()
    {
        return $this->_opened;
    }

    /**
     * __get
     *
     * @param string $name
     * @return mixed
     * @see http://www.php.net/manual/ja/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * __set
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @see http://www.php.net/manual/ja/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * __isset
     *
     * @param string $name
     * @return boolean
     * @see http://www.php.net/manual/ja/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    /**
     * __unset
     *
     * @param string $name
     * @return void
     * @see http://www.php.net/manual/ja/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }

    /**
     * getIterator
     *
     * @param void
     * @return array
     * @see http://www.php.net/manual/ja/class.iteratoraggregate.php
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    /**
     * count
     *
     * @param void
     * @return int
     * @see http://www.php.net/manual/ja/class.countable.php
     */
    public function count()
    {
        return count($this->_data);
    }
}
