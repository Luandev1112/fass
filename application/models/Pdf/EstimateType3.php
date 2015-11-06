<?php
/**
 * class Shared_Model_Pdf_EstimateType3
 * PDF生成 見積書3
 *
 * @package Shared
 * @subpackage Shared_Model
 * @version
 */
 require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');
 
 class MYPDF extends TCPDF {
 
 	protected $_documentName;
 	
 	public function setDocumentName($documentName) {
 		$this->_documentName = $documentName;
 	}
 	
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-40);
        // Set font
        $this->SetFont('ipagp', '', 8);
        // Page number
        $this->Cell(0, 10, $this->_documentName . '　ページ'.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
}

class Shared_Model_Pdf_EstimateType3
{    
    /**
     * PDF作成
     * @param $data[]
     */
    public static function makeSingle($estimateData, $versionData, $companyData)
    {
	    ini_set('display_errors', 0);
	    
		// =============================================
		// TCPDFを使用する
		// 
		// MultiCell  http://tcpdf.penlabo.net/method/m/MultiCell.html
		// Cell       http://tcpdf.penlabo.net/method/c/Cell.html
		// =============================================
        // コンストラクタ
        // 1. orientation
        //    # P または Portrait 
        //    # L または Landscape 
        // 
        // 2. unit
        //    # pt (ポイント)
        //    # mm（ミリメートル）
        //    # cm（センチメートル）
        //    # in（インチ）
        //
        // 3. format
        //    # A3
        //    # A4
        //    # A5
        //    # Letter
        //    # Legal
        //    # array($width, $height)
        // 
        
        $pdf = new MYPDF("P", "pt", "A4", true, "UTF-8", false);
        
        $pdf->SetDefaultMonospacedFont('ipagp');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // フォント・サブセットを使用するか（初期はtrueなので呼ばなくてもいい）  
        $pdf->setFontSubsetting(false);  
        
        $pdf->setDocumentName('（' . $companyData['company_name'] . '　見積書管理番号:' . $estimateData['display_id'] . '）');
        // 内容描画
        self::makeContent($pdf, $estimateData, $versionData, $companyData);
        
        
        /* PDF を出力*/
        $tmpFileName = uniqid() . '.pdf';
        $savePath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $tmpFileName;
        $savePathCompressed = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . '見積書_' . $estimateData['display_id'] . '.pdf';
        
        /* PDF を保存 */
        $pdf->Output($savePath, 'F');
        
        // ファイル圧縮
        Shared_Model_Pdf_Abstract::compress($savePath, $savePathCompressed);
        
        unlink($savePath);
  
      	header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Transfer-Encoding: binary");
		header("Content-Type: application/pdf");
		print file_get_Contents($savePathCompressed);
        exit;
        
    }
    
    
    /**
     * PDF明細書1件描画
     * @param $data[]
     */
    public static function makeContent($pdf, $estimateData, $versionData, $companyData)
    {
    	// 仮のパラメータ
    	$logoPath = PUBLIC_PATH . '/resource/logo_group/logo.png';
    	
    	//echo 'Page W: ' . $pdf->getPageWidth();
    	//echo 'Page H: ' . $pdf->getPageHeight();
    	//exit;
    	//$pdf->SetAutoPageBreak(true);
    	
    	$estimateDate = '';
    	if (!empty($estimateData['estimate_date'])) {
    		$estimateDate = date('Y年m月d日', strtotime($estimateData['estimate_date']));
    	}
    	
    	$pdf->SetPrintFooter(true);
        $pdf->AddPage();
     	$pdf->SetTextColor(0,0,0);
		$pdf->SetDrawColor (0, 0, 0);
		$pdf->SetLineWidth(0.5);  //0.8は線の太さ
		$pdf->setCellPaddings(3,3,3,3);

		$pdf->SetFont('ipagp', '', 14);
		$pdf->MultiCell(270, 0, $versionData['to_name'], '0', 'L', false, 0, '', '', true, 0);
		$pdf->SetFont('ipagp', '', 9);
		$pdf->MultiCell(0, 0, $estimateDate, '0', 'R', false, 1, '', '', true, 0);
		
		
    	$pdf->Image($logoPath, 330, 50, 0, 24, 'PNG', '', '', true, 300, '', false, false, 0);
    	
    	$pdf->setCellPaddings(0,0,0,0);
    	$pdf->SetFont('ipagp', '', 12);
    	$pdf->MultiCell(0, 0, $companyData['company_name'], '0', 'L', false, 1, '330', '78', true, 0);
    	$pdf->SetFont('ipagp', '', 9);
    	$pdf->MultiCell(0, 0, $companyData['address'], '0', 'L', false, 1, '330', '', true, 0);
		$pdf->MultiCell(0, 0, 'TEL：' . $companyData['tel'] . '　　FAX：' . $companyData['fax'], '0', 'L', false, 1, '330', '', true, 0);
		$pdf->MultiCell(0, 0, '担当者：' . $companyData['user_name'], '0', 'L', false, 1, '330', '', true, 0);
		
		$pdf->MultiCell(0, 0, '', '0', 'L', false, 1, '330', '', true, 0);


		// 印鑑
        $cellBorderStamp = array(
        	'R' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        	'L' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        	'T' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        	'B' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
		$pdf->MultiCell(50, 0, '作成', $cellBorderStamp, 'C', false, 0, '460', '', true, 0);
		$pdf->MultiCell(50, 0, '承認', $cellBorderStamp, 'C', false, 1, '', '', true, 0);
		
		$heightStamp = $pdf->getY();
		
		if (!empty($versionData['approval_user_id'])) {
			$stampCreaterPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'stamp'  . DIRECTORY_SEPARATOR . $versionData['created_user_id'] . '.png';
			$stampApprovalPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'stamp'  . DIRECTORY_SEPARATOR . $versionData['approval_user_id'] . '.png';
			$pdf->Image($stampCreaterPath, 465, $heightStamp + 5, 38, 38, 'PNG', '', '', true, 300, '', false, false, 0);
			$pdf->Image($stampApprovalPath, 515, $heightStamp + 5, 38, 38, 'PNG', '', '', true, 300, '', false, false, 0);
		}
		// 枠
		$pdf->setY($heightStamp);
		$pdf->MultiCell(50,50, '', $cellBorderStamp, 'L', false, 0, '460', '', true, 0);
		$pdf->MultiCell(50,50, '', $cellBorderStamp, 'L', false, 1, '', '', true, 0);
		
		
		$pdf->MultiCell(0, 10, '', '', 'C', false, 1, '', '', true, 0);
		
		// タイトル
		$pdf->setCellPaddings(3,3,3,5);
		$pdf->SetFont('ipagp', '', 16);
		$pdf->SetLineStyle(array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		$pdf->MultiCell(420, 0, '　' . $versionData['title'] . '　', 'B', 'C', false, 1, '87', '', true, 0);
		
		$pdf->SetFont('ipagp', '', 9);
		$pdf->MultiCell(420, 0, '　' . '見積書管理番号：　' . $estimateData['display_id'], '', 'R', false, 1, '87', '', true, 0);
		
		
		//$pdf->MultiCell(0, 10, '', '', 'C', false, 1, '', '', true, 0);
		
		
		// 見積もり表
		$pdf->setCellPaddings(5,5,5,5);
		$pdf->SetLineStyle(array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		$pdf->MultiCell(0, 1, '', 'B', 'C', false, 1, '', '', true, 0);
		
        $cellBorder = array(
        	'R' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 1.3, 'color' => array(0, 0, 0)),
        	'B' => array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
        $cellBorderLast = array(
        	'B' => array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
        
		$maxCount = max(array(
			substr_count($versionData['labels']['label_1'], "\n"),
			substr_count($versionData['labels']['label_2'], "\n"),
			substr_count($versionData['labels']['label_3'], "\n"),
			substr_count($versionData['labels']['label_4'], "\n"),
			substr_count($versionData['labels']['label_5'], "\n"),
		)) + 1;
			
        $pdf->MultiCell( 40, $maxCount * 16, $versionData['labels']['label_1'], $cellBorder, 'L', false, 0, '', '', true, 0); // No.
        $pdf->MultiCell(200, $maxCount * 16, $versionData['labels']['label_2'], $cellBorder, 'L', false, 0, '', '', true, 0); // 項目名
        $pdf->MultiCell( 60, $maxCount * 16, $versionData['labels']['label_3'], $cellBorder, 'C', false, 0, '', '', true, 0); // 参考小売価格（税込）
        $pdf->MultiCell(100, $maxCount * 16, $versionData['labels']['label_4'], $cellBorder, 'C', false, 0, '', '', true, 0); // 参考小売価格（税別）
        $pdf->MultiCell(  0, $maxCount * 16, $versionData['labels']['label_5'], $cellBorderLast, 'C', false, 1, '', '', true, 0); // 仕入掛率
	
		foreach ($versionData['item_list'] as $each) {
			$maxCount = max(array(
				substr_count($each['id'], "\n"),
				substr_count($each['item_name'], "\n"),
				substr_count($each['standard_price_tax'], "\n"),
				substr_count($each['standard_price'], "\n"),
				substr_count($each['unit'], "\n"),
				substr_count($each['wholesale_rate'], "\n"),
				substr_count($each['unit_price'], "\n"),
			)) + 1;
			
			if ($each === end($versionData['item_list'])) {
				$cellBorder['B']['width']    = '2';
				$cellBorderLast['B']['width'] = '2'; 
			}
			
	        $pdf->MultiCell( 40, $maxCount * 16, $each['id'], $cellBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(200, $maxCount * 16, $each['item_name'], $cellBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell( 60, $maxCount * 16, $each['unit'], $cellBorder, 'R', false, 0, '', '', true, 0);
	        $pdf->MultiCell(100, $maxCount * 16, $each['unit_price'], $cellBorder, 'R', false, 0, '', '', true, 0);
	        $pdf->MultiCell(  0, $maxCount * 16, $each['price_per_month'], $cellBorderLast,  'R', false, 1, '', '', true, 0);
		}

		
		// 備考
		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
		$pdf->MultiCell(0, 30, $versionData['memo'], '', 'L', false, 1, '', '', true, 0);
		
    }


}
