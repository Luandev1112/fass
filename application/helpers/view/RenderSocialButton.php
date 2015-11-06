<?php
/**
 * class Shared_Helper_View_RenderSocialButton
 *
 * ソーシャルボタン描画ヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version $Id: RenderSocialButton.php 516 2012-01-25 12:25:11Z miurat $
 */
class Shared_Helper_View_RenderSocialButton extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderSocialButton.phtml';

    /**
     * ソーシャルボタン描画ヘルパー
     * @param array $entryData entry table recode
     * @return string html
     */
    public function renderSocialButton(array $bloggerData, array $entryData)
    {
        return $this->getView()->sharedPartial(self::PARTIALNAME, array('bloggerData' => $bloggerData, 'entryData' => $entryData));
    }
}
