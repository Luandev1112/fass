<?php
/**
 * class Shared_Helper_View_RenderEntry
 *
 * ビデオ部分描画ヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version $Id: RenderEntry.php 3636 2012-08-10 12:07:53Z miurat $
 */
class Shared_Helper_View_RenderEntry extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderEntry.phtml';

    /**
     * ビデオ部分描画ヘルパー
     * @param string $playerHtml
     * @param array $entryData entry table recode
     * @return string html
     */
    public function renderEntry($playerUrl, array $entryData, $prevEntryData = null, $nextEntryData = null)
    {
        $params = array(
            'playerUrl' => $playerUrl,
            'entryData' => $entryData,
            'prevEntryData' => $prevEntryData,
            'nextEntryData' => $nextEntryData,
        );
        return $this->getView()->sharedPartial(self::PARTIALNAME, $params);
    }
}
