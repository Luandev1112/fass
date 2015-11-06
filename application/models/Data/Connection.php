<?php
/**
 * class Shared_Model_Data_Connection
 *
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_Connection extends Shared_Model_Data_DbAbstract
{

    protected $_tableName = 'frs_connection';

    protected $_fields = array(
        'id',                                  // ID
        'management_group_id',                 // 管理グループID
        'display_id',                          // 表示ID XX＋西暦下二桁＋5桁
		'status',                              // ステータス
		
		'gs_supplier_id',                      // goosaサプライヤーID
		'gs_supplier_display_id',              // goosaサプライヤー表示ID
		'gs_buyer_id',                         // goosaバイヤーID
		'gs_buyer_display_id',                 // goosaバイヤー表示ID
		
		'gsc_supplier_id',                     // GOOSCAサプライヤーID
		'gsc_supplier_display_id',             // GOOSCAサプライヤー表示ID
		
		'company_name',                        // 企業名
		'company_name_kana',                   // 企業名カナ

		'type',                                // 種別
		'types_of_our_business',               // 関連当社事業区分
		'relation_types',                      // 当社取引関係
		'relation_type_other_text',            // 当社取引関係 その他 テキスト
		'sales_relations',                     // 主な商談ポジション
		'industry_types',                      // 業種
		
		'description',                         // 事業内容
		'corporate_number',                    // 法人番号
		'country',                             // 国
		'head_office_postal_code',             // 本社所在地郵便番号
		'head_office_prefecture',              // 本社・都道府県
		'head_office_city',                    // 本社・市区町村
		'head_office_address',                 // 本社・丁番地
		'head_office_building',                // 本社・建物名・階／号室
		'representative_name',                 // 代表者名
		'representative_name_kana',            // 代表者名カナ
		'tel',                                 // 電話番号
		'fax',                                 // FAX番号
		'web_url',                             // 企業URL
		'duty',                                // 課税・免税
		
		'memo',                                // 取引先情報メモ
		
		'foundation_date',                     // 会社設立年月日
		'company_form',                        // 会社形態
		'capital',                             // 資本金
		'employees',                           // 従業員数
		'branch_offices',                      // 営業店舗数

		'main_stockholder',                    // 主な株主
		'main_bank',                           // 主要取引銀行
		'main_connection',                     // 主要取引先企業
		
		'detective_season',                    // 興信所・調査時期
		'detective_name',                      // 興信所・調査機関名
		'detective_result',                    // 興信所・信用格付結果
		'detective_own',                       // 当社信用格付
		'detective_memo',                      // 他信用特記メモ
		
		'financial_closing',                   // 決算情報
		
		'sales_ec',                            // EC
		'sales_real',                          // 実店舗
		'sales_overseas',                      // 海外販路
		'sales_memo',                          // メモ
		'sales_payment_method',                // 通常入金方法
		'sales_payment_method_other_text',     // 
		'sales_payment_conditions',            // 通常入金条件
		
		'supply_content',                      // 主要取引品目・委託内容
		'supply_payment_method',               // 通常支払方法
		'supply_payment_method_other_text',    //
		'supply_payment_conditions',           // 通常支払条件
		
		'supply_account_bank',                 // 金融機関名（本支店名）
		'supply_account_type',                 // 預金種別
		'supply_account_no',                   // 口座番号
		'supply_account_name',                 // 口座名義
		'supply_account_name_kana',            // 口座名義カナ

		'inv_fin_relation',                    // 主要投融資関係
		'inv_fin_memo',                        // メモ
		
		'inv_fin_account_bank',                // 金融機関名（本支店名）
		'inv_fin_account_type',                // 預金種別
		'inv_fin_account_no',                  // 口座番号
		'inv_fin_account_name',                // 口座名義
		'inv_fin_account_name_kana',           // 口座名義カナ
		
		'gs_bank_renewaled_datetime',          // 振込先口座更新日
		'gs_bank_confirmed',                   // 振込先口座確認ステータス
		'gs_bank_confirmed_date_time',         // 振込先口座確認日
		'gs_bank_confirmed_user_id',           // 振込先口座確認者
		'gs_basic_bank_select',
		'gs_other_bank_name',
		'gs_bank_code',
		'gs_bank_branch_id',
		'gs_bank_branch_name',
		'gs_bank_account_type',
		'gs_bank_account_no',
		'gs_bank_account_name',
		'gs_bank_account_name_kana',
  
		'created_user_id',                     // 初期登録者ユーザーID
		'last_update_user_id',                 // 最終更新者ユーザーID
		
        'created',                             // レコード作成日時
        'updated',                             // レコード更新日時

    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'company_name',                        // 企業名
		'company_name_kana',                   // 企業名カナ
		
		'relation_type_other_text',            // 当社取引関係 その他 テキスト
		
		'description',                         // 事業内容
		'corporate_number',                    // 法人番号
		'head_office_postal_code',             // 本社所在地郵便番号
		'head_office_prefecture',              // 本社所在地住所（都道府県のみ）
		'head_office_address',                 // 本社所在地住所（全部）
		'representative_name',                 // 代表者名
		'representative_name_kana',            // 代表者名カナ
		'tel',                                 // 電話番号
		'fax',                                 // FAX番号
		'web_url',                             // 企業URL

		'memo',                                // 取引先情報メモ
		
		'foundation_date',                     // 会社設立年月日
		'capital',                             // 資本金
		'employees',                           // 従業員数
		'branch_offices',                      // 営業店舗数
		
		'fiscal_year1',                        // 決算期年度1
		'fiscal_amount1',                      // 決算期売上高1
		'fiscal_ordinary1',                    // 決算期経常利益1
		'fiscal_year2',                        // 決算期年度2
		'fiscal_amount2',                      // 決算期売上高2
		'fiscal_ordinary2',                    // 決算期経常利益2
		
		'main_stockholder',                    // 主な株主
		'main_bank',                           // 主要取引銀行
		'main_connection',                     // 主要取引先企業
		
		'detective_season',                    // 興信所・調査時期
		'detective_name',                      // 興信所・調査機関名
		'detective_result',                    // 興信所・信用格付結果
		'detective_own',                       // 当社信用格付
		'detective_memo',                      // 他信用特記メモ
		
		'financial_closing',                   // 決算情報
		
		'sales_content',                       // 
		'sales_memo',                          // 
		'sales_payment_method',
		'sales_payment_method_other_text',
		'sales_payment_conditions',
		
		'supply_content',                      // 
		'supply_payment_method',
		'supply_payment_method_other_text',
		'supply_payment_conditions',
		
		
		'supply_account_bank',                 // 
		'supply_account_no',                   // 
		'supply_account_name',                 // 	
		'supply_account_name_kana',            // 	

		'supply_in_order_1_name',              // 
		'supply_in_order_1_name_kana',         // 	
		'supply_in_order_1_department',        // 
		'supply_in_order_1_position',          // 
		'supply_in_order_1_tel',               // 
		'supply_in_order_1_fax',               // 
		'supply_in_order_1_mail',              // 
		'supply_in_order_1_memo',              // 

		'inv_fin_relation',                    // 
		'inv_fin_memo',                        // 
		'inv_fin_account_bank',                // 	
		'inv_fin_account_no',                  // 
		'inv_fin_account_name',                // 
		'inv_fin_account_name_kana',           // 
		

		'gs_other_bank_name',
		'gs_bank_code',
		'gs_bank_branch_id',
		'gs_bank_branch_name',
		'gs_bank_account_no',
		'gs_bank_account_name',
		'gs_bank_account_name_kana',
    );

    /**
     * 取引先IDで取得
     * @param int $managementGroupId
     * @param int $id
     * @return array
     */
    public function getById($managementGroupId, $id)
    {
    	$selectObj = $this->select();
    	//$selectObj->where('management_group_id = ?', $managementGroupId);
    	$selectObj->where('frs_connection.id = ?', $id);
    	$data = $selectObj->query()->fetch();
    	
    	if (!empty($data)) {   	
	    	$data['types_of_our_business'] = unserialize($data['types_of_our_business']);
	    	$data['relation_types']        = unserialize($data['relation_types']);
	    	$data['sales_relations']       = unserialize($data['sales_relations']);
	    	$data['industry_types']        = unserialize($data['industry_types']);
	    	$data['sales_payment_method']  = unserialize($data['sales_payment_method']);
	    	$data['supply_payment_method'] = unserialize($data['supply_payment_method']);
	    	$data['inv_fin_relation']      = unserialize($data['inv_fin_relation']);
	    	$data['financial_closing']     = json_decode($data['financial_closing'], true);
	    	$data['sales_payment_conditions'] = json_decode($data['sales_payment_conditions'], true);
	    	$data['supply_payment_conditions'] = json_decode($data['supply_payment_conditions'], true);
    	} 
    	return $data;
    }
		
    /**
     * 企業名 / 取引拠点名で取得
     * @param string $companyName
     * @return array
     */
    public function findByCompanyName($companyName)
    {
    	$selectObj = $this->select();
    	$selectObj->where($this->aesdecrypt('company_name', false) . ' = ?', $companyName);
		return $selectObj->query()->fetch();
    }
    
    /**
     * 振込先未確認件数
     * @param none
     * @return array
     */
    public function getCountBankNotConfirmed()
    {
        $selectObj = $this->select(array(new Zend_Db_Expr('COUNT(`id`) as item_count')));
        $selectObj->where('frs_connection.gs_bank_confirmed = ?', Shared_Model_Code::BANK_CONFIRM_STATUS_RENEWALED);
        $data = $selectObj->query()->fetch();
        
        if (!empty($data)) {
            return $data['item_count'];
        }
        return 0;
    }
    
    /**
     * 次の取引先ID (XX＋西暦下二桁＋5桁)
     * @param none
     * @return array
     */
    public function getNextDisplayId()
    {
    	$selectObj = $this->select();
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
						throw new Zend_Exception('Shared_Model_Data_Connection display_id id is over-flowed');
					}
					
					return 'TS' . $year . $nextAlphabet . '0001';
				} 
				return 'TS' . $year . $lastAlphabet . sprintf('%04d', $lastCount + 1);
			}
		}
		
		return 'TS' . $year . '0' . '0001';
    }
    
        
    /**
     * 取引先IDで取得
     * @param int $connectionId
     * @return array
     */
    public function getByConnectionId($connectionId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('display_id = ?', $id);
    	$selectObj->where('status != ?', Shared_Model_Code::CONNECTION_STATUS_REMOVE);
    	$selectObj->order('id DESC');
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

