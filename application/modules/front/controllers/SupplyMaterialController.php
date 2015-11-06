<?php
/**
 * class SupplyMaterialController
 */
class SupplyMaterialController extends Front_Model_Controller
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
		$this->view->mainCategoryName = '仕入・調達管理';
		$this->view->menuCategory     = 'supply';

		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-material/add                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先詳細 - 入手資料                           |
    +----------------------------------------------------------------------------*/
    public function addAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        $this->view->backUrl = 'javascript:void(0);';
        $this->view->saveUrl = 'javascript:void(0);';
        $this->view->saveButtonName = '登録';
        
		$request = $this->getRequest();
		$this->view->itemType = $itemType = $request->getParam('item_type');
		$this->view->id = $id = $request->getParam('id');
		$this->view->materialType = $materialType = $request->getParam('material_type');
		
		if ($itemType === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT) {
			$productTable        = new Shared_Model_Data_SupplyProduct();
			$productProjectTable = new Shared_Model_Data_SupplyProductProject();
			$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
			$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
			
			$this->view->backRedirectUrl = '/supply-product/supplier-detail?id=' . $id;
			
		} else if ($itemType === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCTION) {
			$productionTable        = new Shared_Model_Data_SupplyProduction();
			$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
			$this->view->data = $data = $productionTable->getById($this->_adminProperty['management_group_id'], $id);
			$this->view->projectData = $projectData= $productionProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
			
			$this->view->backRedirectUrl = '/supply-production/supplier-detail?id=' . $id;
			
		} else if ($itemType === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_SUBCONTRACTING) {
			$subcontractingTable        = new Shared_Model_Data_SupplySubcontracting();
			$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
			$this->view->data = $data = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $id);
			$this->view->projectData = $projectData= $subcontractingProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
			
			$this->view->backRedirectUrl = '/supply-subcontracting/supplier-detail?id=' . $id;
			
		} else if ($itemType === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_FIXTURE) {
			$fixtureTable        = new Shared_Model_Data_SupplyFixture();
			$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
			$this->view->data = $data = $fixtureTable->getById($this->_adminProperty['management_group_id'], $id);
			$this->view->projectData = $projectData = $fixtureProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);	
			
			$this->view->backRedirectUrl = '/supply-fixture/supplier-detail?id=' . $id;
			
		}
		
		if (empty($data)) {
			throw new Zend_Exception('/supply-product/add - no target item data');
		}
		
		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-material/add-post                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料 新規登録(Ajax)                                        |
    +----------------------------------------------------------------------------*/
    public function addPostAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$itemType = $request->getParam('item_type');
		$id       = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['kind']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「資料名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['explanation']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「説明および注意事項」を選択してください'));
                    return;
                } else if (!empty($errorMessage['file_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ファイル」をアップロードしてください'));
                    return;
                } else if (!empty($errorMessage['temp_file_name']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「ファイル」をアップロードしてください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$materialTable = new Shared_Model_Data_Material();
		
				$materialTable->getAdapter()->beginTransaction();
					
				try {
					
					//$nextOrder = $materialTable->getNextOrder($id);
					
					$data = array(
				        'management_group_id'    => $this->_adminProperty['management_group_id'],       // 管理グループID
				        'type'                   => $success['material_type'],                          // 種別
				        'item_type'              => $success['item_type'],
				        
				        'product_id'             => 0,
				        'supply_id'              => $id,
						'status'                 => Shared_Model_Code::MATERIAL_STATUS_AVAILABLE,       // ステータス
				
						'kind'                   => $success['kind'],                                   // 種別
						
						'title'                  => $success['title'],                                  // 資料名
						'explanation'            => $success['explanation'],                            // 資料説明及び注意事項
						
						'not_for_shared'         => 0,                                                  // 配布禁止
						
						'file_type'              => 0,                                                  // ファイル種類
						'file_name'              => $success['temp_file_name'],                         // 保存ファイル名
						'default_file_name'      => $success['file_name'],                              // 初期ファイル名						
						'display_order'          => 1,                                                  // 並び順
						
		                'created'                => new Zend_Db_Expr('now()'),
		                'updated'                => new Zend_Db_Expr('now()'),
					);
					
					if (!empty($success['not_for_shared'])) {
						$data['not_for_shared'] = 1;
					}
					
					$materialTable->create($data);
					$materialId = $materialTable->getLastInsertedId('id');

					if (!empty($success['file_name'])) {
				        $result = Shared_Model_Resource_Material::makeResource($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name'], Shared_Model_Resource_TemporaryPrivate::getBinary($success['temp_file_name']));
	
		            	// tempファイルを削除
						Shared_Model_Resource_TemporaryPrivate::removeResource($success['temp_file_name']);
						
						$materialTable->updateById($this->_adminProperty['management_group_id'], $materialId, array(
							'file_size' => Shared_Model_Resource_Material::getFileSize($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name']),
						));
						
						
						$historyTable = new Shared_Model_Data_MaterialHistory();
						
						$historyData = array(
					        'material_id'            => $materialId,                    // 資料ID
					        'version_id'             => $historyTable->getNextVersionId($materialId), // バージョンID

							'file_type'              => 0,                             // ファイル種類
							'file_size'              => 111,            // ファイルサイズ
							'file_name'              => $success['temp_file_name'],    // 保存ファイル名
							'default_file_name'      => $success['file_name'],         // 初期ファイル名
							
			                'created'                => new Zend_Db_Expr('now()'),
			                'updated'                => new Zend_Db_Expr('now()'),
						);
						
						$historyTable->create($historyData);
					}

					// commit
					$materialTable->getAdapter()->commit();
               
				} catch (Exception $e) {
                	$materialTable->getAdapter()->rollBack();
					throw new Zend_Exception('/supply-material/add-post transaction faied: ' . $e);
					
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-material/detail                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入手資料 - 詳細                                            |
    +----------------------------------------------------------------------------*/
    public function detailAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		$this->view->materialId = $materialId = $request->getParam('material_id');
		$this->view->posTop = $request->getParam('pos');
		
		$this->view->backUrl = '/supply-product/supplier-detail?id=' . $id;

		$materialTable   = new Shared_Model_Data_Material();
		$this->view->data = $data = $materialTable->getById($this->_adminProperty['management_group_id'], $materialId);
		
		
		if ($data['item_type'] === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCT) {
			$productTable        = new Shared_Model_Data_SupplyProduct();
			$productProjectTable = new Shared_Model_Data_SupplyProductProject();
			$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $data['supply_id']);
			$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
			
			$this->view->backUrl = '/supply-product/supplier-detail?id=' . $id;

		} else if ($data['item_type'] === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_PRODUCTION) {
			$productionTable        = new Shared_Model_Data_SupplyProduction();
			$productionProjectTable = new Shared_Model_Data_SupplyProductionProject();
			$this->view->data = $data = $productionTable->getById($this->_adminProperty['management_group_id'], $data['supply_id']);
			$this->view->projectData = $projectData= $productionProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
			
			$this->view->backUrl = '/supply-production/supplier-detail?id=' . $id;
			
		} else if ($data['item_type'] === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_SUBCONTRACTING) {
			$subcontractingTable        = new Shared_Model_Data_SupplySubcontracting();
			$subcontractingProjectTable = new Shared_Model_Data_SupplySubcontractingProject();
			$this->view->data = $data = $subcontractingTable->getById($this->_adminProperty['management_group_id'], $data['supply_id']);
			$this->view->projectData = $projectData= $subcontractingProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
			
			$this->view->backUrl = '/supply-subcontracting/supplier-detail?id=' . $id;
			
		} else if ($data['item_type'] === (string)Shared_Model_Code::MATERIAL_ITEM_TYPE_SUPPLY_FIXTURE) {
			$fixtureTable        = new Shared_Model_Data_SupplyFixture();
			$fixtureProjectTable = new Shared_Model_Data_SupplyFixtureProject();
			$this->view->data = $data = $fixtureTable->getById($this->_adminProperty['management_group_id'], $data['supply_id']);
			$this->view->projectData = $projectData = $fixtureProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);	
			
			$this->view->backUrl = '/supply-fixture/supplier-detail?id=' . $id;
			
		}
		

		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		$this->view->materialData = $materialTable->getById($this->_adminProperty['management_group_id'], $materialId);
		
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-material/update                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 商品詳細 - 資料 更新(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function updateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id = $request->getParam('id');
		$materialId = $request->getParam('material_id');
		
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
                if (!empty($errorMessage['material_type']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「形式」を選択してください'));
                    return;
                } else if (!empty($errorMessage['kind']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「種別」を選択してください'));
                    return;
                } else if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「資料名」を入力してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$materialTable = new Shared_Model_Data_Material();
		
				//$materialTable->getAdapter()->beginTransaction();
					
				try {

					$data = array(
						'kind'                   => $success['kind'],                               // 種別  
						
						'title'                  => $success['title'],                              // 資料名
						'explanation'            => $success['explanation'],                        // 資料説明及び注意事項
						'not_for_shared'         => 0,
					);

					if (!empty($success['not_for_shared'])) {
						$data['not_for_shared'] = 1;
					}
						
					if (!empty($success['file_name'])) {
						$data['file_type']              = 0;                                         // ファイル種類
						$data['file_name']              = $success['temp_file_name'];                // 保存ファイル名
						$data['default_file_name']      = $success['file_name'];                     // 初期ファイル名
						
				        $result = Shared_Model_Resource_Material::makeResource($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name'], Shared_Model_Resource_TemporaryPrivate::getBinary($success['temp_file_name']));
						
						$data['file_size'] = Shared_Model_Resource_Material::getFileSize($this->_adminProperty['management_group_id'], $materialId, $success['temp_file_name']);
						
		            	// tempファイルを削除
						Shared_Model_Resource_TemporaryPrivate::removeResource($success['temp_file_name']);
						
						$historyTable = new Shared_Model_Data_MaterialHistory();
						
						$historyData = array(
					        'material_id'            => $materialId,                    // 資料ID
					        'version_id'             => $historyTable->getNextVersionId($materialId), // バージョンID

							'file_type'              => 0,                             // ファイル種類
							'file_size'              => $data['file_size'],            // ファイルサイズ
							'file_name'              => $success['temp_file_name'],    // 保存ファイル名
							'default_file_name'      => $success['file_name'],         // 初期ファイル名
							
			                'created'                => new Zend_Db_Expr('now()'),
			                'updated'                => new Zend_Db_Expr('now()'),
						);
						
						$historyTable->create($historyData);
					}

					$materialTable->updateById($this->_adminProperty['management_group_id'], $materialId, $data);
					
					// commit
					//$materialTable->getAdapter()->commit();
               
				} catch (Exception $e) {
                	//$materialTable->getAdapter()->rollBack();
					throw new Zend_Exception('/supply-material/update transaction faied: ' . $e);
					
				}
				
			    $this->sendJson(array('result' => 'OK'));
		    	return;
			}
		}
		
		$this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	}
	
	
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-material/version                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料製品 - 仕入先詳細 - ファイル更新履歴                   |
    +----------------------------------------------------------------------------*/
    public function versionAction()
    {
    	$this->_helper->layout->setLayout('back_menu');
        
		$request = $this->getRequest();
		$this->view->itemType = $itemType = $request->getParam('item_type');
		$this->view->id = $id = $request->getParam('id');
		$this->view->materialId = $materialId = $request->getParam('material_id');
		$this->view->posTop = $request->getParam('pos');
		
		$this->view->backUrl = '/supply-product/supplier-detail?id=' . $id;

		$productTable    = new Shared_Model_Data_SupplyProduct();
		$connectionTable = new Shared_Model_Data_Connection();
		$materialTable   = new Shared_Model_Data_Material();
		$historyTable    = new Shared_Model_Data_MaterialHistory();
		
		$this->view->data = $data = $productTable->getById($this->_adminProperty['management_group_id'], $id);
		
		
		if (!empty($data['target_connection_id'])) {
			$this->view->connectionData = $connectionTable->getById($this->_adminProperty['management_group_id'], $data['target_connection_id']);
		}
		
		
		$productProjectTable = new Shared_Model_Data_SupplyProductProject();
		$this->view->projectData  = $projectData = $productProjectTable->getById($this->_adminProperty['management_group_id'], $data['project_id']);
        $this->view->supplierList = $productTable->getListByProjectId($this->_adminProperty['management_group_id'], $data['project_id']);

		// 資料種別
		$kindTable = new Shared_Model_Data_MaterialKind();
		$this->view->materialKindList = $kindTable->getList();
		
		$this->view->materialData = $materialTable->getById($this->_adminProperty['management_group_id'], $materialId);
		
		$this->view->historyList = $historyTable->getHitoryListByMaterialId($materialId);
		
    }
    
      
    /*----------------------------------------------------------------------------+
    |  action_URL    * /supply-material/upload                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入手見積アップロード(Ajax)                                 |
    +----------------------------------------------------------------------------*/
    public function uploadAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
        
		if (empty($_FILES['file']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		$fileName = $_FILES['file']['name'];
		
		$exploded = explode('.', $fileName);
		$ext = end($exploded);
		
		$tempFileName = uniqid() . '.' . $ext;
		
		// 仮保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::makeResource($tempFileName, file_get_contents($_FILES['file']['tmp_name']));
        
        $this->sendJson(array('result' => true, 'file_name' => $fileName, 'temp_file_name' => $tempFileName));
        return;
	}
	
}

