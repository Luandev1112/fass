<?php
/**
 * class Shared_Model_Data_ConnectionDepartment
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionDepartment extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection_department';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'connection_id',                       // 取引先ID
        
        'display_id',                          // 部門コード
		'status',                              // ステータス
		'department_name',                     // 部門名
		
		'order_no',                            // 並び順
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'department_name',                     // 部門名
    );

    /**
     * IDで取得
     * @param int $id
     * @return array
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 取引先IDで取得
     * @param int $connectionId
     * @return array
     */
    public function getListByConnectionId($connectionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->order('order_no ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 担当者 部署名で検索
     * @param int $connectionId
     * @param string $departmentName
     * @return array
     */
    public function findByDepartmentName($connectionId, $departmentName)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->where($this->aesdecrypt('department_name', false) . ' = ?', $departmentName);
		return $selectObj->query()->fetch();
    }

    /**
     * 存在するか
     * @param int   $id
     * @return array
     */
    public function isExist($id)
    {
        $selectObj = $this->select();
        $selectObj->where('id = ?', $id);
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
        	return true;
        }
        
        return false;
    }

    /**
     * 次の枝番
     * @param int $connectionId
     * @return array
     */
    public function getNextDisplayId($connectionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->order('id DESC');
    	$data = $selectObj->query()->fetch();

		if (!empty($data)) {
			$lastCount = (int)$data['display_id'];

			if ($lastCount === 999) {
				throw new Zend_Exception('display_id id is over-flowed');
			}
			
			return sprintf('%03d', $lastCount + 1);
		}
		
		return '001';
    }
    
    /**
     * 次の並び順番号
     * @param int   $connectionId
     * @return array
     */
    public function getNextOrderNo($connectionId)
    {
        $selectObj = $this->select();
        $selectObj->where('connection_id = ?', $connectionId);
        $selectObj->order('order_no DESC');
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
        	return (int)$data['order_no'] + 1;
        }
        
        return 1;
    }
    
    /**
     * 更新
     * @param int   $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }
    
}

