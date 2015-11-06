<?php
/**
 * class Shared_Model_Utility_Text
 *
 * テキストユーティリティ
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Utility_Text
{
	// 機種依存文字で分割
	static public function replaceMachineChar($str)
	{
		$search  = array('㌢','㍍','㌘','㌧','㍑','㍊','㎜','㎝','㎞','㎎','㎏','㏄','㎡', 'ℓ');
		$replace = array('cm','m','g','トン','L','mL','mm','cm','km','mg','kg','cc', '平方m', 'L');
		
		$result = str_replace($search, $replace, $str);
		// 半角カナを全角カナ 全角英字を半角英字
		$result = mb_convert_kana($result, "KV");
		
		// 機種依存文字を変換
		$ret = str_replace($search, $replace, $str);
		
		return $ret;
	}

	// あらゆる空白文字で分割
	// https://qiita.com/mpyw/items/a704cb900dfda0fc0331
	static function extractKeywords($input, $limit = -1)
	{
	    return preg_split('/[\p{Z}\p{Cc}]++/u', $input, $limit, PREG_SPLIT_NO_EMPTY);
	}
	
	// 銀行文字チェック
	static public function bankStringValid($str)
	{
		$jchk = "1234567890".
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
		"アイウエオカキクケコ".
		"サシスセソタチツテト".
		"ナニヌネノハヒフヘホ".
		"マミムメモヤユヨ".
		"ラリルレロワン".
		"ガギグゲゴザジズゼゾ".
		"ダヂヅデドバビブベボ".
		"パピプペポ".
		"ヴ(),.\-\/\ ";

		$jchk = mb_convert_kana($jchk, 'k');
		
		//var_dump($str);
		//var_dump($jchk);
		//exit;
		
		mb_regex_encoding('UTF-8');
		return(preg_match("/^[".$jchk."]+$/u", $str, $data, PREG_OFFSET_CAPTURE));
    }
}