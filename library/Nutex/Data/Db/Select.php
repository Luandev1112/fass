<?php
/**
 * class Nutex_Data_Db_Select
 *
 * Zend_Db_Select拡張
 *
 * @package Nutex
 */
class Nutex_Data_Db_Select extends Zend_Db_Select
{
    public function query($fetchMode = null, $bind = array())
    {
    
        if (!empty($bind)) {
            $this->bind($bind);
        }

        $stmt = $this->_adapter->query($this);
        if ($fetchMode == null) {
            $fetchMode = $this->_adapter->getFetchMode();
        }
        $stmt->setFetchMode($fetchMode);
        
        return $stmt;
    }
    
}