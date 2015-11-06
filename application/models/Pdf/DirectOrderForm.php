<?php
/**
 * class Shared_Model_Pdf_DirectOrderForm
 * PDF生成 請求書
 *
 * @package Shared
 * @subpackage Shared_Model
 * @version
 */
 require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');
 
 class ORDERFORMEPDF extends TCPDF {
 
 	protected $_documentName;
 	protected $_documentLanguage;
 	
 	public function setDocumentName($documentName) {
 		$this->_documentName = $documentName;
 	}

 	public function setDocumentLanguage($documentLanguage) {
 		$this->_documentLanguage = $documentLanguage;
 	}
 	
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-40);
        // Set font
        $this->SetFont('ipagp', '', 8);
        // Page number
        if ($this->_documentLanguage == Shared_Model_Code::LANGUAGE_EN) {
        	$this->Cell(0, 10, $this->_documentName . '　Page'.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        } else {
        	$this->Cell(0, 10, $this->_documentName . '　ページ'.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'L', 0, '', 0, false, 'T', 'M');
    	}
    }
}

class Shared_Model_Pdf_DirectOrderForm
{    
    /**
     * PDF作成
     * @param $orderFormData
     * @param $companyData
     */
    public static function makeSingle($orderFormData, $companyData, $viewObj)
    {
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
        
        $pdf = new ORDERFORMEPDF("P", "pt", "A4", true, "UTF-8", false);
        
        $pdf->SetDefaultMonospacedFont('ipagp');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // フォント・サブセットを使用するか（初期はtrueなので呼ばなくてもいい）  
        $pdf->setFontSubsetting(false);  

		$displayIdLabel = '発注書管理番号';
		if ($orderFormData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$displayIdLabel = 'ORDER FORM No. ';
		}
        $pdf->setDocumentName('（' . $companyData['company_name'] . '　' . $displayIdLabel . ':' . $orderFormData['display_id'] . '）');
        $pdf->setDocumentLanguage($orderFormData['language']);
        
        // 内容描画
        self::makeContent($pdf, $orderFormData, $companyData, $viewObj);
        
        
        /* PDF を出力します */
        //$pdf->Output($savePath, 'I');
        

        /* PDF を出力*/
        $tmpFileName = uniqid() . '.pdf';
        $savePath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $tmpFileName;
        $savePathCompressed = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . '発注書_' . $orderFormData['display_id'] . '.pdf';
        
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
     * PDF1件描画
     * @param $pdf
     * @param $orderFormData
     * @param $companyData
     * @param $viewObj
     */
    public static function makeContent($pdf, $orderFormData, $companyData, $viewObj)
    {
		$currencyTable    = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getById($orderFormData['management_group_id'], $orderFormData['currency_id']);
		$orderFormData['currency_mark'] = $currencyData['symbol'];
		
    	// 仮のパラメータ
    	$logoPath = PUBLIC_PATH . DIRECTORY_SEPARATOR . '/resource/logo_group/logo.png';
    	
    	//echo 'Page W: ' . $pdf->getPageWidth();
    	//echo 'Page H: ' . $pdf->getPageHeight();
    	//exit;
    	//$pdf->SetAutoPageBreak(true);
    	$invoiceDate = '';
    	if (!empty($orderFormData['order_date'])) {
    		if ($orderFormData['language'] == Shared_Model_Code::LANGUAGE_EN) {
    			$invoiceDate = date('F d, Y', strtotime($orderFormData['order_date']));
    		} else {
    			$invoiceDate = date('Y年m月d日', strtotime($orderFormData['order_date']));
    		}
    	}
    	
    	$pdf->SetPrintFooter(true);
        $pdf->AddPage();
     	$pdf->SetTextColor(0,0,0);
		$pdf->SetDrawColor (0, 0, 0);
		$pdf->SetLineWidth(0.5);  //0.8は線の太さ
		$pdf->setCellPaddings(3,3,3,3);

		$pdf->SetFont('ipagp', '', 14);
		$pdf->MultiCell(270, 0, $orderFormData['to_name'], '0', 'L', false, 0, '', '', true, 0);
		$pdf->SetFont('ipagp', '', 9);
		$pdf->MultiCell(0, 0, $invoiceDate, '0', 'R', false, 1, '', '', true, 0);

		
    	$pdf->Image($logoPath, 330, 50, 0, 24, 'PNG', '', '', true, 300, '', false, false, 0);
    	
    	$pdf->setCellPaddings(0,0,0,0);
    	$pdf->SetFont('ipagp', '', 12);
    	$pdf->MultiCell(0, 0, $companyData['company_name'], '0', 'L', false, 1, '330', '78', true, 0);
    	$pdf->SetFont('ipagp', '', 9);
    	$pdf->MultiCell(0, 0, $companyData['address'], '0', 'L', false, 1, '330', '', true, 0);
		$pdf->MultiCell(0, 0, 'TEL：' . $companyData['tel'] . '　　FAX：' . $companyData['fax'], '0', 'L', false, 1, '330', '', true, 0);

		$picLabel = '担当者';
        $author   = '作成';
        $approval = '承認';
		if ($orderFormData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$picLabel = 'PIC';
			$author   = 'PIC';
			$approval = 'approval';
		}
		$pdf->MultiCell(0, 0, $picLabel . '：' . $companyData['user_name'], '0', 'L', false, 1, '330', '', true, 0);
		
		// 印鑑
        $cellBorderStamp = array(
        	'R' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        	'L' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        	'T' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        	'B' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
		
		$pdf->MultiCell(50, 0, $author, $cellBorderStamp, 'C', false, 0, '460', '', true, 0);
		$pdf->MultiCell(50, 0, $approval, $cellBorderStamp, 'C', false, 1, '', '', true, 0);
		
		$heightStamp = $pdf->getY();
		
		if (!empty($orderFormData['approval_user_id'])) {
			$stampCreaterPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'stamp'  . DIRECTORY_SEPARATOR . $orderFormData['created_user_id'] . '.png';
			$stampApprovalPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'stamp'  . DIRECTORY_SEPARATOR . $orderFormData['approval_user_id'] . '.png';
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
		$pdf->MultiCell(420, 0, '　' . $orderFormData['title'] . '　', 'B', 'C', false, 1, '87', '', true, 0);
		
		$pdf->SetFont('ipagp', '', 9);
		$displayIdLabel = '発注書管理番号';
		if ($orderFormData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$displayIdLabel = 'ORDER FORM No. ';
		}
		
		$pdf->MultiCell(420, 0, '　' . $displayIdLabel . '：　' . $orderFormData['display_id'], '', 'R', false, 1, '87', '', true, 0);
		
		
		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
		
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
        
        $pdf->MultiCell( 40, 0, $orderFormData['labels']['label_1'], $cellBorder, 'L', false, 0, '', '', true, 0); // No.
        $pdf->MultiCell(270, 0, $orderFormData['labels']['label_2'], $cellBorder, 'L', false, 0, '', '', true, 0); // 項目
        $pdf->MultiCell( 80, 0, $orderFormData['labels']['label_3'], $cellBorder, 'C', false, 0, '', '', true, 0); // 単価
        $pdf->MultiCell( 60, 0, $orderFormData['labels']['label_4'], $cellBorder, 'C', false, 0, '', '', true, 0); // 数量
        $pdf->MultiCell(  0, 0, $orderFormData['labels']['label_5'], $cellBorderLast,  'C', false, 1, '', '', true, 0);// 金額：円
		
		// 高さ計算用
		$pdf2 = clone $pdf;
		$pdf2->setCellPaddings(5,5,5,5);
		$pdf2->AddPage();
		$pdf2->SetFont('ipagp', '', 9);
		
		foreach ($orderFormData['item_list'] as $each) {
			/*
			$maxCount = max(array(
				substr_count($each['id'], "\n"),
				substr_count($each['item_name'], "\n"),
				substr_count($each['unit_price'], "\n"),
				substr_count($each['amount'], "\n"),
				substr_count($each['price'], "\n"),
			)) + 1;
			*/
			
			if ($each === end($orderFormData['item_list'])) {
				$cellBorder['B']['width']    = '2';
				$cellBorderLast['B']['width'] = '2'; 
			}
			
			
			// セル高さ計算
			$height0 = $pdf2->getY();
			$pdf2->MultiCell(270, 0, $each['item_name'], $cellBorder, 'L', false, 1, '', '', true, 0);
			$height1 = $pdf2->getY();
			//var_dump($height1 - $height0);
			

			$unitPrice = '0';
			$amount    = '0';
			$price     = '0';
			
			if (!empty($each['unit_price'])) {
				$unitPrice = $orderFormData['currency_mark'] . ' ' . $viewObj->numberFormat($each['unit_price']);
			}
			if (!empty($each['amount'])) {
				$amount    = $viewObj->numberFormat($each['amount']);
			}
			if (!empty($each['price'])) {
				$price     = $orderFormData['currency_mark'] . ' ' . number_format($each['price']);
			}
			$pdf->SetFont('ipagp', '', 9);
	        $pdf->MultiCell( 40, $height1 - $height0, $each['id'], $cellBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(270, $height1 - $height0, $each['item_name'], $cellBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 9);
	        $pdf->MultiCell( 80, $height1 - $height0, $unitPrice, $cellBorder, 'R', false, 0, '', '', true, 0);
	        $pdf->MultiCell( 60, $height1 - $height0, $amount, $cellBorder, 'R', false, 0, '', '', true, 0);
	        $pdf->MultiCell(  0, $height1 - $height0, $price, $cellBorderLast,  'R', false, 1, '', '', true, 0);
		}

//exit;

		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
        $subTotalBorder = array(
        	'B' => array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
        
        $pdf->SetFont('helvetica', '', 12);
		$subTotalLabel = '合計（税別）';
		$taxLabel      = '消費税 (' . $orderFormData['tax_percentage'] .  '%)';
		$totalLabel    = '合計（税込）';
		
		if ($orderFormData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$subTotalLabel = 'SUB TOTAL';
			$taxLabel      = 'TAX (' . $orderFormData['tax_percentage'] . '%)';
			$totalLabel    = 'GRAND TOTAL';
		}
	    			
        $subtotal = $orderFormData['currency_mark'] . '0';
        if (!empty($orderFormData['subtotal'])) {
        	$subtotal =  $orderFormData['currency_mark'] . ' ' . number_format($orderFormData['subtotal']);
        }

        $tax = $orderFormData['currency_mark'] . '0';
        if (!empty($orderFormData['tax'])) {
        	$tax = $orderFormData['currency_mark'] . ' ' . number_format($orderFormData['tax']);
        }

        $total = mb_convert_encoding($orderFormData['currency_mark'], 'HTML-ENTITIES', 'UTF-8') . '0';
        if (!empty($orderFormData['total_with_tax'])) {
        	$total = $orderFormData['currency_mark'] . ' ' . number_format($orderFormData['total_with_tax']);
        }
        
        
        if (!empty($orderFormData['including_tax'])) {
	        // 合計（税込）
	        $pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(310, 0, '', $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(120, 0, $totalLabel, $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $total, $subTotalBorder,  'R', false, 1, '', '', true, 0);
	        
        } else {
	        // 合計（税抜）
	        $pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(310, 0, '', $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(120, 0, $subTotalLabel, $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $subtotal, $subTotalBorder,  'R', false, 1, '', '', true, 0);
			
			// 消費税
			$pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(310, 0, '', '', 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(120, 0, $taxLabel, '', 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $tax, '',  'R', false, 1, '', '', true, 0);
	        
	        // 合計（税込）
	        $pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(310, 0, '', '', 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(120, 0, $totalLabel, '', 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $total, '',  'R', false, 1, '', '', true, 0);
        }

        
		
		$pdf->SetFont('ipagp', '', 9);
		// 備考
		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
		$pdf->MultiCell(0, 30, $orderFormData['memo'], '', 'L', false, 1, '', '', true, 0);
		
    }


}
