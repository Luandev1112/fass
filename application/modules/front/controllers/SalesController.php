<?php
/**
 * class SalesController
 */
 
class SalesController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '営業管理';
		$this->view->menuCategory     = 'sales';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /sales/index                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 調達管理                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
    
    
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /sales/production                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託                                               |
    +----------------------------------------------------------------------------*/
    public function productionAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'production';
    
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /sales/subcontracting                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託                                                   |
    +----------------------------------------------------------------------------*/
    public function subcontractingAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'subcontracting';
    
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /sales/fixtures                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材                                                   |
    +----------------------------------------------------------------------------*/
    public function fixturesAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'fixtures';
    
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /sales/competition                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * コンペ                                                     |
    +----------------------------------------------------------------------------*/
    public function competitionAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'competition';
    
    }
    
}

