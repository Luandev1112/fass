<?php
/**
 * class Shared_Model_Utility_GmoBank
 *
 * GMOあおぞら銀行
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Utility_GmoBank
{
    const AUTH_METHOD   = "POST"; // Your Auth method BASIC or POST
    
    public static function getToken($id)
    {
    	require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
    	
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$gmoLoginAccount = $gmoTable->getById($id);

        if (empty($gmoLoginAccount) || empty($gmoLoginAccount['gmo_reflesh_token'])) {
            return false;
        }
        
        $now = new Zend_Date(NULL, NULL, 'ja_JP');
        
        $refleshExpire = new Zend_Date($gmoLoginAccount['gmo_access_token_expired_datetime'], NULL, 'ja_JP');
        
        if ($refleshExpire->isEarlier($now) || $refleshExpire->equals($now)) {
        	// 期限切れの場合
        	$ganb = new Ganb\Auth($gmoLoginAccount['app_client_id'], $gmoLoginAccount['app_client_secret'], self::AUTH_METHOD);
        
	    	try {
			    $token = $ganb->refreshTokens($gmoLoginAccount['gmo_reflesh_token']);
    
    		    $zDate1 = new Zend_Date(NULL, NULL, 'ja_JP');
                $zDate1->add('2592000', Zend_Date::SECOND);
                $zDate1->sub('1', Zend_Date::DAY);
                $accessTokenExpireIn = $zDate1->get('yyyy-MM-dd HH:mm:ss');
                
    		    $zDate2 = new Zend_Date(NULL, NULL, 'ja_JP');
                $zDate2->add('7776000', Zend_Date::SECOND);
                $zDate2->sub('1', Zend_Date::DAY);
                $refleshTokenExpireIn = $zDate2->get('yyyy-MM-dd HH:mm:ss');
                
                // リフレッシュトークンの保存
                $gmoTable->updateById($id, array(
                    'gmo_access_token_fr'                     => $token->access_token,     // GMOあおぞら銀行(フレスコ)アクセストークン
    		        'gmo_access_token_fr_expired_datetime'    => $accessTokenExpireIn,     // GMOあおぞら銀行(フレスコ)アクセストークン有効期限
                    'gmo_reflesh_token_fr'                    => $token->refresh_token,    // GMOあおぞら銀行(フレスコ)リフレッシュトークン
    		        'gmo_reflesh_token_fr_expired_datetime'   => $refleshTokenExpireIn,    // GMOあおぞら銀行(フレスコ)リフレッシュトークン有効期限
                ));

                return $token->access_token;
                
			} catch (Exception $e) {
			    echo $e->getMessage();
			}
		        	
        }
        
        return $gmoLoginAccount['gmo_access_token'];
        
    }



    public static function reflesh($id)
    {
    	require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
    	
		$gmoTable = new Shared_Model_Data_ManagementGmoAccount();
		$gmoLoginAccount = $gmoTable->getById($id);
        
    	$ganb = new Ganb\Auth($gmoLoginAccount['app_client_id'], $gmoLoginAccount['app_client_secret'], self::AUTH_METHOD);
    
    	try {
		    $token = $ganb->refreshTokens($gmoLoginAccount['gmo_reflesh_token']);

		    $zDate1 = new Zend_Date(NULL, NULL, 'ja_JP');
            $zDate1->add('2592000', Zend_Date::SECOND);
            $zDate1->sub('1', Zend_Date::DAY);
            $accessTokenExpireIn = $zDate1->get('yyyy-MM-dd HH:mm:ss');
            
		    $zDate2 = new Zend_Date(NULL, NULL, 'ja_JP');
            $zDate2->add('7776000', Zend_Date::SECOND);
            $zDate2->sub('1', Zend_Date::DAY);
            $refleshTokenExpireIn = $zDate2->get('yyyy-MM-dd HH:mm:ss');
            
            // リフレッシュトークンの保存
            $gmoTable->updateById($id, array(
                'gmo_access_token'                     => $token->access_token,     // GMOあおぞら銀行(フレスコ)アクセストークン
		        'gmo_access_token_expired_datetime'    => $accessTokenExpireIn,     // GMOあおぞら銀行(フレスコ)アクセストークン有効期限
                'gmo_reflesh_token'                    => $token->refresh_token,    // GMOあおぞら銀行(フレスコ)リフレッシュトークン
		        'gmo_reflesh_token_expired_datetime'   => $refleshTokenExpireIn,    // GMOあおぞら銀行(フレスコ)リフレッシュトークン有効期限
            ));

		} catch (Exception $e) {
		    echo $e->getMessage();
		}
    } 
}