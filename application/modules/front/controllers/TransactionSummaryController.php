<?php
/**
 * class TransactionSummaryController
 */
 
class TransactionSummaryController extends Front_Model_Controller
{
    const PER_PAGE = 100;
    
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
		$this->view->bodyLayoutName   = 'one_column.phtml';
		$this->view->mainCategoryName = '取引処理';
		$this->view->menuCategory     = 'transaction';
		$this->view->menu = 'summary';  

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-summary                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カテゴリ一覧                                               |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
		
		$dbAdapter = $categoryTable->getAdapter();

        $selectObj = $categoryTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id'], $managementGroupId);
		$selectObj->order('content_order ASC');
		
        $this->view->items = $selectObj->query()->fetchAll();
    }

    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-summary/category                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * カテゴリ集計                                               |
    +----------------------------------------------------------------------------*/
    public function categoryAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/transaction-summary';
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId  = $request->getParam('category_id', '1');
		
		$this->view->sumType  = $sumType  = $request->getParam('type', 'accrual');
		$this->view->year  = $year  = $request->getParam('year', date('Y'));
		$this->view->month = $month = $request->getParam('month', date('m'));
		
		if ($month == 'all') {
	    	$this->view->from = $from = $year . '-01-01';
	   		$this->view->to   = $to   = $year . '-12-31';
			
		} else {
	    	$this->view->from = $from = $year . '-' . $month . '-01';
	   		$this->view->to   = $to   = $year . '-' . $month . '-' . Nutex_Date::getMonthEndDay($year, $month);
	
			$zDate = new Zend_Date($year . '-' . $month . '-01', NULL, 'ja_JP');
			
			$zDate->sub('1', Zend_Date::MONTH);
			$this->view->prevUrl = '/transaction-summary/category?category_id=' . $categoryId . '&year=' . $zDate->get(Zend_Date::YEAR) . '&month=' . $zDate->get(Zend_Date::MONTH) . '&type=' . $sumType;
			
			$zDate->add('2', Zend_Date::MONTH);
			$this->view->nextUrl = '/transaction-summary/category?category_id=' . $categoryId . '&year=' . $zDate->get(Zend_Date::YEAR) . '&month=' . $zDate->get(Zend_Date::MONTH) . '&type=' . $sumType;

		}
		//var_dump('from: ' . $this->view->from);
		//var_dump('to: ' . $this->view->to);
		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
        $this->view->categoryData = $categoryData = $categoryTable->getById($this->_adminProperty['management_group_id'], $categoryId);
		
		
		$layoutTable = new Shared_Model_Data_AccountTotalingGroupLayout();
        $this->view->items = $layoutTable->getListByCategoryId($this->_adminProperty['management_group_id'], $categoryId, $categoryData['layout_version_id']);
        
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /transaction-summary/detail                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 明細                                                       |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
        
        //$this->view->saveUrl = 'javascript:void(0);';
        //$this->view->saveButtonName = '保存';
        
		$request = $this->getRequest();
		$this->view->categoryId = $categoryId  = $request->getParam('category_id');
		$this->view->id = $id  = $request->getParam('id');
		
		$this->view->sumType  = $sumType  = $request->getParam('type', 'accrual');
		
		//$this->view->backUrl = '/transaction-summary/category?category_id=' . $categoryId;


		$this->view->year  = $year  = $request->getParam('year', date('Y'));
		$this->view->month = $month = $request->getParam('month', date('m'));
		
		if ($month == 'all') {
	    	$this->view->from = $from = $year . '-01-01';
	   		$this->view->to   = $to   = $year . '-12-31';
			
		} else {
	    	$this->view->from = $from = $year . '-' . $month . '-01';
	   		$this->view->to   = $to   = $year . '-' . $month . '-' . Nutex_Date::getMonthEndDay($year, $month);
		}

		
		$categoryTable = new Shared_Model_Data_AccountTotalingGroupCategory();
        $this->view->categoryData = $categoryData = $categoryTable->getById($this->_adminProperty['management_group_id'], $categoryId);
		
		
		$layoutTable = new Shared_Model_Data_AccountTotalingGroupLayout();
        $this->view->rowData = $rowData = $layoutTable->getById($this->_adminProperty['management_group_id'], $id);
        
        // 通貨リスト
		$currencyTable = new Shared_Model_Data_Currency();
		$currencyList = array();
		$currencyItems = $currencyTable->getList($this->_adminProperty['management_group_id']);        
        foreach ($currencyItems as $each) {
        	$currencyList[$each['id']] = $each;
        	
        	$total[$each['id']] = $each;
        	$total[$each['id']]['item_count'] = 0;
        	$total[$each['id']]['total'] = 0;
        	
        	$unrecievedTotal[$each['id']] = $each;
        	$unrecievedTotal[$each['id']]['item_count'] = 0;
        	$unrecievedTotal[$each['id']]['total'] = 0;
        }
		$this->view->currencyList = $currencyList;
		
		// 会計科目
		$accountTitleTable = new Shared_Model_Data_AccountTitle();
        $acountTitleList   = array();
        $accountTitleItems = $accountTitleTable->getList($this->_adminProperty['management_group_id']);
        
        foreach ($accountTitleItems as $each) {
        	$acountTitleList[$each['id']] = $each;
        }
        $this->view->accountTitleList = $acountTitleList;
        
		// 採算コード
		$groupTable = new Shared_Model_Data_AccountTotalingGroup();
		$groupList = array();
		$groupItems = $groupTable->getAllList($this->_adminProperty['management_group_id']);
		
        foreach ($groupItems as $each) {
        	$groupList[$each['id']] = $each['title'] . '(' . $each['category_name'] . ')';
        }
		$this->view->groupList = $groupList;
    }
}

