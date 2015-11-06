<?php
/**
 * class Shared_Model_Data_DbAbstract
 *
 * @package Shared
 * @subpackage Shared_Model
 * @version $Id:
 */
abstract class Shared_Model_Data_DbAbstract extends Nutex_Data_Db_Abstract
{
    const VERSION = 'version';

    /**
     * パラメータ加工用フック
     *
     * @params array $params
     * @return void
     */
    public function onSetParams(&$params)
    {
        if (isset($params['mail']) && in_array('mail_hash', $this->getFields())) {
            $params['mail_hash'] = $this->mailHash($params['mail']);
        }
    }

    /**
     * @see Nutex_Data_Db_Abstract::create()
     */
    public function create($input)
    {
        return parent::create($this->addNowDateToParams($input, array('created', 'updated')));
    }

    /**
     * @see Nutex_Data_Db_Abstract::update()
     */
    public function update($input, $condition)
    {
        if (in_array(self::VERSION, $this->getFields())) {
            throw new Nutex_Exception_Error('please use updateWithVersion()');
        }

        return parent::update($this->addNowDateToParams($input, array('updated')), $condition);
    }

    /**
     * updateWithVersion
     *
     * @param array $input
     * @param mixed $condition
     * @param int $version
     * @return boolean
     */
    public function updateWithVersion($input, $condition, $version)
    {
        if (!in_array(self::VERSION, $this->getFields())) {
            throw new Nutex_Exception_Error('please use update()');
        }

        //楽観ロック用バージョン条件追加、バージョン番号をインクリメント
        $condition[] = array(self::VERSION . ' = ?', $version);
        $input[self::VERSION] = new Zend_Db_Expr(self::VERSION . ' + 1');

        return parent::update($this->addNowDateToParams($input, array('updated')), $condition);
    }

    /**
     * @see Nutex_Data_Db_Abstract::delete()
     */
    public function delete($condition)
    {
        if (in_array(self::VERSION, $this->getFields())) {
            throw new Nutex_Exception_Error('please use deleteWithVersion()');
        }

        return parent::delete($condition);
    }

    /**
     * deleteWithVersion
     *
     * @param mixed $condition
     * @param int $version
     * @return boolean
     */
    public function deleteWithVersion($condition, $version)
    {
        if (!in_array(self::VERSION, $this->getFields())) {
            throw new Nutex_Exception_Error('please use delete()');
        }

        //楽観ロック用バージョン条件追加
        $condition[] = array(self::VERSION . ' = ?', $version);

        return parent::delete($condition);
    }

    /**
     * versionを無視して、指定のinclimentを更新する
     * @param array $condition
     * @param String $columnName
     * @param int $num
     * @return boolean
     */
    public function updateIncrement($condition, $columnName, $num)
    {
        $input[$columnName] = new Zend_Db_Expr("$columnName+($num)");
        if ($this->getAdapter()->update($this->getTableName(), $input, $this->_makeConditionString($condition)) > 0) {
            return true;
        } else {
            return false;
        }
    }
}