<?php
/**
 * class Shared_Model_Data_Item
 * アイテム
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Item extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
		'status',                      // ステータス
		'cost_calc_status',            // 原価計算ステータス
		'cost_calc_updated',           // 原価計算更新日時
		
		'item_type',                   // アイテム種別
		'item_type_id',                // アイテム種別ID
		'display_id',                  // 表示ID XX＋西暦下二桁＋5桁
		
		'category_id',                 // カテゴリID
		'gs_display_id',               // Goosa連携
		
		'strategy',                    // 商品戦略
	
		'product_name_type',           // 商品名区分
		'item_name',                   // アイテム名(日本語)
		'item_name_en',                // アイテム名(英語)
		'is_temporary_name',           // 仮名称
		'buying_item_name',            // 仕入商品名
		
		'delivery_item_name',          // 配送向け内容品表記(日本語)
		'delivery_item_name_en',       // 配送向け内容品表記(英語)
		
		'jan_code',                    // JANコード
		
		'connection_id',               // 仕入先(取引先)ID
		
// 廃止予定 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		'connection_base_name',        // 取引拠点名

		'classification_material',     // 区分 原料
		'classification_own',          // 区分 自社製造品
		'classification_buying',       // 区分 仕入他社製品
		'classification_oem_product',  // 区分 OEM委託品
		'classification_oem_supplied', // 区分 OEM受託品
		'classification_service',      // 区分 役務
		'classification_other',        // 区分 その他
		'classification_other_text',   // 区分 その他 テキスト
		
		'category_food',               // 分類 食品
		'category_drink',              // 分類 飲料
		'category_supplement',         // 分類 サプリメント
		'category_health_appliance',   // 分類 健康器具
		'category_sanitary',           // 分類 衛生商品
		'category_equip_environment',  // 分類 環境設備装置
		'category_equip_product',      // 分類 製造設備装置
		'category_other',              // 分類 その他
		'category_other_text',         // 分類 その他 テキスト
		
		'sales_status_retail_mail',        // 取扱状況 小売通販可
		'sales_status_retail_closed',      // 取扱状況 小売クローズ販売可
		'sales_status_domestic_wholesale', // 取扱状況 国内卸販売可
		'sales_status_overseas_wholesale', // 取扱状況 海外卸販売可
		'sales_status_discontinued',       // 取扱状況 販売不可
		'sales_status_memo',               // 取扱状況メモ
// 廃止予定 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		
		'product_classes',            // 調達製造区分
		'product_class_other_text',   // 調達製造区分 その他テキスト
		'product_categories',         // 商品区分(分類)
		'product_category_other_text',// 商品区分(分類) その他テキスト
		'product_markets',            // 販売可能範囲
		
		'product_sales_status',       // 販売状況
		'next_generation_item_id',    // 後継品商品ID
		'sales_status_memo',          // 取扱状況メモ

// 廃止予定 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓	
		'supply_method_individual',   // 調達方法 個別発注
		'supply_method_rakuten',      // 調達方法 楽天
		'supply_method_amazon',       // 調達方法 Amazon
		'supply_method_monotaro',     // 調達方法 モノタロウ
		'supply_method_yahoo',        // 調達方法 Yahoo!ショッピング
		'supply_method_askul',        // 調達方法 アスクル
		'supply_method_etc',          // 調達方法 その他
		'supply_method_etc_text',     // 調達方法 その他 テキスト
// 廃止予定 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

		'supply_methods',               // 調達方法
		'supply_method_other_text',     // 調達方法 その他 テキスト
		'supply_method_memo',           // 調達方法メモ

		'production_process',           // 製品化の手順
		'production_process_other_text',// 製品化の手順 その他 テキスト

		'registered_user_id',           // 登録者社員ID
		
		'estimation_date',              // 入手見積書日付 
		'estimation_files',             // 入手見積書・補足資料アップロード
		'memo_estimation',              // 見積メモ
		
		'relational_connection_list',   // 仕入先・製造委託先(リスト)
		
		'requested_sale_price',         // 希望小売価格
		'requested_wholesale_price',    // 希望卸価格
		'restriction_price',            // 販売価格制約(廃止)
		'restriction_method',           // 販売方法制約(廃止)
		
		'price_restriction_for_customer',         // 顧客向け当社販売価格制約
		'method_restriction_for_customer_text',   // 顧客向け当社販売価格制約メモ
		'price_restriction_from_supplier',        // 仕入先上代遵守ルール有無
		'price_restriction_from_supplier_price',  // 仕入先上代遵守ルール有無 金額
		'method_restriction_from_supplyer_text',  // 仕入先指定販売条件
		'sales_condition_memo',                   // 販売条件メモ
		
		'cost_memo',                    // 原価メモ

// 廃止予定 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
		'purchasing_lot_1',             // 購入ロット1
		'purchasing_lot_unit1',         // ロット単位1
		'purchasing_lot_price1',        // 仕入価格1
		'purchasing_logistics_cost1',   // 物流費1

		'purchasing_lot_2',             // 購入ロット2
		'purchasing_lot_unit2',         // ロット単位2
		'purchasing_lot_price2',        // 仕入価格2
		'purchasing_logistics_cost2',   // 物流費2

		'purchasing_lot_3',             // 購入ロット3
		'purchasing_lot_unit3',         // ロット単位3
		'purchasing_lot_price3',        // 仕入価格3
		'purchasing_logistics_cost3',   // 物流費3
		
		'purchasing_lot_4',             // 購入ロット4
		'purchasing_lot_unit4',         // ロット単位4
		'purchasing_lot_price4',        // 仕入価格4
		'purchasing_logistics_cost4',   // 物流費4
		
		'purchasing_lot_5',             // 購入ロット5
		'purchasing_lot_unit5',         // ロット単位5
		'purchasing_lot_price5',        // 仕入価格5
		'purchasing_logistics_cost5',   // 物流費5
// 廃止予定 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		'purchasing_list',              // 仕入購入条件(リスト)
		

		'stock_count',                  // 在庫数                 (frs_warehouse_itemに移行)
		'useable_count',                // 引当可能在庫数         (frs_warehouse_itemに移行)
		'alert_count',                  // アラート在庫数         (frs_warehouse_itemに移行)
		'minimum_count',                // 最低在庫数             (frs_warehouse_itemに移行)
		'safety_count',                 // 安全在庫数             (frs_warehouse_itemに移行)
		
		'unit_type',                    // 単位種別               (frs_warehouse_itemに移行)
		'image_file_name',              // 画像ファイル名
		'memo',                         // 商品内容
  
        'gs_item_name',                 // GS商品名
        'gs_item_name_kana',            // GS商品名（ふりがな）
        'gs_price_display_method',      // GS卸価格提示条件
        
        'gs_sales_status',              // GS販売状況
        'gs_sales_method',              // GS販売方法
        'gs_sales_start_date',          // GS掲載開始日
        'gs_sales_end_date',            // GS掲載終了日
        'gs_price_discount',            // 割引設定
        'gs_price_discount_percent',    // 割引%
        'gs_price_discount_start_date', // 割引開始日
        'gs_price_discount_end_date',   // 割引終了日
        
        'gs_stock_status',              // 在庫状況
        'gs_stamp',                     // スタンプ
        'gs_img_license',               // 画像転載許可
        'gs_img_supplying',             // GS広告作成用の画像提供
        'gs_bulk_discount',             // まとめ買い割引設定
        
        'gs_catch_copy',                // キャッチコピー
        'gs_comment',                   // コメント
        'gs_superiority',               // 商品の競合優位性
        'gs_bland_name',                // ブランド名
        'gs_size',                      // サイズ・容量
        'gs_standard',                  // 規格
        'gs_attention',                 // 注意事項

		'created_user_id',              // 初期登録者ユーザーID
		'last_update_user_id',          // 最終更新者ユーザーID
		
        'created',                      // レコード作成日時
        'updated',                      // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'item_name',                    // アイテム名
		'item_name_en',                 // アイテム名(英語)
		'buying_item_name',             // 仕入商品名
		
		'delivery_item_name',           // 配送向け内容品表記(日本語)
		'delivery_item_name_en',        // 配送向け内容品表記(英語)
		
		'product_classes',              // 区分
		'product_class_other_text',     // 区分 その他テキスト
		'product_categories',           // 分類
		'product_category_other_text',  // 分類 その他テキスト
		'product_markets',              // 販売可能範囲

		'sales_status_memo',            // 取扱状況メモ

		'supply_methods',               // 調達方法
		'supply_method_other_text',     // 調達方法 その他 テキスト
		'supply_method_memo',           // 調達方法メモ
		
		'estimation_files',             // 入手見積書・補足資料アップロード
		'memo_estimation',              // 見積メモ
		
		'relational_connection_list',   // 仕入先・製造委託先(リスト)
		
		'purchasing_list',              // 仕入購入条件(リスト)
		
		'supply_methods',               // 調達方法
		'supply_method_other_text',     // 調達方法 その他 テキスト
		'supply_method_memo',           // 調達方法メモ

		'requested_sale_price',         // 希望小売価格
		'requested_wholesale_price',    // 希望卸価格
		'restriction_price',            // 販売価格制約
		'restriction_method',           // 販売方法制約
		
		'method_restriction_for_customer_text',   // 顧客向け当社販売価格制約メモ
		'price_restriction_from_supplier_price',  // 仕入先上代遵守ルール有無 金額
		'method_restriction_from_supplyer_text',  // 仕入先指定販売条件
		'sales_condition_memo',         // 販売条件メモ
		
		'cost_memo',                    // 原価メモ
		
        'gs_item_name',                 // GS商品名
        'gs_item_name_kana',            // GS商品名（ふりがな）	

        'gs_catch_copy',                // キャッチコピー
        'gs_comment',                   // コメント
        'gs_superiority',               // 商品の競合優位性
        'gs_bland_name',                // ブランド名
        'gs_size',                      // サイズ・容量
        'gs_standard',                  // 規格
        'gs_attention',                 // 注意事項
        
		'memo',                         // 商品内容
		'image_file_name',
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
    	$data = $selectObj->query()->fetch();
    	$data['product_classes']        = unserialize($data['product_classes']);
    	$data['product_categories']     = unserialize($data['product_categories']);
    	$data['product_markets']        = unserialize($data['product_markets']);
    	$data['supply_methods']         = unserialize($data['supply_methods']);
    	$data['relational_connection_list'] = json_decode($data['relational_connection_list'], true);
    	$data['purchasing_list']            = json_decode($data['purchasing_list'], true);
    	return $data;
    }

    /**
     * IDで取得(拠点情報を含む)
     * @param int $id
     * @return array
     */
    public function getByIdWithBaseInfo($id)
    {
    	$selectObj = $this->select();
    	$selectObj->where('frs_item.id = ?', $id);
    	$selectObj->joinLeft('frs_item_base', 'frs_item_base.item_id = frs_item.id AND frs_item_base.base_id = ' . '1', array('shelf_no'));
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

    /**
     * アイテム種別の次ID
     * @param  int $itemType
     * @return int $nextId
     */
    public function getNextItemTypeId($itemType)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_type = ?', $itemType);
    	$selectObj->order('id DESC');
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return (int)$data['item_type_id'] + 1;
        }
        return 1;
    }

    /**
     * 次の商品ID (XX＋西暦下二桁＋5桁)
     * @param none
     * @return array
     */
    public function getNextDisplayIdWithItemType($itemType)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_type = ?', $itemType);
    	$selectObj->where('display_id IS NOT NULL');
    	$selectObj->order('id DESC');
    	$data = $selectObj->query()->fetch();
		
		$year = '' . date('y');
		
		if (!empty($data)) {
			$lastDate = substr($data['display_id'], 2, 2);
			
			if ($lastDate == $year) {
				$lastAlphabet = substr($data['display_id'], 4, 1);
				$lastCount = (int)substr($data['display_id'], 5, 4);

				if ($lastCount >= 9999) {
					$nextAlphabet = '';
					$isMatched = false;
					$alphabetCodeList = Shared_Model_Code::getIdAlpahabet();
					foreach ($alphabetCodeList as $each) {
						if ($isMatched === true) {
							$nextAlphabet = $each;
							break;
						} else if ($each === $lastAlphabet) {
							$isMatched = true;
						}
					}
					
					if ($nextAlphabet === '') {
						throw new Zend_Exception('display_id id is over-flowed');
					}
					
					return 'SH' . $year . $nextAlphabet . '0001';
				} 
				return 'SH' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'SH' . $year . '0' . '0001';
    }
    
    /**
     * カテゴリ別件数を取得
     * @param  int $categoryId
     * @return int $count
     */
    public function getItemCountByCategoryId($categoryId)
    {
    	$selectObj = $this->select(array(
    		new Zend_Db_Expr('COUNT(`id`) as item_count')
    	));
    	$selectObj->where('category_id = ?', $categoryId);
    	$selectObj->where('status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
    	
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }

    /**
     * カテゴリ別リストを取得
     * @param  int $itemType
     * @return array
     */
    public function getItemList($itemType)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_type = ?', $itemType);
    	$selectObj->where('status = ?', Shared_Model_Code::ITEM_STATUS_ACTIVE);
    	$selectObj->order('id ASC');
        return $selectObj->query()->fetchAll();
    }
    


// 以下廃止予定 ---------------------------------------------------------------------------------
	
    /**
     * 在庫追加
     * @param  int $id
     * @param  int $addCount
     * @return array
     */
    /*
    public function addStock($id, $addCount)
    {
    	$data = $this->getById('1', $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    	}
    	
    	$this->updateById($id, array(
    		'stock_count'   => $data['stock_count'] + $addCount,
    		'useable_count' => $data['useable_count'] + $addCount,
    	));
	}
	*/

    /**
     * 引当可能在庫数を減らす
     * @param  int $id
     * @param  int $addCount
     * @return array
     */
    /*
    public function subUseableCount($id, $subCount)
    {
    	$data = $this->getById('1', $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    	}
    	
    	$this->updateById($id, array(
    		'useable_count' => $data['useable_count'] - $subCount,
    	));
	}
	*/

    /**
     * 引当可能在庫数を戻す(増やす)
     * @param  int $id
     * @param  int $addCount
     * @return array
     */
    /*
    public function addUseableCount($id, $addCount)
    {
    	$data = $this->getById('1', $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    	}
    	
    	$this->updateById($id, array(
    		'useable_count' => $data['useable_count'] + $addCount,
    	));
	}
	*/
	
    /**
     * 在庫消費
     * @param  int $id
     * @param  int $subCount
     * @return array
     */
    /*
    public function subStock($id, $subCount)
    {
    	$data = $this->getById('1', $id);
    	
    	if (empty($data)) {
    		throw new Zend_Exception('Shared_Model_Data_Item - Target item data not found.');
    		
    	} else if ((int)$data['stock_count'] < $subCount || (int)$data['useable_count'] < $subCount) {
    		throw new Zend_Exception('Shared_Model_Data_Item - stock_count is not enough');
    		
    	}
    	
    	$this->updateById($id, array(
    		'stock_count'   => $data['stock_count'] - $subCount,
    		'useable_count' => $data['useable_count'] - $subCount,
    	));
	}
	*/

}

