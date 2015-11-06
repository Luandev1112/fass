<?php
/**
 * class ManagementController
 * モール営業管理
 */
 
class ManagementController extends Front_Model_Controller
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
		$this->view->mainCategoryName = 'モール営業管理';
		$this->view->menuCategory     = 'management';
		$this->view->menu             = 'management';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');	
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /index                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗                                                   |
    +----------------------------------------------------------------------------*/
    public function indexAction()
    {
		$request = $this->getRequest();
	
		$session = new Zend_Session_Namespace('connection_progress_condition2');
		
		if (!empty($session->indexPos)) {
			$this->view->posTop = $session->indexPos;
			$session->indexPos = 0;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['sheet_id']            = $request->getParam('sheet_id', '');
			$session->conditions['user_department_id']  = $request->getParam('user_department_id', '');
			$session->conditions['user_name']           = $request->getParam('user_name', '');
			$session->conditions['user_id']             = $request->getParam('user_id', '');
			$session->conditions['area_name']           = $request->getParam('area_name', '');
			$session->conditions['area_id']             = $request->getParam('area_id', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			
			$session->conditions['start_type']          = $request->getParam('start_type', '');
			$session->conditions['start_tag_name']      = $request->getParam('start_tag_name', '');
			$session->conditions['start_tag_id']        = $request->getParam('start_tag_id', '');
			$session->conditions['importance']          = $request->getParam('importance', '');
			$session->conditions['task']                = $request->getParam('task', '');
			
			$session->conditions['possibility']         = $request->getParam('possibility', array());
			$session->conditions['progress']            = $request->getParam('progress', array());
			$session->conditions['after']               = $request->getParam('after', array());
			
			$session->conditions['item1_name']          = $request->getParam('item1_name', '');
			$session->conditions['item1_id']            = $request->getParam('item1_id', '');
			$session->conditions['item2_name']          = $request->getParam('item2_name', '');
			$session->conditions['item2_id']            = $request->getParam('item2_id', '');
			$session->conditions['item3_name']          = $request->getParam('item3_name', '');
			$session->conditions['item3_id']            = $request->getParam('item3_id', '');
			$session->conditions['item4_name']          = $request->getParam('item4_name', '');
			$session->conditions['item4_id']            = $request->getParam('item4_id', '');
			$session->conditions['item5_name']          = $request->getParam('item5_name', '');
			$session->conditions['item5_id']            = $request->getParam('item5_id', '');
			
			$session->conditions['industry1_name']      = $request->getParam('industry1_name', '');
			$session->conditions['industry1_id']        = $request->getParam('industry1_id', '');
			$session->conditions['industry2_name']      = $request->getParam('industry2_name', '');
			$session->conditions['industry2_id']        = $request->getParam('industry2_id', '');
			$session->conditions['industry3_name']      = $request->getParam('industry3_name', '');
			$session->conditions['industry3_id']        = $request->getParam('industry3_id', '');
			$session->conditions['industry4_name']      = $request->getParam('industry4_name', '');
			$session->conditions['industry4_id']        = $request->getParam('industry4_id', '');
			$session->conditions['industry5_name']      = $request->getParam('industry5_name', '');;
			$session->conditions['industry5_id']        = $request->getParam('industry5_id', '');
			
			$session->conditions['list_order']          = $request->getParam('list_order', '1');
			
			$session->conditions['page'] = 1;
		} else if (empty($session->conditions)) {
			$session->conditions['sheet_id']            = '';
			$session->conditions['user_department_id']  = '';
			$session->conditions['user_name']           = '';
			$session->conditions['user_id']             = '';
			$session->conditions['area_name']           = '';
			$session->conditions['area_id']             = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
			$session->conditions['start_type']          = '';
			$session->conditions['start_tag_name']      = '';
			$session->conditions['start_tag_id']        = '';
			$session->conditions['importance']          = '';
			$session->conditions['task']                = '';
			
			$session->conditions['possibility']         = array();
			$session->conditions['progress']            = array();
			$session->conditions['after']               = array();
			
			$session->conditions['item1_name']          = '';
			$session->conditions['item1_id']            = '';
			$session->conditions['item2_name']          = '';
			$session->conditions['item2_id']            = '';
			$session->conditions['item3_name']          = '';
			$session->conditions['item3_id']            = '';
			$session->conditions['item4_name']          = '';
			$session->conditions['item4_id']            = '';
			$session->conditions['item5_name']          = '';
			$session->conditions['item5_id']            = '';
			
			$session->conditions['industry1_name']      = '';
			$session->conditions['industry1_id']        = '';
			$session->conditions['industry2_name']      = '';
			$session->conditions['industry2_id']        = '';
			$session->conditions['industry3_name']      = '';
			$session->conditions['industry3_id']        = '';
			$session->conditions['industry4_name']      = '';
			$session->conditions['industry4_id']        = '';
			$session->conditions['industry5_name']      = '';
			$session->conditions['industry5_id']        = '';
			
			$session->conditions['list_order']          = '1';
		}
		
		
		
		$this->view->conditions = $conditions = $session->conditions;
		
		$itemTable = new Shared_Model_Data_ConnectionProgressItem();
		$userTable = new Shared_Model_Data_User();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
    	$selectObj->joinLeft('frs_connection', 'frs_connection_progress_item.connection_id = frs_connection.id', array($itemTable->aesdecrypt('company_name', false) . 'AS company_name', 'industry_types'));
    	$selectObj->joinLeft('frs_connection_progress_start_tag', 'frs_connection_progress_item.start_tag_id = frs_connection_progress_start_tag.id', array('tag_name'));
		$selectObj->joinLeft('frs_connection_area', 'frs_connection_progress_item.area_id = frs_connection_area.id', array($itemTable->aesdecrypt('area_name', false) . 'AS area_name'));
		$selectObj->joinLeft('frs_user', 'frs_connection_progress_item.user_id = frs_user.id', array($itemTable->aesdecrypt('user_name', false) . 'AS user_name', 'user_department_id'));
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array($itemTable->aesdecrypt('department_name', false) . 'AS department_name'));
		
		// グループID
		$selectObj->where('frs_connection_progress_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->where('frs_connection_progress_item.status = ?', 1);
		
        if (!empty($session->conditions['sheet_id'])) {
	        $selectObj->where('sheet_id = ?', $session->conditions['sheet_id']);
        }

        if (!empty($session->conditions['user_department_id'])) {
	        $selectObj->where('frs_user.user_department_id = ?', $session->conditions['user_department_id']);
        }
        
        if (!empty($session->conditions['user_id'])) {
	        $selectObj->where('frs_connection_progress_item.user_id = ?', $session->conditions['user_id']);
	        
	        // 担当者名
	        $this->view->userData = $userTable->getById($session->conditions['user_id']);
        }

        if (!empty($session->conditions['area_id'])) {
	        $selectObj->where('frs_connection_progress_item.area_id = ?', $session->conditions['area_id']);
        }

        if (!empty($session->conditions['connection_id'])) {
	        $selectObj->where('frs_connection_progress_item.connection_id = ?', $session->conditions['connection_id']);
        }

        if (!empty($session->conditions['start_type'])) {
	        $selectObj->where('frs_connection_progress_item.start_type = ?', $session->conditions['start_type']);
        }
          
        if (!empty($session->conditions['start_tag_id'])) {
	        $selectObj->where('frs_connection_progress_item.start_tag_id = ?', $session->conditions['start_tag_id']);
        } 
          
        if (!empty($session->conditions['importance'])) {
	        $selectObj->where('frs_connection_progress_item.importance = ?', $session->conditions['importance']);
        }
        
        if (!empty($session->conditions['task'])) {
	        if ($session->conditions['task'] === '1') {
	        	$selectObj->where('frs_connection_progress_item.has_no_task = 0');
	        } else if ($session->conditions['task'] === '0') {
		        $selectObj->where('frs_connection_progress_item.has_no_task = 1');
		    }
        }
		
		$itemIdWnereString = '';
		for ($count = 1; $count <= 5; $count++) {
	        if (!empty($session->conditions['item' . $count . '_id'])) {
		        
        		if ($itemIdWnereString !== '') {
        			$itemIdWnereString .= ' OR ';
        		}
				
        		$itemIdWnereString .= $dbAdapter->quoteInto('`item_ids` LIKE ?', '%"' . $session->conditions['item' . $count . '_id'] .'"%');
	        }
        }
        
        if ($itemIdWnereString !== '') {
        	$selectObj->where($itemIdWnereString);
        }
        

        // 可能性
        $possibilityWhereString = '';
        if (!empty($session->conditions['possibility'])) {
	        foreach($session->conditions['possibility'] as $eachPossibility) {
        		if ($possibilityWhereString !== '') {
        			$possibilityWhereString .= ' OR ';
        		}
				
        		$possibilityWhereString .= $dbAdapter->quoteInto('`possibility` = ?', $eachPossibility);
	        }
	        
	        if ($possibilityWhereString !== '') {
	        	$selectObj->where($possibilityWhereString);
	        }
        }
        if ($possibilityWhereString !== '') {
        	$selectObj->where($possibilityWhereString);
        } 
        
		// 業種
        $industoryTypeTable = new Shared_Model_Data_IndustryType();
        
		$industyWhereString = '';
		for ($count = 1; $count <= 5; $count++) {
	        if (!empty($session->conditions['industry' . $count . '_id'])) {
		        
		        if (strpos($session->conditions['industry' . $count . '_id'], 'C') !== false){
			        // 業種カテゴリ 対象の業種を取得
			        $categoryTypeList = $industoryTypeTable->getListByCategoryId(str_replace('C', '', $session->conditions['industry' . $count . '_id']));
			        
			        foreach ($categoryTypeList as $eachType) {
		        		if ($industyWhereString !== '') {
		        			$industyWhereString .= ' OR ';
		        		}
		        		$industyWhereString .= $dbAdapter->quoteInto('`frs_connection`.`industry_types` LIKE ?', '%"' . $eachType['id'] .'"%');
			        }
			        
		        } else {
	        		if ($industyWhereString !== '') {
	        			$industyWhereString .= ' OR ';
	        		}
					
	        		$industyWhereString .= $dbAdapter->quoteInto('`frs_connection`.`industry_types` LIKE ?', '%"' . $session->conditions['industry' . $count . '_id'] .'"%');
				}
	        }
        }

        if ($industyWhereString !== '') {
        	$selectObj->where($industyWhereString);
        } 
        
        // 実績
        $progressWhereString = '';
        if (!empty($session->conditions['progress'])) {
	        foreach($session->conditions['progress'] as $eachProgress) {
        		if ($progressWhereString !== '') {
        			$progressWhereString .= ' OR ';
        		}
				
        		$progressWhereString .= $dbAdapter->quoteInto('`progress` LIKE ?', '%"' . $eachProgress .'"%');
	        }
	        
	        if ($progressWhereString !== '') {
	        	$selectObj->where($progressWhereString);
	        }
        }
		
		// 見積後ヒア
        $afterWhereString = '';
        if (!empty($session->conditions['after'])) {
	        foreach($session->conditions['after'] as $eachAfter) {
        		if ($afterWhereString !== '') {
        			$afterWhereString .= ' OR ';
        		}
				
        		$afterWhereString .= $dbAdapter->quoteInto('`after` LIKE ?', '%"' . $eachAfter .'"%');
	        }
	        
	        if ($afterWhereString !== '') {
	        	$selectObj->where($afterWhereString);
	        }
        }
        
        if (!empty($session->conditions['list_order'])) {
	        if ($session->conditions['list_order'] === '1') {
		        $selectObj->order('possibility ASC');
		        $selectObj->order('updated DESC');
		        
	        } else if ($session->conditions['list_order'] === '2') {
		        $selectObj->order('possibility ASC');
		        $selectObj->order('importance ASC');
		        
		    } else if ($session->conditions['list_order'] === '3') { 
		        $selectObj->order('importance ASC');
		        $selectObj->order('possibility ASC');
		        
	        } else if ($session->conditions['list_order'] === '4') {
		        $selectObj->order('area_id ASC');
		        $selectObj->order('possibility ASC');
		        $selectObj->order('importance ASC');
		        
	        }
        }
        
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        

		// シート
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
        $sheetItems = $sheetTable->getList($this->_adminProperty['management_group_id']);
        
        $sheetList = array();
        foreach ($sheetItems as $each) {
	        $sheetList[$each['id']] = $each;
        }
        
        $this->view->sheetList = $sheetList;

        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);
		
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressActiveList = $progressTable->getActiveList($this->_adminProperty['management_group_id']);
		
        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);
        
		// 業種
    	$industryTypeTable = new Shared_Model_Data_IndustryType();
    	$industryTypeItems = $industryTypeTable->getAllList();
    	$industryTypeList = array();
    	foreach ($industryTypeItems as $each) {
	    	$industryTypeList[$each['id']] = $each;
    	}
    	$this->view->industryList = $industryTypeList;

    	
		// 業種カテゴリ
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
    	
    	// 部署
    	$departmentTable  = new Shared_Model_Data_UserDepartment();
    	$this->view->deparmentList = $departmentTable->getListExceptAccountOffice($this->_adminProperty['management_group_id']);
    	

	}

	/*----------------------------------------------------------------------------+
    |  action_URL    * /management/goosa-open                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function goosaOpenAction()
    {
		$this->view->menu = 'goosa-open';

		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$session = new Zend_Session_Namespace('connection_list');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']          = '';
			$session->conditions['type']            = '';
			$session->conditions['relation_type']   = '';
			$session->conditions['sales_relation']  = '';
			$session->conditions['industry_type']   = '';
			$session->conditions['gs']              = '';
			$session->conditions['gb']              = '';
			$session->conditions['keyword']         = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['status']          = $request->getParam('status', '');
			$session->conditions['type']            = $request->getParam('type', '');
			$session->conditions['relation_type']   = $request->getParam('relation_type', '');
			$session->conditions['sales_relation']  = $request->getParam('sales_relation', '');
			$session->conditions['industry_type']   = $request->getParam('industry_type', '');
			$session->conditions['gs']              = $request->getParam('gs', '');
			$session->conditions['gb']              = $request->getParam('gb', '');
			$session->conditions['keyword']         = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;

		
		$connectionTable = new Shared_Model_Data_Connection();
		$dbAdapter = $connectionTable->getAdapter();
        $selectObj = $connectionTable->select(array('id', 'status', 'updated', 'display_id', 'company_name', 'relation_types', 'gs_supplier_id', 'gs_buyer_id', 'gsc_supplier_id', 'type'));
        $selectObj->joinLeft('frs_connection_staff', 'frs_connection.id = frs_connection_staff.connection_id', NULL);
		
		// グループID
		//$selectObj->where('frs_connection.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if ($session->conditions['status'] != '') {
        	$selectObj->where('frs_connection.status = ?', $session->conditions['status']);
        }
        
        if ($session->conditions['type'] != '') {
        	$selectObj->where('frs_connection.type = ?', $session->conditions['type']);
        }

        if ($session->conditions['relation_type'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`relation_types`  LIKE ?', '%"' . $session->conditions['relation_type'] .'"%');
            $selectObj->where($relationTypeString);
        }

        if ($session->conditions['sales_relation'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`sales_relations`  LIKE ?', '%"' . $session->conditions['sales_relation'] .'"%');
            $selectObj->where($relationTypeString);
        }
        
        if ($session->conditions['industry_type'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`industry_types`  LIKE ?', '%"' . $session->conditions['industry_type'] .'"%');
            $selectObj->where($relationTypeString);
        }

        if ($session->conditions['gs'] != '') {
            $selectObj->where('gs_supplier_id != 0');
        }
        
        if ($session->conditions['gb'] != '') {
            $selectObj->where('gs_buyer_id != 0');
        }
    
        if ($session->conditions['country'] != '') {
        	$selectObj->where('frs_connection.country = ?', $session->conditions['country']);
        }
        
        if (!empty($session->conditions['keyword'])) {
        	$keywordString = '';
        	$columns = array(
        		'display_id', 'company_name', 'company_name_kana', 'description', 'head_office_postal_code', 'head_office_prefecture', 'head_office_city',
        		'head_office_city', 'head_office_address', 'head_office_building', 'staff_name',
        	);
        	
        	foreach ($columns as $each) {
        		if ($keywordString !== '') {
        			$keywordString .= ' OR ';
        		}

        		if ($connectionTable->isCryptField($each) || $each == 'staff_name') {   
        			$keyword = $dbAdapter->quote('%' . $session->conditions['keyword'] . '%');
        			$keywordString .= $connectionTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordString .= $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $session->conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where($keywordString);
        }
        
        $selectObj->group('frs_connection.id');
		$selectObj->order('frs_connection.updated DESC');
		// var_dump($selectObj->__toString());exit;
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();
				
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
    }
   
    /*----------------------------------------------------------------------------+
    |  action_URL    * /management/goosa-public                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function goosaPublicAction()
    {
    	$this->view->menu = 'goosa-public';
   		$request = $this->getRequest();
	
		$session = new Zend_Session_Namespace('connection_progress_condition2');
		
		if (!empty($session->indexPos)) {
			$this->view->posTop = $session->indexPos;
			$session->indexPos = 0;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['sheet_id']            = $request->getParam('sheet_id', '');
			$session->conditions['user_department_id']  = $request->getParam('user_department_id', '');
			$session->conditions['user_name']           = $request->getParam('user_name', '');
			$session->conditions['user_id']             = $request->getParam('user_id', '');
			$session->conditions['area_name']           = $request->getParam('area_name', '');
			$session->conditions['area_id']             = $request->getParam('area_id', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			
			$session->conditions['start_type']          = $request->getParam('start_type', '');
			$session->conditions['start_tag_name']      = $request->getParam('start_tag_name', '');
			$session->conditions['start_tag_id']        = $request->getParam('start_tag_id', '');
			$session->conditions['importance']          = $request->getParam('importance', '');
			$session->conditions['task']                = $request->getParam('task', '');
			
			$session->conditions['possibility']         = $request->getParam('possibility', array());
			$session->conditions['progress']            = $request->getParam('progress', array());
			$session->conditions['after']               = $request->getParam('after', array());
			
			$session->conditions['item1_name']          = $request->getParam('item1_name', '');
			$session->conditions['item1_id']            = $request->getParam('item1_id', '');
			$session->conditions['item2_name']          = $request->getParam('item2_name', '');
			$session->conditions['item2_id']            = $request->getParam('item2_id', '');
			$session->conditions['item3_name']          = $request->getParam('item3_name', '');
			$session->conditions['item3_id']            = $request->getParam('item3_id', '');
			$session->conditions['item4_name']          = $request->getParam('item4_name', '');
			$session->conditions['item4_id']            = $request->getParam('item4_id', '');
			$session->conditions['item5_name']          = $request->getParam('item5_name', '');
			$session->conditions['item5_id']            = $request->getParam('item5_id', '');
			
			$session->conditions['industry1_name']      = $request->getParam('industry1_name', '');
			$session->conditions['industry1_id']        = $request->getParam('industry1_id', '');
			$session->conditions['industry2_name']      = $request->getParam('industry2_name', '');
			$session->conditions['industry2_id']        = $request->getParam('industry2_id', '');
			$session->conditions['industry3_name']      = $request->getParam('industry3_name', '');
			$session->conditions['industry3_id']        = $request->getParam('industry3_id', '');
			$session->conditions['industry4_name']      = $request->getParam('industry4_name', '');
			$session->conditions['industry4_id']        = $request->getParam('industry4_id', '');
			$session->conditions['industry5_name']      = $request->getParam('industry5_name', '');;
			$session->conditions['industry5_id']        = $request->getParam('industry5_id', '');
			
			$session->conditions['list_order']          = $request->getParam('list_order', '1');
			
			$session->conditions['page'] = 1;
		} else if (empty($session->conditions)) {
			$session->conditions['sheet_id']            = '';
			$session->conditions['user_department_id']  = '';
			$session->conditions['user_name']           = '';
			$session->conditions['user_id']             = '';
			$session->conditions['area_name']           = '';
			$session->conditions['area_id']             = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
			$session->conditions['start_type']          = '';
			$session->conditions['start_tag_name']      = '';
			$session->conditions['start_tag_id']        = '';
			$session->conditions['importance']          = '';
			$session->conditions['task']                = '';
			
			$session->conditions['possibility']         = array();
			$session->conditions['progress']            = array();
			$session->conditions['after']               = array();
			
			$session->conditions['item1_name']          = '';
			$session->conditions['item1_id']            = '';
			$session->conditions['item2_name']          = '';
			$session->conditions['item2_id']            = '';
			$session->conditions['item3_name']          = '';
			$session->conditions['item3_id']            = '';
			$session->conditions['item4_name']          = '';
			$session->conditions['item4_id']            = '';
			$session->conditions['item5_name']          = '';
			$session->conditions['item5_id']            = '';
			
			$session->conditions['industry1_name']      = '';
			$session->conditions['industry1_id']        = '';
			$session->conditions['industry2_name']      = '';
			$session->conditions['industry2_id']        = '';
			$session->conditions['industry3_name']      = '';
			$session->conditions['industry3_id']        = '';
			$session->conditions['industry4_name']      = '';
			$session->conditions['industry4_id']        = '';
			$session->conditions['industry5_name']      = '';
			$session->conditions['industry5_id']        = '';
			
			$session->conditions['list_order']          = '1';
		}
		
		
		
		$this->view->conditions = $conditions = $session->conditions;
		
		$itemTable = new Shared_Model_Data_ConnectionProgressItem();
		$userTable = new Shared_Model_Data_User();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
    	$selectObj->joinLeft('frs_connection', 'frs_connection_progress_item.connection_id = frs_connection.id', array($itemTable->aesdecrypt('company_name', false) . 'AS company_name', 'industry_types'));
    	$selectObj->joinLeft('frs_connection_progress_start_tag', 'frs_connection_progress_item.start_tag_id = frs_connection_progress_start_tag.id', array('tag_name'));
		$selectObj->joinLeft('frs_connection_area', 'frs_connection_progress_item.area_id = frs_connection_area.id', array($itemTable->aesdecrypt('area_name', false) . 'AS area_name'));
		$selectObj->joinLeft('frs_user', 'frs_connection_progress_item.user_id = frs_user.id', array($itemTable->aesdecrypt('user_name', false) . 'AS user_name', 'user_department_id'));
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array($itemTable->aesdecrypt('department_name', false) . 'AS department_name'));
		
		// グループID
		$selectObj->where('frs_connection_progress_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->where('frs_connection_progress_item.status = ?', 1);
		
        if (!empty($session->conditions['sheet_id'])) {
	        $selectObj->where('sheet_id = ?', $session->conditions['sheet_id']);
        }

        if (!empty($session->conditions['user_department_id'])) {
	        $selectObj->where('frs_user.user_department_id = ?', $session->conditions['user_department_id']);
        }
        
        if (!empty($session->conditions['user_id'])) {
	        $selectObj->where('frs_connection_progress_item.user_id = ?', $session->conditions['user_id']);
	        
	        // 担当者名
	        $this->view->userData = $userTable->getById($session->conditions['user_id']);
        }

        if (!empty($session->conditions['area_id'])) {
	        $selectObj->where('frs_connection_progress_item.area_id = ?', $session->conditions['area_id']);
        }

        if (!empty($session->conditions['connection_id'])) {
	        $selectObj->where('frs_connection_progress_item.connection_id = ?', $session->conditions['connection_id']);
        }

        if (!empty($session->conditions['start_type'])) {
	        $selectObj->where('frs_connection_progress_item.start_type = ?', $session->conditions['start_type']);
        }
          
        if (!empty($session->conditions['start_tag_id'])) {
	        $selectObj->where('frs_connection_progress_item.start_tag_id = ?', $session->conditions['start_tag_id']);
        } 
          
        if (!empty($session->conditions['importance'])) {
	        $selectObj->where('frs_connection_progress_item.importance = ?', $session->conditions['importance']);
        }
        
        if (!empty($session->conditions['task'])) {
	        if ($session->conditions['task'] === '1') {
	        	$selectObj->where('frs_connection_progress_item.has_no_task = 0');
	        } else if ($session->conditions['task'] === '0') {
		        $selectObj->where('frs_connection_progress_item.has_no_task = 1');
		    }
        }
		
		$itemIdWnereString = '';
		for ($count = 1; $count <= 5; $count++) {
	        if (!empty($session->conditions['item' . $count . '_id'])) {
		        
        		if ($itemIdWnereString !== '') {
        			$itemIdWnereString .= ' OR ';
        		}
				
        		$itemIdWnereString .= $dbAdapter->quoteInto('`item_ids` LIKE ?', '%"' . $session->conditions['item' . $count . '_id'] .'"%');
	        }
        }
        
        if ($itemIdWnereString !== '') {
        	$selectObj->where($itemIdWnereString);
        }
        

        // 可能性
        $possibilityWhereString = '';
        if (!empty($session->conditions['possibility'])) {
	        foreach($session->conditions['possibility'] as $eachPossibility) {
        		if ($possibilityWhereString !== '') {
        			$possibilityWhereString .= ' OR ';
        		}
				
        		$possibilityWhereString .= $dbAdapter->quoteInto('`possibility` = ?', $eachPossibility);
	        }
	        
	        if ($possibilityWhereString !== '') {
	        	$selectObj->where($possibilityWhereString);
	        }
        }
        if ($possibilityWhereString !== '') {
        	$selectObj->where($possibilityWhereString);
        } 
        
		// 業種
        $industoryTypeTable = new Shared_Model_Data_IndustryType();
        
		$industyWhereString = '';
		for ($count = 1; $count <= 5; $count++) {
	        if (!empty($session->conditions['industry' . $count . '_id'])) {
		        
		        if (strpos($session->conditions['industry' . $count . '_id'], 'C') !== false){
			        // 業種カテゴリ 対象の業種を取得
			        $categoryTypeList = $industoryTypeTable->getListByCategoryId(str_replace('C', '', $session->conditions['industry' . $count . '_id']));
			        
			        foreach ($categoryTypeList as $eachType) {
		        		if ($industyWhereString !== '') {
		        			$industyWhereString .= ' OR ';
		        		}
		        		$industyWhereString .= $dbAdapter->quoteInto('`frs_connection`.`industry_types` LIKE ?', '%"' . $eachType['id'] .'"%');
			        }
			        
		        } else {
	        		if ($industyWhereString !== '') {
	        			$industyWhereString .= ' OR ';
	        		}
					
	        		$industyWhereString .= $dbAdapter->quoteInto('`frs_connection`.`industry_types` LIKE ?', '%"' . $session->conditions['industry' . $count . '_id'] .'"%');
				}
	        }
        }

        if ($industyWhereString !== '') {
        	$selectObj->where($industyWhereString);
        } 
        
        // 実績
        $progressWhereString = '';
        if (!empty($session->conditions['progress'])) {
	        foreach($session->conditions['progress'] as $eachProgress) {
        		if ($progressWhereString !== '') {
        			$progressWhereString .= ' OR ';
        		}
				
        		$progressWhereString .= $dbAdapter->quoteInto('`progress` LIKE ?', '%"' . $eachProgress .'"%');
	        }
	        
	        if ($progressWhereString !== '') {
	        	$selectObj->where($progressWhereString);
	        }
        }
		
		// 見積後ヒア
        $afterWhereString = '';
        if (!empty($session->conditions['after'])) {
	        foreach($session->conditions['after'] as $eachAfter) {
        		if ($afterWhereString !== '') {
        			$afterWhereString .= ' OR ';
        		}
				
        		$afterWhereString .= $dbAdapter->quoteInto('`after` LIKE ?', '%"' . $eachAfter .'"%');
	        }
	        
	        if ($afterWhereString !== '') {
	        	$selectObj->where($afterWhereString);
	        }
        }
        
        if (!empty($session->conditions['list_order'])) {
	        if ($session->conditions['list_order'] === '1') {
		        $selectObj->order('possibility ASC');
		        $selectObj->order('updated DESC');
		        
	        } else if ($session->conditions['list_order'] === '2') {
		        $selectObj->order('possibility ASC');
		        $selectObj->order('importance ASC');
		        
		    } else if ($session->conditions['list_order'] === '3') { 
		        $selectObj->order('importance ASC');
		        $selectObj->order('possibility ASC');
		        
	        } else if ($session->conditions['list_order'] === '4') {
		        $selectObj->order('area_id ASC');
		        $selectObj->order('possibility ASC');
		        $selectObj->order('importance ASC');
		        
	        }
        }
        
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        

		// シート
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
        $sheetItems = $sheetTable->getList($this->_adminProperty['management_group_id']);
        
        $sheetList = array();
        foreach ($sheetItems as $each) {
	        $sheetList[$each['id']] = $each;
        }
        
        $this->view->sheetList = $sheetList;

        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);
		
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressActiveList = $progressTable->getActiveList($this->_adminProperty['management_group_id']);
		
        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);
        
		// 業種
    	$industryTypeTable = new Shared_Model_Data_IndustryType();
    	$industryTypeItems = $industryTypeTable->getAllList();
    	$industryTypeList = array();
    	foreach ($industryTypeItems as $each) {
	    	$industryTypeList[$each['id']] = $each;
    	}
    	$this->view->industryList = $industryTypeList;

    	
		// 業種カテゴリ
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
    	
    	// 部署
    	$departmentTable  = new Shared_Model_Data_UserDepartment();
    	$this->view->deparmentList = $departmentTable->getListExceptAccountOffice($this->_adminProperty['management_group_id']);
    	
    }
   
	/*----------------------------------------------------------------------------+
    |  action_URL    * /management/goosca-open                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function gooscaOpenAction()
    {
    	$this->view->menu = 'gooska-open';
    	$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		$session = new Zend_Session_Namespace('connection_list');

		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
		
		if (empty($session->conditions)) {
			$session->conditions['page']                = '1';
			$session->conditions['status']          = '';
			$session->conditions['type']            = '';
			$session->conditions['relation_type']   = '';
			$session->conditions['sales_relation']  = '';
			$session->conditions['industry_type']   = '';
			$session->conditions['gs']              = '';
			$session->conditions['gb']              = '';
			$session->conditions['keyword']         = '';
		}
			
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']                = $request->getParam('page');
		}

		$search = $request->getParam('search', '');
		// 検索条件
		if (!empty($search)) {
			$session->conditions['status']          = $request->getParam('status', '');
			$session->conditions['type']            = $request->getParam('type', '');
			$session->conditions['relation_type']   = $request->getParam('relation_type', '');
			$session->conditions['sales_relation']  = $request->getParam('sales_relation', '');
			$session->conditions['industry_type']   = $request->getParam('industry_type', '');
			$session->conditions['gs']              = $request->getParam('gs', '');
			$session->conditions['gb']              = $request->getParam('gb', '');
			$session->conditions['keyword']         = $request->getParam('keyword', '');
		}
		$this->view->conditions = $conditions = $session->conditions;

		
		$connectionTable = new Shared_Model_Data_Connection();
		$dbAdapter = $connectionTable->getAdapter();
        $selectObj = $connectionTable->select(array('id', 'status', 'updated', 'display_id', 'company_name', 'relation_types', 'gs_supplier_id', 'gs_buyer_id', 'gsc_supplier_id', 'type'));
        $selectObj->joinLeft('frs_connection_staff', 'frs_connection.id = frs_connection_staff.connection_id', NULL);
		
		// グループID
		//$selectObj->where('frs_connection.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
        if ($session->conditions['status'] != '') {
        	$selectObj->where('frs_connection.status = ?', $session->conditions['status']);
        }
        
        if ($session->conditions['type'] != '') {
        	$selectObj->where('frs_connection.type = ?', $session->conditions['type']);
        }

        if ($session->conditions['relation_type'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`relation_types`  LIKE ?', '%"' . $session->conditions['relation_type'] .'"%');
            $selectObj->where($relationTypeString);
        }

        if ($session->conditions['sales_relation'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`sales_relations`  LIKE ?', '%"' . $session->conditions['sales_relation'] .'"%');
            $selectObj->where($relationTypeString);
        }
        
        if ($session->conditions['industry_type'] != '') {
            $relationTypeString = $dbAdapter->quoteInto('`industry_types`  LIKE ?', '%"' . $session->conditions['industry_type'] .'"%');
            $selectObj->where($relationTypeString);
        }

        if ($session->conditions['gs'] != '') {
            $selectObj->where('gs_supplier_id != 0');
        }
        
        if ($session->conditions['gb'] != '') {
            $selectObj->where('gs_buyer_id != 0');
        }
    
        if ($session->conditions['country'] != '') {
        	$selectObj->where('frs_connection.country = ?', $session->conditions['country']);
        }
        
        if (!empty($session->conditions['keyword'])) {
        	$keywordString = '';
        	$columns = array(
        		'display_id', 'company_name', 'company_name_kana', 'description', 'head_office_postal_code', 'head_office_prefecture', 'head_office_city',
        		'head_office_city', 'head_office_address', 'head_office_building', 'staff_name',
        	);
        	
        	foreach ($columns as $each) {
        		if ($keywordString !== '') {
        			$keywordString .= ' OR ';
        		}

        		if ($connectionTable->isCryptField($each) || $each == 'staff_name') {   
        			$keyword = $dbAdapter->quote('%' . $session->conditions['keyword'] . '%');
        			$keywordString .= $connectionTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordString .= $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $session->conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where($keywordString);
        }
        
        $selectObj->group('frs_connection.id');
		$selectObj->order('frs_connection.updated DESC');
		//var_dump($selectObj->__toString());exit;
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        
		$countryTable = new Shared_Model_Data_Country();
		$this->view->countryList = $countryTable->getList();
				
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
   	
    }
   
    /*----------------------------------------------------------------------------+
    |  action_URL    * /management/goosca-public                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function gooscaPublicAction()
    {
    	$this->view->menu = 'gooska-public';
    		$request = $this->getRequest();
	
		$session = new Zend_Session_Namespace('connection_progress_condition2');
		
		if (!empty($session->indexPos)) {
			$this->view->posTop = $session->indexPos;
			$session->indexPos = 0;
		}
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}

		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['sheet_id']            = $request->getParam('sheet_id', '');
			$session->conditions['user_department_id']  = $request->getParam('user_department_id', '');
			$session->conditions['user_name']           = $request->getParam('user_name', '');
			$session->conditions['user_id']             = $request->getParam('user_id', '');
			$session->conditions['area_name']           = $request->getParam('area_name', '');
			$session->conditions['area_id']             = $request->getParam('area_id', '');
			$session->conditions['connection_name']     = $request->getParam('connection_name', '');
			$session->conditions['connection_id']       = $request->getParam('connection_id', '');
			
			$session->conditions['start_type']          = $request->getParam('start_type', '');
			$session->conditions['start_tag_name']      = $request->getParam('start_tag_name', '');
			$session->conditions['start_tag_id']        = $request->getParam('start_tag_id', '');
			$session->conditions['importance']          = $request->getParam('importance', '');
			$session->conditions['task']                = $request->getParam('task', '');
			
			$session->conditions['possibility']         = $request->getParam('possibility', array());
			$session->conditions['progress']            = $request->getParam('progress', array());
			$session->conditions['after']               = $request->getParam('after', array());
			
			$session->conditions['item1_name']          = $request->getParam('item1_name', '');
			$session->conditions['item1_id']            = $request->getParam('item1_id', '');
			$session->conditions['item2_name']          = $request->getParam('item2_name', '');
			$session->conditions['item2_id']            = $request->getParam('item2_id', '');
			$session->conditions['item3_name']          = $request->getParam('item3_name', '');
			$session->conditions['item3_id']            = $request->getParam('item3_id', '');
			$session->conditions['item4_name']          = $request->getParam('item4_name', '');
			$session->conditions['item4_id']            = $request->getParam('item4_id', '');
			$session->conditions['item5_name']          = $request->getParam('item5_name', '');
			$session->conditions['item5_id']            = $request->getParam('item5_id', '');
			
			$session->conditions['industry1_name']      = $request->getParam('industry1_name', '');
			$session->conditions['industry1_id']        = $request->getParam('industry1_id', '');
			$session->conditions['industry2_name']      = $request->getParam('industry2_name', '');
			$session->conditions['industry2_id']        = $request->getParam('industry2_id', '');
			$session->conditions['industry3_name']      = $request->getParam('industry3_name', '');
			$session->conditions['industry3_id']        = $request->getParam('industry3_id', '');
			$session->conditions['industry4_name']      = $request->getParam('industry4_name', '');
			$session->conditions['industry4_id']        = $request->getParam('industry4_id', '');
			$session->conditions['industry5_name']      = $request->getParam('industry5_name', '');;
			$session->conditions['industry5_id']        = $request->getParam('industry5_id', '');
			
			$session->conditions['list_order']          = $request->getParam('list_order', '1');
			
			$session->conditions['page'] = 1;
		} else if (empty($session->conditions)) {
			$session->conditions['sheet_id']            = '';
			$session->conditions['user_department_id']  = '';
			$session->conditions['user_name']           = '';
			$session->conditions['user_id']             = '';
			$session->conditions['area_name']           = '';
			$session->conditions['area_id']             = '';
			$session->conditions['connection_name']     = '';
			$session->conditions['connection_id']       = '';
			
			$session->conditions['start_type']          = '';
			$session->conditions['start_tag_name']      = '';
			$session->conditions['start_tag_id']        = '';
			$session->conditions['importance']          = '';
			$session->conditions['task']                = '';
			
			$session->conditions['possibility']         = array();
			$session->conditions['progress']            = array();
			$session->conditions['after']               = array();
			
			$session->conditions['item1_name']          = '';
			$session->conditions['item1_id']            = '';
			$session->conditions['item2_name']          = '';
			$session->conditions['item2_id']            = '';
			$session->conditions['item3_name']          = '';
			$session->conditions['item3_id']            = '';
			$session->conditions['item4_name']          = '';
			$session->conditions['item4_id']            = '';
			$session->conditions['item5_name']          = '';
			$session->conditions['item5_id']            = '';
			
			$session->conditions['industry1_name']      = '';
			$session->conditions['industry1_id']        = '';
			$session->conditions['industry2_name']      = '';
			$session->conditions['industry2_id']        = '';
			$session->conditions['industry3_name']      = '';
			$session->conditions['industry3_id']        = '';
			$session->conditions['industry4_name']      = '';
			$session->conditions['industry4_id']        = '';
			$session->conditions['industry5_name']      = '';
			$session->conditions['industry5_id']        = '';
			
			$session->conditions['list_order']          = '1';
		}
		
		
		
		$this->view->conditions = $conditions = $session->conditions;
		
		$itemTable = new Shared_Model_Data_ConnectionProgressItem();
		$userTable = new Shared_Model_Data_User();
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
    	$selectObj->joinLeft('frs_connection', 'frs_connection_progress_item.connection_id = frs_connection.id', array($itemTable->aesdecrypt('company_name', false) . 'AS company_name', 'industry_types'));
    	$selectObj->joinLeft('frs_connection_progress_start_tag', 'frs_connection_progress_item.start_tag_id = frs_connection_progress_start_tag.id', array('tag_name'));
		$selectObj->joinLeft('frs_connection_area', 'frs_connection_progress_item.area_id = frs_connection_area.id', array($itemTable->aesdecrypt('area_name', false) . 'AS area_name'));
		$selectObj->joinLeft('frs_user', 'frs_connection_progress_item.user_id = frs_user.id', array($itemTable->aesdecrypt('user_name', false) . 'AS user_name', 'user_department_id'));
		$selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array($itemTable->aesdecrypt('department_name', false) . 'AS department_name'));
		
		// グループID
		$selectObj->where('frs_connection_progress_item.management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$selectObj->where('frs_connection_progress_item.status = ?', 1);
		
        if (!empty($session->conditions['sheet_id'])) {
	        $selectObj->where('sheet_id = ?', $session->conditions['sheet_id']);
        }

        if (!empty($session->conditions['user_department_id'])) {
	        $selectObj->where('frs_user.user_department_id = ?', $session->conditions['user_department_id']);
        }
        
        if (!empty($session->conditions['user_id'])) {
	        $selectObj->where('frs_connection_progress_item.user_id = ?', $session->conditions['user_id']);
	        
	        // 担当者名
	        $this->view->userData = $userTable->getById($session->conditions['user_id']);
        }

        if (!empty($session->conditions['area_id'])) {
	        $selectObj->where('frs_connection_progress_item.area_id = ?', $session->conditions['area_id']);
        }

        if (!empty($session->conditions['connection_id'])) {
	        $selectObj->where('frs_connection_progress_item.connection_id = ?', $session->conditions['connection_id']);
        }

        if (!empty($session->conditions['start_type'])) {
	        $selectObj->where('frs_connection_progress_item.start_type = ?', $session->conditions['start_type']);
        }
          
        if (!empty($session->conditions['start_tag_id'])) {
	        $selectObj->where('frs_connection_progress_item.start_tag_id = ?', $session->conditions['start_tag_id']);
        } 
          
        if (!empty($session->conditions['importance'])) {
	        $selectObj->where('frs_connection_progress_item.importance = ?', $session->conditions['importance']);
        }
        
        if (!empty($session->conditions['task'])) {
	        if ($session->conditions['task'] === '1') {
	        	$selectObj->where('frs_connection_progress_item.has_no_task = 0');
	        } else if ($session->conditions['task'] === '0') {
		        $selectObj->where('frs_connection_progress_item.has_no_task = 1');
		    }
        }
		
		$itemIdWnereString = '';
		for ($count = 1; $count <= 5; $count++) {
	        if (!empty($session->conditions['item' . $count . '_id'])) {
		        
        		if ($itemIdWnereString !== '') {
        			$itemIdWnereString .= ' OR ';
        		}
				
        		$itemIdWnereString .= $dbAdapter->quoteInto('`item_ids` LIKE ?', '%"' . $session->conditions['item' . $count . '_id'] .'"%');
	        }
        }
        
        if ($itemIdWnereString !== '') {
        	$selectObj->where($itemIdWnereString);
        }
        

        // 可能性
        $possibilityWhereString = '';
        if (!empty($session->conditions['possibility'])) {
	        foreach($session->conditions['possibility'] as $eachPossibility) {
        		if ($possibilityWhereString !== '') {
        			$possibilityWhereString .= ' OR ';
        		}
				
        		$possibilityWhereString .= $dbAdapter->quoteInto('`possibility` = ?', $eachPossibility);
	        }
	        
	        if ($possibilityWhereString !== '') {
	        	$selectObj->where($possibilityWhereString);
	        }
        }
        if ($possibilityWhereString !== '') {
        	$selectObj->where($possibilityWhereString);
        } 
        
		// 業種
        $industoryTypeTable = new Shared_Model_Data_IndustryType();
        
		$industyWhereString = '';
		for ($count = 1; $count <= 5; $count++) {
	        if (!empty($session->conditions['industry' . $count . '_id'])) {
		        
		        if (strpos($session->conditions['industry' . $count . '_id'], 'C') !== false){
			        // 業種カテゴリ 対象の業種を取得
			        $categoryTypeList = $industoryTypeTable->getListByCategoryId(str_replace('C', '', $session->conditions['industry' . $count . '_id']));
			        
			        foreach ($categoryTypeList as $eachType) {
		        		if ($industyWhereString !== '') {
		        			$industyWhereString .= ' OR ';
		        		}
		        		$industyWhereString .= $dbAdapter->quoteInto('`frs_connection`.`industry_types` LIKE ?', '%"' . $eachType['id'] .'"%');
			        }
			        
		        } else {
	        		if ($industyWhereString !== '') {
	        			$industyWhereString .= ' OR ';
	        		}
					
	        		$industyWhereString .= $dbAdapter->quoteInto('`frs_connection`.`industry_types` LIKE ?', '%"' . $session->conditions['industry' . $count . '_id'] .'"%');
				}
	        }
        }

        if ($industyWhereString !== '') {
        	$selectObj->where($industyWhereString);
        } 
        
        // 実績
        $progressWhereString = '';
        if (!empty($session->conditions['progress'])) {
	        foreach($session->conditions['progress'] as $eachProgress) {
        		if ($progressWhereString !== '') {
        			$progressWhereString .= ' OR ';
        		}
				
        		$progressWhereString .= $dbAdapter->quoteInto('`progress` LIKE ?', '%"' . $eachProgress .'"%');
	        }
	        
	        if ($progressWhereString !== '') {
	        	$selectObj->where($progressWhereString);
	        }
        }
		
		// 見積後ヒア
        $afterWhereString = '';
        if (!empty($session->conditions['after'])) {
	        foreach($session->conditions['after'] as $eachAfter) {
        		if ($afterWhereString !== '') {
        			$afterWhereString .= ' OR ';
        		}
				
        		$afterWhereString .= $dbAdapter->quoteInto('`after` LIKE ?', '%"' . $eachAfter .'"%');
	        }
	        
	        if ($afterWhereString !== '') {
	        	$selectObj->where($afterWhereString);
	        }
        }
        
        if (!empty($session->conditions['list_order'])) {
	        if ($session->conditions['list_order'] === '1') {
		        $selectObj->order('possibility ASC');
		        $selectObj->order('updated DESC');
		        
	        } else if ($session->conditions['list_order'] === '2') {
		        $selectObj->order('possibility ASC');
		        $selectObj->order('importance ASC');
		        
		    } else if ($session->conditions['list_order'] === '3') { 
		        $selectObj->order('importance ASC');
		        $selectObj->order('possibility ASC');
		        
	        } else if ($session->conditions['list_order'] === '4') {
		        $selectObj->order('area_id ASC');
		        $selectObj->order('possibility ASC');
		        $selectObj->order('importance ASC');
		        
	        }
        }
        
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
        

		// シート
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
        $sheetItems = $sheetTable->getList($this->_adminProperty['management_group_id']);
        
        $sheetList = array();
        foreach ($sheetItems as $each) {
	        $sheetList[$each['id']] = $each;
        }
        
        $this->view->sheetList = $sheetList;

        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);
		
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressActiveList = $progressTable->getActiveList($this->_adminProperty['management_group_id']);
		
        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);
        
		// 業種
    	$industryTypeTable = new Shared_Model_Data_IndustryType();
    	$industryTypeItems = $industryTypeTable->getAllList();
    	$industryTypeList = array();
    	foreach ($industryTypeItems as $each) {
	    	$industryTypeList[$each['id']] = $each;
    	}
    	$this->view->industryList = $industryTypeList;

    	
		// 業種カテゴリ
    	$industryCategoryTable = new Shared_Model_Data_IndustryCategory();
    	$industryTypeTable     = new Shared_Model_Data_IndustryType();
    	$categoryList = $industryCategoryTable->getList();
    	
    	foreach ($categoryList as &$each) {
    		$each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
    	}
    	$this->view->industryCategoryList = $categoryList;
    	
    	// 部署
    	$departmentTable  = new Shared_Model_Data_UserDepartment();
    	$this->view->deparmentList = $departmentTable->getListExceptAccountOffice($this->_adminProperty['management_group_id']);
   	
    }
   
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/delete                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 破棄(管理権限あり)(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		$id         = $request->getParam('target_id');
		
		if (!empty($this->_adminProperty['allow_delete_row_data'])) {
			$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
		}
		
		// POST送信時
		if ($request->isPost()) {
			$itemTable = new Shared_Model_Data_ConnectionProgressItem();

			try {
				$itemTable->getAdapter()->beginTransaction();
				
				$itemTable->updateById($this->_adminProperty['management_group_id'], $id, array(
					'status' => 0,
				));
			
                // commit
                $itemTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $itemTable->getAdapter()->rollBack();
                throw new Zend_Exception('/connection-progress/delete transaction faied: ' . $e);
            }
            
		    $this->sendJson(array('result' => 'OK'));
	    	return;	
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/add                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		
		// シート
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
        $this->view->sheetList = $sheetTable->getList($this->_adminProperty['management_group_id']);
        
        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);

        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/add-post                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 新規案件登録(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
   
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['sheet_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「管理シート」を選択してください'));
                    return;
                } else if (!empty($errorMessage['user_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「自社担当」を選択してください'));
                    return;
                } else if (!empty($errorMessage['connection_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['start_type']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「発足区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['start_tag_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「発足名称」を選択してください'));
                    return;
                } else if (!empty($errorMessage['importance']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「重要度」を選択してください'));
                    return;
                } else if (!empty($errorMessage['possibility']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「可能性」を選択してください'));
                    return;   
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable = new Shared_Model_Data_ConnectionProgressItem();
				
				$itemIds = array();
				
				$itemList = explode(',', $success['item_list']);
	            foreach ($itemList as $eachItem) {
	            	$itemId = $request->getParam($eachItem . '_item_id');
                    if (!empty($itemId)) {
	                    $itemIds[] = $itemId;
                    }
                }
			
				$data = array(
			        'management_group_id'    => $this->_adminProperty['management_group_id'],            // 管理グループID
					'status'                 => 1,                            // ステータス
					
					'sheet_id'               => $success['sheet_id'],         // シートID
					
					'user_id'                => $success['user_id'],          // 担当者ユーザーID
					
			        'connection_id'          => $success['connection_id'],    // 取引先ID
			        'staff_id'               => $success['staff_id'],         // 取引先担当者ID
					'area_id'                => $success['area_id'],          // 営業エリア
					
			
					'start_type'             => $success['start_type'],       // 発足区分
					'start_tag_id'           => $success['start_tag_id'],     // 発足名称タグID
			
					'proposition_id'         => 0,                            // 案件ID
					'proposition_category'   => 0,                            // 案件分野
			
					'item_ids'              => serialize($itemIds),          // 案件対象商品
			
					'importance'             => $success['importance'],       // 重要度
					'possibility'            => $success['possibility'],      // 可能性
					
					'progress'               => serialize($success['progress']), // 実績
					'after'                  => serialize($success['after']),    // 見積後ヒア
					
					'has_no_task'            => 0,                            // 宿題なし
					'task'                   => $success['task'],             // 宿題
					'details'                => $success['details'],          // 詳細
					'other_memo'             => $success['other_memo'],       // その他展望等
					
					'created_user_id'        => $this->_adminProperty['id'],  // 初期登録者ユーザーID
					'last_update_user_id'    => $this->_adminProperty['id'],  // 最終更新者ユーザーID

	                'created'                => new Zend_Db_Expr('now()'),
	                'updated'                => new Zend_Db_Expr('now()'),
				);

				if (!empty($success['has_no_task'])) {
					$data['has_no_task'] = 1;
				} else {
					if (empty($success['task'])) {
	                	$this->sendJson(array('result' => 'NG', 'message' => '「宿題」を入力するかまたは「宿題なし」をチェックしてください'));
	                    return;
					}
				}

				try {
					$itemTable->getAdapter()->beginTransaction();
					
					$data['display_id'] = $itemTable->getNextDisplayId();

					$itemTable->create($data);
					
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection-progress/add-post transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/detail                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 詳細                                            |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
	    $this->_helper->layout->setLayout('back_menu');
	    
		$request = $this->getRequest();
		$this->view->id     = $id     = $request->getParam('id');
		$this->view->from   = $from   = $request->getParam('from');
		$this->view->direct = $direct = $request->getParam('direct');
		
		$indexPos = $request->getParam('index_pos');

		if (empty($direct)) {
			$this->view->backUrl = '/connection-progress';
		}
		
		if (!empty($indexPos)) {
			$session = new Zend_Session_Namespace('connection_progress');
			$session->indexPos = $indexPos;
		}

		$itemTable = new Shared_Model_Data_ConnectionProgressItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);

		// シート
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
        $sheetItems = $sheetTable->getList($this->_adminProperty['management_group_id']);
        
        $sheetList = array();
        foreach ($sheetItems as $each) {
	        $sheetList[$each['id']] = $each;
        }
        
        $this->view->sheetList = $sheetList;
        
        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);

        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);  
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/update                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 更新(Ajax)                                        |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request    = $this->getRequest();
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

                if (!empty($errorMessage['sheet_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「管理シート」を選択してください'));
                    return;
                } else if (!empty($errorMessage['user_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「自社担当」を選択してください'));
                    return;
                } else if (!empty($errorMessage['start_type']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「発足区分」を選択してください'));
                    return;
                } else if (!empty($errorMessage['start_tag_id']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「発足名称」を選択してください'));
                    return;
                } else if (!empty($errorMessage['importance']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「重要度」を選択してください'));
                    return;
                } else if (!empty($errorMessage['possibility']['isEmpty'])) {
                	$this->sendJson(array('result' => 'NG', 'message' => '「可能性」を選択してください'));
                    return;   
                }

			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable = new Shared_Model_Data_ConnectionProgressItem();

				$itemIds = array();
				
				$itemList = explode(',', $success['item_list']);
	            foreach ($itemList as $eachItem) {
	            	$itemId = $request->getParam($eachItem . '_item_id');
                    if (!empty($itemId)) {
	                    $itemIds[] = $itemId;
                    }
                }
			
				$data = array(
					'sheet_id'               => $success['sheet_id'],         // シートID
					
					'user_id'                => $success['user_id'],          // 自社担当者

					'staff_id'               => $success['staff_id'],         // 取引先担当者
					
					'area_id'                => $success['area_id'],          // 営業エリア
					
					'start_type'             => $success['start_type'],       // 発足区分
					'start_tag_id'           => $success['start_tag_id'],     // 発足名称タグID
			
					'item_ids'              => serialize($itemIds),          // 案件対象商品
			
					'importance'             => $success['importance'],       // 重要度
					'possibility'            => $success['possibility'],      // 可能性
					
					'progress'               => serialize($success['progress']), // 実績
					'after'                  => serialize($success['after']),    // 見積後ヒア
					
					'has_no_task'            => 0,                            // 宿題なし
					'task'                   => $success['task'],             // 宿題
					'details'                => $success['details'],          // 詳細
					'other_memo'             => $success['other_memo'],       // その他展望等
					
				
					'last_update_user_id'    => $this->_adminProperty['id'],  // 最終更新者ユーザーID
				);
				
				if (!empty($success['has_no_task'])) {
					$data['has_no_task'] = 1;
				} else {
					if (empty($success['task'])) {
	                	$this->sendJson(array('result' => 'NG', 'message' => '「宿題」を入力するかまたは「宿題なし」をチェックしてください'));
	                    return;
					}
				}

				try {
					$itemTable->getAdapter()->beginTransaction();
					
					$itemTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
					
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/connection-progress/add-post transaction faied: ' . $e);
	                
	            }
	            
			    $this->sendJson(array('result' => 'OK'));
		    	return;
            }
		}

	    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    } 


    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/record                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 案件詳細 - 議事録                                          |
    +----------------------------------------------------------------------------*/
    public function recordAction()
    {
	    $this->_helper->layout->setLayout('back_menu');

		$request = $this->getRequest();
		$this->view->id     = $id     = $request->getParam('id');
		$this->view->from   = $from   = $request->getParam('from');
		$this->view->direct = $direct = $request->getParam('direct');
		$page = $request->getParam('page');

		$session = new Zend_Session_Namespace('connection_progress_record');
		
		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}
        

		if (empty($direct)) {
			$this->view->backUrl = '/connection-progress';
		}
		
		if (!empty($indexPos)) {
			$session = new Zend_Session_Namespace('connection_progress');
			$session->indexPos = $indexPos;
		}

		$itemTable = new Shared_Model_Data_ConnectionProgressItem();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		
		// 議事録
		$recordTable = new Shared_Model_Data_ConnectionRecord();

        $selectObj = $recordTable->select();
        $selectObj->joinLeft('frs_user', 'frs_connection_record.created_user_id = frs_user.id', array($recordTable->aesdecrypt('user_name', false) . 'AS user_name'));
        $selectObj->joinLeft('frs_user_department', 'frs_user.user_department_id = frs_user_department.id', array($recordTable->aesdecrypt('department_name', false) . 'AS department_name'));
        $selectObj->where('progress_item_id = ?', $data['id']);
        $selectObj->order('id DESC');
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(10);
		$paginator->setCurrentPageNumber($session->conditions['page']);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/sheet-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - シート一覧                                      |
    +----------------------------------------------------------------------------*/
    public function sheetListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection-progress';
        
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
        $this->view->items = $sheetTable->getList($this->_adminProperty['management_group_id']);

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/sheet-update-order                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * シート並び順更新(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function sheetUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/sheet-update-order failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();

			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$sheetTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/sheet-detail                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - シート・編集                                    |
    +----------------------------------------------------------------------------*/
    public function sheetDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'sheet_name'              => '',      // シート名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $sheetTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/connection-progress/sheet-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/sheet-update                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - シート・編集(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function sheetUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$sheetTable = new Shared_Model_Data_ConnectionProgressSheet();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/sheet-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['sheet_name'])) {
                    $message = '「シート名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				if (empty($id)) {
					// 新規登録
					if ($sheetTable->isExistSheetName($this->_adminProperty['management_group_id'], $success['sheet_name'], $id)) {
					    $this->sendJson(array('result' => 'NG', 'message' => 'その「シート名」は既に登録されています'));
			    		return;
					}

					// 新規登録
					$contentOrder = $sheetTable->getNextContentOrder($this->_adminProperty['management_group_id']);

					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス
						
				        'sheet_name'          => $success['sheet_name'],         // シート名
				        'content_order'       => $contentOrder,                 // 並び順
				        
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$sheetTable->create($data);
					
				} else {
					// 編集
					$data = array(
						'sheet_name'            => $success['sheet_name'],      // シート名
					);

					$sheetTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    } 
     
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/area-list                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * エリア一覧                                                 |
    +----------------------------------------------------------------------------*/
    public function areaListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection-progress';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		
		$areaTable = new Shared_Model_Data_ConnectionArea();
        $this->view->items = $areaTable->getList($this->_adminProperty['management_group_id']);

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/area-list-select                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * エリア 一覧(ポップアップ用)                                |
    +----------------------------------------------------------------------------*/
    public function areaListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$conditions = array();
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$areaTable = new Shared_Model_Data_ConnectionArea();
		
		$dbAdapter = $areaTable->getAdapter();

        $selectObj = $areaTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        if (!empty($conditions['keyword'])) {
        	$likeString1 = $dbAdapter->quoteInto('`area_name` LIKE ?', '%' . $conditions['keyword'] .'%');
        	
        	$selectObj->where($likeString1 . 'OR ' . $likeString2);
		}
        
		$selectObj->order('content_order ASC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        
        $url = 'javascript:pageArea($page);';
        $this->view->pager($paginator, $url);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/area-update-order                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * エリア並び順更新(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function areaUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$areaTable = new Shared_Model_Data_ConnectionArea();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/area-update-order failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();

			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$areaTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/area-detail                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - エリア・編集                                    |
    +----------------------------------------------------------------------------*/
    public function areaDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$areaTable = new Shared_Model_Data_ConnectionArea();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'area_name'               => '',      // エリア名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $areaTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/connection-progress/area-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/area-update                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - エリア・編集(Ajax)                              |
    +----------------------------------------------------------------------------*/
    public function areaUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$areaTable = new Shared_Model_Data_ConnectionArea();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/area-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['area_name'])) {
                    $message = '「エリア名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				if (empty($id)) {
					// 新規登録
					if ($areaTable->isExistAreaName($this->_adminProperty['management_group_id'], $success['area_name'], $id)) {
					    $this->sendJson(array('result' => 'NG', 'message' => 'その「エリア名」は既に登録されています'));
			    		return;
					}

					// 新規登録
					$contentOrder = $areaTable->getNextContentOrder($this->_adminProperty['management_group_id']);

					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス
						
				        'area_name'           => $success['area_name'],         // エリア名
				        'content_order'       => $contentOrder,                 // 並び順
				        
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

					$areaTable->create($data);
					
				} else {
					// 編集
					$data = array(
						'area_name'            => $success['area_name'],      // エリア名
					);

					$areaTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    } 
    
    
     

    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/progress-list                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 進捗定義                                                   |
    +----------------------------------------------------------------------------*/
    public function progressListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection-progress';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$progressTable = new Shared_Model_Data_ConnectionProgress();
		
		$dbAdapter = $progressTable->getAdapter();

        $selectObj = $progressTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/progress-update-order                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 進捗定義並び順更新(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function progressUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$progressTable = new Shared_Model_Data_ConnectionProgress();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/progress-update-order failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();

			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$progressTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/progress-detail                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 進捗定義・編集                                             |
    +----------------------------------------------------------------------------*/
    public function progressDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$progressTable = new Shared_Model_Data_ConnectionProgress();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'title'            => '',                    // 科目名
				'status'           => 0,
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $progressTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/connection-progress/progress-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/progress-update                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 進捗定義・編集(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function progressUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$progressTable = new Shared_Model_Data_ConnectionProgress();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/progress-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {

				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                $message = '';
                if (isset($errorMessage['title'])) {
                    $message = '「項目名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($progressTable->isExistTitle($this->_adminProperty['management_group_id'], $success['title'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「項目名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $progressTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'title'               => $success['title'],             // 項目名
						'status'              => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
						'content_order'       => $contentOrder,                 // 並び順
					);
					
					if (!empty($success['status'])) {
						$data['status'] = Shared_Model_Code::CONTENT_STATUS_ACTIVE;
					}

					$progressTable->create($data);
				} else {
					// 編集
					$data = array(
						'title'               => $success['title'],             // 項目名
						'status'              => Shared_Model_Code::CONTENT_STATUS_INACTIVE,
					);
					
					if (!empty($success['status'])) {
						$data['status'] = Shared_Model_Code::CONTENT_STATUS_ACTIVE;
					}

					$progressTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }



    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/after-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積後ヒア定義                                             |
    +----------------------------------------------------------------------------*/
    public function afterListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection-progress';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');

		$afterTable = new Shared_Model_Data_ConnectionProgressAfter();
		
		$dbAdapter = $afterTable->getAdapter();

        $selectObj = $afterTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		$selectObj->order('content_order ASC');
		$this->view->items = $selectObj->query()->fetchAll();
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/progress-update-order                 |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積後ヒア定義 並び順更新(Ajax)                            |
    +----------------------------------------------------------------------------*/
    public function afterUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$afterTable = new Shared_Model_Data_ConnectionProgressAfter();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/after-update-order failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();

			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$afterTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/progress-detail                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積後ヒア定義 編集                                        |
    +----------------------------------------------------------------------------*/
    public function afterDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$afterTable = new Shared_Model_Data_ConnectionProgressAfter();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(
				'title'            => '',                    // 科目名
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $afterTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/connection-progress/after-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }

    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/after-update                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 見積後ヒア定義 編集(Ajax)                                  |
    +----------------------------------------------------------------------------*/
    public function afterUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$afterTable = new Shared_Model_Data_ConnectionProgressAfter();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/after-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {

				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                $message = '';
                if (isset($errorMessage['title'])) {
                    $message = '「項目名」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {

				if ($afterTable->isExistTitle($this->_adminProperty['management_group_id'], $success['title'], $id)) {
				    $this->sendJson(array('result' => 'NG', 'message' => 'その「項目名」は既に登録されています'));
		    		return;
				}

				if (empty($id)) {
					// 新規登録
					$contentOrder = $afterTable->getNextContentOrder($this->_adminProperty['management_group_id']);
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'title'               => $success['title'],             // 項目名
						'content_order'       => $contentOrder,                 // 並び順
					);

					$afterTable->create($data);
				} else {
					// 編集
					$data = array(
						'title'               => $success['title'],             // 項目名
					);

					$afterTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    
    
    
    
    
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/start-list                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - タグ一覧                                        |
    +----------------------------------------------------------------------------*/
    public function startListAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = '/connection-progress';
        
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		$type = $request->getParam('type');
		$session = new Zend_Session_Namespace('connection_progress_start_tags');
		
		if (!empty($type)) {
			$session->conditions['type'] = $type;
		}

		$page = $request->getParam('page');
		if (!empty($page)) {
			$session->conditions['page']      = $request->getParam('page');
		} else if (empty($session->conditions)) {
			$session->conditions['page']      = '1';
		}
		
		$search = $request->getParam('search', '');
		if (!empty($search)) {
			$session->conditions['keyword']   = $request->getParam('keyword');
			$session->conditions['page'] = 1;
			
		} else if (empty($session->conditions['keyword'])) {
			$session->conditions['keyword'] = '';

		}
		
		$this->view->conditions = $session->conditions;
		
		
		$tagTable = new Shared_Model_Data_ConnectionProgressStartTag();
		
		$dbAdapter = $tagTable->getAdapter();

        $selectObj = $tagTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
        

        
        if ($session->conditions['type'] === 'general') {
	        $selectObj->where('is_general = 1');
	        $selectObj->order('content_order ASC');
	        $this->view->items = $selectObj->query()->fetchAll();
        
        } else {
	        if (!empty($session->conditions['keyword'])) {
	        	$likeString1 = $dbAdapter->quoteInto('`tag_name` LIKE ?', '%' . $session->conditions['keyword'] .'%');
	        	$likeString2 = $dbAdapter->quoteInto('`search_words_list` LIKE ?', '%"' . $session->conditions['keyword'] .'"%');
	        	
	        	$selectObj->where($likeString1 . 'OR ' . $likeString2);
			}
	        
	        $selectObj->where('is_general = 0');
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

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/start-update-order                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 進捗定義並び順更新(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function startUpdateOrderAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_master'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$tagTable = new Shared_Model_Data_ConnectionProgressStartTag();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/start-update-order failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();

			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				// テーブル中身
				if (!empty($success['item_list'])) {
					$itemList = explode(',', $success['item_list']);

					$count = 1;
	            	
		            foreach ($itemList as $eachId) {
						$tagTable->updateById($this->_adminProperty['management_group_id'], $eachId, array(
							'content_order' => $count,
						));
						$count++;
					}
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/start-list-select                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - タグ一覧(ポップアップ用)                        |
    +----------------------------------------------------------------------------*/
    public function startListSelectAction()
    {
    	$this->_helper->layout->setLayout('blank');
    	
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');

		$conditions = array();
		$conditions['type']           = $type = $request->getParam('type', 'general');
		$conditions['keyword']        = $request->getParam('keyword', '');
		$this->view->conditions       = $conditions;
		
		$tagTable = new Shared_Model_Data_ConnectionProgressStartTag();
		
		$dbAdapter = $tagTable->getAdapter();

        $selectObj = $tagTable->select();
        $selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
        
        if ($type === 'general') {
	        $selectObj->where('is_general = 1');
	        
	        if (!empty($conditions['keyword'])) {
	        	$likeString1 = $dbAdapter->quoteInto('`tag_name` LIKE ?', '%' . $conditions['keyword'] .'%');
	        	$likeString2 = $dbAdapter->quoteInto('`search_words_list`  LIKE ?', '%"' . $conditions['keyword'] .'"%');
	        	
	        	$selectObj->where($likeString1 . 'OR ' . $likeString2);
			}
			
	        $selectObj->order('content_order ASC');
	        $this->view->items = $selectObj->query()->fetchAll();
	        
        } else {   
	        $selectObj->where('is_general = 0');
	        
	        if (!empty($conditions['keyword'])) {
	        	$likeString1 = $dbAdapter->quoteInto('`tag_name` LIKE ?', '%' . $conditions['keyword'] .'%');
	        	$likeString2 = $dbAdapter->quoteInto('`search_words_list`  LIKE ?', '%"' . $conditions['keyword'] .'"%');
	        	
	        	$selectObj->where($likeString1 . 'OR ' . $likeString2);
			}
			
			$selectObj->order('id DESC');
			
	        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
	        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
			$paginator->setCurrentPageNumber($page);
			
			$items = array();
	        
			foreach ($paginator->getCurrentItems() as $eachItem) {
				$items[] = $eachItem; 
			}
	
	        $this->view->items = $items;
	        
	        $url = 'javascript:pageTag($page);';
	        $this->view->pager($paginator, $url);
		}
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/start-detail                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - タグ・編集                                      |
    +----------------------------------------------------------------------------*/
    public function startDetailAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_tag'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		
		$tagTable = new Shared_Model_Data_ConnectionProgressStartTag();
		
		if (empty($id)) {
			// 新規登録
			$this->view->saveButtonName = '登録';

			$this->view->data = array(		
		        'tag_name'                => '',      // タグ名称
		        'is_general'              => 0,       // 汎用タグ
		        'search_words_list'       => '',      // 検索ワードリスト
		        'descripition'            => '',      // 詳細
			);

		} else {
			// 編集
        	$this->view->saveButtonName = '保存';
        	
        	$data = $tagTable->getById($this->_adminProperty['management_group_id'], $id);

	        if (empty($data)) {
				throw new Zend_Exception('/connection-progress/start-detail filed to fetch account title data');
			}

        	$this->view->data = $data;
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /connection-progress/start-update                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - タグ・編集(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function startUpdateAction()
    {
    	if (empty($this->_adminProperty['allow_connection_progress_tag'])) {
			throw new Zend_Controller_Action_Exception('アクセス権限がありません', 404);
		}
		
	    $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$tagTable = new Shared_Model_Data_ConnectionProgressStartTag();
				
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			if (empty($config)) {
				throw new Zend_Exception('/connection-progress/start-update failed to load config');
			}
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                $message = '';
                if (isset($errorMessage['tag_name'])) {
                    $message = '「タグ名称」を入力してください';
                }

			    $this->sendJson(array('result' => 'NG', 'message' => $message));
	    		return;
	    		
			} else {
				$itemList = array();
				
				if (!empty($success['item_list'])) {
					$itemIdList = explode(',', $success['item_list']);
	            	
		            foreach ($itemIdList as $eachId) {
						$title = $request->getParam($eachId . '_title');
		                
		                if (!empty($title)) {
			                $itemList[] = $title;
		                }

		            }
				}	
				
				if (empty($id)) {
					// 新規登録
					
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => Shared_Model_Code::CONTENT_STATUS_ACTIVE,  // ステータス
						
				        'tag_name'            => $success['tag_name'],      // タグ名称
				        
				        'is_general'          => 0,                         // 汎用タグ
				        
				        'search_words_list'   => serialize($itemList),      // 検索ワードリスト
				        'descripition'        => '',      // 詳細

		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);
					
					
					$session = new Zend_Session_Namespace('connection_progress_start_tags');
					
					if (!empty($success['is_general'])) {
						$data['is_general'] = 1;
						$session->conditions['type'] = 'general';
					} else {
						$session->conditions['type'] = 'individual';
					}
					
					$tagTable->create($data);
					
				} else {
					// 編集
					$data = array(
						'tag_name'            => $success['tag_name'],      // タグ名称
						'is_general'          => 0,                         // 汎用タグ
				        'search_words_list'   => serialize($itemList),      // 検索ワードリスト
				        'descripition'        => '',      // 詳細
					);
					
					$session = new Zend_Session_Namespace('connection_progress_start_tags');
					
					if (!empty($success['is_general'])) {
						$data['is_general'] = 1;
						$session->conditions['type'] = 'general';
					} else {
						$session->conditions['type'] = 'individual';
					}

					$tagTable->updateById($this->_adminProperty['management_group_id'], $id, $data);
				}

			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
}

