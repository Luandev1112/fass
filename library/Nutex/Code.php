<?php
/**
 * class Nutex_Code
 *
 * 静的コード群の管理クラス
 * 静的遅延束縛 get_called_class() が入っているのでphp5.3以上のみ対応です
 *
 * @package Nutex
 * @subpackage Nutex_Code
 */
class Nutex_Code
{
    const STACK_NAME = '_codeStack';

    /**
     * @var string
     */
    public static $noneString = '指定無し';

    /**
     * @var array
     */
    protected static $_codeStack = array(
        'year' => array(
        ),

        'month' => array(
        ),

        'day' => array(
        ),

        'hour' => array(
        ),

        'minute' => array(
        ),

        'second' => array(
        ),

        'prefecture' => array(
            '1' => '北海道',
            '2' => '青森県',
            '3' => '岩手県',
            '4' => '宮城県',
            '5' => '秋田県',
            '6' => '山形県',
            '7' => '福島県',
            '8' => '茨城県',
            '9' => '栃木県',
            '10' => '群馬県',
            '11' => '埼玉県',
            '12' => '千葉県',
            '13' => '東京都',
            '14' => '神奈川県',
            '15' => '新潟県',
            '16' => '富山県',
            '17' => '石川県',
            '18' => '福井県',
            '19' => '山梨県',
            '20' => '長野県',
            '21' => '岐阜県',
            '22' => '静岡県',
            '23' => '愛知県',
            '24' => '三重県',
            '25' => '滋賀県',
            '26' => '京都府',
            '27' => '大阪府',
            '28' => '兵庫県',
            '29' => '奈良県',
            '30' => '和歌山県',
            '31' => '鳥取県',
            '32' => '島根県',
            '33' => '岡山県',
            '34' => '広島県',
            '35' => '山口県',
            '36' => '徳島県',
            '37' => '香川県',
            '38' => '愛媛県',
            '39' => '高知県',
            '40' => '福岡県',
            '41' => '佐賀県',
            '42' => '長崎県',
            '43' => '熊本県',
            '44' => '大分県',
            '45' => '宮崎県',
            '46' => '鹿児島県',
            '47' => '沖縄県',
            '100' => '日本国外',
        ),
    );

    /**
     * @var array
     */
    protected static $_instance = null;

    /**
     * codes
     *
     * @param string $codeName
     * @param string|array $omits
     * @return array
     */
    public static function codes($codeName, $omits = null)
    {
        return self::getInstance()->getCodes($codeName, $omits);
    }

    /**
     * codes
     *
     * @param string $codeName
     * @param string|array $omits
     * @return array
     */
    public static function codesWithNone($codeName, $omits = null)
    {
        return self::addNone(self::getInstance()->getCodes($codeName, $omits));
    }

    /**
     * codes
     *
     * @param string $codeName
     * @return array
     */
    public static function addNone($codes)
    {
        $new = array(
            '' => eval('return ' . get_class(self::getInstance()) . '::$noneString;'),
        );
        foreach ($codes as $key => $value) {
            $new[$key] = $value;
        }
        return $new;
    }

    /**
     * k2v
     *
     * @param string $codeName
     * @param int|string $key
     * @return array
     */
    public static function k2v($codeName, $key)
    {
        return self::getInstance()->keyToValue($codeName, $key);
    }

    /**
     * v2k
     *
     * @param string $codeName
     * @param string $value
     * @return int|string
     */
    public static function v2k($codeName, $value)
    {
        return self::getInstance()->valueToKey($codeName, $value);
    }

    /**
     * getInstance
     *
     * @param void
     * @return Nutex_Code
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            $class = get_called_class();
            self::$_instance = new $class();
        }
        return self::$_instance;
    }

    /**
     * __construct
     *
     * @param void
     */
    public function __construct()
    {
        foreach (range(0, 59) as $num) {
            $val = sprintf('%02d', $num);

            if ($num > 0 && $num <= 12) {
                self::$_codeStack['month'][$val] = $val;
            }

            if ($num > 0 && $num <= 31) {
                self::$_codeStack['day'][$val] = $val;
            }

            if ($num <= 24) {
                self::$_codeStack['hour'][$val] = $val;
            }

            self::$_codeStack['minute'][$val] = $val;
            self::$_codeStack['second'][$val] = $val;
        }

        $year = (int) Nutex_Date::get('yyyy');
        foreach (range($year - 50, $year) as $num) {
            $val = sprintf('%04d', $num);
            self::$_codeStack['year'][$val] = $val;
        }

        return null;
    }


    /**
     * getCodes
     *
     * @param string $codeName
     * @param string|array $omits
     * @return array
     */
    public function getCodes($codeName, $omits = null)
    {
        $classes = array();
        $classes[] = get_class($this);
        $class = get_parent_class($this);
        while ($class !== false) {
            $classes[] = $class;
            $class = get_parent_class($class);
        }

        $codes = array();
        foreach (array_unique($classes) as $class) {
            $classCodes = $this->_loadCodes($class);
            if (isset($classCodes[$codeName])) {
                $codes = $classCodes[$codeName];
                break;
            }
        }

        if ($omits !== null && !is_array($omits)) {
            $omits = array($omits => $omits);
        }
        if (is_array($omits)) {
            foreach ($omits as $key) {
                if (array_key_exists($key, $codes)) {
                    unset($codes[$key]);
                }
            }
        }

        return $codes;
    }

    /**
     * keyToValue
     *
     * @param string $codeName
     * @param int|string $key
     * @return mixed
     */
    public function keyToValue($codeName, $key)
    {
        $codes = $this->getCodes($codeName);
        if (array_key_exists($key, $codes)) {
            return $codes[$key];
        }
        return null;
    }

    /**
     * valueToKey
     *
     * @param string $codeName
     * @param string $value
     * @return int|string
     */
    public function valueToKey($codeName, $value)
    {
        $codes = $this->getCodes($codeName);
        $key = array_search($value, $codes);
        if ($key !== false) {
            return $key;
        }
        return null;
    }

    /**
     * _loadCodes
     *
     * @param string $class
     * @return array
     */
    protected function _loadCodes($class)
    {
        if (@class_exists($class)) {
            $stack = eval('return ' . $class . '::$' . self::STACK_NAME . ';');
            if (is_array($stack)) {
                return $stack;
            }
        }
        return array();
    }
}
