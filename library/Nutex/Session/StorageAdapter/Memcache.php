<?php
/**
 * Nutex_Session_StorageAdapter_Memcache
 *
 * セッション ストレージアダプタ memcache
 *
 * @package Nutex
 * @subpackage Nutex_Session_StorageAdapter
 */
class Nutex_Session_StorageAdapter_Memcache extends Nutex_Session_StorageAdapter_Abstract
{
    /**
     * @var Memcache
     */
    protected $_memcache;

    /**
     * __construct
     *
     * @param string $settings
     * @throws Nutex_Exception_Error
     */
    public function __construct($settings)
    {
        if (extension_loaded('memcache') === false) {
            throw new Nutex_Exception_Error("memcache extention does not loaded");
        }
        if (!is_array($settings)) {
            throw new Nutex_Exception_Error("memcache setting array is required");
        }

        $this->_memcache = new Memcache();
        $this->_memcache->addServer(
            $settings['host'],
            $settings['port'],
            $settings['persistent'],
            $settings['weight'],
            $settings['timeout'],
            $settings['retry_interval']
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
        return $this->_memcache->get($this->getKey());
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
        $this->_memcache->set($this->getKey(), $data, 0, $this->getLifetime());
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
        $this->_memcache->delete($this->getKey($id));
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
        return ($this->_memcache->get($this->getKey($id)) !== false);
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

        return 'session_memcache_' . $id;
    }
}
