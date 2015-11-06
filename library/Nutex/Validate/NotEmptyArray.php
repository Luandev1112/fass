<?php
/**
 * class Nutex_Validate_NotEmptyArray
 *
 * 配列用バリデータ
 *
 * チェック対象の深さなどを制御可能な再帰的チェックを実装
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_NotEmptyArray extends Zend_Validate_Abstract
{
    /**
     * エラータイプ
     *
     * @var string
     */
    const NOT_ENOUGH = 'notEnough';
    const NOT_EMPTY = 'notEmpty';

    /**
     * エラーメッセージ
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ENOUGH => '入力されていない項目があります',
        self::NOT_EMPTY => '必須項目です',
    );

    /**
     * 全ての項目に値を必要とするかフラグ
     *
     * 配列で深さ別にも指定できます
     * その場合 array(0はじまりintの配列深さ => boolean) です
     *
     * @var mixed
     */
    protected $_requireAll = false;

    /**
     * 配列深さ別に判定を行うかフラグ
     * trueにすると配列深さごとに一つでもfalseがあればfalseになります
     *
     * @var boolean
     */
    protected $_checkByDepths = false;

    /**
     * 各種チェック用バッファ
     *
     * @var mixed
     */
    protected $_notEmpty = null;
    protected $_arrayDepth = 0;
    protected $_resultsByDepths = array();
    protected $_emptyAll = true;

    /**
     * Constructor
     *
     * @param string|array|Zend_Config $options OPTIONAL
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['type'] = array_shift($options);
                $temp['requireAll'] = array_shift($options);
                $temp['checkByDepths'] = array_shift($options);
            }

            $options = $temp;
        }

        if (is_array($options) && array_key_exists('type', $options)) {
            $this->setType($options['type']);
        }
        if (is_array($options) && array_key_exists('requireAll', $options)) {
            $this->setType($options['requireAll']);
        }
        if (is_array($options) && array_key_exists('checkByDepths', $options)) {
            $this->setType($options['checkByDepths']);
        }
    }

    /**
     * 全ての項目に値を必要とするかフラグをセットする
     *
     * @param array|boolean $flag
     * @return provides a fluent interface
     */
    public function setRequireAll($flag)
    {
        $this->_requireAll = $flag;
        return $this;
    }

    /**
     * 全ての項目に値を必要とするかフラグを取得する
     *
     * @param void
     * @return array|boolean
     */
    public function getRequireAll()
    {
        return $this->_requireAll;
    }

    /**
     * 配列深さ別に判定を行うかフラグをセットする
     *
     * @param boolean $flag
     * @return provides a fluent interface
     */
    public function setCheckByDepths($flag)
    {
        $this->_checkByDepths = (boolean)$flag;
        return $this;
    }

    /**
     * 配列深さ別に判定を行うかフラグを取得する
     *
     * @param void
     * @return array|boolean
     */
    public function getCheckByDepths()
    {
        return $this->_checkByDepths;
    }

    /**
     * バリデーション
     *
     * @param array $values 評価する値
     * @return boolean
     */
    public function isValid($values)
    {
        if(is_array($values) === false) {
            $values = array($values);
        }
        $this->_setValue($values);

        //再帰的に配列をチェック
        $this->_resultsByDepths = array();
        $result = true;
        if ($this->getCheckByDepths()) {
            $this->_isValidRecursive($values);
            foreach ($this->_resultsByDepths as $depth => $results) {
                foreach ($results as $ret) {
                    if (!$ret) {
                        $result = false;
                        break 2;
                    }
                }
            }
        } else {
            $result = $this->_isValidRecursive($values);
        }

        if (!$result) {
            if ($this->_emptyAll) {
                $this->_error(self::NOT_EMPTY);
            } else {
                $this->_error(self::NOT_ENOUGH);
            }
        }

        return $result;

    }

    /**
     * 再帰的バリデーション
     *
     * @param array $values 評価する値
     * @return boolean
     */
    protected function _isValidRecursive($values)
    {
        $results = array();
        foreach ($values as $val) {
            if (is_array($val)) {
                $this->_arrayDepth++;
                $results[] = $this->_isValidRecursive($val);
                $this->_arrayDepth--;
            } else {
                $results[] = $this->_isValid($val);
            }
        }

        //$resultsはtrueかfalseしか入っていない一次元配列なのでユニーク化して判定
        $results = array_values(array_unique($results));
        $result = false;
        switch (count($results)) {

            case 0:
                $result = false;
                break;

            case 1:
                $result = array_shift($results);
                break;

            default:
                if (is_array($requireAll = $this->getRequireAll())) {
                    if (array_key_exists($this->_arrayDepth, $this->_requireAll) && $requireAll[$this->_arrayDepth]) {
                        $result = false;
                    } else {
                        $result = true;
                    }
                } else {
                    if ($this->getRequireAll()) {
                        $result = false;
                    } else {
                        $result = true;
                    }
                }
                break;

        }

        //配列深さ別の結果を格納
        if (!array_key_exists($this->_arrayDepth, $this->_resultsByDepths)) {
            $this->_resultsByDepths[$this->_arrayDepth] = array();
        }
        $this->_resultsByDepths[$this->_arrayDepth][] = $result;
        if ($this->_emptyAll && $result) {
            $this->_emptyAll = false;
        }

        return $result;
    }

    /**
     * バリデーションロジックの実体
     *
     * @param string $value 評価する値
     * @return boolean
     */
    protected function _isValid($value)
    {
        if ($this->_notEmpty === null) {
            $this->_notEmpty = new Zend_Validate_NotEmpty();
        }
        return $this->_notEmpty->isValid($value);
    }
}