<?php
/**
 * class Nutex_Validate_Abstract_DuplicateData
 *
 * データ重複バリデータ抽象クラス
 * 調べたい領域にアクセスできるNutex_Data_Abstractモデルを必要とします
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
abstract class Nutex_Validate_Abstract_DuplicateData extends Zend_Validate_Abstract
{
    /**
     * @var string
     */
    const DUPLICATING = 'duplicating';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::DUPLICATING => "'%value%' は既に登録されています",
    );

    /**
     * @var Nutex_Data_Abstract
     */
    protected $_dataModel = null;

    /**
     * カラム名など
     * @var string
     */
    protected $_identifier = null;

    /**
     * 追加条件
     * @var array
     */
    protected $_conditions = array();

    /**
     * __construct
     *
     * @param array|Zend_Config $options
     * @return void
     * @throws Nutex_Exception_Error
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            throw new Nutex_Exception_Error('invalid options');
        }
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
        if (!$this->getDataModel()) {
            throw new Nutex_Exception_Error('data model is required');
        }
    }

    /**
     * getDataModel
     *
     * @param void
     * @return Nutex_Data_Abstract
     */
    public function getDataModel()
    {
        return $this->_dataModel;
    }

    /**
     * setDataModel
     *
     * @param Nutex_Data_Abstract|string $model
     * @return $this
     * @throws Nutex_Exception_Error
     */
    public function setDataModel($model)
    {
        if ($model instanceof Nutex_Data_Abstract) {
            $this->_dataModel = $model;
        } else {
            if (@class_exists($model) == false) {
                throw new Nutex_Exception_Error('data model class does not exist');
            }
            $this->_dataModel = new $model;
            if (!$this->_dataModel instanceof Nutex_Data_Abstract) {
                throw new Nutex_Exception_Error('invalid data model');
            }
        }

        return $this;
    }

    /**
     * getIdentifier
     *
     * @param void
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * setIdentifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->_identifier = (string)$identifier;
        return $this;
    }

    /**
     * getConditions
     *
     * @param void
     * @return array
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     * setConditions
     *
     * @param array $conditions
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $this->_conditions = $conditions;
        return $this;
    }

    /**
     * addConditions
     *
     * @param mixed $condition
     * @return $this
     */
    public function addConditions($condition)
    {
        $this->_conditions[] = $condition;
        return $this;
    }
}
