<?php
/**
 * class Shared_Model_Data_CostDeliveryOverseas
 * 原単位・輸出物流
 * @package Shared
 * @subpackage Shared_Model_Data
 */
class Shared_Model_Data_CostDeliveryOverseasversion extends Shared_Model_Data_DbAbstract
{
    protected $_tableName = 'frs_cost_delivery_overseas_version';

    protected $_fields = array(
        'id',                          // ID
        'management_group_id',         // 管理グループID
        'parent_id',
		'version_id',
		'version_status',              // ステータス
		
		'export_unit', 'import_unit', // 通貨単位
		'products_quantity_1','products_quantity_2','products_quantity_3','products_quantity_4','products_quantity_5', // 製品数量
		'quantity_per_box_1','quantity_per_box_2','quantity_per_box_3','quantity_per_box_4','quantity_per_box_5', // 箱入り数
		'box_quantity_1','box_quantity_2','box_quantity_3','box_quantity_4','box_quantity_5', // 箱数
		'volume_per_box_1','volume_per_box_2','volume_per_box_3','volume_per_box_4','volume_per_box_5', // 箱当たり荷量
		'total_volume_1','total_volume_2','total_volume_3','total_volume_4','total_volume_5', // 全体荷量
		'weight_per_box_1','weight_per_box_2','weight_per_box_3','weight_per_box_4','weight_per_box_5', // 箱当たり重量
		'total_weight_1','total_weight_2','total_weight_3','total_weight_4','total_weight_5', // 全重量
		
		'ex_works_unit_price_1','ex_works_unit_price_2','ex_works_unit_price_3','ex_works_unit_price_4','ex_works_unit_price_5', // 工場倉庫出荷単価
		'ex_works_amount_1','ex_works_amount_2','ex_works_amount_3','ex_works_amount_4','ex_works_amount_5', // 工場倉庫出荷総額
		'packing_cost_1','packing_cost_2','packing_cost_3','packing_cost_4','packing_cost_5', // 梱包費用
		'delivery_cost_1','delivery_cost_2','delivery_cost_3','delivery_cost_4','delivery_cost_5', // 国内輸送費
		'export_expense_1_label', 'export_expense_1_1','export_expense_1_2','export_expense_1_3','export_expense_1_4','export_expense_1_5', // 輸出諸掛1
		'export_expense_2_label', 'export_expense_2_1','export_expense_2_2','export_expense_2_3','export_expense_2_4','export_expense_2_5', // 輸出諸掛2
		'export_expense_3_label', 'export_expense_3_1','export_expense_3_2','export_expense_3_3','export_expense_3_4','export_expense_3_5', // 輸出諸掛3
		'fob_amount_1','fob_amount_2','fob_amount_3','fob_amount_4','fob_amount_5', // 本船渡し総額
		'fob_unit_price_1','fob_unit_price_2','fob_unit_price_3','fob_unit_price_4','fob_unit_price_5', // 本船渡し単価
		'fob_index_1','fob_index_2','fob_index_3','fob_index_4','fob_index_5', // FOB指数
		'transport_cost_1','transport_cost_2','transport_cost_3','transport_cost_4','transport_cost_5', // 海上輸送費
		'transport_charge_1_1','transport_charge_1_2','transport_charge_1_3','transport_charge_1_4','transport_charge_1_5', // その他輸送諸掛1
		'rate_insurance_coverage_1','rate_insurance_coverage_2','rate_insurance_coverage_3','rate_insurance_coverage_4','rate_insurance_coverage_5', // 保険対象増幅率
		'insurance_coverage_amount_1','insurance_coverage_amount_2','insurance_coverage_amount_3','insurance_coverage_amount_4','insurance_coverage_amount_5', // 保険適用額
		'rate_insurance_1','rate_insurance_2','rate_insurance_3','rate_insurance_4','rate_insurance_5', // 保険率
		'insurance_price_1','insurance_price_2','insurance_price_3','insurance_price_4','insurance_price_5', // 保険料
		'rate_exchange_diffrence_1','rate_exchange_diffrence_2','rate_exchange_diffrence_3','rate_exchange_diffrence_4','rate_exchange_diffrence_5', // 金利利率
		'exchange_diffrence_1','exchange_diffrence_2','exchange_diffrence_3','exchange_diffrence_4','exchange_diffrence_5', // 金利
		'export_cif_cip_amount_1','export_cif_cip_amount_2','export_cif_cip_amount_3','export_cif_cip_amount_4','export_cif_cip_amount_5', // 運賃保険料込み総額
		'export_cif_cip_unt_price_1','export_cif_cip_unt_price_2','export_cif_cip_unt_price_3','export_cif_cip_unt_price_4','export_cif_cip_unt_price_5', // 運賃保険料込み単価
		'export_cif_cip_index_1','export_cif_cip_index_2','export_cif_cip_index_3','export_cif_cip_index_4','export_cif_cip_index_5', // CIF指数

		'basis_rate','rate',// 為替レートの根拠・為替レート
		'import_cif_cip_unit_price_1','import_cif_cip_unit_price_2','import_cif_cip_unit_price_3','import_cif_cip_unit_price_4','import_cif_cip_unit_price_5', // 運賃保険料込み単価
		'import_cif_cip_amount_1','import_cif_cip_amount_2','import_cif_cip_amount_3','import_cif_cip_amount_4','import_cif_cip_amount_5', // 運賃保険料込み総額
		'duty_rate_1','duty_rate_2','duty_rate_3','duty_rate_4','duty_rate_5', // 関税率
		'duty_amount_1','duty_amount_2','duty_amount_3','duty_amount_4','duty_amount_5', // 関税
		
		'import_expense_1_label','import_expense_1_1','import_expense_1_2','import_expense_1_3','import_expense_1_4', 'import_expense_1_5',// 輸入諸掛1
		'import_expense_2_label','import_expense_2_1','import_expense_2_2','import_expense_2_3','import_expense_2_4', 'import_expense_2_5',// 輸入諸掛2
		'import_expense_3_label','import_expense_3_1','import_expense_3_2','import_expense_3_3','import_expense_3_4', 'import_expense_3_5',// 輸入諸掛3
		'import_expense_4_label','import_expense_4_1','import_expense_4_2','import_expense_4_3','import_expense_4_4', 'import_expense_4_5',// 輸入諸掛4
		'import_expense_5_label','import_expense_5_1','import_expense_5_2','import_expense_5_3','import_expense_5_4', 'import_expense_5_5',// 輸入諸掛5
		'other_expense_1_1','other_expense_1_2','other_expense_1_3','other_expense_1_4', 'other_expense_1_5',// その他国内費用1
		'other_expense_2_1','other_expense_2_2','other_expense_2_3','other_expense_2_4', 'other_expense_2_5',// その他国内費用2
		'ddp_amount_1','ddp_amount_2','ddp_amount_3','ddp_amount_4', 'ddp_amount_5',// 関税込み持ち込み渡し総額
		'ddp_unit_price_1','ddp_unit_price_2','ddp_unit_price_3','ddp_unit_price_4', 'ddp_unit_price_5',// 関税込み持ち込み渡し単価
		'ddp_unit_price_rated_1','ddp_unit_price_rated_2','ddp_unit_price_rated_3','ddp_unit_price_rated_4', 'ddp_unit_price_rated_5',// 関税込み持ち込み渡し単価（輸出国通貨）
		'ddp_index_1','ddp_index_2','ddp_index_3','ddp_index_4', 'ddp_index_5',// DDP指数
				
        'created',                     // レコード作成日時
        'updated',                     // レコード更新日時
    );

