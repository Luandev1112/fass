<?php
/**
 * class Shared_Model_Data_SystemLog
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_SystemLog extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_system_log';

    protected $_fields = array(
        'id',                // ID
		'target',            // 対象
		'params',            // パラメーターなど
		'message',           // エラー本文
		
        'created',           // レコード作成日時
        'updated',           // レコード更新日時  
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'params',            // パラメーターなど
		'message',           // エラー本文
    );

    /**
     * addLog
     * @param  string $target
     * @param  string $params
     * @param  string $message 
     * @return boolean
     */
    public function addLog($target, $params, $message)
    {
		$this->create(array(
	        'target'     => $target,
	        'params'     => $params,
	        'message'    => $message,
		));
    }

    /**
     * IDで取得
     * @param int $id
     * @return boolean
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	
    	return $selectObj->query()->fetch();
    }
    

}
