<?php
/**
 * class OpenController
 * モール営業管理
 */
 
class OpenController extends Front_Model_Controller
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
        $this->view->menuCategory     = 'open';
        $this->view->menu             = 'open';
        
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
    public function goosaAction()
    {
        $this->view->menu = 'goosa';

        $request = $this->getRequest();
        $this->view->posTop = $request->getParam('pos');
        $session = new Zend_Session_Namespace('goosa');

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

        
        $openTable = new Shared_Model_Data_ConnectionOpen();
        
        $dbAdapter = $openTable->getAdapter();
        $selectObj = $openTable->select(array('*','fc.display_id','fc.company_name', 'fcpst.tag_name' ));
        $selectObj->joinLeft('frs_connection_staff as fcs', 'frs_connection_open.staff_id = fcs.id', NULL);
        $selectObj->joinLeft('frs_connection as fc', 'fc.id = frs_connection_open.connection_id', array(
            $openTable->aesdecrypt('fc.company_name', false) . 'AS connection_company_name'
        ));
        $selectObj->joinLeft('frs_item_product_category as fipc', 'fipc.id = frs_connection_open.main_category_id', array(
            $openTable->aesdecrypt('fipc.name', false) . 'AS main_product_name'
        ));
        $selectObj->joinLeft('frs_connection_progress_start_tag as fcpst', 'fcpst.id = frs_connection_open.start_tag_id', null );
        
        
        // $selectObj->group('frs_connection.id');
        $selectObj->order('frs_connection_open.updated DESC');
        // var_dump($selectObj->__toString());exit;
        
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
        $paginator->setCurrentPageNumber($page);
        
        $items = array();
    
        foreach ($paginator->getCurrentItems() as $eachItem) {
            $staffTable = new Shared_Model_Data_ConnectionStaff();
            $staffObj = $staffTable->select(array('id', 'staff_name', 'staff_department', 'staff_position', 'staff_tel', 'staff_mail', 'staff_tel' ));
            $staffObj->where('connection_id  = ?', $eachItem['connection_id']);
            $staffObj->order('updated ASC');
            $staffs = $staffObj->query()->fetchAll();
            $eachItem['first_staff'] = false;
            $eachItem['last_staff'] = false;
            $st_count = count($staffs);
            if($st_count > 0)
            {
                $eachItem['first_staff'] = $staffs[0];
                $eachItem['last_staff'] = $staffs[$st_count-1];
            }
            $items[] = $eachItem;
            // var_dump($eachItem);
            // exit();
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
    
    
    public function goosaDetailAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
        $request = $this->getRequest();
        $this->view->id = $id = $request->getParam('id');
        $this->view->posTop = $request->getParam('pos');
        
        // $connectionTable = new Shared_Model_Data_Connection();
        $userTable       = new Shared_Model_Data_User();
        $openTable = new Shared_Model_Data_ConnectionOpen();
        $this->view->data = $data = $openTable->getById($this->_adminProperty['management_group_id'], $id);
        
        var_dump($data);
        exit();
        
        $this->view->createdUser     = $userTable->getById($data['create_user_id']);
        $this->view->lastUpdatedUser = $userTable->getById($data['last_update_user_id']);
        $this->view->direct = $direct  = $request->getParam('direct');
        if (empty($direct)) {
            $this->view->backUrl = '/open/goosa';
        }
        
        // 国
        $countryTable = new Shared_Model_Data_Country();
        $this->view->countryList = $countryTable->getList();

        // 当社事業区分
        $ourBusinessTable = new Shared_Model_Data_OurBusiness();
        $this->view->ourBusinessList = $ourBusinessTable->getList();
        
        // 業種
        $industryCategoryTable = new Shared_Model_Data_IndustryCategory();
        $industryTypeTable     = new Shared_Model_Data_IndustryType();
        $categoryList = $industryCategoryTable->getList();
        
        foreach ($categoryList as &$each) {
            $each['items'] = $industryTypeTable->getListByCategoryId($each['id']);
        }
        $this->view->industryCategoryList = $categoryList;      
        
    }
    
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /open/add-goosa                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function addGoosaAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
        $request = $this->getRequest();
        
        // 実績
        $progressTable = new Shared_Model_Data_ConnectionProgress();
        $this->view->progressList = $progressTable->getList($this->_adminProperty['management_group_id']);
        
        $categoryTable = new Shared_Model_Data_ItemProductCategory();
        $this->view->productCategoryList = $categoryTable->getList();

        // 見積後ヒア
        $afterTable = new Shared_Model_Data_ConnectionProgressAfter();
        $this->view->afterList = $afterTable->getList($this->_adminProperty['management_group_id']);
    }
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /open/add-post-goosa                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 新規案件登録(Ajax)                                |
    +----------------------------------------------------------------------------*/
    public function addPostGoosaAction()
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

                if (!empty($errorMessage['target_business']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「対象事業」を選択してください'));
                    return;
                } else if (!empty($errorMessage['user_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「自社担当」を選択してください'));
                    return;
                } else if (!empty($errorMessage['connection_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「取引先」を選択してください'));
                    return;
                } else if (!empty($errorMessage['start_tag_id']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「発足名称」を選択してください'));
                    return;
                } else if (!empty($errorMessage['supplier_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「重要度」を選択してください'));
                    return;
                } else if (!empty($errorMessage['possibility']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「可能性」を選択してください'));
                    return;   
                }

                $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
                return;
                
            } else {
                $openTable = new Shared_Model_Data_ConnectionOpen();
                
                
                $target_business_arr = implode(',', $success['target_business']);
                $business_id = '';
                if(strpos($target_business_arr, '1'))
                {
                    $business_id = $openTable->getNextDisplayId('Be');
                }else{
                    if(strpos($target_business_arr, '2')){
                        $business_id = $openTable->getNextDisplayId('Ce');
                    }
                }
                
                $supplier_type_arr = implode(',', $success['supplier_type']);
                
                $create_user_id = $this->_adminProperty['id'];
                $created_at = date('Y-m-d H:i:s');
            
                $data = array(
                    'connection_id'          => $success['connection_id'],    // 取引先ID
                    'staff_id'               => $success['staff_id'],         // 取引先担当者ID
                    'target_business_arr'    => $target_business_arr,          // 営業エリア
                    'business_id'            => $business_id,
            
                    // 'start_type'             => $success['start_type'],       // 発足区分
                    'start_tag_id'           => $success['start_tag_id'],     // 発足名称タグID
                    'supplier_type_arr'      => $supplier_type_arr,                            // 案件ID
                    'business_product_url'   => $success['business_product_url'],                             // 案件分野
                    'create_user_id'         => $create_user_id,
                    'created_at'             => $created_at,
                    'status'                 => $success['status'], 
                    'willing_status'         => $success['willing_status'],
                    'estimate_reception_date'         => $success['estimate_reception_date'],
                    'product_point'         => $success['product_point'],
                    'main_category_id'         => $success['main_category_id'],
                    'business_mode'         => $success['business_mode'],
                    'settlement_terms'         => $success['settlement_terms'],
                    'ads_status'         => $success['ads_status'],
                    'failed_date'         => $success['failed_date'],
                    'problems'         => $success['problems'],
                    'created'                => new Zend_Db_Expr('now()'),
                    'updated'                => new Zend_Db_Expr('now()')
                );
                
                // var_dump($data);
                // exit();

                try {

                    $openTable->create($data);
                    
                    // commit
                    // $openTable->getAdapter()->commit();
                    
                } catch (Exception $e) {
                    $openTable->getAdapter()->rollBack();
                    throw new Zend_Exception('/open/add-post-goosa transaction faied: ' . $e);
                    
                }
                
                $this->sendJson(array('result' => 'OK'));
                return;
            }
        }

        $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
    }
    
   
    /*----------------------------------------------------------------------------+
    |  action_URL    * /management/goosa-public                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 営業進捗 - 新規登録                                        |
    +----------------------------------------------------------------------------*/
    public function gooscaAction()
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
    
    public function gooscaDetailAction()
    {
        
    }
   
}

