<?php
/**
 * class Shared_Model_Data_OrderDeliveryCodeImportLog
 * 注文配送伝票番号取込ログ
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_OrderDeliveryCodeImportLog extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_order_delivery_code_import_log';

    protected $_fields = array(
        'id',                    // ID
		'import_key',            // 取り込みキー
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
     * @param int     $orderId
     * @param int     $rowCount
     * @param string  $message
     * @param string  $result
     * @return array
     */
    public function addLog($importKey, $orderId, $rowCount, $result, $message = NULL)
    {
        $this->create(array(
			'import_key'       => $importKey,
			'order_id'         => $orderId,
			'csv_row'          => $rowCount,
			'result'           => $result,
			'message'          => $message,
            'created'          => new Zend_Db_Expr('now()'),
            'updated'          => new Zend_Db_Expr('now()'),
        ));
    }
    
    /**
     * 取り込みキーで一覧取得
     * @param string $importKey
     * @return array
     */
    public function getItemsByImportKey($importKey)
    {
    	$selectObj = $this->select();
    	$selectObj->where('import_key = ?', $importKey);
    	$selectObj->where('frs_order_delivery_code_import_log.import_key = ?', $importKey);
    	$selectObj->order('frs_order_delivery_code_import_log.csv_row ASC');
    	return $selectObj->query()->fetchAll();
    }

}

