<?php
/**
 * class Shared_Model_Data_IndustryType
 * 業種
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_IndustryType extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_industry_type';

    protected $_fields = array(
        'id',                    // ID
        
        'industry_category_id',  // 業種カテゴリID
        'name',                  // 業種名
		'content_order',         // 並び順
        
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'name',
    );
    
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

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getListByCategoryId($categoryId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('industry_category_id = ?', $categoryId);
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getAllList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('industry_category_id ASC');
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getAllListWithCategory()
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_industry_category', 'frs_industry_type.industry_category_id = frs_industry_category.id', array($this->aesdecrypt('name', false) . 'AS category_name'));
    	$selectObj->order('industry_category_id ASC');
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
    }


    /**
     * 次の並び順
     * @param int $categoryId
     * @return array
     */
    public function getNextContentOrder($categoryId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('industry_category_id = ?', $categoryId);
    	$selectObj->order('content_order DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return (int)$data['content_order'] + 1;
    	}
    	return 1;
    }
    
    /**
     * カテゴリ名が登録されているか？
     * @param string $name
     * @param int    $exceptId
     * @return array
     */
    public function isExistName($name, $exceptId)
    {
    	$selectObj = $this->select();
    	
    	$dbAdapter = $this->getAdapter();
    	$titleWhere = $dbAdapter->quoteInto($this->aesdecrypt('name', false) . ' = ?', $name);
    	$selectObj->where($titleWhere);
    	
    	if (!empty($exceptId)) {
    		$selectObj->where('id != ?', $exceptId);
    	}
    	
    	$data = $selectObj->query()->fetch();
		
		if (!empty($data)) {
			return true;
		}
		
		return false;
    }
    
    /**
     * 更新
     * @param int $id
     * @param array $columns
     * @return boolean
     */
    public function updateById($id, $columns)
    {
		return $this->update($columns, array('id' => $id));
    }

}

