<?php
/**
 * class SystemLogController
 */
 
class SystemLogController extends Front_Model_Controller
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
		$this->view->mainCategoryName = 'システムログ';
		$this->view->menuCategory     = 'system-log';
		$this->view->menu             = 'system-log';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /system-log/list                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ログ履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->page = $page = $request->getParam('page', 1);
		/*
		$session = new Zend_Session_Namespace('management_supplier_2');
		
		if (empty($session->conditions)) {
			$session->conditions['page']                  = '1';
			$session->conditions['status']                = '';
			$session->conditions['keyword']               = '';
			$session->conditions['is_for_demo']           = '';
		}
			
		$page = $request->getParam('page');

		if (!empty($page)) {
			$session->conditions['page']                  = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['status']                = $request->getParam('status', '');
			$session->conditions['keyword']               = $request->getParam('keyword', '');
			$session->conditions['is_for_demo']           = $request->getParam('is_for_demo', '');
		}
		$this->view->conditions = $conditions = $session->conditions;
		*/
		
		$logTable = new Shared_Model_Data_SystemLog();
		
		$selectObj = $logTable->select();
		$selectObj->order('id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}
		
        $this->view->items = $items;
        $this->view->pager($paginator);  
        
    }
    
}

