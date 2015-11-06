<?php
/**
 * class FormDiscloseSettings
 *
 * 公開設定のフォーム生成ヘルパー
 *
 * @version $Id: FormDiscloseSettings.php 3637 2012-08-14 03:06:26Z miyano $
 */
class Shared_Helper_View_FormDiscloseSettings extends Nutex_Helper_View_Abstract
{
    /**
     * 公開設定のフォーム生成
     * @param string $name
     * @param string $disclose_settings
     * @return string
     */
    public function formDiscloseSettings($name, $disclose_settings)
    {
        if ($disclose_settings && is_array($disclose_settings) && in_array($name, $disclose_settings)) {
            return '<label class="disclose_settings"><input type="checkbox" name="disclose_settings[]" value="' . $name . '" checked="checked" /><span>公開</span></label>';
        } else {
            return '<label class="disclose_settings"><input type="checkbox" name="disclose_settings[]" value="' . $name . '" /><span>公開</span></label>';
        }
    }
}
