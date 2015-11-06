<?php
/**
 * class Shared_Model_Payment_Jaccs
 * ジャックスアトディーネAPI連携
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Payment_Jaccs
{
    /**
     * 会社名で検索
     * @return array $data
     */
    public static function getSetting()
    {
	    return array(
		    'member_code'    => 'atd000536501', // 加盟店コード
		    'link_id'        => 'original00',   // 接続先特定ID
		    'link_password'  => 'H6A5GtxWpG',   // 接続先特定ID
	    );
	}
	
	
    /**
     * 取引登録
     * @param array $data
     * @param array $orderItems
     * @param array $memberData
     * @param array $userData
     */
    public static function reister($data, $orderItems, $memberData, $userData)
    {
	    $setting = self::getSetting();
	    
	    //var_dump($userData['mail']);exit;
	    
	    $userData['mail'] = 'goosca.net@gmail.com';
	    
        $orderer = array();
        $prefectureList = Shared_Model_Code::codes('prefecture');
        
        
        //var_dump($memberData);
        
		if (!empty($memberData)) {
	    	$buyerTable = new Shared_Model_Data_Buyer();
	    	$buyerData = $buyerTable->getById($memberData['buyer_id']);
		    //var_dump($buyerData);
		    //var_dump('会員');
			// 会員
    	    if ($buyerData['country_select'] === (string)Shared_Model_Code::COUNTTRY_JP) {
    			$postalCode = substr($buyerData['postal_code'], 0, 3) . '-' . substr($buyerData['postal_code'], 3, 4);
    		} else {
    			throw new Zend_Exception('Shared_Model_Payment_Jaccs - reister: other countires');
    		}

			$orderer['user_name_sei']      = $userData['user_name_sei'];
			$orderer['user_name_mei']      = $userData['user_name_mei'];
			$orderer['user_name_kana_sei'] = $userData['user_name_kana_sei'];
			$orderer['user_name_kana_mei'] = $userData['user_name_kana_mei'];
			$orderer['postal_code']        = $postalCode;
			$orderer['address']            = $prefectureList[$buyerData['prefecture']] . $buyerData['city'] . $buyerData['address'];
			if (!empty($data['building'])) {
        	    $orderer['address'] .= '　' . $data['building'];
        	}
        	
			$orderer['tel']                = $userData['staff_tel'];
			$orderer['mail']               = $userData['mail'];
			

		} else {
		    //var_dump('非会員');
			// 非会員
			$orderer['user_name_sei']      = $data['orderer_name_sei'];
			$orderer['user_name_mei']      = $data['orderer_name_mei'];
			$orderer['user_name_kana_sei'] = $data['orderer_name_sei_kana'];
			$orderer['user_name_kana_mei'] = $data['orderer_name_mei_kana'];
			$orderer['postal_code']        = $data['orderer_postal_code'];
			$orderer['address']            = $prefectureList[$data['orderer_prefecture']] . $data['orderer_city'] . $data['orderer_address'];
			if (!empty($data['building'])) {
			    $orderer['address'] .= '　' . $data['orderer_building'];
			}
			$orderer['tel']                = $data['orderer_tel'];
			$orderer['mail']               = $data['orderer_mobile'];
		}


        $deliveryPostalCode = '';
	    if ($data['country_select'] === (string)Shared_Model_Code::COUNTTRY_JP) {
			$deliveryPostalCode = substr($data['delivery_postal_code'], 0, 3) . '-' . substr($data['delivery_postal_code'], 3, 4);
		} else {
			throw new Zend_Exception('Shared_Model_Payment_Jaccs - reister: delivery other countires');
		}
    	
    	
    	$deliveryAddress = $prefectureList[$data['delivery_prefecture']] . $data['delivery_city'] . $data['delivery_address1'];
    	if (!empty($data['delivery_address2'])) {
    	    $deliveryAddress .= '　' . $data['delivery_address2'];
    	}			

	    $xml = '';
        $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<request>';
        $xml .=   '<linkInfo>';
        $xml .=     '<shopCode>' . $setting['member_code'] . '</shopCode>';
        $xml .=     '<linkId>'. $setting['link_id'] .'</linkId>';
        $xml .=     '<linkPassword>'. $setting['link_password'] .'</linkPassword>';
        $xml .=   '</linkInfo>';
        $xml .=   '<browserInfo>';
        $xml .=     '<httpHeader></httpHeader>';
        $xml .=     '<deviceInfo></deviceInfo>';
        $xml .=   '</browserInfo>';

        $xml .=   '<customer>';
        $xml .=     '<shopOrderId>' . $data['display_id'] . '</shopOrderId>';
        $xml .=     '<shopOrderDate>' . date('Y/m/d') . '</shopOrderDate>';
        $xml .=     '<name>' . $orderer['user_name_sei'] . '　' . $orderer['user_name_mei'] . '</name>';
        $xml .=     '<kanaName>' . $orderer['user_name_kana_sei'] . '　' . $orderer['user_name_kana_mei'] . '</kanaName>';
        $xml .=     '<zip>' . $orderer['postal_code'] . '</zip>';
        $xml .=     '<address>' . $orderer['address'] . '</address>';
        $xml .=     '<companyName></companyName>';
        $xml .=     '<sectionName></sectionName>';
        $xml .=     '<tel>' . $orderer['tel'] . '</tel>';
        $xml .=     '<email>' . $orderer['mail'] . '</email>';
        $xml .=     '<billedAmount>' . $data['total_with_tax'] . '</billedAmount>';
        $xml .=     '<expand1></expand1>';
        $xml .=     '<service>2</service>';
        $xml .=   '</customer>';

        var_dump($orderer);
        
        
        $xml .=   '<ship>';
        //$xml .=     '<shipName>' . $data['delivery_name'] . '</shipName>';
        //$xml .=     '<shipKananame>' . $data['delivery_name_kana'] . '</shipKananame>';
        // 仮
        $xml .=     '<shipName>' . $orderer['user_name_sei'] . '　' . $orderer['user_name_mei'] . '</shipName>';
        $xml .=     '<shipKananame>' . $orderer['user_name_kana_sei'] . '　' . $orderer['user_name_kana_mei'] . '</shipKananame>';   
        
        $xml .=     '<shipZip>' . $deliveryPostalCode . '</shipZip>';
        $xml .=     '<shipAddress>' . $deliveryAddress . '</shipAddress>';
        $xml .=     '<shipCompanyName></shipCompanyName>';	
        $xml .=     '<shipSectionName></shipSectionName>';
        $xml .=     '<shipTel>' . $data['delivery_tel'] . '</shipTel>';
        $xml .=   '</ship>';
        
        $xml .=   '<details>';
        foreach ($orderItems as $lotId => $eachOrderItem) {
            //var_dump($eachOrderItem['priceData']);
            //var_dump($eachOrderItem['orderItemData']);
            $xml .=     '<detail>';
            $xml .=       '<goods>' . $eachOrderItem['priceData']['item_name'] . '</goods>';
            $xml .=       '<goodsPrice>' . $eachOrderItem['orderItemData']['total_with_tax'] . '</goodsPrice>';
            $xml .=       '<goodsAmount>' . $eachOrderItem['orderItemData']['amount'] . '</goodsAmount>';
            $xml .=       '<expand2>1</expand2>';
            $xml .=       '<expand3></expand3>';
            $xml .=       '<expand4></expand4>';
            $xml .=     '</detail>';
        }
        $xml .=   '</details>';
        $xml .= '</request>';
        
        //echo htmlspecialchars($xml);
        //exit;

		//$url = 'https://www.manage.atodene.jp/api/transaction.do';   // 本番
		$url = 'https://devwb01.manage.atodene.jp/api/transaction.do'; // 開発
		
        $httpClient = new Zend_Http_Client($url);
        $httpClient->setHeaders(array(
	    	'Content-Type'          => 'application/xml; charset=UTF-8',
			'Content-Length'        => strlen($xml),
	    ));
		
        $httpClient->setRawData($xml, 'application/XML');
        
        $response = $httpClient->request(Zend_Http_Client::POST);
		//var_dump(htmlspecialchars($response->getBody()));
        //exit;
        
        if ($response->getStatus() != '200') {
            return false;
        }
        
        return simplexml_load_string($response->getBody());
	}



    /**
     * 請求書印字データ取得API
     */
    public static function importInvoiceData($transactionId)
    {
        $setting = self::getSetting();
        
	    $xml = '';
        $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<request>';
        $xml .=   '<linkInfo>';
        $xml .=     '<shopCode>' . $setting['member_code'] . '</shopCode>';
        $xml .=     '<linkId>'. $setting['link_id'] .'</linkId>';
        $xml .=     '<linkPassword>'. $setting['link_password'] .'</linkPassword>';
        $xml .=   '</linkInfo>';
        
        $xml .=   '<transactionInfo>';
        $xml .=     '<transactionId>' . $transactionId . '</transactionId>';
        $xml .=   '</transactionInfo>';

        $xml .= '</request>';
        //var_dump(htmlspecialchars($xml));exit;

		//$url = 'https://www.manage.atodene.jp/api/getinvoicedata.do';   // 本番
		$url = 'https://devwb01.manage.atodene.jp/api/getinvoicedata.do'; // 開発
		
        $httpClient = new Zend_Http_Client($url);
        $httpClient->setHeaders(array(
	    	'Content-Type'          => 'application/xml; charset=UTF-8',
			'Content-Length'        => strlen($xml),
	    ));
		
        $httpClient->setRawData($xml, 'application/XML');
        
        $response = $httpClient->request(Zend_Http_Client::POST);
		//var_dump(htmlspecialchars($response->getBody()));
        //exit;
        
        if ($response->getStatus() != '200') {
            return false;
        }
        
        return simplexml_load_string($response->getBody());
        
    }


    

}