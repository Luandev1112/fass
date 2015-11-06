<?php
/**
 * class RsrcController
 */
 
class RsrcController extends Front_Model_Controller
{
    /**
     * preDispatch
     *
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_helper->disableView();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/manual                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * マニュアルアップロードファイル                             |
    +----------------------------------------------------------------------------*/
    public function manualAction()
    {
		$request      = $this->getRequest();
		$manualId     = $request->getParam('manual_id');
		$itemId       = $request->getParam('item_id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_Manual::isExist($manualId, $itemId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'bmp' || $ext == 'gif') {
		        $this->_helper->binaryOutput(Shared_Model_Resource_Manual::getBinary($manualId, $itemId, $fileName), array(
		            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
		            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'image/' . $ext,
		        ));	
			} else if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Manual::getFileSize($manualId, $itemId, $fileName));
				echo file_get_contents(Shared_Model_Resource_Manual::getResourceObjectPath($manualId, $itemId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Manual::getBinary($manualId, $itemId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/stamp/:user_id                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 印鑑画像                                                   |
    +----------------------------------------------------------------------------*/
    public function stampAction()
    {
		$request      = $this->getRequest();
		$userId       = $request->getParam('user_id');
		
        $this->_helper->binaryOutput(Shared_Model_Resource_Stamp::getBinary($userId), array(
            Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
            Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'image/png',
        ));	
    }
 
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/warehouse-item/:id/:file_name                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 在庫資材写真                                               |
    +----------------------------------------------------------------------------*/
    public function warehouseItemAction()
    {
		$request      = $this->getRequest();
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		$arr = explode('.', $fileName);
		
        $isExist = Shared_Model_Resource_WarehouseItem::isExist($id, $arr[0]);

        if ($isExist) {	
            $this->_helper->binaryOutput(Shared_Model_Resource_WarehouseItem::getBinary($id, $arr[0]), array(
                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'image/jpg',
            ));
        } else {
 			// 404
			throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
       
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/item-document                                        |
    +-----------------------------------------------------------------------------+
    |  アクション名  * アイテム 資料                                              |
    +----------------------------------------------------------------------------*/
    public function itemDocumentAction()
    {
		$request      = $this->getRequest();
		$itemId       = $request->getParam('item_id');
		$documentId   = $request->getParam('document_id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_ItemDocument::isExist($itemId, $documentId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_ItemDocument::getFileSize($itemId, $documentId, $fileName));
				echo file_get_contents(Shared_Model_Resource_ItemDocument::getResourceObjectPath($itemId, $documentId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_ItemDocument::getBinary($itemId, $documentId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}

        } else {
 			// 404
			throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }


    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/material                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料                                                       |
    +----------------------------------------------------------------------------*/
    public function materialAction()
    {
		$request      = $this->getRequest();
		$materialId   = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
		$materialTable = new Shared_Model_Data_Material();
		$materialData = $materialTable->getById($this->_adminProperty['management_group_id'], $materialId);
		

        $isExist = Shared_Model_Resource_Material::isExist($this->_adminProperty['management_group_id'], $materialId, $materialData['file_name']);
        
        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Material::getFileSize($this->_adminProperty['management_group_id'], $materialId, $materialData['file_name']));
				echo file_get_contents(Shared_Model_Resource_Material::getResourceObjectPath($this->_adminProperty['management_group_id'], $materialId, $materialData['file_name']));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Material::getBinary($this->_adminProperty['management_group_id'], $materialId, $materialData['file_name']), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}

        } else {
 			// 404
			throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/material-history                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 資料(履歴)                                                 |
    +----------------------------------------------------------------------------*/
    public function materialHistoryAction()
    {
		$request      = $this->getRequest();
		$historyId    = $request->getParam('history_id');
		$fileName     = $request->getParam('file_name');

		$materialHistoryTable = new Shared_Model_Data_MaterialHistory();
		$historyData = $materialHistoryTable->getById($historyId);

        $isExist = Shared_Model_Resource_Material::isExist($this->_adminProperty['management_group_id'], $historyData['material_id'], $historyData['file_name']);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Material::getFileSize($this->_adminProperty['management_group_id'], $historyData['material_id'], $historyData['file_name']));
				echo file_get_contents(Shared_Model_Resource_Material::getResourceObjectPath($this->_adminProperty['management_group_id'], $historyData['material_id'], $historyData['file_name']));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Material::getBinary($this->_adminProperty['management_group_id'], $historyData['material_id'], $historyData['file_name']), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}

        } else {
 			// 404
			throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/supply-product                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 原料・製品 入手見積書・補足資料                            |
    +----------------------------------------------------------------------------*/
    public function supplyProductAction()
    {
		$request      = $this->getRequest();
		$productId    = $request->getParam('product_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');

        $isExist = Shared_Model_Resource_SupplyProduct::isExist($productId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_SupplyProduct::getFileSize($productId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_SupplyProduct::getResourceObjectPath($productId, $id, $fileName));
				
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_SupplyProduct::getBinary($productId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
            
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/supply-production                                    |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 製造加工委託 入手見積書・補足資料                          |
    +----------------------------------------------------------------------------*/
    public function supplyProductionAction()
    {
		$request      = $this->getRequest();
		$productionId = $request->getParam('production_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');

        $isExist = Shared_Model_Resource_SupplyProduction::isExist($productionId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_SupplyProduction::getFileSize($productionId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_SupplyProduction::getResourceObjectPath($productionId, $id, $fileName));
				
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_SupplyProduction::getBinary($productionId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
            
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/supply-subcontracting                                |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 業務委託 入手見積書・補足資料                              |
    +----------------------------------------------------------------------------*/
    public function supplySubcontractingAction()
    {
		$request          = $this->getRequest();
		$subcontractingId = $request->getParam('subcontracting_id');
		$id               = $request->getParam('id');
		$fileName         = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_SupplySubcontracting::isExist($subcontractingId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_SupplySubcontracting::getFileSize($subcontractingId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_SupplySubcontracting::getResourceObjectPath($subcontractingId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_SupplySubcontracting::getBinary($subcontractingId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/supply-fixture                                       |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 入手見積書・補足資料                              |
    +----------------------------------------------------------------------------*/
    public function supplyFixtureAction()
    {
		$request      = $this->getRequest();
		$fixtureId    = $request->getParam('fixture_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_SupplyFixture::isExist($fixtureId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_SupplyFixture::getFileSize($fixtureId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_SupplyFixture::getResourceObjectPath($fixtureId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_SupplyFixture::getBinary($fixtureId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/supply-competition                                   |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 入手見積書・補足資料                              |
    +----------------------------------------------------------------------------*/
    public function supplyCompetitionAction()
    {
		$request        = $this->getRequest();
		$competitionId  = $request->getParam('competition_id');
		$id             = $request->getParam('id');
		$fileName       = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_SupplyCompetition::isExist($competitionId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_SupplyCompetition::getFileSize($competitionId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_SupplyCompetition::getResourceObjectPath($competitionId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_SupplyCompetition::getBinary($competitionId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/estimate                                             |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 備品資材 入手見積書・補足資料                              |
    +----------------------------------------------------------------------------*/
    public function estimateAction()
    {
		$request        = $this->getRequest();
		$estimateId     = $request->getParam('estimate_id');
		$versionId      = $request->getParam('version_id');
		$fileName       = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_EstimateUpload::isExist($estimateId, $versionId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_EstimateUpload::getFileSize($estimateId, $versionId, $fileName));
				echo file_get_contents(Shared_Model_Resource_EstimateUpload::getResourceObjectPath($estimateId, $versionId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_EstimateUpload::getBinary($estimateId, $versionId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/invoice                                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求書アップロード                                         |
    +----------------------------------------------------------------------------*/
    public function invoiceAction()
    {
		$request        = $this->getRequest();
		$invoiceId      = $request->getParam('invoice_id');
		$fileName       = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_Invoice::isExist($invoiceId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Invoice::getFileSize($invoiceId, $fileName));
				echo file_get_contents(Shared_Model_Resource_Invoice::getResourceObjectPath($invoiceId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Invoice::getBinary($invoiceId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
        
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/order-form                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 注文書アップロード                                         |
    +----------------------------------------------------------------------------*/
    public function orderFormAction()
    {
		$request        = $this->getRequest();
		$orderFormId    = $request->getParam('order_form_id');
		$fileName       = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_OrderForm::isExist($orderFormId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_OrderForm::getFileSize($orderFormId, $fileName));
				echo file_get_contents(Shared_Model_Resource_OrderForm::getResourceObjectPath($orderFormId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_OrderForm::getBinary($orderFormId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/online-purchase                                      |
    +-----------------------------------------------------------------------------+
    |  アクション名  * ネット購入委託管理 添付資料                                |
    +----------------------------------------------------------------------------*/
    public function onlinePurchaseAction()
    {
		$request           = $this->getRequest();
		$onlinePurchaseId  = $request->getParam('online_purchase_id');
		$fileName          = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_OnlinePurchase::isExist($onlinePurchaseId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_OnlinePurchase::getFileSize($onlinePurchaseId, $fileName));
				echo file_get_contents(Shared_Model_Resource_OnlinePurchase::getResourceObjectPath($onlinePurchaseId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_OnlinePurchase::getBinary($onlinePurchaseId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/direct-order                                         |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 受注管理 添付資料                                          |
    +----------------------------------------------------------------------------*/
    public function directOrderAction()
    {
		$request        = $this->getRequest();
		$directOrderId  = $request->getParam('direct_order_id');
		$fileName       = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_DirectOrder::isExist($directOrderId, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_DirectOrder::getFileSize($directOrderId, $fileName));
				echo file_get_contents(Shared_Model_Resource_DirectOrder::getResourceObjectPath($directOrderId, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_DirectOrder::getBinary($directOrderId, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
			}
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/payable                                              |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 請求支払申請 請求書ファイルアップロード                    |
    +----------------------------------------------------------------------------*/
    public function payableAction()
    {
		$request      = $this->getRequest();
		$payableId    = $request->getParam('payable_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_Payable::isExist($payableId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Payable::getFileSize($payableId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_Payable::getResourceObjectPath($payableId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Payable::getBinary($payableId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/payable-template                                     |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支払管理 参考資料ファイルアップロード                  |
    +----------------------------------------------------------------------------*/
    public function payableTemplateAction()
    {
		$request      = $this->getRequest();
		$templateId   = $request->getParam('template_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_PayableTemplate::isExist($templateId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_PayableTemplate::getFileSize($templateId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_PayableTemplate::getResourceObjectPath($templateId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_PayableTemplate::getBinary($templateId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }

    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/receivable                                           |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 入金管理 ファイルアップロード                              |
    +----------------------------------------------------------------------------*/
    public function receivableAction()
    {
		$request      = $this->getRequest();
		$receivableId = $request->getParam('receivable_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_Receivable::isExist($receivableId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Receivable::getFileSize($receivableId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_Receivable::getResourceObjectPath($receivableId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Receivable::getBinary($receivableId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/receivable-template                                  |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 毎月支入金管理 参考資料ファイルアップロード                |
    +----------------------------------------------------------------------------*/
    public function receivableTemplateAction()
    {
		$request      = $this->getRequest();
		$templateId   = $request->getParam('template_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_ReceivableTemplate::isExist($templateId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_ReceivableTemplate::getFileSize($templateId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_ReceivableTemplate::getResourceObjectPath($templateId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_ReceivableTemplate::getBinary($templateId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
    
    /*----------------------------------------------------------------------------+
    |  action_URL    * /rsrc/record                                               |
    +-----------------------------------------------------------------------------+
    |  アクション名  * 議事録ファイルアップロード                                 |
    +----------------------------------------------------------------------------*/
    public function recordAction()
    {
		$request      = $this->getRequest();
		$recordId     = $request->getParam('record_id');
		$id           = $request->getParam('id');
		$fileName     = $request->getParam('file_name');
		
        $isExist = Shared_Model_Resource_Record::isExist($recordId, $id, $fileName);

        if ($isExist) {
        	$arr = explode('.', $fileName);
			$ext = end($arr);
			
			if ($ext == 'pdf') {
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="' . $fileName . '"');
				header('Content-Length: ' . Shared_Model_Resource_Record::getFileSize($recordId, $id, $fileName));
				echo file_get_contents(Shared_Model_Resource_Record::getResourceObjectPath($recordId, $id, $fileName));
			} else {
	            $this->_helper->binaryOutput(Shared_Model_Resource_Record::getBinary($recordId, $id, $fileName), array(
	                Nutex_Helper_Action_BinaryOutput::OPT_BINARY => true,
	                Nutex_Helper_Action_BinaryOutput::OPT_CONTENT_TYPE => 'application/octet-stream',
	            ));
            }
        } else {
           // 404
           throw new Zend_Controller_Action_Exception('document not exist', 404);
        }
    }
}

