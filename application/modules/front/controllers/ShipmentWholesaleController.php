<?php
/**
 * class ShipmentWholesaleController
 */
 
class ShipmentWholesaleController extends Front_Model_Controller
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
		$this->view->menu = 'wholesale';
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment-wholesale/order-list                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 卸出荷管理                                                 |
    +----------------------------------------------------------------------------*/
    public function orderListAction()
    {
		$request = $this->getRequest();

		$page    = $request->getParam('page', '1');

		$directOrderShipmentTable = new Shared_Model_Data_DirectOrderShipment();
		$dbAdapter = $directOrderShipmentTable->getAdapter();

        $selectObj = $directOrderShipmentTable->select();
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

    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment-wholesale/sample-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷管理                                           |
    +----------------------------------------------------------------------------*/
    public function sampleListAction()
    {
		$request = $this->getRequest();

		$page    = $request->getParam('page', '1');

		
    	$sampleTable = new Shared_Model_Data_DirectOrderSample();

		$dbAdapter = $sampleTable->getAdapter();

        $selectObj = $sampleTable->select();
        $selectObj->joinLeft('frs_connection', 'frs_direct_order_sample.target_connection_id = frs_connection.id', array($sampleTable->aesdecrypt('company_name', false) . 'AS company_name'));
        $selectObj->joinLeft('frs_user', 'frs_direct_order_sample.created_user_id = frs_user.id',array($sampleTable->aesdecrypt('user_name', false) . 'AS user_name'));
		$selectObj->where('frs_direct_order_sample.type = ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT_SAMPLE);
		$selectObj->where('frs_direct_order_sample.warehouse_id = ?', $this->_warehouseSession->warehouseId);
		$selectObj->where('frs_direct_order_sample.status = ?', Shared_Model_Code::DIRECT_ORDER_STATUS_APPROVED);
		$selectObj->order('frs_direct_order_sample.id DESC');
		
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





    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment-wholesale/sample-detail                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプル出荷 - 詳細                                        |
    +----------------------------------------------------------------------------*/
    public function sampleDetailAction()
    {
		$request = $this->getRequest();	
    	$this->view->id          = $id = $request->getParam('id');
		$this->view->posTop      = $request->getParam('pos');
		$this->view->approvalId  = $approvalId = $request->getParam('approval_id', 0);
		$this->view->direct      = $direct     = $request->getParam('direct', 0);
		
		$sampleTable              = new Shared_Model_Data_DirectOrderSample();
		$connectionTable          = new Shared_Model_Data_Connection();
		$connectionBaseTable      = new Shared_Model_Data_ConnectionBase();
		$warehouseTable           = new Shared_Model_Data_Warehouse();
		$userTable                = new Shared_Model_Data_User();

		// サンプル出荷データ
		$this->view->data = $data = $sampleTable->getById($this->_adminProperty['management_group_id'], $id);

        $this->view->saveUrl          = 'javascript:void(0);';
        $this->view->saveButtonName   = '出荷済みにする';


		$this->_helper->layout->setLayout('back_menu');
		$this->view->backUrl = '/shipment-wholesale/sample-list';
		
		
		// 依頼元取引先
    	$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);


		// 納入先拠点
		if (!empty($data['base_id'])) {
			$this->view->baseData = $connectionBaseTable->getById($this->_adminProperty['management_group_id'], $data['base_id']);
		}

		// 棚卸数量単位
		$unitTypeTable = new Shared_Model_Data_StockUnitType();
		$unitTypeList = array();
		$unitTypeItems = $unitTypeTable->getList();
		foreach ($unitTypeItems as $each) {
			$unitTypeList[$each['id']] = $each;
		}
		$this->view->unitTypeList = $unitTypeList;
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /shipment-wholesale/shipped                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出荷済みにする                                             |
    +----------------------------------------------------------------------------*/
    public function shippedAction()
    {
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
				
				if (!empty($errorMessage['shipped_date']['isEmpty'])) {
					$this->sendJson(array('result' => 'NG', 'message' => '「出荷完了日」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['delivery_company']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「配送業者」を入力してください'));
                    return;
                    
                } else if (!empty($errorMessage['delivery_code']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「伝票番号」を入力してください'));
                    return;
                    
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
			    $sampleTable = new Shared_Model_Data_DirectOrderSample();
			    
			    $sampleTable->updateById($id, array(
				    'shipment_status'  => Shared_Model_Code::SHIPMENT_WHOLESALE_STATUS_SHIPPED,
				    'shipped_date'     => $success['shipped_date'],
				    'delivery_company' => $success['delivery_company'],
				    'delivery_code'    => $success['delivery_code'],
			    ));
			    
			    
			    // 在庫を落とす
			    
			    
			    
			    
			    
			    
			    $this->sendJson(array('result' => 'OK'));
				return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
		
}

