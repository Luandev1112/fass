<?php
/**
 * class Front_Model_Controller
 *
 * @package Front
 * @subpackage Front_Model
 */
class Front_Model_Controller extends Shared_Model_Controller
{

    protected $_requireAuth = true; // 本番時はtrueに
    protected $_adminProperty;
    protected $_warehouseSession;

    /**
     * init
     *
     * @param void
     * @return void
     */
    public function init()
    {
        parent::init();
		
		$this->_warehouseSession = new Zend_Session_Namespace('shipment_login');
		
        // ★セッション認証処理★
        if ($this->_requireAuth === false) {
            return;

        } else if (!($this->hasPermission())) {
			
			if ('ajax' == $this->_getParam('req_type')) {
				echo 'timeout';
				exit;
				
			} else if ('ajax_json' == $this->_getParam('req_type')) {
				$error['timeout'] = true;
				echo Zend_Json::encode($error);exit;
				
			} else if ($this->_getParam('controller') != 'login' && $this->_getParam('controller') != 'develop') {
            	// 権限がない場合はログイン画面へ
            	$this->_redirect('/login?redirect=' . urlencode($_SERVER["REQUEST_URI"]));
				
			}
			
			
        }
    }
     
    /**
     * preDispatch
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $request = $this->getRequest();
        
        $this->view->getHelper('HeadTitle')->setTitle('FASS');
        $this->view->pageTitle = 'FASS';
    }

    /**
     * postDispatch
     */
    public function postDispatch()
    {
        parent::postDispatch();
        
    	if ($this->view->menuCategory === 'transaction') {
			$connectionTable = new Shared_Model_Data_Connection();
			$this->view->bankUnconfirmedCount = $connectionTable->getCountBankNotConfirmed();
		}
			
    }
    


    /************************************************
    * ログインセッション認証確認
    * hasPermission()
    ***********************************************/
    protected function hasPermission()
    {
        // セッションオブジェクトの取得
        $adminLoginSession = new Zend_Session_Namespace('management_login');

        // セッションを持っている場合
        if ($adminLoginSession->isSuccess && !(empty($adminLoginSession->adminProperty))) {

            // セッション有効期限を更新する
            $adminLoginSession->setExpirationSeconds(3600); 
        
            $this->view->adminProperty = $this->_adminProperty = $adminLoginSession->adminProperty;
            return true;

        // セッションが開始されていない場合は表示を許可しない
        } else {
            return false;
        }
    }

	
    /************************************************
    * ページ取得権限判定処理
    * hasPermission()
    * ページを見る権限があるかどうか判定する
    ***********************************************/
    protected function hasRole($roleNo)
    {
		if (1 != $this->_adminProperty['role_' . $roleNo]) {
			$loginSession = new Zend_Session_Namespace('management_login');
			$loginSession->roleNo = $roleNo;
			$this->_redirect('/index/permission');
		}
    }
	
    /**************************************************************************
     * 管理者ログイン成功時処理
     * loginSuccess($authResult)
     *
     * @arg1       $authResult  ログインしたユーザー情報
     * @return     none
     * @exception  Zend_Exception
     **************************************************************************/
    protected function _loginSuccess ($authResult)
    {
    	// セッションオブジェクトの取得
		$adminLoginSession = new Zend_Session_Namespace('management_login');

        //セッションにログイン情報を保存
        $adminLoginSession->isSuccess = true;
        $adminLoginSession->adminProperty = $authResult;

        // 有効時間は180分
        $adminLoginSession->setExpirationSeconds(60 * 180);

        $adminLoginSession->lock();
    }

    /**
     * JSONデータを出力する
     * @param array $data
     * @return none
     */
    public function sendJson($data)
    {
        $this->getResponse()
             ->setHeader('Content-Type', 'application/json')
             ->setBody(Zend_Json::encode($data));
    }

    /**************************************************************************
     * 色々な画像ファイルからイメージオブジェクトを作成
     * imageCreateFromAny()
     *
     * @arg        $filepath
     * @return     boolean 
     * @exception  Zend_Exception
     **************************************************************************/
    function imageCreateFromAny($filepath)
    {
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        switch ($type) {
            case 1 :
                $im = imageCreateFromGif($filepath);
            break;
            case 2 :
                $im = imageCreateFromJpeg($filepath);
            break;
            case 3 :
                $im = imageCreateFromPng($filepath);
            break;
            case 6 :
                $im = imageCreateFromBmp($filepath);
            break;
        }   
        return $im; 
    }

    /**
     * _createMonthPeriod
     * 一月の配列を作成
     * @param void
     * @return void
     */
    protected function _createMonthPeriod($startDate, $endDate, $defaultValueKeys)
    { 
        // ひと月の配列を生成
        $diff = (strtotime($endDate) - strtotime($startDate)) / ( 60 * 60 * 24);
        
        $period = array();
        
        for($i = 0; $i <= $diff; $i++) {
            $targetDate = date('Y-m-d', strtotime($startDate . '+' . $i . 'days'));
            
            $array = array(
                'day' => date('d', strtotime($startDate . '+' . $i . 'days')),
            );
            
            foreach ($defaultValueKeys as $eachKey) {
                $array[$eachKey] = 0;
            }
            
            $period[$targetDate] = $array;
        }
        
        return $period;
    }

    protected function _getReturnPagerParams($paginator)
    {
        $pages = $paginator->getPages();

        $pagerKeys = array(
            'currentPage' => 'current',
            'previousPage' => 'previous',
            'nextPage' => 'next',
            'lastPage' => 'last',
            'pagesInRange' => 'pagesInRange',
            'itemPerPage' => 'itemCountPerPage',
            'currentItemCount' => 'currentItemCount',
            'totalItemCount' => 'totalItemCount'
        );

        foreach ($pagerKeys as $k => $v) {
            $pagerParams[$k] = isset($pages->$v) ? $pages->$v : -1;
        }

        return $pagerParams;
    }
}
