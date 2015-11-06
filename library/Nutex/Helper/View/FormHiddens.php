<?php
/**
 * class Nutex_Helper_View_FormHiddens
 *
 * データ群からhiddenを作成
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_FormHiddens extends Nutex_Helper_View_Abstract
{
    /**
     * データ群からhiddenを作成
     *
     * @param array $input
     * @param array $omits
     * @return string
     */
    public function formHiddens(array $input = array(), array $omits = array())
    {
        $htmls = array();

        $view = $this->getView();
        if ($input == array() && $view instanceof Nutex_View) {
            $input = $view->getData();
        }

        foreach ($input as $name => $value) {
            if (in_array($name, $omits)) {
                continue;
            }

            if (is_array($value)) {
                $arrayInput = array();
                foreach ($value as $key => $val) {
                    $arrayInput[$name . '[' . $key . ']'] = $val;
                }
                $htmls[] = $this->formHiddens($arrayInput, $omits);
            } else {
                $htmls[] = $view->formHidden($name, $value);
            }
        }

        return implode("\r\n", $htmls);
    }
}
