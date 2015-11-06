<?php
/**
 * class Nutex_Validate_DuplicateDbRow
 *
 * DBデータ行単位の重複バリデータ
 * 調べたい領域にアクセスできるNutex_Data_Db_Abstractモデルを必要とします
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_DuplicateDbRow extends Nutex_Validate_Abstract_DuplicateData
{
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::DUPLICATING => "'%value%' は既に登録されています",
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        $condition = array();
        $condition[] = array($this->getDataModel()->getAdapter()->quoteIdentifier($this->getIdentifier()) . ' = ?', $value);
        foreach ($this->getConditions() as $key => $value) {
            if (preg_match('/\?/', $key)) {
                $condition[] = array($key, $value);
            } elseif (is_string($key)) {
                $condition[$key] = $value;
            } else {
                $condition[] = $value;
            }
        }

        if ($this->getDataModel()->find($condition)) {
            $this->_error(self::DUPLICATING);
            return false;
        }

        return true;
    }

    /**
     * setDataModel
     *
     * @param string $className
     * @return $this
     * @throws Nutex_Exception_Error
     */
    public function setDataModel($className)
    {
        parent::setDataModel($className);
        if (!$this->getDataModel() instanceof Nutex_Data_Db_Abstract) {
            throw new Nutex_Exception_Error('invalid data model');
        }
        return $this;
    }
}
