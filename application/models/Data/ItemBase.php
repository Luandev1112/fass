<?php
/**
 * class Shared_Model_Data_ItemBase
 * アイテム
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemBase extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_base';

    protected $_fields = array(
        'id',                    // ID		
		'base_id',               // 拠点ID
		'item_id',               // 商品ID
		'shelf_no',              // 棚番号(4文字以下)
        
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
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * IDで取得
     * @param int   $baseId
     * @param int   $itemId
     * @return array
     */
    public function getByItemId($baseId, $itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('base_id = ?', $baseId);
    	$selectObj->where('item_id = ?', $itemId);
    	return $selectObj->query()->fetch();
    }
    
    /**
     * 更新
     * @param int   $baseId
     * @param int   $itemId
     * @param array $columns
     * @return boolean
     */
    public function updateByItemId($baseId, $itemId, $columns)
    {
    	$data = $this->getByItemId($baseId, $itemId);
    	
    	if (!empty($data)) {
    		$this->update($columns, array('base_id' => $baseId, 'item_id' => $itemId));
    		
    	} else {
			$data = array(
				'base_id'       => $baseId,
				'item_id'       => $itemId,				
                'created'       => new Zend_Db_Expr('now()'),
                'updated'       => new Zend_Db_Expr('now()'),
			);
			
			foreach ($columns as $key => $each) {
				$data[$key] = $each;
			}

			$this->create($data);
    	}
    }

}

