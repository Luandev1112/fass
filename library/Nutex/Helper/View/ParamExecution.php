<?php
/**
 * class Nutex_Helper_View_ParamExecution
 *
 * 実行用パラメータを付与するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_ParamExecution extends Nutex_Helper_View_Abstract
{
    /**
     * 実行用パラメータを付与
     *
     * @param string $url
     * @return string
     */
    public function paramExecution($url = null)
    {
        if (is_string($url)) {
            $param = rawurlencode(Nutex_Request::PARAM_EXECUTION);
            if (preg_match('/\?/', $url)) {
                return $url . '&' . $param . '=' . $param;
            } else {
                return $url . '?' . $param . '=' . $param;
            }
        } else {
            return $this->getView()->formHidden(Nutex_Request::PARAM_EXECUTION, Nutex_Request::PARAM_EXECUTION);
        }
    }
}
