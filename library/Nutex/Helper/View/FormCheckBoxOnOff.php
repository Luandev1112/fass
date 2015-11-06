<?php
/**
 * class Nutex_Helper_View_FormCheckBoxOnOff
 *
 * オンオフだけのチェックボックスを作成する
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_FormCheckBoxOnOff extends Nutex_Helper_View_Abstract
{
    /**
     * オンオフだけのチェックボックスを作成する
     *
     * @param string $name  フォームのname
     * @param mixed $value  フォームのvalue
     * @param string $label フォームのラベル
     * @return string
     */
    public function formCheckBoxOnOff($name, $value, $label = null)
    {
        $attr = array();
        if ($label) {
            $id = 'checkbox_' . $name . '_1';
            $attr = array('id' => $id);
            $label = '<label for="' . $this->getView()->escape($id) . '">' . $this->getView()->escape($label) . '</label>';
        }
        return $this->getView()->formCheckbox($name, $value, $attr, array('1', '0')) . (($label) ? $label : '');
    }
}
