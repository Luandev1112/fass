<?php
/**
 * class Shared_Helper_View_RenderProfile
 *
 * プロフィールヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version
 */
class Shared_Helper_View_RenderMasterMenu extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderMasterMenu.phtml';

    /**
     * マスター用ヘッダータブメニュー
     * @param array $officeName
     * @param array $currentTab
     * @param array $pageParam
     * @return string html
     */
    public function renderMasterMenu($officeName, $currentTab, array $pageParam = array(), $bloggerData = NULL)
    {
        return $this->getView()->sharedPartial(self::PARTIALNAME, array(
            'officeName' => $officeName,
            'currentTab' => $currentTab,
            'pageParam' => $pageParam,
			'bloggerData' => $bloggerData
        ));
    }
}
