<?php
/**
 * class Api_PayableController
 */

class Api_OrderController extends Api_Model_Controller
{
	
    /**
     * addAction
     * 登録・更新
     */
    public function addAction()
    {
        $request    = $this->getRequest();
		$params     = $request->getParams();


		$connectionTable  = new Shared_Model_Data_Connection();
		$connectionData   = $connectionTable->getById('1', $params['fass_connection_id']);
  
		try {
			
			$orderFormTable   = new Shared_Model_Data_DirectOrderForm();

            $defaultItems = array();
            $defaultItems[] = array(
	            'id'           => '1',
	            'supply_type'  => '1',
	            'supply_id'    => '1',
	            'item_name'    => 'XXXXXXXXXXXXXXXXXX',
	            'spec'         => '',
	            'unit_price'   => '560',
	            'amount'       => '10',
	            'price'        => '5600',
            );

	        $defaultConditionItems = array();
            $defaultConditionItems[] = array(
	            'id'         => '1', 'label'      => '', 'content' => '',
            );
            
			$nextOrderFormId = $orderFormTable->getNextDisplayId();
            
            
$memo  = "【注文前提条件】\n";
$memo .= "　納入期限：最短納期を希望\n";
$memo .= "　納入場所：〒" . $params['postal_code'] . "\n";
$memo .= "　　　　　　東京都XXXXXXXXXXXXXX\n"; 
$memo .= "　　　　　　XXXXXXXX宛\n";
$memo .= "　　　　　　TEL：XX-XXXX-XXXX\n";
$memo .= "\n";
$memo .= "【発送者名】XXXXXXXXXXXXXXX";

/*
$memo .= "\n";
$memo .= "【注文後納期回答】必要";
$memo .= "【配送伝票番号連絡】必要";
*/
        		
            
			$data = array(
				'language'                          => '1', // 言語選択
		        'management_group_id'               => '1',
		        'display_id'                        => $nextOrderFormId,
				'status'                            => Shared_Model_Code::ORDER_FORM_STATUS_DRAFT,
				'order_form_type'                   => Shared_Model_Code::ORDER_FORM_TYPE_CREATE,
				'target_connection_id'              => $params['fass_connection_id'],
				'to_name'                           => $connectionData['company_name'] . ' 御中',  // 宛先
				
				'order_date'                        => date('Y-m-d'),                             // 発注日
					
				'title'                             => '発注書',
					
				'labels'                            => json_encode($orderFormTable->getDefaultLabels(Shared_Model_Code::LANGUAGE_JP)),     // テーブル項目ラベル
				'item_list'                         => json_encode($defaultItems),
				'warehouse_id'                      => 0,                                      // 納入希望先倉庫ID
				'conditions'                        => json_encode($defaultConditionItems),    // 前提条件
				
				'memo'                              => $memo,      // 備考
				'memo_private'                      => '',         // 社内メモ
				'approval_comment'                  => '',         // 承認コメント
		
				'subtotal'                          => $params['subtotal'],            // 小計
				'tax_percentage'                    => $params['tax_percentage'],      // 消費税率
				'tax'                               => $params['tax'],                 // 消費税
				'total_with_tax'                    => $params['total_with_tax'],      // 合計
			
				'created_user_id'                   => '1',    // 作成者ユーザーID
				'last_update_user_id'               => '1',    // 最終更新者ユーザーID
				'approval_user_id'                  => 0,      // 承認者ユーザーID
				
                'created'                           => new Zend_Db_Expr('now()'),
                'updated'                           => new Zend_Db_Expr('now()'),
			);

			$data['currency_mark'] = '¥';
				
			$orderFormTable->getAdapter()->beginTransaction();
        	  
            try {
				$orderFormTable->create($data);
				$id = $orderFormTable->getLastInsertedId('id');

                // commit
                $orderFormTable->getAdapter()->commit();
                
            } catch (Exception $e) {
                $orderFormTable->getAdapter()->rollBack();
                //throw new Zend_Exception('/api/order/add transaction faied: ' . $e);
                var_dump($e);
            }
			
		    $this->sendJson(array('result' => 'OK', 'id' => $id));
	    	return;
			
			
        } catch (Exception $e) {
            $payableTable->getAdapter()->rollBack();
            throw new Zend_Exception('/api/order/add transaction faied: ' . $e);
        }

		return $this->sendJson($params);
    }



}