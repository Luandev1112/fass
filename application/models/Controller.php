<?php
/**
 * class Shared_Model_Controller
 *
 * 基底コントローラ
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Controller extends Nutex_Controller_Base
{
    /**
     * @var boolean
     */
    protected $_isRegenerateSession = true;

    /**
     * @var boolean
     */
    protected $_httpsOnly = false;

    /**
     * @var boolean|array
     */
    protected $_https = false;

    /**
     * セッションの各種パラメータ名
     * @var string
     */
    const SESSION_USER = 'user';

    /**
     * init()
     * @see Nutex_Controller_Abstract::init()
     */
    public function init()
    {
        parent::init();

        //ログイン用の名前空間をモジュール間で共通にする（admin以外で）
        Nutex_Login::setCurrentDivision('SHARED_LOGIN_NAMESPACE');
		
		
		if ($this->getClient() instanceof Nutex_Client_SmartPhone) {
			$response = $this->getResponse();
			
			$headers['Cache-Control'] = 'no-cache';
			$headers['Pragma'] = 'no-cache';
	
			//ヘッダにセット
			foreach ($headers as $name => $value) {
				$response->setHeader($name, $value, true);
			}
		}
		
    }

    /**
     * preDispatch
     *
     * @param void
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (APPLICATION_ENV === 'production') {
            if ($this->_httpsOnly) {
                $this->_rejectIfHttp();
            }
        }

        //if ($this->_sessionActive && $this->_isRegenerateSession) {
        //    $settings = $this->getExtraConfig('session');
        //    Nutex_Session::regenerateIfIntervalPassed($settings['regenarateInterval']);
        //}
    }

    /**
     * _getUserData()
     * @param string $name
     * @return mixed
     */
    public function _getUserData($name = null)
    {
        $data = Nutex_Login::getData(self::SESSION_USER);

        if ($name === null) {
            return $data;
        }

        if (is_array($data) && array_key_exists($name, $data)) {
            return $data[$name];
        } else {
            return null;
        }
    }

    /**
	 * _setLoginData()
     */
    public function _setLoginData($userData)
    {
        Nutex_Login::login(array(
            self::SESSION_USER => $userData,
        ));
    }

    /**
     * user情報をリフレッシュする
     * @param array
     */
    protected function _refreshUser(array $userData)
    {
        $sessionData = Nutex_Login::getData();
        // userだけ上書きする
        if (Nutex_Login::getData(self::SESSION_TARGET_USER)) {
            $sessionData[self::SESSION_TARGET_USER] = $userData;
        } else {
            $sessionData[self::SESSION_USER] = $userData;
        }
        Nutex_Login::refleshData($sessionData);
    }


}
