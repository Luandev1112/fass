<?php
/**
 * Nutex_Session_StorageAdapter_Memcached
 *
 * セッション ストレージアダプタ memcached
 *
 * @package Nutex
 * @subpackage Nutex_Session_StorageAdapter
 */
class Nutex_Session_StorageAdapter_Memcached extends Nutex_Session_StorageAdapter_Abstract
{
    /**
     * @var Memcache
     */
    protected $_memcached;

    /**
     * __construct
     *
     * @param string $settings
     * @throws Nutex_Exception_Error
     */
    public function __construct($settings)
    {
        if (extension_loaded('memcached') === false) {
            throw new Nutex_Exception_Error("memcached extention does not loaded");
        }
        if (!is_array($settings)) {
            throw new Nutex_Exception_Error("memcached setting array is required");
        }

        $this->_memcached = new Memcached();
        $this->_memcached->addServer(
            $settings['host'],
            $settings['port'],
            $settings['weight']
        );
    }

    /**
     * read
     *
     * @param void
     * @return array
     * @throws Nutex_Exception_Error
     */
    public function read()
    {
        return $this->_memcached->get($this->getKey());
    }

    /**
     * write
     *
     * @param array $data
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function write(array $data)
    {
        $this->_memcached->set($this->getKey(), $data, $this->getLifetime());
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
        $this->_memcached->delete($this->getKey($id));
    }

    /**
     * gc : garbageCollection
     *
     * @param void
     * @return void
     */
    public function gc()
    {
        //nothing to do
    }

    /**
     * alreadyExists
     *
     * @param string|null $id
     * @return boolean
     */
    public function alreadyExists($id = null)
    {
        return ($this->_memcached->get($this->getKey($id)) !== false);
    }

    /**
     * getKey
     *
     * @param string $id
     * @param string
     */
    public function getKey($id = null)
    {
        if (!is_string($id)) {
            $id = $this->getId();
        }

        return 'session_memcached_' . $id;
    }
}
