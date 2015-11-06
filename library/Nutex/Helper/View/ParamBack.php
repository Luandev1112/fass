<?php
/**
 * class Nutex_Helper_View_ParamBack
 *
 * 戻る用パラメータを付与するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_ParamBack extends Nutex_Helper_View_Abstract
{
    /**
     * 戻る用パラメータを付与
     *
     * @param string $url
     * @return string
     */
    public function paramBack($url = null)
    {
        if (is_string($url)) {
            $param = rawurlencode(Nutex_Request::PARAM_BACK);
            if (preg_match('/\?/', $url)) {
                return $url . '&' . $param . '=' . $param;
            } else {
                return $url . '?' . $param . '=' . $param;
            }
        } else {
            return $this->getView()->formHidden(Nutex_Request::PARAM_BACK, Nutex_Request::PARAM_BACK);
        }
    }
}
