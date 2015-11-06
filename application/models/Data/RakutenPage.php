<?php
/**
 * class Shared_Model_Data_RakutenPage
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_RakutenPage extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_rakuten_page';

    protected $_fields = array(
        'id',                    // ID
		'status',
		'customer_id',
		'title',
		'pc_product_text',
		'sp_product_text',
		'pc_sales_text',
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
        
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'title',
		'pc_product_text',
		'sp_product_text',
		'pc_sales_text',
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

