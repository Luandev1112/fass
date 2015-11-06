<?php
/**
 * class Shared_Helper_View_RenderSettingMenu
 *
 * 設定用ヘッダータブメニュービューヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version
 */
class Shared_Helper_View_RenderSettingMenu extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderSettingMenu.phtml';

    /**
     * 設定用ヘッダータブメニュー
     * @param array $currentTab
     * @param array $pageParam
     * @return string html
     */
    public function renderSettingMenu($isBlogger, $isManager, $params)
    {
        return $this->getView()->sharedPartial(self::PARTIALNAME, array(
			'isBlogger' => $isBlogger,
            'isManager' => $isManager,
            'params' => $params,
        ));
    }
	

}
