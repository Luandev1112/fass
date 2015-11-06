<?php
/**
 * class ItemController
 */
 
class ItemController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '出荷・在庫管理';
		$this->view->menuCategory     = 'shipment';
		$this->view->menu             = 'item';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/list/:type                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アイテム一覧                                               |
    +----------------------------------------------------------------------------*/
    public function listAction()
    {
		$request = $this->getRequest();
		$this->view->type = $type = $request->getParam('type');

		$page    = $request->getParam('page', '1');
		
		if (empty($type)) {
			throw new Zend_Exception('/item/list/:type type is empty');
		}
		
		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		$this->view->typeCode = $typeCode;
		
		$itemTable = new Shared_Model_Data_Item();
		$selectObj->where('management_group_id = ?', $this->_adminProperty['management_group_id']);
		
		$dbAdapter = $itemTable->getAdapter();

        $selectObj = $itemTable->select();
        $selectObj->where('frs_item.status != ?', Shared_Model_Code::ITEM_STATUS_REMOVE);
        $selectObj->where('frs_item.item_type = ?', $typeCode);
        
        /*
        if (!empty($conditions['id'])) {
        	$selectObj->where('fbc_item.id = ?', $conditions['id']);
        }
        
        if (!empty($conditions['category_id'])) {
        	$selectObj->where('fbc_item.category_id = ?', $conditions['category_id']);
        }
        
        if (!empty($conditions['machine_id'])) {
        	$selectObj->where('fbc_item.machine_id = ?', $conditions['machine_id']);	
        }
        
        if (!empty($conditions['purpose'])) {
        	$selectObj->where('fbc_item.purpose = ?', $conditions['purpose']);	
        }

        if (!empty($conditions['status'])) {
        	$selectObj->where('fbc_item.status = ?', $conditions['status']);	
        }
        
        if (!empty($conditions['keyword'])) {
        	$keywordString = '';
        	
        	$columns = array(
        		'maker_name', 'maker_name_en', 'model_name', 'model_year', 'spec_main_jp', 'spec_main_en', 'spec_main_en', 'spec_jp', 'spec_en',
        		'owner_name', 'owner_name_in_charge', 'info_from', 'info_from_in_charge', 'storage_place', 'storage_state',
        		'production_number', 'season_stop_using', 'season_limit', 'buying_in_requirement', 'buying_in_price', 'buying_in_price',
        		'sale_requirement', 'sale_price', 'bland_new_price', 'memo',
        	);
        	
        	foreach ($columns as $each) {
        		if ($keywordString !== '') {
        			$keywordString .= ' OR ';
        		}

        		if ($itemTable->isCryptField($each)) {   
        			$keyword = $dbAdapter->quote('%' . $conditions['keyword'] . '%');     			
        			$keywordString .= $itemTable->aesdecrypt($each, false) . ' LIKE ' . $keyword;
        		} else {
        			$keywordString .= $dbAdapter->quoteInto('`' . $each . '` LIKE ?', '%' . $conditions['keyword'] .'%');
        		}
        	}

        	$selectObj->where($keywordString);
        }
           
        if (!empty($conditions['user_id_in_charge'])) {
        	$selectObj->where('fbc_item.user_id_in_charge = ?', $conditions['user_id_in_charge']);	
        }
        */
        
		$selectObj->order('frs_item.id ASC');
		
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
    |  action_URL    * /item/add/:type                                            |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アイテム新規登録                                           |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->type = $type = $request->getParam('type');
		
		if (empty($type)) {
			throw new Zend_Exception('/item/add/:type type is empty');
		}
		
		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		$this->view->typeCode = $typeCode;
		
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/add-post                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品新規登録(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$type    = $request->getParam('type');

		// アイテム種別
		$itemTypeList = Shared_Model_Code::codes('item_type_code');
		$typeCode = 0;
		foreach ($itemTypeList as $eachCode => $codeName) {
			if ($type == $codeName) {
				$typeCode = $eachCode;
			}
		}
		
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['status']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ステータス」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable     = new Shared_Model_Data_Item();

				$itemTable->getAdapter()->beginTransaction();
            	  
	            try {
	            
					$itemTypeId = $itemTable->getNextItemTypeId($typeCode);
					
					// 新規登録
					$data = array(
						'management_group_id' => $this->_adminProperty['management_group_id'],
						'status'              => $success['status'],
						'item_type'           => $typeCode,
						'item_type_id'        => $itemTypeId,
						
						'category_id'         => serialize($success['relation_types']),
						'item_name'           => $success['item_name'],
						
						'jan_code'            => $success['jan_code'],
						'stock_count'         => 0,
						
						'useable_count'       => 0,
						'alert_count'         => 0,
						
		                'created'             => new Zend_Db_Expr('now()'),
		                'updated'             => new Zend_Db_Expr('now()'),
					);

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
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/basic                                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報                                                   |
    +----------------------------------------------------------------------------*/
    public function basicAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->posTop = $request->getParam('pos');
		
		$itemTable     = new Shared_Model_Data_Item();
		$itemBaseTable = new Shared_Model_Data_ItemBase();
		
		$data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		$data['shelf_no'] = '';
		
		$dataBase = $itemBaseTable->getByItemId('1', $id); // 拠点毎情報
		
		if (!empty($dataBase)) {
			$data['shelf_no'] = $dataBase['shelf_no'];
		}
		
		$this->view->data = $data;
		
		$typeCodeList = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/item/list/' . $typeCodeList[$data['item_type']];
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/update-basic                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function updateBasicAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$id      = $request->getParam('id');
		
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
                    $this->sendJson(array('result' => 'NG', 'message' => '「アイテム名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['alert_count']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アラート在庫数」を入力してください'));
                    return;
                } else if (!empty($errorMessage['alert_count']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アラート在庫数」は半角数字のみで入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$itemTable     = new Shared_Model_Data_Item();
				$itemBaseTable = new Shared_Model_Data_ItemBase();
				
				// 更新
				$data = array(
					'item_name'             => $success['item_name'],
					'item_name_en'          => $success['item_name_en'],
					'delivery_item_name'    => $success['delivery_item_name'],
					'delivery_item_name_en' => $success['delivery_item_name_en'],
					'jan_code'              => $success['jan_code'],
					'memo'                  => $success['memo'],
				);
				
				$itemTable->updateById($id, $data);
				
				$itemBaseTable->updateByItemId('1', $id, array(
					'shelf_no' => $success['shelf_no'],
				));
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}
		
	    $this->sendJson(array('result' => 'NG'));
    }
    
    /**
     * uploadImageAction
     *
     * @param void
     * @return void
     */
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/upload-image                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品画像アップロード                                       |
    +----------------------------------------------------------------------------*/
    public function uploadImageAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
        
		if (empty($_FILES['image']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		$key = uniqid();
		$fileName = $key . '.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE;
		
		// PNGに変換して保存
		$tempFilePath = Shared_Model_Resource_Temporary::getResourceObjectPath($fileName);
		$img =  $this->imageCreateFromAny($_FILES['image']['tmp_name']);

        if (empty($img)) {
        	throw new Zend_Exception('/item/crop no object image');
        }
        
        $width = ImageSx($img);
        $height = ImageSy($img);
        
        $resizedWidth = 840;
        $out = ImageCreateTrueColor($resizedWidth, $height/ $width * $resizedWidth);
        ImageCopyResampled($out, $img, 0,0,0,0, $resizedWidth, floor($height/ $width * $resizedWidth), $width, $height);
        
        // 画像を保存
        $result = ImageJPEG($out, $tempFilePath);
        
        if ($result === false) {
        	throw new Zend_Exception('/item/crop save failed');
        }
        
        $this->sendJson(array('result' => true, 'key' => $key, 'file_url' => Shared_Model_Resource_Temporary::getImageUrl($fileName)));
        return;

	}

    /**
     * cropAction
     *
     * @param void
     * @return void
     */
    public function cropAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $request = $this->getRequest();
        $x       = $request->getParam('x', 0);
        $y       = $request->getParam('y', 0);
        $w       = $request->getParam('w', 0);
        $h       = $request->getParam('h', 0);
        $id      = $request->getParam('id');
        $key     = $request->getParam('base_image_key', '');

		$baseImagePath = Shared_Model_Resource_Temporary::getResourceObjectPath($key . '.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE);

        $img = $this->imageCreateFromAny($baseImagePath);
  
        $baseWidth = ImageSx($img);
        $baseHeight = ImageSy($img);

	
        // クロッピング画像
		$new = ImageCreateTrueColor($w * 2, $h * 2);
        imagecopyresampled($new, $img, 0, 0, $x * 2, $y * 2, $w * 2, $h * 2, $w * 2, $h * 2);
        
        $cropImagePath = Shared_Model_Resource_Temporary::getResourceObjectPath($key . '_crop.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE);

		// 画像を保存
        $resultCrop = ImageJPEG($new, $cropImagePath);
        
        if ($resultCrop === false) {
        	throw new Zend_Exception('/item/crop crop failed');
        }

        $resultLastProcess = Shared_Model_Resource_Item::makeResource($id, $key, file_get_contents($cropImagePath));
        if ($resultLastProcess === false) {
        	throw new Zend_Exception('/item/crop save failed');
        }
        
		$itemTable = new Shared_Model_Data_Item();

		// 更新
		$itemTable->updateById($id, array('image_file_name' => $key));
        
        
        Shared_Model_Resource_Temporary::removeResource($key . '.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE);
        Shared_Model_Resource_Temporary::removeResource($key . '_crop.' . Shared_Model_Resource_Item::EXTENTION_FOR_IMAGE);
        
        $result = array();
        $result['result']    = 'OK';
        $result['key']       = $key;
        $result['image_url'] = Shared_Model_Resource_Item::getResourceUrl($id, $key);
        
        $this->sendJson($result);
        return;
            
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/stock-warehouse                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function stockWarehouseAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id   = $id   = $request->getParam('id');
		$this->view->page = $page = $request->getParam('page', '1');
		$this->view->posTop = $request->getParam('pos');
		
		// 商品データ
		$itemTable     = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/item/list/' . $typeCodeList[$data['item_type']];
		
		
		$itemStockTable     = new Shared_Model_Data_ItemStock();
		$selectObj = $itemStockTable->getActiveList($id, true);
		$selectObj->where('action_code < ?', Shared_Model_Code::STOCK_ACTION_SHIPMENT);
		$selectObj->order('id DESC');
		
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($selectObj));
        $paginator->setDefaultItemCountPerPage(self::PER_PAGE);
		$paginator->setCurrentPageNumber($id, $page);
		
		$items = array();
        
		foreach ($paginator->getCurrentItems() as $eachItem) {
			$items[] = $eachItem; 
		}

        $this->view->items = $items;
        $this->view->pager($paginator);
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/warehouse-cancel                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫予定キャンセル(Ajax)                                   |
    +----------------------------------------------------------------------------*/
    public function warehouseCancelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$stockId  = $request->getParam('target_id');
		
		if (empty($stockId)) {
			throw new Zend_Exception('/item/warehouse-cancel target_id is empty');
		}

		// POST送信時
		if ($request->isPost()) {
			$itemStockTable  = new Shared_Model_Data_ItemStock();
			
			$planData = $itemStockTable->getById($stockId);

			if (empty($planData)) {
				throw new Zend_Exception('/item/warehouse-cancel plan data not found');
			}
		
			$itemStockTable->updateById($stockId, array('status' => Shared_Model_Code::STOCK_STATUS_INACTIVE));

			$result = array('result' => 'OK');
		    $this->sendJson($result);
	    	return;
		}
		
		$result = array('result' => 'NG');
	    $this->sendJson($result);
	}
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/stock-warehouse-add                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫追加                                                   |
    +----------------------------------------------------------------------------*/
    public function stockWarehouseAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->planStockId = $planStockId = $request->getParam('plan_stock_id', '');
		
		$itemTable     = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		
		$this->view->backUrl = 'javascript:void(0)';
		$this->view->today = date('Y-m-d H:i:s');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/stock-warehouse-add-post                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫追加(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function stockWarehouseAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id');
		$planStockId = $request->getParam('plan_stock_id', '');
		
		if (empty($itemId)) {
			throw new Zend_Exception('/item/stock-warehouse-add-post item_id is empty');
		}

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['action_time_day']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(日)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['action_time_hour']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(時)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['action_time_min']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(分)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['warehouse_action']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アクション」を選択してください'));
                    return;

                } else if (!empty($errorMessage['lot']['isEmpty'])) {
                    $result['result'] = 'NG';
                    if ($success['warehouse_action'] == Shared_Model_Code::STOCK_ACTION_PLAN_WAREHOUSE) {
                    	$result['message'] = '「入庫予定数」を入力してください';
                    } else {
                    	$result['message'] = '「ロット単位」を入力してください';
                    }
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['lot']['notDigits'])) {
                    $result['result'] = 'NG';
                    if ($success['warehouse_action'] == Shared_Model_Code::STOCK_ACTION_PLAN_WAREHOUSE) {
                    	$result['message'] = '「入庫予定数」は半角数字のみで入力してください';
                    } else {
                    	$result['message'] = '「ロット単位」は半角数字のみで入力してください';
                    }
                    $this->sendJson($result);
                    return;
                } else if (!empty($errorMessage['number_of_lot']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ロット数」は半角数字のみで入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$actionTimeString    = str_replace('/', '-', $success['action_time_day']) . ' ' . $success['action_time_hour'] . ':' . $success['action_time_min'] . ':00';
				
				$itemTable       = new Shared_Model_Data_Item();
				$itemStockTable  = new Shared_Model_Data_ItemStock();
				
				
				$lotNumbers = (int)$success['number_of_lot'];
				
				if ($success['warehouse_action'] == Shared_Model_Code::STOCK_ACTION_PLAN_WAREHOUSE) {
					$lotNumbers = 1;
				}
				
				// テーブルロック
				$itemStockTable->getAdapter()->query("LOCK TABLES frs_item WRITE, frs_item_stock WRITE")->execute();
				$itemStockTable->getAdapter()->beginTransaction();
				
	            try {
	            	$warehouseManageId = $itemStockTable->getNextId();

					for ($count = 0; $count < $lotNumbers; $count++) {
						$data = array(
					        'item_id'      => $itemId,
					        'user_id'      => 0,
							'status'       => Shared_Model_Code::STOCK_STATUS_ACTIVE,
							
							'warehouse_manage_id' => $warehouseManageId,
							'lot_count'    => $count + 1,
							'action_date'  => $actionTimeString,           // アクション日
							'action_code'  => $success['warehouse_action'],
							
							'expiration_date' => NULL,
							
							'amount'       => $success['lot'],
							'sub_count'    => 0,
							'last_count'   => $success['lot'],
							
							'warehouse_id' => 1,
							
							'order_id'     => 0,
							'memo'         => '',
		
			                'created'      => new Zend_Db_Expr('now()'),
			                'updated'      => new Zend_Db_Expr('now()'),
						);
						
						if (!empty($success['expiration_date'])) {
							$data['expiration_date'] = str_replace('/', '-',$success['expiration_date']);
						}
					
						$itemStockTable->create($data);
					}
					
					if ($success['warehouse_action'] == Shared_Model_Code::STOCK_ACTION_WAREHOUSE) {
						// 入庫の場合 商品テーブルの在庫数更新
						$itemTable->addStock($itemId, (int)$success['lot'] * $lotNumbers);	
					}
					
					// 入庫予定からの入庫登録の場合は入庫予定を削除
					if (!empty($planStockId)) {
						$itemStockTable->updateById($planStockId, array('status' => Shared_Model_Code::STOCK_STATUS_INACTIVE));
					}
					
	                // commit
	                $itemStockTable->getAdapter()->commit();
	                $itemStockTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	            } catch (Exception $e) {
	                $itemStockTable->getAdapter()->rollBack();
	                $itemStockTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	                throw new Zend_Exception('/item/stock-warehouse-add-post transaction faied: ' . $e);   
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/stock-consumption                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出庫履歴                                                   |
    +----------------------------------------------------------------------------*/
    public function stockConsumptionAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->page = $page = $request->getParam('page', '1');
		
		// 商品情報
		$itemTable     = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/item/list/' . $typeCodeList[$data['item_type']];
		
		$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
		$selectObj = $consumptionTable->getActiveList($id, true);
		$selectObj->order('action_date DESC');
		
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
    |  action_URL    * /item/stock-consumption-add                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 出庫追加                                                   |
    +----------------------------------------------------------------------------*/
    public function stockConsumptionAddAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id      = $id      = $request->getParam('id');
		$this->view->stockId = $stockId = $request->getParam('stock_id');
		
		$itemTable     = new Shared_Model_Data_Item();
		$stockTable    = new Shared_Model_Data_ItemStock();
		
		// 商品情報
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		// 対象在庫情報
		$this->view->stockData = $stockTable->getById($stockId);
		
		$this->view->backUrl = 'javascript:void(0)';		
		$this->view->today = date('Y-m-d H:i:s');

    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/stock-consumption-add-post                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入庫追加(Ajax)                                             |
    +----------------------------------------------------------------------------*/
    public function stockConsumptionAddPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
		$request = $this->getRequest();
		$itemId  = $request->getParam('item_id');
		$stockId = $request->getParam('stock_id');
		
		if (empty($itemId)) {
			throw new Zend_Exception('/item/stock-consumption-add-post item_id is empty');
		}

		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['action_time_day']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(日)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['action_time_hour']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(時)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['action_time_min']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「日時(分)」を入力してください'));
                    return;
                } else if (!empty($errorMessage['consumption_action']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「アクション」を選択してください'));
                    return;
                } else if (!empty($errorMessage['amount']['notDigits'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「数量」は半角数字のみで入力してください'));
                    return;
                }

			    $this->sendJson(array('result' => 'NG', 'error' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$actionTimeString    = str_replace('/', '-', $success['action_time_day']) . ' ' . $success['action_time_hour'] . ':' . $success['action_time_min'] . ':00';
				
				$itemTable         = new Shared_Model_Data_Item();
				$itemStockTable    = new Shared_Model_Data_ItemStock();
				$consumptionTable  = new Shared_Model_Data_ItemStockConsumption();
				
				// 新規登録
				$data = array(
			        'item_id'      => $itemId,
			        'user_id'      => 0,
					'status'       => Shared_Model_Code::STOCK_STATUS_ACTIVE,
					
					'action_date'  => $actionTimeString,           // アクション日
					'action_code'  => $success['consumption_action'],
					
					'sub_count'       => $success['amount'],
					'target_stock_id' => $stockId,// 対象の在庫
					
					'order_id'     => 0,
					'memo'         => '',

	                'created'      => new Zend_Db_Expr('now()'),
	                'updated'      => new Zend_Db_Expr('now()'),
				);
				
				// テーブルロック
				$consumptionTable->getAdapter()->query("LOCK TABLES frs_item WRITE, frs_item_stock WRITE, frs_item_stock_consumption WRITE")->execute();
				
				$consumptionTable->getAdapter()->beginTransaction();
            	
	            try {
	            	// 消費データの追加
					$consumptionTable->create($data);
					$consumptionId = $consumptionTable->getLastInsertedId('id');
				
					// 在庫データの在庫数を減らす
					$itemStockTable->consumeStock($stockId, $success['amount']);
					
					// 商品データの在庫数を減らす
					$itemTable->subStock($itemId, $success['amount']);	
					
	                // commit
	                $consumptionTable->getAdapter()->commit();
	                $consumptionTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	            } catch (Exception $e) {
	                $consumptionTable->getAdapter()->rollBack();
	                $consumptionTable->getAdapter()->query("UNLOCK TABLES")->execute();
	                
	                throw new Zend_Exception('/item/stock-consumption-add-post transaction faied: ' . $e);   
	            }
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
	    $this->sendJson(array('result' => 'NG'));
	}
    
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/data-shipping                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * データ分析 - 出荷                                          |
    +----------------------------------------------------------------------------*/
    public function dataShippingAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$itemTable     = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/item/list/' . $typeCodeList[$data['item_type']];

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
            $eachCount['count'] = mt_rand(0, 20);
            
            $zendDate = new Zend_Date($eachDate, NULL, 'ja_JP');
            if ($zendDate->isEarlier($zendDateToday)) {
                $dateCountForMonth++;
            }
        }
        //var_dump($period);

       
        $this->view->dataList = $period;
		
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/data-stock                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * データ分析 - 出荷                                          |
    +----------------------------------------------------------------------------*/
    public function dataStockAction()
    {
        $this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$itemTable     = new Shared_Model_Data_Item();
		$this->view->data = $data = $itemTable->getById($this->_adminProperty['management_group_id'], $id);
		
		$typeCodeList     = Shared_Model_Code::codes('item_type_code');
		$this->view->backUrl = '/item/list/' . $typeCodeList[$data['item_type']];

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
        
        $sample = array(
        	'2018-07-01' => 100,
        	'2018-07-02' => 98,
        	'2018-07-03' => 98,
        	'2018-07-04' => 94,
        	'2018-07-05' => 94,
        	'2018-07-06' => 94,
        	'2018-07-07' => 90,
        	'2018-07-08' => 140,
        	'2018-07-09' => 140,
        	'2018-07-10' => 133,
        	'2018-07-11' => 129,
        	'2018-07-12' => 120,
        	'2018-07-13' => 120,
        	'2018-07-14' => 100,
        	'2018-07-15' => 98,
        	'2018-07-16' => 86,
        	'2018-07-17' => 136,
        	'2018-07-18' => 132,
        	'2018-07-19' => 130,
        	'2018-07-20' => 130,
        	'2018-07-21' => 126,
        );
		
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
            if (array_key_exists($eachDate, $sample)) {
            	$eachCount['count'] = $sample[$eachDate];
            }
            
            $eachCount['available'] = true;
            
            $zendDate = new Zend_Date($eachDate, NULL, 'ja_JP');
            if ($zendDate->isEarlier($zendDateToday)) {
                $dateCountForMonth++;
            } else {
            	$eachCount['available'] = false;
            }
        }
        //var_dump($period);

       
        $this->view->dataList = $period;
		
    }       
}

