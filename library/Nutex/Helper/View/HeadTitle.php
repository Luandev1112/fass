<?php
/**
 * class Nutex_Helper_View_HeaderTitle
 *
 * titleタグを表示するビューヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_HeadTitle extends Nutex_Helper_View_Abstract
{
    /**
     * @var string
     */
    protected $_noInfoEnvironments = array(
        'production',
    );

    /**
     * @var string
     */
    protected $_title = '';

    /**
     * 詳細の配列
     * @var array
     */
    protected $_details = array();

    /**
     * 詳細の区切り文字
     * @var string
     */
    protected $_separator = ' | ';

    /**
      * titleタグ
      *
      * @param string $title
      * @param array $details
      * @return string
      */
    public function headTitle($title = null, array $details = array())
    {
        if (!is_string($title)) {
            $title = $this->getTitle();
        }

        //環境情報を付与
        if (in_array(APPLICATION_ENV, $this->getNoInfoEnvironments())) {
            $titleString = $title;
        } else {
            $titleString = '[' . APPLICATION_ENV . ']' . $title;
        }

        //詳細を付与
        if ($details == array()) {
            $details = $this->getDetails();
        }
        if (count($details) > 0) {
            $titleString .= $this->_separator . implode($this->_separator, $details);
        }

        return '<title>' . $titleString . '</title>';
    }

    /**
     * タイトル取得
     *
     * @param void
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * タイトルセット
     *
     * @param string $title
     * @return provides a fluent interface
     */
    public function setTitle($title)
    {
        $this->_title = (string)$title;
        return $this;
    }

    /**
     * 詳細群を取得
     *
     * @param void
     * @return array
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * 詳細を追加する
     *
     * @param string $detail
     * @return provides a fluent interface
     */
    public function addDetail($detail)
    {
        $this->_details[] = (string)$detail;
        return $this;
    }

    /**
     * 詳細をリセットする
     *
     * @param void
     * @return provides a fluent interface
     */
    public function resetDetail()
    {
        $this->_details = array();
        return $this;
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
