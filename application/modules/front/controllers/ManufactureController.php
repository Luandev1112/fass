<?php
/**
 * class ManufactureController
 */
 
class ManufactureController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '製造管理';
		$this->view->menuCategory     = 'manufacture';
		
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /manufacture/management                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 生産管理                                                   |
    +----------------------------------------------------------------------------*/
    public function managementAction()
    {
		$request = $this->getRequest();
		$page    = $request->getParam('page', '1');
		$this->view->menu = 'management';
		
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
    |  action_URL    * /manufacture/performance                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 標準作業                                                   |
    +----------------------------------------------------------------------------*/
    public function performanceAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->backUrl = '/manufacture/management';
		
        if (!empty($targetDate)) {
            $startDate   = date('Y-m', strtotime($targetDate)) . '-01'; // 月初日
            $targetYear  = date('Y', strtotime($targetDate));
            $targetMonth = date('m', strtotime($targetDate));
        } else {
            $startDate   = date('Y-m-' . '1'); // 月初日
            $targetYear  = date('Y');
            $targetMonth = date('m');
        }
			
		// 月末日を取得
        $endDate = date($targetYear . '-' . $targetMonth . '-' . Nutex_Date::getMonthEndDay($targetYear, $targetMonth));
        
		
        // 期間データ初期化
        $period = $this->_createMonthPeriod($startDate, $endDate, array('count'));
        

        $zendDateToday = new Zend_Date(NULL, NULL, 'ja_JP');
        
        $dateCountForMonth = 0;
        foreach ($period as $eachDate => &$eachCount) {
            /*
            foreach ($loginDataList as $each) {
                if ($eachDate == $each['action_date']) {
                    $eachCount['logined'] = $each['login_count'];
                    $loginedMonthlyTotal += (int)$eachCount['logined'];
                    break;
                }
            }
            */
			
            if ((int)$eachCount['day'] %10 === 1) {
            	$eachCount['count'] = 50;
            }
            
            
            $zendDate = new Zend_Date($eachDate, NULL, 'ja_JP');
            if ($zendDate->isEarlier($zendDateToday)) {
                $dateCountForMonth++;
            }
        }
        //var_dump($period);

       
        $this->view->dataList = $period;
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /manufacture/history                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function historyAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->backUrl = '/manufacture/management';
		
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
    |  action_URL    * /manufacture/step                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造工程                                                   |
    +----------------------------------------------------------------------------*/
    public function stepAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		$this->view->backUrl = '/manufacture/management';
		
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
    |  action_URL    * /manufacture/stock                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造在庫管理                                               |
    +----------------------------------------------------------------------------*/
    public function stockAction()
    {
		$this->view->menu = 'stock';
		
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
    |  action_URL    * /manufacture/add                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品新規登録                                               |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();

		
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /manufacture/add-post                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品新規登録(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$typeCode = Shared_Model_Code::ITEM_TYPE_PRODUCT;

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['item_name']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「アイテム名」を入力してください';
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $result['result'] = 'NG';
                    $result['message'] = '「ステータス」を選択してください';
                    $this->sendJson($result);
                    return;
                }
                
				$result = array('result' => 'NG', 'error' => '予期せぬエラーが発生しました');
			    $this->sendJson($result);
	    		return;
	    		
			} else {
				$itemTable     = new Shared_Model_Data_Item();
				
				// 新規登録
				$data = array(
					'status'        => $success['status'],
					'item_type'     => $typeCode,
					
					'category_id'   => 0,
					'item_name'     => $success['item_name'],
					
					'jan_code'      => $success['jan_code'],
					'stock_count'   => 0,
					
					'useable_count' => 0,
					'alert_count'   => 0,
					
	                'created'       => new Zend_Db_Expr('now()'),
	                'updated'       => new Zend_Db_Expr('now()'),
				);

				$itemTable->getAdapter()->beginTransaction();
            	  
	            try {
					$itemTable->create($data);
					$itemId = $itemTable->getLastInsertedId('id');
					
					/*
					$imageNameList = array();
					
					if (!empty($success['photo_order'])) {
						// 画像
						$photoOrder = explode(',', $success['photo_order']);
						
						foreach ($photoOrder as $eachImageName) {
							$cropImagePath = Shared_Model_Resource_Temporary::getResourceObjectPath($eachImageName . '_crop' . '.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE);
							
							// 拡大用画像の保存
							Shared_Model_Resource_Item::makeResource($itemId, $eachImageName, file_get_contents($cropImagePath));

							// サムネイルの保存
							$img = $this->imageCreateFromAny($cropImagePath);
							$width = ImageSx($img);
				            $height = ImageSy($img);
				            $resizedWidth = 300;
				            $out = ImageCreateTrueColor($resizedWidth, $height / $width * $resizedWidth);
				            ImageCopyResampled($out, $img, 0,0,0,0, $resizedWidth, floor($height / $width * $resizedWidth), $width, $height);
							
							$imagePath = Shared_Model_Resource_Temporary::getResourceObjectPath(uniqid() . '.' . 'jpg');
							ImageJPEG($out, $imagePath);
							
							Shared_Model_Resource_Item::makeResource($itemId, $eachImageName . '_thumb', file_get_contents($imagePath));
		
							$imageNameList[] = $eachImageName;		
						}
						
						$itemTable->updateById($itemId, array(
							'main_image_name'   => $imageNameList[0],
							'image_name_list'   => json_encode($imageNameList),
						));
					}*/
					
	                // commit
	                $itemTable->getAdapter()->commit();
	                
	            } catch (Exception $e) {
	                $itemTable->getAdapter()->rollBack();
	                throw new Zend_Exception('/item/add-post/:type transaction faied: ' . $e);
	                
	            }
				
				$result = array('result' => 'OK');
			    $this->sendJson($result);
		    	return;
			}
		}
		
		$result = array('result' => 'NG');
	    $this->sendJson($result);
	}
	






	
}

