<?php
/**
 * class Nutex_Filter_MbConvertKana_ZenkakuKana
 *
 * 全角カタカナ変換フィルタ
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Filter_Kana extends Nutex_Filter_MbConvertKana_ZenkakuKana
{
    /**
     * フィルタリング
     *
     * @param  string $value
     * @return string $value
     */
    public function filter($value)
    {
        return mb_ereg_replace(' ', '　', parent::filter($value));
    }
}
