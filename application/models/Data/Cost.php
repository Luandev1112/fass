<?php
/**
 * class Shared_Model_Data_Cost
 * 原価計算
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Cost extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_cost';

    protected $_fields = array(
        'id',                    // ID
        'management_group_id',   // 管理グループID
        'item_id',               // 対象商品ID
        
        'version_id',            // バージョンID
		'version_status',        // バージョンステータス
		
		'memo_profit',           // 販売価格／想定利益率メモ
		'memo_manufacture',      // 製品原価メモ
		'memo_shipping',         // 発送費用メモ
		
		'column_name_1',         // 項目名
		'column_name_2',
		'column_name_3',
		'column_name_4',
		'column_name_5',
		'column_name_6',
		'column_name_7',
		'column_name_8',
		'column_name_9',
		'column_name_10',
		'column_name_11',
		'column_name_12',
		'column_name_13',
		'column_name_14',
		'column_name_15',
			
		'sales_price',                   // 販売価格

	
		'customer_delivery_cost_1',      // 顧客送料総負担額
		'customer_delivery_cost_2',
		'customer_delivery_cost_3',
		'customer_delivery_cost_4',
		'customer_delivery_cost_5',
		'customer_delivery_cost_6',
		'customer_delivery_cost_7',
		'customer_delivery_cost_8',
		'customer_delivery_cost_9',
		'customer_delivery_cost_10',
		'customer_delivery_cost_11',
		'customer_delivery_cost_12',
		'customer_delivery_cost_13',
		'customer_delivery_cost_14',
		'customer_delivery_cost_15',

		'tax_percentage_1',              // 日本消費税・関税・現地課税
		'tax_percentage_2',
		'tax_percentage_3',
		'tax_percentage_4',
		'tax_percentage_5',
		'tax_percentage_6',
		'tax_percentage_7',
		'tax_percentage_8',
		'tax_percentage_9',
		'tax_percentage_10',
		'tax_percentage_11',
		'tax_percentage_12',
		'tax_percentage_13',
		'tax_percentage_14',
		'tax_percentage_15',
		
		'discount_percentage_1',         // 値引率
		'discount_percentage_2',
		'discount_percentage_3',
		'discount_percentage_4',
		'discount_percentage_5',
		'discount_percentage_6',
		'discount_percentage_7',
		'discount_percentage_8',
		'discount_percentage_9',
		'discount_percentage_10',
		'discount_percentage_11',
		'discount_percentage_12',
		'discount_percentage_13',
		'discount_percentage_14',
		'discount_percentage_15',
		
		'overseas_percentage_1',         // 海外上代割増率
		'overseas_percentage_2',
		'overseas_percentage_3',
		'overseas_percentage_4',
		'overseas_percentage_5',
		'overseas_percentage_6',
		'overseas_percentage_7',
		'overseas_percentage_8',
		'overseas_percentage_9',
		'overseas_percentage_10',
		'overseas_percentage_11',
		'overseas_percentage_12',
		'overseas_percentage_13',
		'overseas_percentage_14',
		'overseas_percentage_15',
		
		// 製造コスト
		'summary_manufacture_total_cost', // 製造原価
		'summary_material_cost',          // A. 原料・製品調達費
		'summary_package_cost',           // B. 資材費
		'summary_expendable_cost',        // C. 消耗品費
		'summary_processing_cost',        // D. 加工費用

		'cost_material_list',             // 原料・製品調達費
		'cost_package_list',              // 資材費
		'cost_expendable_list',           // 消耗品費
		'cost_processing_list',           // 加工費用
		
		'memo',                           // 備考
		
		// 輸送梱包費
		'amount_per_package_1',           // 1件当たりの輸送個数
		'amount_per_package_2',
		'amount_per_package_3',
		'amount_per_package_4',
		'amount_per_package_5',
		'amount_per_package_6',
		'amount_per_package_7',
		'amount_per_package_8',
		'amount_per_package_9',
		'amount_per_package_10',
		'amount_per_package_11',
		'amount_per_package_12',
		'amount_per_package_13',
		'amount_per_package_14',
		'amount_per_package_15',
		
		'cost_postage_id_1',               // 輸送費原単位
		'cost_postage_id_2',
		'cost_postage_id_3',
		'cost_postage_id_4',
		'cost_postage_id_5',
		'cost_postage_id_6',
		'cost_postage_id_7',
		'cost_postage_id_8',
		'cost_postage_id_9',
		'cost_postage_id_10',
		'cost_postage_id_11',
		'cost_postage_id_12',
		'cost_postage_id_13',
		'cost_postage_id_14',
		'cost_postage_id_15',
		
		'cost_package_id_1',               // 梱包仕様原単位
		'cost_package_id_2',
		'cost_package_id_3',
		'cost_package_id_4',
		'cost_package_id_5',
		'cost_package_id_6',
		'cost_package_id_7',
		'cost_package_id_8',
		'cost_package_id_9',
		'cost_package_id_10',
		'cost_package_id_11',
		'cost_package_id_12',
		'cost_package_id_13',
		'cost_package_id_14',
		'cost_package_id_15',
		
		'approval_comment',      // 承認コメント
		
		
		'created_user_id',       // 初期登録者ユーザーID
		'last_update_user_id',   // 最終更新者ユーザーID
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'column_name_1',                 // 項目名
		'column_name_2',
		'column_name_3',
		'column_name_4',
		'column_name_5',
		'column_name_6',
		'column_name_7',
		'column_name_8',
		'column_name_9',
		'column_name_10',
		'column_name_11',
		'column_name_12',
		'column_name_13',
		'column_name_14',
		'column_name_15',

		'item_amount_1',                 // 商品数／件
		'item_amount_2',
		'item_amount_3',
		'item_amount_4',
		'item_amount_5',
		'item_amount_6',
		'item_amount_7',
		'item_amount_8',
		'item_amount_9',
		'item_amount_10',
		'item_amount_11',
		'item_amount_12',
		'item_amount_13',
		'item_amount_14',
		'item_amount_15',
		
		'customer_delivery_cost_1',      // 顧客送料総負担額
		'customer_delivery_cost_2',
		'customer_delivery_cost_3',
		'customer_delivery_cost_4',
		'customer_delivery_cost_5',
		'customer_delivery_cost_6',
		'customer_delivery_cost_7',
		'customer_delivery_cost_8',
		'customer_delivery_cost_9',
		'customer_delivery_cost_10',
		'customer_delivery_cost_11',
		'customer_delivery_cost_12',
		'customer_delivery_cost_13',
		'customer_delivery_cost_14',
		'customer_delivery_cost_15',
		
		
		'sales_price',              // 販売価格
		

		'tax_percentage_1',         // 日本消費税・関税・現地課税 
		'tax_percentage_2',
		'tax_percentage_3',
		'tax_percentage_4',
		'tax_percentage_5',
		'tax_percentage_6',
		'tax_percentage_7',
		'tax_percentage_8',
		'tax_percentage_9',
		'tax_percentage_10',
		'tax_percentage_11',
		'tax_percentage_12',
		'tax_percentage_13',
		'tax_percentage_14',
		'tax_percentage_15',

		'discount_percentage_1',    // 値引率
		'discount_percentage_2',
		'discount_percentage_3',
		'discount_percentage_4',
		'discount_percentage_5',
		'discount_percentage_6',
		'discount_percentage_7',
		'discount_percentage_8',
		'discount_percentage_9',
		'discount_percentage_10',
		'discount_percentage_11',
		'discount_percentage_12',
		'discount_percentage_13',
		'discount_percentage_14',
		'discount_percentage_15',
		
		'overseas_percentage_1',    // 海外上代割増率
		'overseas_percentage_2',
		'overseas_percentage_3',
		'overseas_percentage_4',
		'overseas_percentage_5',
		'overseas_percentage_6',
		'overseas_percentage_7',
		'overseas_percentage_8',
		'overseas_percentage_9',
		'overseas_percentage_10',
		'overseas_percentage_11',
		'overseas_percentage_12',
		'overseas_percentage_13',
		'overseas_percentage_14',
		'overseas_percentage_15',
		
		'summary_manufacture_total_cost', // 製造原価
		'summary_material_cost',          // A. 原料・製品調達費
		'summary_package_cost',           // B. 資材費
		'summary_expendable_cost',        // C. 消耗品費
		'summary_processing_cost',        // D. 加工費用

		'cost_material_list',             // 原料・製品調達費
		'cost_package_list',              // 資材費
		'cost_expendable_list',           // 消耗品費
		'cost_processing_list',           // 加工費用
		
		'memo',                           // 備考
		
		
		'amount_per_package_1',           // 1件当たりの輸送個数
		'amount_per_package_2',
		'amount_per_package_3',
		'amount_per_package_4',
		'amount_per_package_5',
		'amount_per_package_6',
		'amount_per_package_7',
		'amount_per_package_8',
		'amount_per_package_9',
		'amount_per_package_10',
		'amount_per_package_11',
		'amount_per_package_12',
		'amount_per_package_13',
		'amount_per_package_14',
		'amount_per_package_15',
		
		'cost_postage_id_1',               // 輸送費原単位
		'cost_postage_id_2',
		'cost_postage_id_3',
		'cost_postage_id_4',
		'cost_postage_id_5',
		'cost_postage_id_6',
		'cost_postage_id_7',
		'cost_postage_id_8',
		'cost_postage_id_9',
		'cost_postage_id_10',
		'cost_postage_id_11',
		'cost_postage_id_12',
		'cost_postage_id_13',
		'cost_postage_id_14',
		'cost_postage_id_15',
		
		'cost_package_id_1',               // 梱包仕様原単位
		'cost_package_id_2',
		'cost_package_id_3',
		'cost_package_id_4',
		'cost_package_id_5',
		'cost_package_id_6',
		'cost_package_id_7',
		'cost_package_id_8',
		'cost_package_id_9',
		'cost_package_id_10',
		'cost_package_id_11',
		'cost_package_id_12',
		'cost_package_id_13',
		'cost_package_id_14',
		'cost_package_id_15',
		
		'approval_comment',      // 承認コメント
    );

    /**
     * バージョンリスト
     * @param int $itemId
     * @return boolean
     */
    public function getVersionListByItemId($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->order('version_id DESC');
    	return $selectObj->query()->fetchAll();
    }

      
    /**
     * 次のバージョンID
     * @param  int $itemId
     * @return int $nextId
     */
    public function getNextVersionId($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_id = ?', $itemId);
    	$selectObj->order('version_id DESC');
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return (int)$data['version_id'] + 1;
        }
        return 1;
    }

    /**
     * IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return boolean
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	$data['cost_material_list']    = json_decode($data['cost_material_list'], true);
		$data['cost_package_list']     = json_decode($data['cost_package_list'], true);
    	$data['cost_expendable_list']  = json_decode($data['cost_expendable_list'], true);
    	$data['cost_processing_list']  = json_decode($data['cost_processing_list'], true);
    	return $data;
    }
		
    /**
     * バージョン1作成(なければ新規作成)
     * @param int $managementGroupId
     * @param int $itemId
     * @return boolean
     */
    public function createFirstVersion($managementGroupId, $itemId)
    {
    	$data = array(
	        'management_group_id'  => $managementGroupId,                // 管理グループID
	        'item_id'              => $itemId,                           // 対象商品ID
	        'version_id'           => 1,
			'version_status'       => Shared_Model_Code::COST_CALC_STATUS_NOT_CREATED,   // ステータス
	
			'summary_manufacture_total_cost' => '0',  // 製造原価
			'summary_material_cost'          => '0',  // A. 原料・製品調達費
			'summary_package_cost'           => '0',  // B. 資材費
			'summary_expendable_cost'        => '0',  // C. 消耗品費
			'summary_processing_cost'        => '0',  // D. 加工費用
			
			'column_name_1'         => '単発単品',
			'amount_per_package_1'  => '1',
			'column_name_2'         => '3つセット',
			'amount_per_package_2'  => '3',
			'column_name_3'         => '定期購入',
			'amount_per_package_3'  => '1',
			
			'cost_material_list'   => json_encode(array()),   // 原料・製品調達費
			'cost_package_list'    => json_encode(array()),   // 資材費
			'cost_expendable_list' => json_encode(array()),   // 消耗品費
			'cost_processing_list' => json_encode(array()),   // 加工費用
			
			'created_user_id'      => 0,   // 初期登録者ユーザーID
			'last_update_user_id'  => 0,   // 最終更新者ユーザーID
			
			'created'              => new Zend_Db_Expr('now()'), // レコード作成日時
	        'updated'              => new Zend_Db_Expr('now()'), // レコード更新日時
		);
    			
		for ($count = 4; $count <= 15; $count++) {
			$data['column_name_' . $count] = '---';
		}
			
		$this->create($data);
    }

    /**
     * 一覧
     * @param none
     * @return boolean
     */
    public function getList()
    {
    	$selectObj = $this->select();
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

