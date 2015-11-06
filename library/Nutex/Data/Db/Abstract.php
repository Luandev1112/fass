<?php
/**
 * class Nutex_Data_Db_Abstract
 *
 * データ DB 抽象クラス
 *
 * @package Nutex
 * @subpackage Nutex_Data
 */
abstract class Nutex_Data_Db_Abstract extends Nutex_Data_Abstract
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * メインとなるテーブル名
     * @var string
     */
    protected $_tableName;

    /**
     * __construct
     *
     * @param Zend_Db_Adapter_Abstract|null $adapter
     * @return void
     */
    public function __construct($adapter = null)
    {
        if ($adapter instanceof Zend_Db_Adapter_Abstract) {
            $this->setAdapter($adapter);
        } else {
        $adaptor = Zend_Db_Table::getDefaultAdapter();
            $this->setAdapter(Zend_Db_Table::getDefaultAdapter());
        }
    }

    /**
     * パラメータ加工用フック
     *
     * @params array $params
     * @return void
     */
    public function onSetParams(&$params)
    {
        //override me
    }

    /**
     * create
     *
     * @param array $input
     * @return boolean
     */
    public function create($input)
    {
        $this->onSetParams($input);
		
		$adapter = $this->getAdapter();

		// aesencryptに変更 2018.02.12 Miyano.M
        if ($this->cryptFieldExists()) {
            $input = $this->aesencrypt($input);
        }
			
        if ($this->getAdapter()->insert($this->getTableName(), $input) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * read
     *
     * @param mixed $condition
     * @param array $options
     * @return mixed
     */
    public function read($condition = null, array $options = array())
    {
        $select = $this->select();
        $conds = $this->_makeConditionString($condition, true);
        if ($conds) {
            if (is_array($conds)) {
                foreach ($conds as $cond) {
                    $select->where($cond);
                }
            } else {
                $select->where($conds);
            }
        }

        $limit = (array_key_exists('limit', $options)) ? $options['limit'] : null;
        $offset = (array_key_exists('offset', $options)) ? $options['offset'] : 0;
        if (is_int($limit) || is_int($offset)) {
            $select->limit($limit, $offset);
        }

        $order = (array_key_exists('order', $options)) ? $options['order'] : null;
        if (is_string($order)) {
            $select->order($order);
        } elseif (is_array($order)) {
            foreach ($order as $ord) {
                $select->order($ord);
            }
        }
		
		/*
        if ($this->cryptFieldExists()) {
            $rows = array();
            foreach ($select->query()->fetchAll() as $row) {
                $rows[] = $this->decrypt($row);
            }
            return $rows;
        } else {
            return $select->query()->fetchAll();
        }
        */
        
        return $select->query()->fetchAll();
        
    }
    

    /**
     * find
     *
     * @param mixed $condition
     * @return mixed
     */
    public function find($condition)
    {
        $args = func_get_args();
        if (count($args) == 2) {
            $condition = array($args[0], $args[1]);
        }
        $result = $this->read($condition, array('limit' => 1));
        return array_shift($result);
    }
    

    /**
     * select
     *
     * @param array|string $columns
     * @return Zend_Db_Select
     */
    public function select($columns = null)
    {
        if (is_null($columns)) {
            $columns = $this->getFields();
        }
        if (empty($columns)) {
            $columns = '*';
        }
        $select = new Nutex_Data_Db_Select($this->getAdapter());

        // aesdescrypt追加 2018.02.12 Miyano.M
        if ($this->cryptFieldExists()) {
            $columns = $this->aesdecrypt($columns, true);
        }
        
        // aesdescryptキーの場合にテーブル名を入れる(joinした際にキーがかぶるため) 2018.11.02 Miyano.M
        foreach ($columns as &$each) {
        	if (strpos($each,'AES_DECRYPT') !== false){
        		$each = str_replace('AES_DECRYPT(', 'AES_DECRYPT(' . $this->_tableName . '.', $each);
        	}
        }
        
        $select->from($this->getTableName(), $columns);
        return $select;
    }

    /**
     * update
     *
     * @param array $input
     * @param mixed $condition
     * @return boolean
     */
    public function update($input, $condition)
    {
        $this->onSetParams($input);

		// aesencryptに変更 2018.02.12 Miyano.M
        if ($this->cryptFieldExists()) {
            $input = $this->aesencrypt($input);
        }

        if ($this->getAdapter()->update($this->getTableName(), $input, $this->_makeConditionString($condition)) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * delete
     *
     * @param mixed $condition
     * @return boolean
     */
    public function delete($condition)
    {
        if ($this->getAdapter()->delete($this->getTableName(), $this->_makeConditionString($condition)) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * _makeConditionString
     *
     * @param mixed $condition
     * @param boolean $returnArray
     * @return string
     */
    public function _makeConditionString($condition, $returnArray = false)
    {
        $conditionString = '';
        if (is_array($condition)) {
            $conditionString = $this->joinConditionArray($condition, $returnArray);
        } elseif (is_string($condition)) {
            $conditionString = $condition;
        } else {
            $conditionString = null;
        }
        return $conditionString;
    }

    /**
     * joinConditionArray
     *
     * @param mixed $conditions
     * @param boolean $returnArray
     * @return string
     */
    public function joinConditionArray(array $conditions, $returnArray = false)
    {
        if (count($conditions) == 2
            && array_key_exists(0, $conditions) && array_key_exists(1, $conditions)
            && !is_array($conditions[0]) && !is_array($conditions[1])
        ) {
            $conditions = array($conditions);
        }

        $conditionStrings = array();
        foreach ($conditions as $key => $value) {
            $cond = $this->_makeCondition($key, $value);
            if (is_string($cond)) {
                $conditionStrings[] = $cond;
            }
        }

        return ($returnArray) ? $conditionStrings : implode(' AND ' , $conditionStrings);
    }

    /**
     * _makeCondition
     *
     * @param mixed $key
     * @param mixed $value
     * @return string $condition
     */
    protected function _makeCondition($key, $value)
    {
        if ((!is_int($key) && !is_string($key))) {
            return null;
        }
        if (!is_string($key) && is_string($value)) {
            return $value;
        }

        $adapter = $this->getAdapter();
        $statement = null;
        $field = null;

        if (!is_string($key) && is_array($value)) {
            $statement = array_shift($value);
            $field = preg_replace('/\s.*$/', '', ltrim($statement));
            $value = array_shift($value);
        } else {
            $statement = $adapter->quoteIdentifier($key) . ' = ? ';
            $field = $key;
            $value = $value;
        }

        if ($this->isCryptField($field)) {
            $value = $this->encrypt($value);
        }

        return $adapter->quoteInto($statement, $value);
    }

    /**
     * getTableName
     *
     * @param void
     * @return string
     */
    public function getTableName()
    {
        return $this->_tableName;
    }

    /**
     * getFields
     *
     * @param void
     * @return string
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * getAdapter
     *
     * @param void
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * getLastInsertedId
     *
     * @param string $keyName
     * @return string
     */
    public function getLastInsertedId($keyName)
    {
        return $this->getAdapter()->lastInsertId($this->getTableName(), $keyName);
    }

    /**
     * setAdapter
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     * @return $this
     */
    public function setAdapter(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }
}