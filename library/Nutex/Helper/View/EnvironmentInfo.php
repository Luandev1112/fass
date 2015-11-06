<?php
/**
 * class Nutex_Helper_View_EnvironmentInfo
 *
 * 環境情報を表示するビューヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_EnvironmentInfo extends Nutex_Helper_View_Abstract
{
    /**
     * @var string
     */
    protected $_noInfoEnvironments = array(
        'production',
    );

    /**
      * 環境情報を表示
      *
      * @param void
      * @return string
      */
    public function environmentInfo()
    {
        if (in_array(APPLICATION_ENV, $this->getNoInfoEnvironments())) {
            return '';
        }

        $htmls = array();
        $htmls[] = '<p>' . APPLICATION_ENV . '</p>';

        if (Nutex_Session::isSetup()) {
            $htmls[] = '<p>Session[ id:' . Nutex_Session::getId() . ' storageAdapter:' . get_class(Nutex_Session::getStorageAdapter()) . ' ]</p>';
        }

        if (Zend_Db_Table::getDefaultAdapter() instanceof Zend_Db_Adapter_Abstract) {
            $dbConf = Zend_Db_Table::getDefaultAdapter()->getConfig();
            $htmls[] = '<p>Default DataBase[ host:' . $dbConf['host'] . ' dbname:' . $dbConf['dbname'] . ' username:' . $dbConf['username'] . ' ]</p>';
        }

        return '<div class="environment_info">' . implode('', $htmls) . '</div>';
    }

    /**
     * 環境情報を付与しない環境群を取得
     *
     * @param void
     * @return array
     */
    public function getNoInfoEnvironments()
    {
        return $this->_noInfoEnvironments;
    }

    /**
     * 環境情報を付与しない環境を追加する
     *
     * @param string $env
     * @return provides a fluent interface
     */
    public function addNoInfoEnvironment($env)
    {
        $this->_noInfoEnvironments[] = (string)$env;
        return $this;
    }

    /**
     * 環境情報を付与しない環境をリセットする
     *
     * @param void
     * @return provides a fluent interface
     */
    public function resetNoInfoEnvironment()
    {
        $this->_noInfoEnvironments = array();
        return $this;
    }
}
