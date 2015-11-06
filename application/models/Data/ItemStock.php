<?php
/**
 * class Shared_Model_Data_ItemStock
 * アイテム在庫
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemStock extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_stock';

    protected $_fields = array(
        'id',                    // ID
        
        'item_id',               // アイテムID     （廃止予定）
        'warehouse_item_id',     // 倉庫管理アイテムID
        
        'user_id',               // 担当者ID
		'status',                // ステータス
		
		'warehouse_manage_id',   // 入庫管理ID
		'lot_count',             // ロットカウント
		
		'action_date',           // アクション日
		'action_code',           // アクションコード
		
		'expiration_date',       // 消費期限
		
		'amount',                // 入庫数
		'sub_count',             // マイナス数量
		'last_count',            // 残り数量（入庫データのみ）
		
		'warehouse_id',          // 入庫倉庫ID
		
		'order_id',              // 注文ID（出荷のみ）
		'memo',                  // 備考（破棄理由など）
		
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
     * @return boolean
     */
    public function getById($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $id);
    	return $selectObj->query()->fetch();
    }

    /**
     * 次のID
     * @param none
     * @return int $nextId
     */
    public function getNextId()
    {
    	$selectObj = $this->select();
    	$selectObj->order('id DESC');
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {
    		return $data['id'];
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

    /** 
     * 対象アイテムの履歴取得
     * @param  int      $itemId
     * @param  boolean  $isSelectObj
     * @return array or selectObj
     */
    public function getActiveList($itemId, $isSelectObj = false)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_item_id = ?', $itemId);
    	$selectObj->where('status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
        
        if ($isSelectObj) {
            return $selectObj;
        }
        
        $selectObj->order('id ASC');
        return $selectObj->query()->fetchAll();
    }



    /** 
     * 消費用在庫データ取得(先出し)
     * @param  int      $itemId
     * @return array or selectObj
     */
    public function findFirstStock($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('warehouse_item_id = ?', $itemId);
    	$selectObj->where('status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
    	$selectObj->where('last_count > 0');
    	$selectObj->order('action_date ASC');
		return $selectObj->query()->fetch();
    }
    
    /** 
     * 在庫消費
     * @param  int      $id
     * @param  boolean  $isSelectObj
     * @return array or selectObj
     */
    public function consumeStock($id, $subCount)
    {
    	$data = $this->getById($id);
    	
    	if (empty($data)) {
    		// 対象在庫データなし
    		throw new Zend_Exception('Shared_Model_Data_ItemStock - no target stock data');
    		
    	} else if ((float)$data['last_count'] < (float)$subCount) {
    		// 在庫数より少ない
    		throw new Zend_Exception('Shared_Model_Data_ItemStock - last_count is not enough');
    		
    	}
    	
    	$this->updateById($id, array(
    		'last_count' => $data['last_count'] - $subCount,
    	));
    }


}

