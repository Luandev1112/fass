<?php
/**
 * class Shared_Model_Utility_DateTime
 *
 * 日付・時間ユーティリティ
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Utility_DateTime
{

	public static function hmsFromTimestamp($diff)
	{
	    //時間計算
	    $overHours = floor($diff / 3600  / 100);
	    $hours     = floor($diff / 3600) % 100;
	    $minutes   = floor($diff / 60) % 60;
	    $sec       = floor($diff % 60);
	    if ($overHours > 0) {
	         return $overHours + ':' . sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $sec);
	    }
	    return sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $sec);
	}
    
}