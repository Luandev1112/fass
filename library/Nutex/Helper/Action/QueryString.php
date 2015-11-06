<?php
/**
 * class Nutex_Helper_Action_QueryString
 *
 * クエリ文字列作成ヘルパー
 * ネストされた配列にも対応しています
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_QueryString extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * direct
     *
     * @param array $input
     * @return void
     */
    public function direct(array $input = array())
    {
        if (!$this->getRequest() instanceof Zend_Controller_Request_Http) {
            return $this->getQueryString($input);
        }
        foreach (array_keys($input) as $key) {
            if (is_string($key)) {
                return $this->getQueryString($input);
            }
        }

        $params = array();
        foreach ($this->getRequest()->getQuery() as $name => $value) {
            if (in_array($name, $input)) {
                continue;
            }
            $params[$name] = $value;
        }
        return $this->getQueryString($params);
    }

    /**
     * クエリ文字列取得
     *
     * @param array $params
     * @return string
     */
    public function getQueryString(array $params)
    {
        return implode('&', $this->_makeQueryStringArray($params));
    }

    /**
     * クエリ文字列配列作成
     *
     * @param array $params
     * @return array
     */
    protected function _makeQueryStringArray(array $params)
    {
        $queries = array();
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $arr = array();
                foreach ($value as $ky => $val) {
                    $arr[$key . '[' . $ky . ']'] = $val;
                }
                $queries = array_merge($queries, $this->_makeQueryStringArray($arr));
            } else {
                $queries[] = rawurlencode($key) . '=' . rawurlencode($value);
            }
        }

        return $queries;
    }
}
