<?php
/**
 * class Shared_Helper_View_RenderEntryButton
 *
 * ビデオ追加ボタン描画ヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 */
class Shared_Helper_View_RenderEntryButton extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderEntryButton.phtml';

    /**
     * ビデオ追加ボタン描画ヘルパー
     * @param array $bloggerData
     * @param array $entryData entry table record
     * @param array $previousEntryData
     * @param array $nextEntryData
     * @return string html
     */
    public function renderEntryButton($userData, array $bloggerData, array $entryData, $previousEntryData, $nextEntryData, $isEntry)
    {
        $report = false;
        $follow = false;
        $favorite = false;
        if (Nutex_Login::isLogined()) {
            $reportIns = new Shared_Model_Data_ReportEntry();
            $report = $reportIns->isReporting($entryData['id'], $userData['id']);

            $followIns = new Shared_Model_Data_Follow();
            $result = $followIns->select()
                                ->where('user_id = ?', $userData['id'])
                                ->where('blogger_user_id = ?', $entryData['user_id'])
                                ->query()->fetchAll();
            if (count($result) > 0) {
                $follow = true;
            }

            $favoriteIns = new Shared_Model_Data_Favorite();
            $result = $favoriteIns->select()
                                  ->where('user_id = ?', $userData['id'])
                                  ->where('entry_id = ?', $entryData['id'])
                                  ->query()->fetchAll();
            if (count($result) > 0) {
                $favorite = true;
            }
        }

        $param = array(
            'bloggerData'       => $bloggerData,
            'entryData'         => $entryData,
            'previousEntryData' => $previousEntryData,
            'nextEntryData'     => $nextEntryData,
            'isEntry'           => $isEntry,
            'isReporting'       => $report,
            'isFollow'          => $follow,
            'isFavorite'        => $favorite,
        );

        return $this->getView()->sharedPartial(self::PARTIALNAME, $param);
    }
}
