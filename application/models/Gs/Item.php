<?php
/**
 * class Shared_Model_Gs_Item
 *
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Gs_Item
{

    /**
     * メインカテゴリ取得
     * @param  array $clientData
     * @return array $itemData
     */
    public static function getMainCatgeoryList($clientData)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$url = 'https://' . GS_DOMAIN . '/api/item/main-category';

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
     * サブカテゴリ取得
     * @param  array $clientData
     * @return array $itemData
     */
    public static function getSubCatgeoryList($clientData, $mainCategoryId)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$url = 'https://' . GS_DOMAIN . '/api/item/sub-category?category_id=' . $mainCategoryId;

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
     * 第3カテゴリ取得
     * @param  array $clientData
     * @return array $itemData
     */
    public static function getThirdCatgeoryList($clientData, $subCategoryId)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$url = 'https://' . GS_DOMAIN . '/api/item/third-category?sub_category_id=' . $subCategoryId;

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
     * 第4カテゴリ取得
     * @param  array $clientData
     * @return array $itemData
     */
    public static function getFourthCatgeoryList($clientData, $thirdCategoryId)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$url = 'https://' . GS_DOMAIN . '/api/item/fourth-category?third_category_id=' . $thirdCategoryId;

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
     * 商品新規登録
     * @param  int   $clientData
     * @return array $data
     */
    public static function addItem($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$params = array(
			'supplier_item_id'   => $data['supplier_item_id'],
			'category_id'        => $data['category_id'],
			'sub_category_id'    => $data['sub_category_id'],
			'third_category_id'  => $data['third_category_id'],
			'fourth_category_id' => $data['fourth_category_id'],
			'item_name'          => $data['item_name'],
			'item_name_kana'     => $data['item_name_kana'],
		);
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN. '/api/item/add?supplier_id=' . $clientData['supplier_id']);
        
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
     * 商品新規登録
     * @param  int   $clientData
     * @return array $data
     */
    public static function connectItem($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
    	
		$params = array(
			'supplier_item_id'   => $data['supplier_item_id'],
		);
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN. '/api/item/connect?supplier_id=' . $clientData['supplier_id'] . '&id=' . $data['gs_display_id']);
        
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
     * 商品情報取得
     * @param  array $clientData
     * @param  int   $displayId
     * @return array $itemData
     */
    public static function getDataByDisplayId($clientData, $displayId)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
		$url = 'https://' . GS_DOMAIN . '/api/item/data?supplier_id=' . $clientData['supplier_id'] . '&id=' . $displayId;

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
     * 商品情報取得
     * @param  array $clientData
     * @param  int   $displayId
     * @return array $itemData
     */
    public static function update($clientData, $displayId, $params)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
		$url = 'https://' . GS_DOMAIN . '/api/item/update?supplier_id=' . $clientData['supplier_id'] . '&id=' . $displayId;

        $httpClient = new Zend_Http_Client($url);
        
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
     * 標準卸価格リスト取得
     * @param  array $clientData
     * @param  int   $displayId
     * @return array $priceList
     */
    public static function getPriceListByDisplayId($clientData, $displayId)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Gs_Item clientData is empty');
    	}
		$url = 'https://' . GS_DOMAIN . '/api/item/price-list?supplier_id=' . $clientData['supplier_id'] . '&id=' . $displayId;

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
     * 並び順更新
     * @param  int   $artistId
     * @return array $data
     */
    public static function updateOrder($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Export_Artist clientData is empty');
    	}
    	
        $params = array(
            'web_id'           => $clientData['management_web_id'],
    		'web_pass'         => $clientData['management_web_pass'],
    		
        	'category_id'      => $data['category_id'],
            'category_order'   => json_encode($data['category_order']),
        );

        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN . '/api/artist/update-order');
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }

        $httpClient->setParameterPost($params);
        $response = $httpClient->request(Zend_Http_Client::POST);
        if ($response->getStatus() != '200') {
            return false;
        }
        
        $responseData = json_decode($response->getBody(), true);
        //var_dump($responseData);exit;
        
		if ($responseData['result']) {
			return true;
		}
		
        return false;
    }


    /**
     * アーティスト写真更新
     * @param  int   $clientData
     * @return array $data
     */
    public static function updatePhoto($clientData, $data)
    {
    	if (empty($clientData)) {
    		throw new Zend_Exception('Shared_Model_Export_Artist clientData is empty');
    	}
    	
        $params = array(
            'web_id'       => $clientData['management_web_id'],
    		'web_pass'     => $clientData['management_web_pass'],
    		
            'artist_id'    => $data['client_artist_id'],
            'type'         => $data['type'],
        );
		
        $httpClient = new Zend_Http_Client('https://' . GS_DOMAIN. '/api/artist/update-photo');
        
        // Basic認証
        if (!empty($clientData['management_web_use_basic_auth'])) {
        	$httpClient->setAuth($clientData['management_web_basic_user'], $clientData['management_web_basic_pass']);
        }

        $httpClient->setParameterPost($params);
        
		$resourcePath = Shared_Model_Resource_Artist::getResourceObjectPath($clientData['id'], $data['client_artist_id'], $data['type']);
        $httpClient->setFileUpload($resourcePath, 'image', null, 'image/jpeg');
        	
        $response = $httpClient->request(Zend_Http_Client::POST);

        if ($response->getStatus() != '200') {
            return false;
        }
        
        $responseData = json_decode($response->getBody(), true);
        
        
		if ($responseData['result']) {
			return true;
		}
		
        return false;
    }


    
}