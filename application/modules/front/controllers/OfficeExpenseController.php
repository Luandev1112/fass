<?php
/**
 * class OfficeExpenseController
 */
 
class OfficeExpenseController extends Front_Model_Controller
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
		$this->view->bodyLayoutName   = 'one_column.phtml';
		$this->view->mainCategoryName = '社内処理';
		$this->view->menuCategory     = 'office';
		
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /office-expense/transport                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 交通宿泊費精算                                             |
    +----------------------------------------------------------------------------*/
    public function transportAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'expense';
		
		/*
		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', $typeCode);
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		*/

        //$this->view->items = $items;
        //$this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /office/rebuilding                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 立替金精算                                                 |
    +----------------------------------------------------------------------------*/
    public function rebuildingAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'expense';
		
		/*
		$itemTable = new Shared_Model_Data_Item();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', $typeCode);
		$selectObj->order('frs_item.id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		*/

        //$this->view->items = $items;
        //$this->view->pager($paginator);
    }



	
}

