<?php
/**
 * class DataController
 */
 
class ShipmentDataController extends Front_Model_Controller
{
    const PER_PAGE = 50;
    
    /**
     * preDispatch
     *
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        // レイアウト
		$this->view->bodyLayoutName = 'one_column.phtml';
		$this->view->mainCategoryName = '出荷・在庫管理';
		$this->view->menuCategory     = 'shipment';
		$this->view->menu = 'data';
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment-data/index                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷データ分析                                             |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$targetDate = $request->getParam('target');
		
        if (!empty($targetDate)) {
            $startDate   = date('Y-m', strtotime($targetDate)) . '-01'; // 月初日
            $targetYear  = date('Y', strtotime($targetDate));
            $targetMonth = date('m', strtotime($targetDate));
        } else {
            $startDate   = date('Y-m-' . '01'); // 月初日
            $targetYear  = date('Y');
            $targetMonth = date('m');
        }
		
		$this->view->startDate = $startDate;
		
		// 月末日を取得
        $endDate = date($targetYear . '-' . $targetMonth . '-' . Nutex_Date::getMonthEndDay($targetYear, $targetMonth));
        
        
        // 月末または今日までの日数
        $dayCount = 0;
        if ($startDate === date('Y-m-01')) {
	        $endZDate = new Zend_Date(NULL, NULL, 'ja_JP');
	        $zDate = new Zend_Date($startDate, NULL, 'ja_JP');
	        $dayCount++;
	        while ($zDate->isEarlier($endZDate)) {
		        $dayCount++;
		        $zDate->add('1', Zend_Date::DAY);
	        }
	        
        } else {
	        $endZDate = new Zend_Date($endDate, NULL, 'ja_JP');
	        $zDate = new Zend_Date($startDate, NULL, 'ja_JP');
	        $dayCount++;
	        while ($zDate->isEarlier($endZDate)) {
		        $dayCount++;
		        $zDate->add('1', Zend_Date::DAY);
	        }
        }
        
        
		$orderTable = new Shared_Model_Data_Order();
		$orderItemTable = new Shared_Model_Data_OrderItem();
		
		$this->view->monthlyOrderCount     = $orderTable->getOrderCountWithTerm($this->_warehouseSession->warehouseId, $startDate, $endDate);
		$this->view->monthlyOrderItemCount = $orderItemTable->getOrderItemCountWithTerm($this->_warehouseSession->warehouseId, $startDate, $endDate);
		$this->view->monthlyOrderPrice     = $orderTable->getOrderPriceWithTerm($this->_warehouseSession->warehouseId, $startDate, $endDate);
		
		$this->view->dailyOrderCount     = floor($this->view->monthlyOrderCount / $dayCount);
		$this->view->dailyOrderItemCount = floor($this->view->monthlyOrderItemCount / $dayCount);
		$this->view->dailyOrderPrice     = floor($this->view->monthlyOrderPrice / $dayCount);
		
		
		$this->view->dayCount = $dayCount;
		
		
        // 期間データ初期化
        $period = $this->_createMonthPeriod($startDate, $endDate, array('count'));
        

        $zendDateToday = new Zend_Date(NULL, NULL, 'ja_JP');
        $dateCountForMonth = 0;
        foreach ($period as $eachDate => &$eachCount) {
            $eachCount['count'] = $orderTable->getOrderCountWithTerm($this->_warehouseSession->warehouseId, $eachDate, $eachDate);
            $eachCount['total'] = $orderTable->getOrderPriceWithTerm($this->_warehouseSession->warehouseId, $eachDate, $eachDate);
            $zendDate = new Zend_Date($eachDate, NULL, 'ja_JP');
            if ($zendDate->isEarlier($zendDateToday)) {
                $dateCountForMonth++;
            }
        }
       
        $this->view->dataList = $period;
        
    }

	
}

