<?php
/**
 * class Api_Model_Controller
 *
 * apiモジュール用 基底コントローラ
 *
 * @package Api
 * @subpackage Api_Model
 */
class Api_Model_Controller extends Shared_Model_Controller
{
    /**
     * @var boolean
     */
    protected $_sessionActive = false;
    protected $_isDisableView = true;
    protected $_httpsOnly     = false;


    /**
     * @var string
     */
    const SESSION_ID_NAME = 'sessionId';

    /**
     * @var boolean
     */
    protected $_isProcessFinished = false;


    /**
     * init
     *
     * @param void
     * @return void
     */
    public function init()
    {
        parent::init();

		$this->startSession();
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

        if ($this->_isDisableView) {
            $this->_helper->disableView();
        }
    }

    /**
     * JSONデータを出力する
     * @param array $data
     */
    public function sendJson($data)
    {
        $this->getResponse()
             ->setHeader('Content-Type', 'application/json')
             ->setBody(Zend_Json::encode($data));
    }
    
    /**
     * パラメータ方式IDセットでセッションを開始
     * @param array $data
     */
    public function startSession()
    {
        /*
         * リクエスト内のパラメータでセッション管理を行いたいため明示的に指定する
         * application.iniで設定すると全モジュールが影響を受けるため、ここで指定する
         */
        $settings = $this->getExtraConfig('session');

        $settings['className'] = 'RequestParameter';
        $settings['idName'] = self::SESSION_ID_NAME;
		//var_dump($settings);exit;
        Nutex_Session::setup($this, $settings);
        Nutex_Session::updateMetaInfo();
	}

    /**
     * _loginSetup
     *
     * @param array $userData
     * @return void
     */
    public function _loginSetup(array $userData)
    {
        Nutex_Login::login(array(
            self::SESSION_USER    => $userData,
        ));
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
}
