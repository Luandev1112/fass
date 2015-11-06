<?php
/**
 * class Shared_Helper_View_RenderProfileAndNews
 *
 * プロフィールヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version $Id: RenderProfileAndNews.php 3210 2012-06-19 08:44:53Z nanjo $
 */
class Shared_Helper_View_RenderProfileAndNews extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderProfileAndNews.phtml';

    /**
     * プロフィールバーを生成する
     * @param array $userData user table record
     * @param array $bloggerData blogger table record
     * @param array $newsNewestData news table record
     * @param array $optionalInfos
     * @return string html
     */
    public function renderProfileAndNews(array $userData, array $bloggerData, $newsNewestData, array $optionalInfos = array(), $isFollow = false)
    {
        return $this->getView()->sharedPartial(self::PARTIALNAME, array(
            'userData' => $userData,
            'bloggerData' => $bloggerData,
            'newsNewestData' => $newsNewestData,
            'optionalInfos' => $optionalInfos,
            'isFollow' => $isFollow,
        ));
    }
}
