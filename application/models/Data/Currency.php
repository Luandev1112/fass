<?php
/**
 * class Shared_Model_Data_Currency
 * 通貨単位
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Currency extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_currency';

    protected $_fields = array(
        'id',                    // ID
        'name',                  // ISO 4217 通貨コード
        'symbol',                // 記号
        
        'general_name',          // 日本語一般名称
        
		'content_order',         // 並び順
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
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
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 通貨コードで取得
     * @param int $managementGroupId
     * @param int $name
     * @return array
     */
    public function getByName($managementGroupId, $name)
    {
    	$selectObj = $this->select();
    	$selectObj->where('name = ?', $name);
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 記号で取得
     * @param int $managementGroupId
     * @param int $symbol
     * @return array
     */
    public function getBySymbol($managementGroupId, $symbol)
    {
    	$selectObj = $this->select();
    	$selectObj->where('symbol = ?', $symbol);
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 一覧
     * @param int $managementGroupId
     * @return boolean
     */
    public function getList($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 次の並び順
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getNextContentOrder($managementGroupId)
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
    }
    
    /**
     * 更新
     * @param int $managementGroupId
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($managementGroupId, $id, $columns)
    {
		return $this->update($columns, array('management_group_id' => $managementGroupId, 'id' => $id));
    }
}

