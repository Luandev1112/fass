<?php
/**
 * class Shared_Model_Data_ConnectionStaff
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ConnectionStaff extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection_staff';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'connection_id',                       // 取引先ID
        'connection_department_id',            // 取引先部署ID
		'status',                              // ステータス
		
		'card_exchange_date',                  // 名刺交換日
		'mail_flag',                           // DM用出力フラグ
		
		'staff_name',                          // 担当者 名前
		'staff_name_kana',                     // 担当者 名前カナ
		'staff_department',                    // 担当者 部署
		'staff_position',                      // 担当者 役職
		'staff_tel',                           // 担当者 電話番号
		'staff_fax',                           // 担当者 FAX番号
		'staff_mobile',                        // 担当者 携帯
		'staff_mail',                          // 担当者 メールアドレス
		'staff_postal_code',                   // 担当者 郵便番号
		'staff_address',                       // 担当者 住所
		'staff_memo',                          // 担当者 メモ
		
		'order_no',                            // 並び順
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時

    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'staff_name',                          // 担当者 名前
		'staff_name_kana',                     // 担当者 名前カナ
		'staff_department',                    // 担当者 部署
		'staff_position',                      // 担当者 役職
		'staff_tel',                           // 担当者 電話番号
		'staff_fax',                           // 担当者 FAX番号
		'staff_mobile',                        // 担当者 携帯電話
		'staff_mail',                          // 担当者 メールアドレス
		'staff_postal_code',                   // 担当者 郵便番号
		'staff_address',                       // 担当者 住所
		'staff_memo',                          // 担当者 メモ
    );

    /**
     * IDで取得
     * @param int $managementGroupId
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
     * 取引先ID＋部署IDで取得
     * @param int $connectionId
     * @param int $departmentId
     * @return array
     */
    public function getListByConnectionIdAndDepartmentId($connectionId, $departmentId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->where('connection_department_id = ?', $departmentId);
    	$selectObj->order('created DESC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 担当者 部署名および名前で検索
     * @param int    $connectionId
     * @param int    $departmentId
     * @param string $staffName
     * @return array
     */
    public function findByDepartmentStaffName($connectionId, $departmentId, $staffName)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->where('connection_department_id = ?', $departmentId);
    	$selectObj->where($this->aesdecrypt('staff_name', false) . ' = ?', $staffName);
		return $selectObj->query()->fetch();
    }
    
    /**
     * 担当者 名前で検索
     * @param int $connectionId
     * @param string $staffName
     * @return array
     */
    public function findByStaffName($connectionId, $staffName)
    {
    	$selectObj = $this->select();
    	$selectObj->where('connection_id = ?', $connectionId);
    	$selectObj->where($this->aesdecrypt('staff_name', false) . ' = ?', $staffName);

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
     * 次の並び順番号
     * @param int   $connectionId
     * @param int   $departmentId
     * @return array
     */
    public function getNextOrderNo($connectionId, $departmentId)
    {
        $selectObj = $this->select();
        $selectObj->where('connection_id = ?', $connectionId);
        $selectObj->where('connection_department_id = ?', $departmentId);
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

