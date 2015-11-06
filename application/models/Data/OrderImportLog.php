<?php
/**
 * class Shared_Model_Data_OrderImportLog
 * 注文取込ログ
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_OrderImportLog extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_order_import_log';

    protected $_fields = array(
        'id',                    // ID
		'import_key',            // 取り込みキー
		'import_format_id',      // 取込フォーマットID
		'csv_row',               // CSV行
		'order_id',              // 注文ID(relational_order_id)
		'result',                // 結果
		'message',               // エラーメッセージ
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
     * ログ追加
     * @param string  $importKey
     * @param int     $formatId
     * @param int     $csvRow
     * @param int     $orderId
     * @param string  $result
     * @param string  $message 
     * @return array
     */
    public function addLog($importKey, $formatId, $csvRow, $orderId, $result, $message = NULL)
    {
        $this->create(array(
			'import_key'       => $importKey,
			'status'           => 1,
			'import_format_id' => $formatId,
			'order_id'         => $orderId,
			'csv_row'          => $csvRow,
			'result'           => $result,
			'message'          => $message,
            'created'          => new Zend_Db_Expr('now()'),
            'updated'          => new Zend_Db_Expr('now()'),
        ));
    }

    /**
     * 更新
     * @param int $orderId
     * @param array $columns
     * @return boolean
     */
    public function updateByOrderId($orderId, $columns)
    {
		return $this->update($columns, array('order_id' => $orderId));
    }
    
    /**
     * 取り込みキーで一覧取得
     * @param string $importKey
     * @return array
     */
    public function getItemsByImportKey($importKey)
    {
    	$selectObj = $this->select();
    	$selectObj->joinLeft('frs_order', 'frs_order_import_log.order_id = frs_order.relational_order_id', array('order_customer_name'));
    	$selectObj->where('frs_order_import_log.import_key = ?', $importKey);
    	$selectObj->where('frs_order.status != ?', Shared_Model_Code::SHIPMENT_STATUS_DELETED);
    	$selectObj->where('frs_order_import_log.status = 1');
    	$selectObj->order('frs_order_import_log.csv_row ASC');
    	return $selectObj->query()->fetchAll();
    }

}

