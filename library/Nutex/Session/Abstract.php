<?php
/**
 * class Nutex_Session_Abstract
 *
 * セッション抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Session
 */
abstract class Nutex_Session_Abstract implements IteratorAggregate, Countable
{
    /**
     * option keys
     * @var string
     */
    const OPTION_ID_NAME = 'idName';
    const OPTION_HASH_SALT = 'hashSalt';
    const OPTION_HASH_FUNCTION = 'hashFunction';
    const OPTION_SESSION_LIFETIME = 'sessionLifetime';

    /**
     * default values
     * @var string
     */
    const DEFAULT_ID_NAME = 'session';
    const DEFAULT_HASH_SALT = '=-NuTex_SeSsIoN-=';
    const DEFAULT_HASH_FUNCTION = 'sha512';
    const DEFAULT_SESSION_LIFETIME = 86400;

    /**
     * @var Nutex_Session_StorageAdapter_Abstract
     */
    protected $_storageAdapter;

    /**
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_options = array();

    /**
     * flags
     * @var boolean
     */
    protected $_destructed = false;
    protected $_shutdowned = false;

    /**
     * @var Nutex_BaseConvert_Convert
     */
    protected $_converter = null;

    /**
     * inRequest
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param array $options
     * @return boolean
     */
    public static function inRequest(Zend_Controller_Request_Abstract $request, array $options = array())
    {
        $name = (isset($options[self::OPTION_ID_NAME])) ? $options[self::OPTION_ID_NAME] : self::DEFAULT_ID_NAME;
        if ($request->getParam($name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * __construct
     *
     * @param Nutex_Session_StorageAdapter_Abstract $dataAdapter
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $options
     * @return void
     */
    public function __construct(
        Nutex_Session_StorageAdapter_Abstract $dataAdapter,
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        $options = array()
    )
    {
        $this->_storageAdapter = $dataAdapter;
        $this->_request = $request;
        $this->_response = $response;
        if (!is_array($options)) {
            $options = array();
        }
        $this->_options = $options;

        //session setup
        $this->setup($this->settleId());
    }

    /**
     * __destruct
     *
     * @param void
     * @return void
     */
    public function __destruct()
    {
        if (!$this->isShutdowned()) {
            $this->shutdown();
        }
    }

    /**
     * setup
     *
     * @param mixed $id
     * @return void
     */
    public function setup($id)
    {
        $this->getStorageAdapter()->setLifetime($this->getOption(self::OPTION_SESSION_LIFETIME, self::DEFAULT_SESSION_LIFETIME));

        //garbage collection
        $this->getStorageAdapter()->gc();

        //session open
        $this->getStorageAdapter()->open($id);

        //session sustain
        $this->sustain();

        $this->_destructed = false;
        $this->_shutdowned = false;
    }

    /**
     * sustain
     * セッションを維持するために必要なアクションを継承先で記述
     *
     * @param void
     * @return void
     */
    public function sustain()
    {
        //nothing
    }

    /**
     * shutdown
     *
     * @param void
     * @return void
     */
    public function shutdown()
    {
        if (!$this->isDestructed()) {
            $this->getStorageAdapter()->close();
        }
        $this->_shutdowned = true;
    }

    /**
     * destroy
     *
     * @param void
     * @return void
     */
    public function destroy()
    {
        $this->getStorageAdapter()->destroy();
        $this->_destructed = true;
    }

    /**
     * settleId
     *
     * @param void
     * @return string
     */
    public function settleId()
    {
        $id = $this->getIdFromRequest();
        if (!$id || !$this->alreadyExists($id)) {
            $id = $this->publishNewId();
        }
        return $id;
    }

    /**
     * getIdFromRequest
     *
     * @param void
     */
    abstract public function getIdFromRequest();

    /**
     * publishNewId
     *
     * @param void
     */
    abstract public function publishNewId();

    /**
     * regenerateId
     *
     * @param void
     */
    public function regenerateId()
    {
        $this->getStorageAdapter()->swapId($this->publishNewId());

        //session sustain
        $this->sustain();
    }

    /**
     * getOption
     *
     * @param string $name
     * @param mixed $dafault
     * @return mixed
     */
    public function getOption($name = null, $dafault = null)
    {
        if (is_null($name)) {
            return $this->_options;
        } elseif (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }

        return $dafault;
    }

    /**
     * get
     *
     * @param string $name
     * @param mixed $dafault
     * @return mixed
     */
    public function get($name = null, $dafault = null)
    {
        return $this->getStorageAdapter()->get($name, $dafault);
    }

    /**
     * set
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        return $this->getStorageAdapter()->set($name, $value);
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
        return $this->getStorageAdapter()->setAll($data);
    }

    /**
     * isOpened
     *
     * @param void
     * @return boolean
     */
    public function isOpened()
    {
        return $this->getStorageAdapter()->isOpened();
    }

    /**
     * isDestructed
     *
     * @param void
     * @return boolean
     */
    public function isDestructed()
    {
        return $this->_destructed;
    }

    /**
     * isShutdowned
     *
     * @param void
     * @return boolean
     */
    public function isShutdowned()
    {
        return $this->_shutdowned;
    }

    /**
     * alreadyExists
     *
     * @param string|null $id
     * @return boolean
     */
    public function alreadyExists($id = null)
    {
        return $this->getStorageAdapter()->alreadyExists($id);
    }

    /**
     * getId
     *
     * @param void
     * @return string
     */
    public function getId()
    {
        return $this->getStorageAdapter()->getId();
    }

    /**
     * getStorageAdapter
     *
     * @param void
     * @return Nutex_Session_StorageAdapter_Abstract
     */
    public function getStorageAdapter()
    {
        return $this->_storageAdapter;
    }

    /**
     * getRequest
     *
     * @param void
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * getResponse
     *
     * @param void
     * @return Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * getConverter
     *
     * @param void
     * @return Nutex_BaseConvert_Convert
     */
    public function getConverter()
    {
        if ($this->_converter === null) {
            $this->_converter = new Nutex_BaseConvert_Convert(array(
                'baseChars' => 'URL',
                'baseCharsFrom' => 16,
            ));
        }
        return $this->_converter;
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
     * @return mixed
     * @see http://www.php.net/manual/ja/language.oop5.overloading.php#language.oop5.overloading.members
     */
    public function __isset($name)
    {
        return $this->getStorageAdapter()->__isset($name);
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
        $this->getStorageAdapter()->__unset($name);
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
        return $this->getStorageAdapter()->getIterator();
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
        return $this->getStorageAdapter()->count();
    }
}
