<?php
/**
 * class Shared_Model_Gs_Account
 *
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Gs_Account
{

    /**
     * 入金完了済み同期
     * @param  array $clientData
     * @param  array $data
     * @return array $json
     */
    public static function updateToReceivedStatus($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}

		$params = array(
			'relational_id'   => $data['relational_id'],
			'received_date'   => $data['received_date'],
		);
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN . '/api/account/received');
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }
		
        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);
		
		//var_dump($response->getBody());exit;
		
        if ($response->getStatus() != '200') {
            return false;
        }

        return json_decode($response->getBody(), true);
	}

    /**
     * 未入金同期
     * @param  array $clientData
     * @param  array $data
     * @return array $json
     */
    public static function updateToUnreceivedStatus($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}

		$params = array(
			'relational_id'   => $data['relational_id'],
		);
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN . '/api/account/unreceived');
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }
		
        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);
		
        if ($response->getStatus() != '200') {
            return false;
        }

        return json_decode($response->getBody(), true);
	}
	
	

    /**
     * 支払完了済み同期
     * @param  int   $clientData
     * @return array $data
     */
    public static function updateToPaidStatus($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$params = array(
			'relational_id'   => $data['relational_id'],
			'paid_date'       => $data['paid_date'],
		);
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN. '/api/account/paid');
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }

        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);
		
        if ($response->getStatus() != '200') {
            return false;
        }

        return json_decode($response->getBody(), true);
    }


    /**
     * 支払未完了同期
     * @param  int   $clientData
     * @return array $data
     */
    public static function updateToUnpaidStatus($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$params = array(
			'relational_id'   => $data['relational_id'],
			'paid_date'       => $data['paid_date'],
		);
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN. '/api/account/unpaid');
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }

        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);

        if ($response->getStatus() != '200') {
            return false;
        }

        return json_decode($response->getBody(), true);
    }



    
}