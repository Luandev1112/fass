<?php
/**
 * class Nutex_Helper_View_Pager
 *
 * ページャーを表示するビューヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_Pager extends Nutex_Helper_View_Abstract
{
    /**
     * @var string ページを表すパラメータ名
     */
    const PAGE_PARAM_NAME = 'page';

    /**
     * @var string デフォルトパーシャルスクリプト名
     */
    const DEFAULT_PARTIAL_NAME = 'pager.phtml';

    /**
     * @var Zend_Paginator
     */
    protected $_paginator = null;

    /**
     * @var array 遷移先ベースURL
     */
    protected $_url = '';

    /**
     * @var mixed 追加パラメータ
     */
    protected $_params = null;

    /**
     * @var string パーシャルスクリプト名
     */
    protected $_partial = self::DEFAULT_PARTIAL_NAME;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->_url = $_SERVER['REQUEST_URI'];
    }

    /**
     * ページャーを表示する
     *
     * @param mixed
     * @return string $html
     */
    public function pager()
    {
        $args = func_get_args();

        if (count($args) == 0) {
            return $this->_renderPager();
        } else {
            foreach ($args as $arg) {
                if ($arg instanceof Zend_Paginator) {
                    $this->setPaginator($arg);
                } elseif (is_string($arg)) {
                    $this->setUrl($arg);
                }
            }
        }
    }

    /**
     * ページネータをセットする
     *
     * @param Zend_Paginator $paginator
     * @return provides a fluent interface
     */
    public function setPaginator(Zend_Paginator $paginator)
    {
        $this->_paginator = $paginator;
        return $this;
    }

    /**
     * ページネータを取得する
     *
     * @param void
     * @return Zend_Paginator $paginator
     */
    public function getPaginator()
    {
        return $this->_paginator;
    }

    /**
     * ページネータがリスト用データを持っているかどうか調べる
     *
     * @param void
     * @return boolean
     */
    public function paginatorHasData()
    {
        $paginator = $this->getPaginator();
        if ($paginator instanceof Zend_Paginator && count($paginator) > 0) {
            return true;
        }
        return false;
    }

    /**
     * URLを取得する
     *
     * @param void
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * URLをセットする
     *
     * @param string $partial
     * @return provides a fluent interface
     */
    public function setUrl($url)
    {
        $this->_url = (string)$url;
        return $this;
    }

    /**
     * パーシャルスクリプトの名前を取得する
     *
     * @param void
     * @return string
     */
    public function getPartial()
    {
        return $this->_partial;
    }

    /**
     * パーシャルスクリプトの名前をセットする
     *
     * @param string $partial
     * @return provides a fluent interface
     */
    public function setPartial($partial)
    {
        $this->_partial = $partial;
        return $this;
    }

    /**
     * 追加パラメータを取得する
     *
     * @param void
     * @return mixed
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * 追加パラメータをセットする
     *
     * @param mixed $params
     * @return provides a fluent interface
     */
    public function setParams($params)
    {
        $this->_params = $params;
        return $this;
    }

    /**
     * ページャーを出力
     *
     * @param int $imgId
     * @return void
     */
    protected function _renderPager()
    {
        $html = '';
        if ($this->paginatorHasData()) {

			if (strpos($this->getUrl(), 'javascript:') !== false) {
				// javascriptの場合はそのまま渡して、pager viewで置換する
				$url = $this->getUrl();
			} else {
            	// URLをページ番号をくっつけるだけでOKになるように加工する
            	$url = preg_replace('/\&?' . preg_quote(self::PAGE_PARAM_NAME . '=') . '[0-9]+/', '', $this->getUrl());
            	$url .= ((preg_match('/\?/', $url)) ? '&' : '?') . self::PAGE_PARAM_NAME . '=';
			}

            $html = $this->getView()->partial(
                $this->getPartial(),
                array(
                    'paginator' => $this->getPaginator(),
                    'url' => $url,
                    'params' => $this->getParams(),
                )
            );
        }

        return $html;
    }
}
