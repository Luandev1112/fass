<?php
/**
 * class Shared_Model_Data_OrderItem
 * 注文商品
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_OrderItem extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_order_item';

    protected $_fields = array(
        'id',                    // ID
		'order_id',              // 注文ID
		'branch_no',             // 枝番号(複数の注文の場合にインクリメント)
		
		'status',                // ステータス
		
		'product_code',          // 商品コード
		'product_name',          // 商品名
		
		'unit_price',            // 単価
		'amount',                // 数量
		
		'item_tax_rate',         // 商品税率
		'item_total',            // 商品小計(税抜)
		'item_total_with_tax',   // 商品小計(税込)
        
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
     * 注文IDで取得
     * @param int $orderId
     * @return array
     */
    public function getListByOrderId($orderId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('order_id = ?', $orderId);
    	$selectObj->where('status = ?', Shared_Model_Code::ORDER_ITEM_STATUS_ACTIVE);
    	$selectObj->order('id ASC');
        return $selectObj->query()->fetchAll();
    }


    /**
     * 月間 注文件数取得
     * @param int $warehouseId
     * @return array
     */
    public function getOrderItemCountWithTerm($warehouseId, $termFrom, $termTo)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`frs_order_item`.`id`) as item_count')
    	));
    	$selectObj->joinLeft('frs_order', 'frs_order_item.order_id = frs_order.id', array());
    	$selectObj->where('frs_order.warehouse_id = ?', $warehouseId);
    	$selectObj->where('frs_order.status = ?', Shared_Model_Code::SHIPMENT_STATUS_SHIPPED);
    	$selectObj->where('frs_order.shipment_datetime >= ?', $termFrom . ' 00:00:00');
    	$selectObj->where('frs_order.shipment_datetime <= ?', $termTo . ' 23:59:59');
		//var_dump($selectObj->__toString());
		//exit;
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    
    
    /**
     * 次の枝番号
     * @param int $orderId
     * @return int $branchNo
     */
    public function getNextBranchNo($orderId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('order_id = ?', $orderId);
    	$selectObj->where('status = ?', Shared_Model_Code::ORDER_ITEM_STATUS_ACTIVE);
    	$selectObj->order('branch_no DESC');
        $data = $selectObj->query()->fetch();
        
        if (empty($data)) {
        	return 1;
        }
        
        return (int)$data['branch_no'] + 1;
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

