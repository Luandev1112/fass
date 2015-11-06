<?php
/**
 * class Shared_Helper_View_RenderProfile
 *
 * プロフィールヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 */
class Shared_Helper_View_RenderProfile extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderProfile.phtml';

    /**
     * プロフィールバーを生成する
     * @param array $userData user table recode
     * @param array $bloggerData blogger table recode
     * @param array $userMenus array(url => title)
     * @param array $optionalInfos
     * @return string html
     */
    public function renderProfile(array $userData, array $bloggerData, array $userMenus, array $optionalInfos = array())
    {
        return $this->getView()->sharedPartial(self::PARTIALNAME, array(
            'userData' => $userData,
            'bloggerData' => $bloggerData,
            'userMenus' => $userMenus,
            'optionalInfos' => $optionalInfos,
        ));
    }
}
