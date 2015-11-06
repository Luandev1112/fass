<?php
/**
 * class Nutex_Helper_View_Parameters
 *
 * Nutex_Parameters_Abstract用のデータを受け取って何かするヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_Parameters extends Nutex_Helper_View_Abstract
{
    /**
     * @var string
     */
    const TYPES_GLUE = ' ';

    /**
     * @var array
     */
    protected $_config = array();

    /**
     * @var array
     */
    protected $_charTypeValidators = array(
        'Alnum' => '半角英数字',
        'Alpha' => '半角英字',
        'Digits' => '半角数字',
        'Ascii' => '半角英数字記号',
        'Hanakaku' => '半角',
        'HanakakuKatakana' => '半角カタカナ',
        'Zenkaku' => '全角',
        'ZenkakuHiragana' => '全角ひらがな',
        'ZenkakuKana' => 'ひらがな又は全角カタカナ',
        'ZenkakuKatakana' => '全角カタカナ',
    );

    /**
     * @var array
     */
    protected $_requiredValidators = array(
        'NotEmpty' => '必須',
    );

    /**
     * @var array
     */
    protected $_multibyteCharTypes = array(
        'HanakakuKatakana',
        'Zenkaku',
        'ZenkakuHiragana',
        'ZenkakuKana',
        'ZenkakuKatakana',
    );

    /**
     * ラベルを表示する | 設定配列をセットする
     *
     * @param string|Nutex_Parameters_Abstract|array $input
     * @return string
     */
    public function parameters($input)
    {
        $htmls = array();

        if ($input instanceof Nutex_Parameters_Abstract || is_array($input)) {
            return $this->setConfig($input);
        } else {
            return $this->getLabel($input);
        }
    }

    /**
     * 設定配列をセットする
     *
     * @param array|Nutex_Parameters_Abstract $input
     * @return provides a fluent interface
     */
    public function setConfig($input)
    {
        if (is_array($input)) {
            $this->_config = $input;
        } elseif ($input instanceof Nutex_Parameters_Abstract) {
            $this->_config = $input->getConfig();
        }
        return $this;
    }

    /**
     * 設定を取得する
     *
     * @param string|null $name
     * @return array
     */
    public function getConfig($name = null)
    {
        if (is_null($name)) {
            return $this->_config;
        } elseif (array_key_exists(Nutex_Parameters_Abstract::PARAMETERS, $this->_config) && array_key_exists($name, $this->_config[Nutex_Parameters_Abstract::PARAMETERS])) {
            return $this->_config[Nutex_Parameters_Abstract::PARAMETERS][$name];
        }
        return array();
    }

    /**
     * ラベルを取得する
     *
     * @param string $name
     * @return string
     */
    public function getLabel($name)
    {
        $config = $this->getConfig($name);
        if (is_array($config) && array_key_exists(Nutex_Parameters_Abstract::PARAMETER_LABEL, $config)) {
            return $config[Nutex_Parameters_Abstract::PARAMETER_LABEL];
        }
        return '';
    }

    /**
     * 最大文字長指定を取得する
     *
     * @param string $name
     * @return int
     */
    public function getMaxlength($name)
    {
        $lengthSetting = $this->getLengthSetting($name, false);
        if (isset($lengthSetting['max'])) {
            return $lengthSetting['max'];
        }
        return null;
    }

    /**
     * 必須かどうかを取得する
     *
     * @param string $name
     * @param boolean $returnMessage
     * @return string|array
     */
    public function getRequired($name, $returnMessage = true)
    {
        $isRequired = false;
        $config = $this->getConfig($name);
        $validators = $this->getValidators($name);

        if (isset($config[Nutex_Parameters_Validate::PARAMETER_REQUIRED]) && $config[Nutex_Parameters_Validate::PARAMETER_REQUIRED]) {
            if (!is_array($validators)) {
                $validators = array();
            }
            $keys = array_keys($this->_requiredValidators);
            $className = array_shift($keys);
            $validators[$className][Nutex_Parameters_Validate::COMPONENT_CLASS_NAME] = $className;
        }

        if (!is_array($validators)) {
            return '';
        }

        foreach ($validators as $key => $setting) {
            if (!is_array($setting)) {
                continue;
            }

            foreach ($this->_retrieveClassNames($key, $setting) as $className) {
                if (array_key_exists($className, $this->_requiredValidators)) {
                    $isRequired = $className;
                    break;
                }
            }
        }

        if (!$returnMessage) {
            return $isRequired;
        }

        if ($isRequired) {
            return $this->_requiredValidators[$isRequired];
        } else {
            return '';
        }
    }

    /**
     * テキストボックス用の属性配列を取得する
     *
     * @param string $name
     * @param array $styles
     * @return int
     */
    public function getFormTextAttr($name, array $styles = array())
    {
        $attr = array();

        $max = $this->getMaxlength($name);
        if ($max) {
            $attr['maxlength'] = $max;
            if (!isset($styles['width'])) {
                $styles['width'] = $this->_caluculateFormTextWidth($max);
            }
        }

        if (!isset($styles['ime-mode'])) {
            if (count(array_intersect($this->_multibyteCharTypes, $this->getCharacterType($name, false))) > 0) {
                $styles['ime-mode'] = 'active';
            } else {
                $styles['ime-mode'] = 'inactive';
            }
        }

        if (count($styles) > 0) {
            $styleStrs = array();
            foreach($styles as $key => $value) {
                $styleStrs[] = $key . ': ' . $value . ';';
            }
            $attr['style'] = implode(' ', $styleStrs);
        }

        return $attr;
    }

    /**
     * 文字長指定を取得する
     *
     * @param string $name
     * @param boolean $returnMessage
     * @return string
     */
    public function getLengthSetting($name, $returnMessage = true)
    {
        $lengthSetting = array(
            'max' => null,
            'min' => null,
        );

        $config = $this->getValidators($name);
        if (!is_array($config)) {
            return ($returnMessage) ? '' : $lengthSetting;
        }

        foreach ($config as $key => $setting) {
            if (!is_array($setting) || !array_key_exists(Nutex_Parameters_Abstract::COMPONENT_OPTIONS, $setting) || !is_array($setting[Nutex_Parameters_Abstract::COMPONENT_OPTIONS])) {
                continue;
            }
            if (in_array('StringLength', $this->_retrieveClassNames($key, $setting))) {
                foreach (array_keys($lengthSetting) as $prop) {
                    if (array_key_exists($prop, $setting[Nutex_Parameters_Abstract::COMPONENT_OPTIONS])) {
                        $lengthSetting[$prop] = $setting[Nutex_Parameters_Abstract::COMPONENT_OPTIONS][$prop];
                    }
                }
                break;
            }
        }

        if (!$returnMessage) {
            return $lengthSetting;
        }

        if ($lengthSetting['max'] && $lengthSetting['min']) {
            if ($lengthSetting['min'] === $lengthSetting['max']) {
                return $lengthSetting['min'] . '文字';
            } else {
                return $lengthSetting['min'] . '～' .  $lengthSetting['max'] . '文字';
            }
        } elseif ($lengthSetting['max']) {
            return $lengthSetting['max'] . '文字以下';
        } elseif ($lengthSetting['min']) {
            return $lengthSetting['min'] . '文字以上';
        }

        return '';
    }

    /**
     * 文字種を取得する
     *
     * @param string $name
     * @param boolean $returnMessage
     * @return string|array
     */
    public function getCharacterType($name, $returnMessage = true)
    {
        $charTypes = array();

        $config = $this->getValidators($name);
        if (!is_array($config)) {
            return ($returnMessage) ? '' : $charTypes;
        }

        foreach ($config as $key => $setting) {
            if (!is_array($setting)) {
                continue;
            }

            foreach ($this->_retrieveClassNames($key, $setting) as $className) {
                if (array_key_exists($className, $this->_charTypeValidators)) {
                    $charTypes[] = $className;
                    break;
                }
            }
        }
        if (!$returnMessage) {
            return $charTypes;
        }

        foreach ($charTypes as &$type) {
            $type = $this->_charTypeValidators[$type];
        }

        return implode(self::TYPES_GLUE, $charTypes);
    }

    /**
     * バリデータ情報を取得する
     *
     * @param string $name
     * @return array|null
     */
    public function getValidators($name)
    {
        $config = $this->getConfig($name);
        if (is_array($config) && array_key_exists(Nutex_Parameters_Validate::PARAMETER_VALIDATORS, $config)) {
            return $config[Nutex_Parameters_Validate::PARAMETER_VALIDATORS];
        }
        return null;
    }

    /**
     * フィルター情報を取得する
     *
     * @param string $name
     * @return array|null
     */
    public function getFilters($name)
    {
        $config = $this->getConfig($name);
        if (is_array($config) && array_key_exists(Nutex_Parameters_Filter::PARAMETER_FILTERS, $config)) {
            return $config[Nutex_Parameters_Filter::PARAMETER_FILTERS];
        }
        return null;
    }

    /**
     * コンポーネントクラス名群を抽出する
     *
     * @param void
     * @return array $names
     */
    protected function _retrieveClassNames($key, $setting)
    {
        $names = array();
        if (array_key_exists(Nutex_Parameters_Abstract::COMPONENT_CLASS_NAME, $setting)) {
            $names[] = $setting[Nutex_Parameters_Abstract::COMPONENT_CLASS_NAME];
        }
        $names[] = $key;
        foreach ($names as &$name) {
            if (preg_match('/[^_]+$/', $name, $match)) {
                $name = $match[0];
            }
            $name = ucfirst($name);
        }
        return $names;
    }

    /**
     * テキストボックスのwidthの値を計算する
     *
     * @param int $max
     * @return int $max
     */
    protected function _caluculateFormTextWidth($max)
    {
        $max = (int)$max;
        $max = ceil($max * 1.1);
        if ($max > 25) {
            $max = 25;
        }
        return (string)$max . 'em';
    }
}