    /**
     * 暗号/復号化するフィールド
     * @var array
     */
    protected $_cryptFields = array(
		'export_unit', 'import_unit', // 通貨単位
		'products_quantity_1','products_quantity_2','products_quantity_3','products_quantity_4','products_quantity_5', // 製品数量
		'quantity_per_box_1','quantity_per_box_2','quantity_per_box_3','quantity_per_box_4','quantity_per_box_5', // 箱入り数
		'box_quantity_1','box_quantity_2','box_quantity_3','box_quantity_4','box_quantity_5', // 箱数
		'volume_per_box_1','volume_per_box_2','volume_per_box_3','volume_per_box_4','volume_per_box_5', // 箱当たり荷量
		'total_volume_1','total_volume_2','total_volume_3','total_volume_4','total_volume_5', // 全体荷量
		'weight_per_box_1','weight_per_box_2','weight_per_box_3','weight_per_box_4','weight_per_box_5', // 箱当たり重量
		'total_weight_1','total_weight_2','total_weight_3','total_weight_4','total_weight_5', // 全重量
		
		'ex_works_unit_price_1','ex_works_unit_price_2','ex_works_unit_price_3','ex_works_unit_price_4','ex_works_unit_price_5', // 工場倉庫出荷単価
		'ex_works_amount_1','ex_works_amount_2','ex_works_amount_3','ex_works_amount_4','ex_works_amount_5', // 工場倉庫出荷総額
		'packing_cost_1','packing_cost_2','packing_cost_3','packing_cost_4','packing_cost_5', // 梱包費用
		'delivery_cost_1','delivery_cost_2','delivery_cost_3','delivery_cost_4','delivery_cost_5', // 国内輸送費
		'export_expense_1_label', 'export_expense_1_1','export_expense_1_2','export_expense_1_3','export_expense_1_4','export_expense_1_5', // 輸出諸掛1
		'export_expense_2_label', 'export_expense_2_1','export_expense_2_2','export_expense_2_3','export_expense_2_4','export_expense_2_5', // 輸出諸掛2
		'export_expense_3_label', 'export_expense_3_1','export_expense_3_2','export_expense_3_3','export_expense_3_4','export_expense_3_5', // 輸出諸掛3
		'fob_amount_1','fob_amount_2','fob_amount_3','fob_amount_4','fob_amount_5', // 本船渡し総額
		'fob_unit_price_1','fob_unit_price_2','fob_unit_price_3','fob_unit_price_4','fob_unit_price_5', // 本船渡し単価
		'fob_index_1','fob_index_2','fob_index_3','fob_index_4','fob_index_5', // FOB指数
		'transport_cost_1','transport_cost_2','transport_cost_3','transport_cost_4','transport_cost_5', // 海上輸送費
		'transport_charge_1_1','transport_charge_1_2','transport_charge_1_3','transport_charge_1_4','transport_charge_1_5', // その他輸送諸掛1
		'rate_insurance_coverage_1','rate_insurance_coverage_2','rate_insurance_coverage_3','rate_insurance_coverage_4','rate_insurance_coverage_5', // 保険対象増幅率
		'insurance_coverage_amount_1','insurance_coverage_amount_2','insurance_coverage_amount_3','insurance_coverage_amount_4','insurance_coverage_amount_5', // 保険適用額
		'rate_insurance_1','rate_insurance_2','rate_insurance_3','rate_insurance_4','rate_insurance_5', // 保険率
		'insurance_price_1','insurance_price_2','insurance_price_3','insurance_price_4','insurance_price_5', // 保険料
		'rate_exchange_diffrence_1','rate_exchange_diffrence_2','rate_exchange_diffrence_3','rate_exchange_diffrence_4','rate_exchange_diffrence_5', // 金利利率
		'exchange_diffrence_1','exchange_diffrence_2','exchange_diffrence_3','exchange_diffrence_4','exchange_diffrence_5', // 金利
		'export_cif_cip_amount_1','export_cif_cip_amount_2','export_cif_cip_amount_3','export_cif_cip_amount_4','export_cif_cip_amount_5', // 運賃保険料込み総額
		'export_cif_cip_unt_price_1','export_cif_cip_unt_price_2','export_cif_cip_unt_price_3','export_cif_cip_unt_price_4','eexport_cif_cip_unt_price_5', // 運賃保険料込み単価
		'export_cif_cip_index_1','export_cif_cip_index_2','export_cif_cip_index_3','export_cif_cip_index_4','export_cif_cip_index_5', // CIF指数
		
		'basis_rate','rate',// 為替レートの根拠・為替レート
		'import_cif_cip_unit_price_1','import_cif_cip_unit_price_2','import_cif_cip_unit_price_3','import_cif_cip_unit_price_4','import_cif_cip_unit_price_5', // 運賃保険料込み単価
		'import_cif_cip_amount_1','import_cif_cip_amount_2','import_cif_cip_amount_3','import_cif_cip_amount_4','import_cif_cip_amount_5', // 運賃保険料込み総額
		'duty_rate_1','duty_rate_2','duty_rate_3','duty_rate_4','duty_rate_5', // 関税率
		'duty_amount_1','duty_amount_2','duty_amount_3','duty_amount_4','duty_amount_5', // 関税
		
		'import_expense_1_label','import_expense_1_1','import_expense_1_2','import_expense_1_3','import_expense_1_4', 'import_expense_1_5',// 輸入諸掛1
		'import_expense_2_label','import_expense_2_1','import_expense_2_2','import_expense_2_3','import_expense_2_4', 'import_expense_2_5',// 輸入諸掛2
		'import_expense_3_label','import_expense_3_1','import_expense_3_2','import_expense_3_3','import_expense_3_4', 'import_expense_3_5',// 輸入諸掛3
		'import_expense_4_label','import_expense_4_1','import_expense_4_2','import_expense_4_3','import_expense_4_4', 'import_expense_4_5',// 輸入諸掛4
		'import_expense_5_label','import_expense_5_1','import_expense_5_2','import_expense_5_3','import_expense_5_4', 'import_expense_5_5',// 輸入諸掛5
		'other_expense_1_1','other_expense_1_2','other_expense_1_3','other_expense_1_4', 'other_expense_1_5',// その他国内費用1
		'other_expense_2_1','other_expense_2_2','other_expense_2_3','other_expense_2_4', 'other_expense_2_5',// その他国内費用2
		'ddp_amount_1','ddp_amount_2','ddp_amount_3','ddp_amount_4', 'ddp_amount_5',// 関税込み持ち込み渡し総額
		'ddp_unit_price_1','ddp_unit_price_2','ddp_unit_price_3','ddp_unit_price_4', 'ddp_unit_price_5',// 関税込み持ち込み渡し単価
		'ddp_unit_price_rated_1','ddp_unit_price_rated_2','ddp_unit_price_rated_3','ddp_unit_price_rated_4', 'ddp_unit_price_rated_5',// 関税込み持ち込み渡し単価（輸出国通貨）
		'ddp_index_1','ddp_index_2','ddp_index_3','ddp_index_4', 'ddp_index_5',// DDP指数
		
    );

    /**
     * バージョンリスト取得
     * @param int $parentId
     * @return boolean
     */
    public function getListByParentId($parentId)
    {
        $selectObj = $this->select();
    	$selectObj->where('parent_id = ?', $parentId);
    	$selectObj->order('version_id DESC');
    	return $selectObj->query()->fetchAll();
    }
    
    /**
     * 次のバージョンID
     * @param  int parentId
     * @return int $nextId
     */
    public function getNextVersionId($parentId)
    {
    	$selectObj = $this->select();
    	$selectObj->where('parent_id = ?', $parentId);
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
    	return $data;
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

