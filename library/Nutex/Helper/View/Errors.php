<?php
/**
 * class Nutex_Helper_View_Errors
 *
 * パラメータに紐づくエラーメッセージなどを表示するヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_Errors extends Nutex_Helper_View_Abstract
{
    /**
     * @var array
     */
    protected $_errors = array();

    /**
     * エラーメッセージを表示する | エラーメッセージ群をセットする
     *
     * @param string|null|Nutex_Parameters_Abstract|array $input
     * @param boolean $wrapping
     * @param string $tag
     * @return string
     */
    public function errors($input = null, $wrapping = false, $tag = 'p')
    {
        $htmls = array();

        if ($input instanceof Nutex_Parameters_Abstract || is_array($input)) {
            return $this->setErrors($input);
        } elseif (is_null($input)) {
            foreach ($this->getErrors() as $errors) {
                foreach ($errors as $error) {
                    $htmls[] = $this->_renderMessage($error, $tag);
                }
            }
        } else {
            foreach ($this->getErrors($input) as $error) {
                $htmls[] = $this->_renderMessage($error, $tag);
            }
        }

        if ($wrapping) {
            $this->_wrapHtmls($htmls);
        }

        return implode("\r\n", $htmls);
    }

    /**
     * エラーメッセージをセットする
     *
     * @param string $name
     * @param string $error
     * @return provides a fluent interface
     */
    public function setError($name, $error)
    {
        if (!isset($this->_errors[$name]) || !is_array($this->_errors[$name])) {
            $this->_errors[$name] = array();
        }
        $this->_errors[$name][] = $error;
        return $this;
    }

    /**
     * エラーメッセージ群をセットする
     *
     * @param array|Nutex_Parameters_Abstract $errors
     * @return provides a fluent interface
     */
    public function setErrors($errors, $reflesh = false)
    {
        $errorMessages = array();
        if ($errors instanceof Nutex_Parameters_Abstract) {
            $errorMessages = $errors->getErrorMessage();
        } elseif (is_array($errors)) {
            $errorMessages = $errors;
        }

        if ($reflesh) {
            $this->_errors = $errorMessages;
        } else {
            $this->_errors = array_merge($this->_errors, $errorMessages);
        }

        return $this;
    }

    /**
     * エラーメッセージ群を取得する
     *
     * @param string|null $name
     * @return array
     */
    public function getErrors($name = null)
    {
        if (is_null($name)) {
            return $this->_errors;
        } elseif (array_key_exists($name, $this->_errors)) {
            return $this->_errors[$name];
        }
        return array();
    }

    /**
     * エラーメッセージをレンダリングする
     *
     * @param string $message
     * @param string $tag
     * @return string
     */
    protected function _renderMessage($message, $tag = 'p')
    {
        return '<' . $tag . ' class="error">' . $this->getView()->escape($message) . '</' . $tag . '>';
    }

    /**
     * エラーメッセージ群をラッピングする
     *
     * @param array htmls
     */
    protected function _wrapHtmls(array &$htmls)
    {
        if (count($htmls) > 0) {
            array_unshift($htmls, '<div class="errorBlock">');
            $htmls[] = '</div>';
        }
    }
}
