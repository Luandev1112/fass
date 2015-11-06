<?php
/**
 * class Cli_StockController
 *
 */
class Cli_StockController extends Cli_Model_Controller
{
    /**
     * init
     *
     * @param void
     * @return void
     * @see Front_Model_Controller::init()
     */
    public function init()
    {
        parent::init();
    }

    /**
     * debugAction 在庫数ロギングテスト
     * cmd - 00 01 * * * php /var/www/fass.fresco-co.net/Web/script/cli.php -p /stock/debug 14
     */
    public function debugAction()
    {
        $request = $this->getRequest();
		$id = $request->getParam(0);

		
		$itemTable    = new Shared_Model_Data_WarehouseItem();
		$historyTable = new Shared_Model_Data_WarehouseItemHistory();
		
        $selectObj = $itemTable->select();
        $selectObj->where('id = ?', $id);
        $each = $selectObj->query()->fetch();
		

		$targetDate = date('Y-m-d');
		
		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		$this->view->today = $zDate->get('yyyy-MM-dd');
		
		// 2日前
		$zDate->sub('2', Zend_Date::DAY);
		$twoDaysAgo = $zDate->get('yyyy-MM-dd');
		
		// 4週間前
		$zDate->sub('28', Zend_Date::DAY);
		$fourWeekAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->sub('3', Zend_Date::DAY);
		$oneMonthAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->add('3', Zend_Date::DAY);
		$zDate->sub('31', Zend_Date::DAY);
		$twoMonthAgo = $zDate->get('yyyy-MM-dd');

		echo 'oneMonthAgo: ' . $oneMonthAgo . "\n";
		echo 'twoDaysAgo: ' . $twoDaysAgo . "\n";
		
		
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		
    	$selectObj = $consumptionTable->select(array('id'));
    	$selectObj->where('frs_item_stock_consumption.warehouse_item_id = ?', $each['id']);
    	$selectObj->where('frs_item_stock_consumption.status = ?', Shared_Model_Code::STOCK_STATUS_ACTIVE);
        $selectObj->where('frs_item_stock_consumption.action_date >= ?', $oneMonthAgo);
        $selectObj->where('frs_item_stock_consumption.action_date <= ?', $twoDaysAgo);
        $selectObj->where('frs_item_stock_consumption.action_code = ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT);
        $data = $selectObj->query()->fetchAll();
		var_dump($data);exit;
		
		
		$thisMonth = $consumptionTable->getTermCount($each['id'], $oneMonthAgo, $twoDaysAgo);
		$lastMonth = $consumptionTable->getTermCount($each['id'], $twoMonthAgo, $fourWeekAgo);
		
		
		$saftyCount = round($thisMonth * $each['safety_base_month'], 1);
		$minimumCount = round($thisMonth * $each['minimum_base_month'], 1);
		
		echo 'thisMonth: ' . $thisMonth . "\n";
		echo 'lastMonth: ' . $lastMonth . "\n";
		
		echo 'saftyCount: ' . $saftyCount . "\n";
		echo 'minimumCount: ' . $minimumCount . "\n";
			
    }
    
    /**
     * logCountAction 在庫数ロギング
     * cmd - 00 01 * * * php /var/www/fass.fresco-co.net/Web/script/cli.php -p /stock/log-count
     */
    public function logCountAction()
    {
        $request = $this->getRequest();
		
		$itemTable    = new Shared_Model_Data_WarehouseItem();
		$historyTable = new Shared_Model_Data_WarehouseItemHistory();

        $selectObj = $itemTable->select();
        $selectObj->order('id ASC');
		$allItems = $selectObj->query()->fetchAll();
		
		
		$targetDate = date('Y-m-d');
		
		$zDate = new Zend_Date(NULL, NULL, 'ja_JP');
		$this->view->today = $zDate->get('yyyy-MM-dd');
		
		// 2日前
		$zDate->sub('2', Zend_Date::DAY);
		$twoDaysAgo = $zDate->get('yyyy-MM-dd');
		
		// 4週間前
		$zDate->sub('28', Zend_Date::DAY);
		$fourWeekAgo = $zDate->get('yyyy-MM-dd');
		
		// 1ヶ月前(30日前)
		$zDate->sub('2', Zend_Date::DAY);
		$oneMonthAgo = $zDate->get('yyyy-MM-dd');
		
		// 3ヶ月前()
		$zDate->sub('60', Zend_Date::DAY);
		$threeMonthAgo = $zDate->get('yyyy-MM-dd');
		
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		
		foreach ($allItems as $each) {
			$thisMonth  = $consumptionTable->getTermCount($each['id'], $oneMonthAgo, $twoDaysAgo);
			$threeMonth = $consumptionTable->getTermCount($each['id'], $threeMonthAgo, $twoDaysAgo);
			$threeMonthAverage = $threeMonth / 3;
			
			$saftyCount = round($threeMonthAverage * $each['safety_base_month'], 1);
			$minimumCount = round($threeMonthAverage * $each['minimum_base_month'], 1);
			
			
			$itemTable->updateById($each['management_group_id'], $each['id'], array(
				'shipped_last_month'      => $thisMonth,
				'shipped_3_month_average' => $threeMonthAverage,
				
				'safety_count'            => $saftyCount,                        // 注意在庫数
				'minimum_count'           => $minimumCount,                      // 警告在庫数
			));
			
			$historyTable->create(array(
		        'management_group_id'   => $each['management_group_id'],       // 管理グループID
		        'warehouse_id'          => $each['warehouse_id'],              // 倉庫ID
		        'warehouse_item_id'     => $each['id'],                        // 倉庫アイテムID
		        
		        'target_date'           => $targetDate,                        // 対象日
		
				'stock_count'           => $each['stock_count'],               // 在庫数
				'useable_count'         => $each['useable_count'],             // 引当可能在庫数
				
				'safety_base_month'     => $each['safety_base_month'],         // 注意基準期間
				'minimum_base_month'    => $each['minimum_base_month'],        // 警告基準期間
				
				'safety_count'          => $saftyCount,                        // 安全在庫数
				'minimum_count'         => $minimumCount,                      // 最低在庫数
				
                'created'               => new Zend_Db_Expr('now()'),
                'updated'               => new Zend_Db_Expr('now()'),
			));
			
		}
		
		
    }


}
