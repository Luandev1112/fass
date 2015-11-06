<?php
/**
 * class Shared_Helper_View_AddBloggerParam
 *
 * マネージャーからブロガーに飛んだ場合のパラメータをurlに付与する
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_AddBloggerParam extends Zend_View_Helper_Partial
{
    /**
     * addBloggerParam
     * @param string $url
     * @param string $bloggerUserId
     */
    public function addBloggerParam($url, $bloggerUserId)
    {
        if (strstr($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= Shared_Model_Controller::TARGET_BLOGGER_PARAM . '=' . rawurlencode($bloggerUserId);
        return $url;
    }
}
