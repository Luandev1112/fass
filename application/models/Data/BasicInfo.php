<?php
/**
 * class Shared_Model_Data_BasicInfo
 * 基本情報
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_BasicInfo extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_basic_info';

    protected $_fields = array(
        'id',                   // ID
        'shop_name',            // ショップ名
		'company_name',         // 会社名
		'shop_url',             // ショップURL
		'zipcode',              // 郵便番号
		'prefecture',           // 都道府県
		'address1',             // 住所
		'address2',             // 建物
		'shop_manager_name',    // ショップ担当者名
		'shop_mail',            // メールアドレス
		'shop_tel',             // 電話番号
		'shop_fax',             // FAX
		'memo',                 // メモ
		
		'logo_file_name',       // ロゴファイル名
		'statement_shop_info',  // 明細書表示 ショップ情報
		
		'statement_tamplate_1',               // 明細書テンプレート1 
		'statement_tamplate_2',               // 明細書テンプレート2
		'statement_tamplate_3',               // 明細書テンプレート3
		'statement_tamplate_subscription_1',  // 定期注文明細書テンプレート1
		'statement_tamplate_subscription_2',  // 定期注文明細書テンプレート2
		'statement_tamplate_subscription_3',  // 定期注文明細書テンプレート3
		
        'created',               // レコード作成日時
        'updated',               // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
        'shop_name',            // ショップ名
		'company_name',         // 会社名
		'shop_url',             // ショップURL
		'zipcode',              // 郵便番号
		'prefecture',           // 都道府県
		'address1',             // 住所
		'address2',             // 建物
		'shop_manager_name',    // ショップ担当者名
		'shop_mail',            // メールアドレス
		'shop_tel',             // 電話番号
		'shop_fax',             // FAX
		'memo',                 // メモ
		
		'logo_file_name',       // ロゴファイル名
		'statement_shop_info',  // 明細書表示 ショップ情報
		
    );
    
    /**
     * IDで取得
     * @param int $warehouseId
     * @return boolean
     */
    public function get($warehouseId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('id = ?', $warehouseId);
    	return $selectObj->query()->fetch();
    }

    /**
     * 更新
     * @param int $warehouseId
     * @param array $columns
     * @return boolean
     */
    public function updateInfo($warehouseId, $columns)
    {
		return $this->update($columns, array('id' => $warehouseId));
    }

}

