<?php
/**
 * class Shared_Model_Task_Data
 *
 * データ関連 タスク
 *
 * @package Shared
 * @subpackage Shared_Model
 */
 
/*

*/
 
class Shared_Model_Task_Data extends Shared_Model_Task_Abstract
{
    
    /**
     * VIPチャット 未視聴スキップ
     * @param int $targetDate (YYYYmmdd)
     * @return void
     */
    public static function downloadSalesData($targetDate)
    {
    	$saveDirPath = '/var/www/' . APPLICATION_DOMAIN . '/batch_itunes/Reporter/';
    	
    	$cmd = 'java -jar ' . $saveDirPath . 'Reporter.jar p=Reporter.properties Sales.getReport 87027585, Sales, Summary, Daily, ' . $targetDate;		
        //echo $cmd;exit;
        
        echo exec($cmd);
    }

}