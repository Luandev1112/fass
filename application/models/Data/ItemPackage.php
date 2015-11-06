<?php
/**
 * class Shared_Model_Data_ItemPackage
 * 商品パッケージ
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_ItemPackage extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_item_package';

    protected $_fields = array(
        'id',                    // ID
        'status',                // ステータス
        'package_type',          // パッケージ区分
        'is_subscription',       // 定期
        'package_name_domestic', // パッケージ名(国内)
        'package_name_overseas', // パッケージ名(海外)
        'price_domestic',        // 価格(国内)
        'price_overseas',        // 価格(海外)
		'product_code',          // 商品コード         --- unique
		'memo',                  // 備考
		'store_own_domestic',    // 自社サイト(国内)
		'store_own_overseas',    // 自社サイト(海外)
		'store_own_rakuten',     // 楽天
		'store_own_yahoo_co_jp', // Yahoo!ショッピング
		'store_own_amazon_co_jp',// Amazon
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
     * アイテムIDで取得
     * @param int $itemId
     * @return boolean
     */
    public function getListByItemId($itemId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('item_id = ?', $itemId);
    	return $selectObj->query()->fetchAll();
    }

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
     * 商品コードで取得
     * @param int $productCode
     * @return boolean
     */
    public function getByProductCode($productCode, $exceptPackageId = false)
    {
    	$selectObj = $this->select();
    	$selectObj->where('product_code = ?', $productCode);
    	
    	if (!empty($exceptPackageId)) {
    		$selectObj->where('id != ?', $exceptPackageId);
    	}
    	
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

