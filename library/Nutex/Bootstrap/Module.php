<?php
/**
 * class Nutex_Bootstrap_Module
 *
 * モジュール別ブートストラップの基底クラス
 *
 * @package Nutex
 * @subpackage Nutex_Bootstrap
 */
class Nutex_Bootstrap_Module extends Zend_Application_Module_Bootstrap
{
    /**
     * モジュール別設定値群
     * @var array
     */
    protected $_moduleConfigs = array();

    /**
     * モジュール別設定ファイルの拡張子
     * @var string
     */
    protected $_moduleConfigExt = 'ini';

    /**
     * 拡張用のオプションを取得
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getExtraOption($key = null, $default = null)
    {
        if ($this->getApplication() instanceof Nutex_Bootstrap_Bootstrap) {
            return $this->getApplication()->getExtraOption($key, $default);
        }
        return $default;
    }

    /**
     * getSetting
     * 共通設定値取得
     * application.iniで定義されているはずの追加設定ファイルを読み込もうとします
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getSetting($name = null, $default = null)
    {
        if ($this->getApplication() instanceof Nutex_Bootstrap_Bootstrap) {
            return $this->getApplication()->getSetting($name, $default);
        }
        return $default;
    }

    /**
     * getModuleOption
     * モジュール固有の設定値を取得
     * [moduleDirectory]/configs/ 以下のものを読みこもうとします
     *
     * @param string|null $name
     * @param string|null $fileName
     * @return mixed
     */
    public function getModuleOption($name = null, $fileName = null)
    {
        if (!is_string($fileName)) {
            //ファイルが指定されなければ、共通設定ファイルと同じファイル名にしてみる
            $fileName = basename($this->getExtraOption('settingFile'));
        }

        if (!preg_match('/\.[^\.]+$/', basename($fileName))) {
            //ファイル名に拡張子がついてなさげだったら、共通の拡張子をつけてみる
            $fileName .= '.' . $this->_moduleConfigExt;
        }

        if (!array_key_exists($fileName, $this->_moduleConfigs)) {
            $filePath = $this->getModuleDirectory() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . $fileName;
            if (is_readable($filePath)) {
                $config = Nutex_LoadConfig::load($filePath)->toArray();
                $this->_moduleConfigs[$fileName] = (array_key_exists(APPLICATION_ENV, $config)) ? $config[APPLICATION_ENV] : $config;
            } else {
                $this->_moduleConfigs[$fileName] = array();
            }
        }

        if (is_null($name)) {
            return $this->_moduleConfigs[$fileName];
        } elseif (array_key_exists($name, $this->_moduleConfigs[$fileName])) {
            return $this->_moduleConfigs[$fileName][$name];
        }

        return null;
    }

    /**
     * getModuleDirectory
     *
     * @param void
     * @return string
     */
    public function getModuleDirectory()
    {
        $front = Zend_Controller_Front::getInstance();
        return $front->getModuleDirectory($front->getRequest()->getModuleName());
    }

    /**
     * _initView
     *
     * @param void
     * @return void
     */
    protected function _initView()
    {
        $options = $this->getExtraOption('view');

        $className = 'Nutex_View';
        if (isset($options['class'])) {
            $className = $options['class'];
        }

        //viewインスタンス作成
        $view = new $className();
        if (!$view instanceof Zend_View_Interface) {
            throw new Nutex_Exception_Error('invalid view class');
        }

        //共通ヘルパーパスの追加
        $view->addHelperPath(LIBRARY_PATH . '/Nutex/Helper/View/', 'Nutex_Helper_View_');
        $view->addHelperPath(APPLICATION_PATH . '/helpers/view/', $this->getApplication()->getAppNamespace() . 'Helper_View_');

        //クライアントオブジェクトを渡しておく
        if ($this->getApplication() instanceof Nutex_Bootstrap_Bootstrap && $this->getApplication()->getClient() instanceof Nutex_Client_Abstract) {
            $view->setClient($this->getApplication()->getClient());
        }

        //viewをセット
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);
    }

    /**
     * _initResponceBodyConverter
     * フィーチャーフォンのクライアントからだったら文字コード変換を噛ませる
     *
     * @param void
     * @return void
     */
    protected function _initResponceBodyConverter()
    {
        $client = $this->getApplication()->getClient();
        $response = Zend_Controller_Front::getInstance()->getResponse();
        if ($client instanceof Nutex_Client_FeaturePhone && $response instanceof Nutex_Response) {
            $filter = new Nutex_Filter_ConvertEncoding();
            $filter->setFrom('UTF-8')
                   ->setTo('SJIS-win');
            $response->setBodyConverter(array($filter, 'filter'));
        }
    }

    /**
     * _initAction
     *
     * @param void
     * @return void
     */
    protected function _initAction()
    {
        //アクションヘルパーにパスを追加
        Zend_Controller_Action_HelperBroker::addPath(LIBRARY_PATH . '/Nutex/Helper/Action/', 'Nutex_Helper_Action_');
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/helpers/action/', $this->getApplication()->getAppNamespace() . 'Helper_Action_');
    }

    /**
     * _initParametersManager
     *
     * @param void
     * @return void
     */
    protected function _initParametersManager()
    {
        Nutex_Parameters_Filter::addPrefix($this->getModuleName() . '_Model_Filter_');
        Nutex_Parameters_Validate::addPrefix($this->getModuleName() . '_Model_Validate_');
    }

    /**
     * ブートストラッピング
     * 当該モジュール以外のブートストラッピングを行わないようにしたもの
     *
     * @param  null|string|array $resource
     * @return void
     * @see Zend_Application_Bootstrap_BootstrapAbstract
     */
    protected function _bootstrap($resource = null)
    {
        if (Zend_Controller_Front::getInstance()->getRequest()->getModuleName() === strtolower($this->getModuleName())) {
            parent::_bootstrap($resource);
        }
    }
}
