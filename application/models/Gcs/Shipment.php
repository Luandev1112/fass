<?php
/**
 * class Shared_Model_Gcs_Shipment
 *
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Gcs_Shipment
{

    /**
     * 発送済み
     * @param  array $clientData
     * @param  array $data
     * @return array $json
     */
    public static function updateToShipped($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Shipment clientData is empty');
    	}

		$params = array(
		    'supplier_id'         => '8',
            'order_id'            => $data['relational_order_id'],
            'delivery_agent'      => '10',                          // 配送業者選択 - ヤマト運輸
            'delivery_code'       => $data['delivery_code'],        // 伝票番号
		);
		
        $httpClient = new Zend_Http_Client('https://' . GCS_DOMAIN . '/api/shipment/update-to-shipped');

        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }
        
        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);
		//var_dump($response->getBody());
		
        if ($response->getStatus() != '200') {
            return false;
        }
        
        return json_decode($response->getBody(), true);
	}
	


    /**
     * 発送済み
     * @param  array $clientData
     * @param  array $data
     * @return array $json
     */
    public static function updateReShipped($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Shipment clientData is empty');
    	}
        
        //var_dump($data['shipment_datetime']);exit;
        
		$params = array(
		    'supplier_id'         => '8',
            'order_id'            => $data['relational_order_id'],
            'shipment_datetime'   => $data['shipment_datetime'],
            'delivery_agent'      => '10',                          // 配送業者選択 - ヤマト運輸
            'delivery_code'       => $data['delivery_code'],        // 伝票番号
		);
		
        $httpClient = new Zend_Http_Client('https://' . GCS_DOMAIN . '/api/shipment/update-re-shipped');

        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }
        
        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);
		
		//var_dump($response->getBody());exit;
		
        if ($response->getStatus() != '200') {
            var_dump($response->getBody());
            
            throw new Zend_Exception('api error');
        }
        //var_dump($response->getBody());
        return json_decode($response->getBody(), true);
	}
	
	
}