<?php
/**
 * class Shared_Model_Pdf_Abstract
 * PDF生成
 *
 * @package Shared
 * @subpackage Shared_Model
 * @version
 */
class Shared_Model_Pdf_Abstract
{
    protected $_pdf;
    
    /**
     * init
     * @param none
     * @return boolean
     */
    public static function init()
    {
        $tmpFileName = uniqid() . '.pdf';
        $savePath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR .  $tmpFileName;
        
        $pdf = new Zend_Pdf();
        
    }

    /**
     * バックグラウンドでコマンドを実行する
     * @param void
     */
    public static function compress($inputPath, $savePath)
    {
	    $cmd = 'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' . $savePath . ' ' .  $inputPath;
        shell_exec($cmd);
         
    }
}
