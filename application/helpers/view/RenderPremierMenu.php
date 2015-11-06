<?php
/**
 * class Shared_Helper_View_RenderPremierMenu
 *
 * プロフィールヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version
 */
class Shared_Helper_View_RenderPremierMenu extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderPremierMenu.phtml';

    /**
     * ブロガー用ヘッダータブメニュー
     * @param array $currentTab
     * @param array $pageParam
     * @return string html
     */
    public function renderPremierMenu($premierName, $currentTab, array $pageParam = array())
    {
        return $this->getView()->sharedPartial(self::PARTIALNAME, array(
			'premierName' => $premierName,
            'currentTab' => $currentTab,
            'pageParam' => $pageParam,
        ));
    }
	
	
	
	
	
}
