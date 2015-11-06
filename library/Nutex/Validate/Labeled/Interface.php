<?php
/**
 * interface Nutex_Validate_Labeled_Interface
 *
 * エラー文言にラベル付きバリデータのインターフェース
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
interface Nutex_Validate_Labeled_Interface
{
    /**
     * setLabel
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * getLabel
     *
     * @param void
     * @return string
     */
    public function getLabel();
}
