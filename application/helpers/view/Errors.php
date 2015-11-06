<?php
/**
 * class Shared_Helper_View_SharedPartial
 *
 * パラメータに紐づくエラーメッセージなどを表示するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Shared_Helper_View_Errors extends Nutex_Helper_View_Errors
{
    /**
     * エラーメッセージを表示する | エラーメッセージ群をセットする
     *
     * @param string|null|Nutex_Parameters_Abstract|array $input
     * @param boolean $wrapping
     * @param string $tag
     * @return string
     */
    public function errors($input = null, $wrapping = false, $tag = 'p', $showDescription = false)
    {
        $errors = parent::errors($input, $wrapping, $tag);
        if (is_string($errors) && $showDescription && empty($errors) && $this->getView()->getHelper('parameters')->getRequired($input, false)) {
            $errors = $this->_renderMessage('必須項目です', 'span');
        }
        return $errors;
    }
}
