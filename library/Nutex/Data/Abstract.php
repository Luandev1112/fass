<?php
/**
 * class Nutex_Data_Abstract
 *
 * データ抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Data
 */
abstract class Nutex_Data_Abstract
{
    /**
     * Zend_Filter_Interface
     * @var array
     */
    protected static $_filters = array();

    /**
     * @var string
     */
    protected $_dateFormat = 'yyyy-MM-dd HH:mm:ss';

    /**
     * フィールド群
     * @var array
     */
    protected $_fields = array();

    /**
     * 暗号/復号化するフィールド名群
     * @var array
     */
    protected $_cryptFields = array();

    /**
     * create
     *
     * @param array $input
     * @return boolean
     */
    abstract public function create($input);

    /**
     * read
     *
     * @param mixed $condition
     * @param array $options
     * @return mixed
     */
    abstract public function read($condition = null, array $options = array());

    /**
     * find
     *
     * @param mixed $condition
     * @return mixed
     */
    abstract public function find($condition);

    /**
     * update
     *
     * @param array $input
     * @param mixed $condition
     * @return boolean
     */
    abstract public function update($input, $condition);

    /**
     * delete
     *
     * @param mixed $condition
     * @return boolean
     */
    abstract public function delete($condition);

    /**
     * encrypt
     *
     * @param array $value
     * @return array $value
     */
    public function encrypt($value)
    {
        $filter = $this->getFilter('Nutex_Filter_Encrypt');
        if (is_array($value)) {
            foreach (array_keys($value) as $key) {
                if ($this->isCryptField($key)) {
                    $value[$key] = $filter->filter($value[$key]);
                }
            }
        } else {
            $value = $filter->filter($value);
        }
        
        return $value;
    }

    /**
     * decrypt
     *
     * @param array $value
     * @return array $value
     */
    public function decrypt($value)
    {
        $filter = $this->getFilter('Nutex_Filter_Decrypt');
        if (is_array($value)) {
            foreach (array_keys($value) as $key) {
                if ($this->isCryptField($key)) {
                    $value[$key] = $filter->filter($value[$key]);
                }
            }
        } else {
            $value = $filter->filter($value);
        }
        return $value;
    }


    /**
     * aesencrypt    追加 2018.02.12 Miyano.M
     *
     * @param array $value
     * @return array $value
     */
    public function aesencrypt($value)
    {
        $filter = $this->getFilter('Nutex_Filter_AesEncrypt');
        if (is_array($value)) {
            foreach (array_keys($value) as $key) {
                if ($this->isCryptField($key)) {
                	// 2018/10/09 M.Miyano json用にバックスラッシュ変換
                    $value[$key] = $filter->filter(str_replace("'", "\'", str_replace('\\', '\\\\', $value[$key])));
                    
                }
            }
        } else {
            $value = $filter->filter($value);
        }
        
        return $value;
    }

    /**
     * aesdecrypt    追加 2018.02.12 Miyano.M
     *
     * @param array $value
     * @param bool  $as
     * @return array $value
     */
    public function aesdecrypt($value, $as)
    {
        $filter = null;
        
        if ($as === true) {
        	$filter = $this->getFilter('Nutex_Filter_AesDecryptAs');
        } else {
        	$filter = $this->getFilter('Nutex_Filter_AesDecrypt');
        }
        
        if (is_array($value)) {
            foreach ($value as &$val) {
                if ($this->isCryptField($val)) {
                    $val = $filter->filter($val);
                }
            }
        } else {
            $value = $filter->filter($value);
        }
        
        //var_dump($value);exit;
        return $value;
    }
    
    /**
     * isCryptField
     *
     * @param string $field
     * @return boolean
     */
    public function isCryptField($field)
    {
        return in_array($field, $this->_cryptFields);
    }

    /**
     * cryptFieldExists
     *
     * @param void
     * @return boolean
     */
    public function cryptFieldExists()
    {
        if (count($this->_cryptFields) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * setCryptFields
     *
     * @param array $cryptFields
     * @return $this
     */
    public function setCryptFields(array $cryptFields)
    {
        $this->_cryptFields = $cryptFields;
        return $this;
    }

    /**
     * resetCryptFields
     *
     * @param void
     * @return $this
     */
    public function resetCryptFields()
    {
        $this->_cryptFields = array();
        return $this;
    }

    /**
     * addCryptField
     *
     * @param string $field
     * @return $this
     */
    public function addCryptField($field)
    {
        $this->_cryptFields[] = $field;
        return $this;
    }

    /**
     * removeCryptField
     *
     * @param string $field
     * @return $this
     */
    public function removeCryptField($field)
    {
        $key = array_search($field, $this->_cryptFields);
        if ($key !== false) {
            unset($this->_cryptFields[$key]);
        }
        return $this;
    }

    /**
     * addFilter
     *
     * @param string|object $class
     * @return provides a fluent interface
     */
    public function addFilter($class)
    {
        $className = (is_object($class)) ? get_class($class) : $class;
        self::$_filters[$className] = $class;
        return $this;
    }

    /**
     * getFilter
     *
     * @param string $className
     * @param array $options
     * @return Zend_Filter_Interface
     */
    public function getFilter($className, $options = array())
    {
        if (array_key_exists($className, self::$_filters) == false) {
            self::$_filters[$className] = $className;
        }

        if (is_object(self::$_filters[$className]) == false) {
            self::$_filters[$className] = new $className();
        }

        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists(self::$_filters[$className], $setter)) {
                self::$_filters[$className]->$setter($value);
            }
        }

        return self::$_filters[$className];
    }

    /**
     * addNowDateToParams
     *
     * @param array $params
     * @param array $cols
     * @return array $params
     */
    public function addNowDateToParams(array $params, array $cols)
    {
        if (is_string($this->_dateFormat)) {
            $date = Nutex_Date::get($this->_dateFormat);
        } else {
            $date = Nutex_Date::getDefaultInstance()->getZendDate();
        }

        foreach ($cols as $col) {
            if (in_array($col, $this->getFields())) {
                $params[$col] = $date;
            }
        }

        return $params;
    }
}
