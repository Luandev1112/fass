<?php
/**
 * class SettingController
 */
 
class SettingController extends Front_Model_Controller
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
		$this->view->menu             = 'setting';
		
		$request = $this->getRequest();
		$this->view->action = $request->getParam('action');
		
		$this->view->allowEditing = true;
		if (!empty($this->_adminProperty['is_accountants_office'])) {
			$this->view->allowEditing = false;
		}
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/basic                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報                                                   |
    +----------------------------------------------------------------------------*/
    public function basicAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$basicTable = new Shared_Model_Data_BasicInfo();
		$this->view->data = $basicTable->get($this->_warehouseSession->warehouseId);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/basic-update                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 基本情報更新(Ajax)                                         |
    +----------------------------------------------------------------------------*/
    public function basicUpdateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$basicTable = new Shared_Model_Data_BasicInfo();
	
				// 更新
				$data = array(
					'shop_name'         => $success['shop_name'],
					'company_name'      => $success['company_name'],
					'shop_url'          => $success['shop_url'],
					'zipcode'           => $success['zipcode'],
					'prefecture'        => $success['prefecture'],
					'address1'          => $success['address1'],
					'address2'          => $success['address2'],
					'shop_manager_name' => $success['shop_manager_name'],
					'shop_mail'         => $success['shop_mail'],
					'shop_tel'          => $success['shop_tel'],
					'shop_fax'          => $success['shop_fax'],
					'memo'              => $success['memo'],
				);
				
				$basicTable->updateInfo($this->_warehouseSession->warehouseId, $data);
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/statement                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 明細書表示                                                 |
    +----------------------------------------------------------------------------*/
    public function statementAction()
    {
		$request = $this->getRequest();
		$this->view->posTop = $request->getParam('pos');
		
		$basicTable = new Shared_Model_Data_BasicInfo();
		$this->view->data = $basicTable->get($this->_warehouseSession->warehouseId);
    }
    

    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/statement-pdf/(standard.pdf or subscription.pdf)  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ステートメントサンプルPDF表示                              |
    +----------------------------------------------------------------------------*/
    public function statementPdfAction()
	{
		$request  = $this->getRequest();
		$id       = $request->getParam('id');
		$fileName = $request->getParam('file');
		
		$basicTable  = new Shared_Model_Data_BasicInfo();
		$templeTable = new Shared_Model_Data_MessageTemplate();
		
		$basicInfo    = $basicTable->get($this->_warehouseSession->warehouseId);

		$params = Shared_Model_Pdf_ShipmentReceipt::createDefaultParams();
		$params['logo_path'] = Shared_Model_Resource_Logo::getResourceObjectPath($basicInfo['logo_file_name']);
		$params['shop_info'] = $basicInfo['statement_shop_info'];
		
		if ($fileName == 'standard.pdf') {
			// 通常
			$templateData1 = $templeTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_1']);
			$params['template_1'] = $templateData1['message'];
			
			$templateData2 = $templeTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_2']);
			$params['template_2'] = $templateData2['message'];
			
			$templateData3 = $templeTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_3']);
			$params['template_3'] = $templateData3['message'];
			
		} else if ($fileName == 'subscription.pdf') {
			// 定期
			$templateData1 = $templeTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_1']);
			$params['template_1'] = $templateData1['message'];
			
			$templateData2 = $templeTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_2']);
			$params['template_2'] = $templateData2['message'];
			
			$templateData3 = $templeTable->getById($this->_warehouseSession->warehouseId, $basicInfo['statement_tamplate_subscription_3']);
			$params['template_3'] = $templateData3['message'];
		}
		
    	Shared_Model_Pdf_ShipmentReceipt::makeSingle($params);
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/statement-update                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 明細書情報更新(Ajax)                                       |
    +----------------------------------------------------------------------------*/
    public function statementUpdateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$basicTable = new Shared_Model_Data_BasicInfo();
	
				// 更新
				$basicTable->updateInfo($this->_warehouseSession->warehouseId, array(
					'statement_shop_info' => $success['statement_shop_info'],
				));
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }

    /**
     * logoUploadAction
     *
     * @param void
     * @return void
     */
    /*----------------------------------------------------------------------------+
    |  action_URL    * /item/logo-upload                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ロゴ画像アップロード                                       |
    +----------------------------------------------------------------------------*/
    public function logoUploadAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
		$request  = $this->getRequest();

		if (empty($_FILES['image']['tmp_name'])) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		$ext = 'png';
		$key = uniqid();
		$fileName = $key . $ext;
		
		// PNGに変換して保存
		$tempFilePath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath($fileName);
		$img =  $this->imageCreateFromAny($_FILES['image']['tmp_name']);
		ImagePNG($img, $tempFilePath);
		
        $saveResult = Shared_Model_Resource_Logo::makeResource($key, file_get_contents($tempFilePath));

		if (empty($saveResult)) {
	        $this->sendJson(array('result' => false));
	        return;
		}
		
		// 更新
		$basicTable = new Shared_Model_Data_BasicInfo();
		$basicTable->updateInfo($this->_warehouseSession->warehouseId, array('logo_file_name' => $key));
        

        $this->sendJson(array('result' => true, 'file_url' => Shared_Model_Resource_Logo::getResourceUrl($key)));
        return;
	}

    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template                                          |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート設定                                           |
    +----------------------------------------------------------------------------*/
    public function templateAction()
    {		
		$request = $this->getRequest();
		$this->view->templateType = $templateType = $request->getParam('type');
		$this->view->posTop = $request->getParam('pos');

		$basicTable  = new Shared_Model_Data_BasicInfo();
		$templeTable = new Shared_Model_Data_MessageTemplate();
		
		$this->view->data = $basicTable->get($this->_warehouseSession->warehouseId);
		$this->view->templateList = $templeTable->getListByTemplateType($this->_warehouseSession->warehouseId, $templateType);
		
		var_dump($this->_warehouseSession->warehouseId);
		var_dump($templateType);
		var_dump($this->view->templateList);
		
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template/:id/sample.pdf                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * サンプルPDF表示                                            |
    +----------------------------------------------------------------------------*/
    public function templateSampleAction()
	{
		$request = $this->getRequest();
		$id = $request->getParam('id');

		$basicTable  = new Shared_Model_Data_BasicInfo();
		$templeTable = new Shared_Model_Data_MessageTemplate();
		
		$basicInfo    = $basicTable->get($this->_warehouseSession->warehouseId);
		$templateData = $templeTable->getById($this->_warehouseSession->warehouseId, $id);


		$params = Shared_Model_Pdf_ShipmentReceipt::createDefaultParams();
		$params['logo_path'] = Shared_Model_Resource_Logo::getResourceObjectPath($basicInfo['logo_file_name']);
		$params['shop_info'] = $basicInfo['statement_shop_info'];
		
		if ($templateData['template_type'] == Shared_Model_Code::STATEMENT_TEMPLATE_TYPE_DEFAULT_1
		 || $templateData['template_type'] == Shared_Model_Code::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_1) {
			$params['template_1'] = $templateData['message'];
		
		} else if ($templateData['template_type'] == Shared_Model_Code::STATEMENT_TEMPLATE_TYPE_DEFAULT_2
		 || $templateData['template_type'] == Shared_Model_Code::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_2) {
		 	$params['template_2'] = $templateData['message'];
		 
		} else if ($templateData['template_type'] == Shared_Model_Code::STATEMENT_TEMPLATE_TYPE_DEFAULT_3
		 || $templateData['template_type'] == Shared_Model_Code::STATEMENT_TEMPLATE_TYPE_SUBSCRIPTION_3) {
			$params['template_3'] = $templateData['message'];
			
		} else {
			throw new Zend_Exception('/setting/template/:id/sample.pdf invalid');
		}
		
    	Shared_Model_Pdf_ShipmentReceipt::makeSingle($params);
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template-select                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート選択(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function templateSelectAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request      = $this->getRequest();
		$templateType = $request->getParam('type');
		$id           = $request->getParam('id');
		
		$templateKeyList   = Shared_Model_Code::codes('template_type_key');
		$columnKey = $templateKeyList[$templateType];
		
		$basicTable  = new Shared_Model_Data_BasicInfo();
		$basicTable->updateInfo($this->_warehouseSession->warehouseId, array($columnKey => $id));

	    $this->sendJson(array('result' => 'OK'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template-add                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート追加                                           |
    +----------------------------------------------------------------------------*/
    public function templateAddAction()
    {
		$request = $this->getRequest();
		$this->view->templateType = $templateType = $request->getParam('type');
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template-edit                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート編集                                           |
    +----------------------------------------------------------------------------*/
    public function templateEditAction()
    {
    	$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		$templeTable = new Shared_Model_Data_MessageTemplate();
		$this->view->data = $templeTable->getById($this->_warehouseSession->warehouseId, $id);
		
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template-update                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート更新(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function templateUpdateAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
    	$request = $this->getRequest();
		$this->view->id = $id = $request->getParam('id');
		
		// POST送信時
		if ($request->isPost()) {
			$config = $this->getActionConfig();
			$validate = new Nutex_Parameters_Validate($config);

            $validationResult = $validate->execute($request->getPost());
            $success = $validate->getFiltered();
         		    
			if ($validationResult == false) {
				// バリデーションエラー時
                $errorMessage = $validate->getErrorMessage();

                if (!empty($errorMessage['title']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「テンプレート名」を入力してください'));
                    return;
                } else if (!empty($errorMessage['message']['isEmpty'])) {
                    $this->sendJson(array('result' => 'NG', 'message' => '「内容」を選択してください'));
                    return;
                }
                
			    $this->sendJson(array('result' => 'NG', 'message' => '予期せぬエラーが発生しました'));
	    		return;
	    		
			} else {
				$templeTable = new Shared_Model_Data_MessageTemplate();
				
				if (!empty($id)) {
					// 更新
					$data = array(
						'title'         => $success['title'],
						'message'       => $success['message'],
					);
					
					$templeTable->updateById($this->_warehouseSession->warehouseId, $id, $data);
					
				} else {
					// 新規登録
					$data = array(
						'warehouse_id'  => $this->_warehouseSession->warehouseId,
				        'template_type' => $success['template_type'],
				        'status'        => Shared_Model_Code::STATEMENT_TEMPLATE_STATUS_ACTIVE,
						'title'         => $success['title'],
						'message'       => $success['message'],
						
		                'created'       => new Zend_Db_Expr('now()'),
		                'updated'       => new Zend_Db_Expr('now()'),
					);
					
					$templeTable->create($data);
				}
			}
			
		    $this->sendJson(array('result' => 'OK'));
	    	return;
		}

	    $this->sendJson(array('result' => 'NG'));
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /setting/template-delete                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * テンプレート削除(Ajax)                                     |
    +----------------------------------------------------------------------------*/
    public function templateDeleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        
        
    }
}

