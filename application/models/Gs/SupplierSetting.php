<?php
/**
 * class Shared_Model_Gs_SupplierSetting
 * goosa連携 サプライヤー設定
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Gs_SupplierSetting
{

    /**
     * 送料設定リスト取得
     * @param  array $clientData
     * @return array $itemData
     */
    public static function getShippingSettingList($clientData)
    {
		$url = 'https://' . GS_DOMAIN . '/api/supplier-setting/shipping-list?supplier_id=' . $clientData['supplier_id'];

        $httpClient = new Zend_Http_Client($url);
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }


        $response = $httpClient->request(Zend_Http_Client::GET);

        if ($response->getStatus() != '200') {
            return false;
        }
        
        return json_decode($response->getBody(), true);	   
	}

    /**
     * 承認待ち件数取得
     * @param  array $clientData
     * @return array $itemData
     */
    public static function getApprovalCount($clientData)
    {
		$url = 'https://' . GS_DOMAIN . '/api/supplier-setting/approval-count';

        $httpClient = new Zend_Http_Client($url);
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }


        $response = $httpClient->request(Zend_Http_Client::GET);

        if ($response->getStatus() != '200') {
            return false;
        }
        
        return json_decode($response->getBody(), true);	   
	}
    
}