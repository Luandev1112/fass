<?php
/**
 * class Shared_Helper_View_RenderProfileTable
 *
 * プロフィールテーブルヘルパー
 *
 * @package Shared
 * @subpackage Shared_Helper_View
 * @version $Id: RenderProfileTable.php 728 2012-02-03 07:28:36Z kawano $
 */
class Shared_Helper_View_RenderProfileTable extends Nutex_Helper_View_Abstract
{
    const PARTIALNAME = 'renderProfileTable.phtml';

    /**
     * フロント側「プロフィール」画面で表示されるタイプのプロフィール表示
     * @param array $userData user table recode
     * @param array $bloggerData blogger table recode
     * @param boolean $isEdit
     * @return string html
     */
    public function renderProfileTable($url, array $userData, array $bloggerData, $isEdit= false, $imageKey=null)
    {
        $param = array(
            'url'         => $url,
            'userData'    => $userData,
            'bloggerData' => $bloggerData,
            'isEdit'      => $isEdit,
            'imageKey'      => $imageKey,
        );
        return $this->getView()->sharedPartial(self::PARTIALNAME, $param);
    }
}
