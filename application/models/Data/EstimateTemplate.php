<?php
/**
 * class Shared_Model_Data_EstimateTemplate
 * 基本情報
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_EstimateTemplate extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_estimate_template';

    protected $_fields = array(
        'id',                                  // ID
        'title',                               // テンプレート名
        'default_labels',                      // デフォルトラベル
		'content_order',                       // 並び順
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',                               // テンプレート名
		'default_labels',                      // デフォルトラベル
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
    	$data = $selectObj->query()->fetch();
    	$data['default_labels'] = json_decode($data['default_labels'], true);
    	return $data;
    }

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getList()
    {
    	$selectObj = $this->select();
    	$selectObj->order('content_order ASC');
    	return $selectObj->query()->fetchAll();
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

