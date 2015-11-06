<?php
/**
 * class Shared_Model_Pdf_Invoice
 * PDF生成 請求書
 *
 * @package Shared
 * @subpackage Shared_Model
 * @version
 */
 require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');
 
 class INVOICEPDF extends TCPDF {
 
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

class Shared_Model_Pdf_Invoice
{    
    /**
     * PDF作成
     * @param $invoiceData
     * @param $companyData
     * @rparam $helper
     */
    public static function makeSingle($invoiceData, $companyData, $helper)
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
        
        $pdf = new INVOICEPDF("P", "pt", "A4", true, "UTF-8", false);
        
        $pdf->SetDefaultMonospacedFont('ipagp');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // フォント・サブセットを使用するか（初期はtrueなので呼ばなくてもいい）  
        $pdf->setFontSubsetting(false);  
        
		$displayIdLabel = '請求書管理番号';
		if ($invoiceData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$displayIdLabel = 'ORDER FORM No. ';
		}
        $pdf->setDocumentName('（' . $companyData['company_name'] . '　' . $displayIdLabel . ':' . $invoiceData['display_id'] . '）');
        $pdf->setDocumentLanguage($invoiceData['language']);
        
        // 内容描画
        self::makeContent($pdf, $invoiceData, $companyData, $helper);
        
        
        /* PDF を出力*/
        $tmpFileName = uniqid() . '.pdf';
        $savePath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . $tmpFileName;
        $savePathCompressed = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . '請求書_' . $invoiceData['display_id'] . '.pdf';
        
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
     * @param $invoiceData
     * @param $companyData
     * @param $helper
     */
    public static function makeContent($pdf, $invoiceData, $companyData, $helper)
    {
		$currencyTable    = new Shared_Model_Data_Currency();
		$currencyData = $currencyTable->getById($invoiceData['management_group_id'], $invoiceData['currency_id']);
		$invoiceData['currency_mark'] = $currencyData['symbol'];
		
    	// 仮のパラメータ
    	$logoPath = PUBLIC_PATH . '/resource/logo_group/logo.png';
    	
    	//echo 'Page W: ' . $pdf->getPageWidth();
    	//echo 'Page H: ' . $pdf->getPageHeight();
    	//exit;
    	//$pdf->SetAutoPageBreak(true);
    	$invoiceDate = '';
    	if (!empty($invoiceData['invoice_date'])) {
    		if ($invoiceData['language'] == Shared_Model_Code::LANGUAGE_EN) {
    			$invoiceDate = date('F d, Y', strtotime($invoiceData['invoice_date']));
    		} else {
    			$invoiceDate = date('Y年m月d日', strtotime($invoiceData['invoice_date']));
    		}
    	}
    	
    	$pdf->SetPrintFooter(true);
        $pdf->AddPage();
     	$pdf->SetTextColor(0,0,0);
		$pdf->SetDrawColor (0, 0, 0);
		$pdf->SetLineWidth(0.5);  //0.8は線の太さ
		$pdf->setCellPaddings(3,3,3,3);

		$pdf->SetFont('ipagp', '', 14);
		$pdf->MultiCell(270, 0, $invoiceData['to_name'], '0', 'L', false, 0, '', '', true, 0);
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
		if ($invoiceData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$picLabel = 'PIC';
			$author   = 'PIC';
			$approval = 'approval';
		}
		$pdf->MultiCell(0, 0, '担当者：' . $companyData['user_name'], '0', 'L', false, 1, '330', '', true, 0);
		
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
		
		if (!empty($invoiceData['approval_user_id'])) {
			$stampCreaterPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'stamp'  . DIRECTORY_SEPARATOR . $invoiceData['created_user_id'] . '.png';
			$stampApprovalPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'stamp'  . DIRECTORY_SEPARATOR . $invoiceData['approval_user_id'] . '.png';
			$pdf->Image($stampCreaterPath, 465, $heightStamp + 5, 38, 38, 'PNG', '', '', true, 300, '', false, false, 0);
			$pdf->Image($stampApprovalPath, 515, $heightStamp + 5, 38, 38, 'PNG', '', '', true, 300, '', false, false, 0);
		}
		// 枠
		$pdf->setY($heightStamp);
		$pdf->MultiCell(50,50, '', $cellBorderStamp, 'L', false, 0, '460', '', true, 0);
		$pdf->MultiCell(50,50, '', $cellBorderStamp, 'L', false, 1, '', '', true, 0);
		
		
		
		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
		// タイトル
		$pdf->setCellPaddings(3,3,3,5);
		$pdf->SetFont('ipagp', '', 16);
		$pdf->SetLineStyle(array('width' => 2, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		$pdf->MultiCell(420, 0, '　' . $invoiceData['title'] . '　', 'B', 'C', false, 1, '87', '', true, 0);
		
		$pdf->SetFont('ipagp', '', 9);
		$displayIdLabel = '請求書管理番号';
		if ($invoiceData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$displayIdLabel = 'ORDER FORM No. ';
		}
		
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
        
        $pdf->MultiCell( 40, 0, $invoiceData['labels']['label_1'], $cellBorder, 'L', false, 0, '', '', true, 0); // No.
        $pdf->MultiCell(320, 0, $invoiceData['labels']['label_2'], $cellBorder, 'L', false, 0, '', '', true, 0); // 項目
        $pdf->MultiCell( 60, 0, $invoiceData['labels']['label_4'], $cellBorder, 'C', false, 0, '', '', true, 0); // 単価
        $pdf->MultiCell( 50, 0, $invoiceData['labels']['label_5'], $cellBorder, 'C', false, 0, '', '', true, 0); // 数量
        $pdf->MultiCell( 0, 0, $invoiceData['labels']['label_6'], $cellBorderLast,  'C', false, 1, '', '', true, 0);// 金額：円

		foreach ($invoiceData['item_list'] as $each) {
			/*
			$keys = array(
				'id'         => array('limit' => 0, 'count' => 0),
				'item_name'  => array('limit' => 22, 'count' => 0),
				'spec'       => array('limit' => 13, 'count' => 0),
				'unit_price' => array('limit' => 0, 'count' => 0),
				'amount'     => array('limit' => 0, 'count' => 0),
				'price'      => array('limit' => 0, 'count' => 0),
			);
			
			foreach ($keys as $eachKey => &$keyData) {
				if (!empty($keyData['limit'])) {
					$exploded = explode("\n", $each[$eachKey]);
					foreach ($exploded as $eachExploded) {
						if (mb_strlen($eachExploded) > $keyData['limit']) {						
							$keyData['count'] += floor(mb_strlen($eachExploded) / $keyData['limit']) ;
						}
					}
				}
			}
			$maxCount = max(array(
				substr_count($each['id'], "\n") + 1 + $keys['id']['count'],
				substr_count($each['item_name'], "\n") + 1 + $keys['item_name']['count'],
				substr_count($each['spec'], "\n") + 1 + $keys['spec']['count'],
				substr_count($each['unit_price'], "\n") + 1 + $keys['unit_price']['count'],
				substr_count($each['amount'], "\n") + 1 +  $keys['amount']['count'],
				substr_count($each['price'], "\n") + 1 + $keys['price']['count'],
			));
			*/		
			$maxCount = max(array(
				substr_count($each['id'], "\n"),
				substr_count($each['item_name'], "\n"),
				substr_count($each['unit_price'], "\n"),
				substr_count($each['amount'], "\n"),
				substr_count($each['price'], "\n"),
			)) + 1;
			
			if ($each === end($invoiceData['item_list'])) {
				$cellBorder['B']['width']    = '2';
				$cellBorderLast['B']['width'] = '2'; 
			}

			$unitPrice = '0';
			$amount    = '0';
			$price     = '0';
			
			if (!empty($each['unit_price'])) {
				$unitPrice = $invoiceData['currency_mark'] . ' ' . number_format($each['unit_price']);
			}
			if (!empty($each['amount'])) {
				$amount    = number_format($each['amount']);
			}
			if (!empty($each['price'])) {
				$price     = $invoiceData['currency_mark'] . ' ' . number_format($each['price']);
			}
			$pdf->SetFont('ipagp', '', 9);
	        $pdf->MultiCell( 40, $maxCount * 18, $each['id'], $cellBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(320, $maxCount * 18, $each['item_name'], $cellBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 9);
	        $pdf->MultiCell( 60, $maxCount * 18, number_format($each['unit_price']), $cellBorder, 'R', false, 0, '', '', true, 0);
	        $pdf->MultiCell( 50, $maxCount * 18, $helper->numberFormat($each['amount']), $cellBorder, 'R', false, 0, '', '', true, 0);
	        $pdf->MultiCell(  0, $maxCount * 18, number_format($each['price']), $cellBorderLast,  'R', false, 1, '', '', true, 0);

		}

		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
        $subTotalBorder = array(
        	'B' => array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
        
        
		$subTotalLabel = '合計（税別）';
		$taxLabel      = '消費税 (' . $invoiceData['tax_percentage'] .  '%)';
		$totalLabel    = '合計（税込）';
		
		if ($invoiceData['language'] == Shared_Model_Code::LANGUAGE_EN) {
			$subTotalLabel = 'SUB TOTAL';
			$taxLabel      = 'TAX (' . $invoiceData['tax_percentage'] . '%)';
			$totalLabel    = 'GRAND TOTAL';
		}
	    			
        $subtotal = $invoiceData['currency_mark'] . '0';
        if (!empty($invoiceData['subtotal'])) {
        	$subtotal =  $invoiceData['currency_mark'] . ' ' . number_format($invoiceData['subtotal']);
        }

        $tax = $invoiceData['currency_mark'] . '0';
        if (!empty($invoiceData['tax'])) {
        	$tax = $invoiceData['currency_mark'] . ' ' . number_format($invoiceData['tax']);
        }

        $total = mb_convert_encoding($invoiceData['currency_mark'], 'HTML-ENTITIES', 'UTF-8') . '0';
        if (!empty($invoiceData['total_with_tax'])) {
        	$total = $invoiceData['currency_mark'] . ' ' . number_format($invoiceData['total_with_tax']);
        }
        
        
        if (!empty($invoiceData['including_tax'])) {
	        // 合計（税込）
	        $pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(350, 0, '', $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(90, 0, $totalLabel, $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $total, $subTotalBorder,  'R', false, 1, '', '', true, 0);
	        
        } else {
	        // 合計（税抜）
	        $pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(350, 0, '', $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(90, 0, $subTotalLabel, $subTotalBorder, 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $subtotal, $subTotalBorder,  'R', false, 1, '', '', true, 0);
			
			// 消費税
			$pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(350, 0, '', '', 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(90, 0, $taxLabel, '', 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $tax, '',  'R', false, 1, '', '', true, 0);
	        
	        // 合計（税込）
	        $pdf->SetFont('ipagp', '', 12);
	        $pdf->MultiCell(350, 0, '', '', 'L', false, 0, '', '', true, 0);
	        $pdf->MultiCell(90, 0, $totalLabel, '', 'L', false, 0, '', '', true, 0);
	        $pdf->SetFont('helvetica', '', 12);
	        $pdf->MultiCell(  0, 0, $total, '',  'R', false, 1, '', '', true, 0);
        }

        
		
		$pdf->SetFont('ipagp', '', 9);
		// 備考
		$pdf->MultiCell(0, 30, '', '', 'C', false, 1, '', '', true, 0);
		
		$pdf->MultiCell(0, 30, $invoiceData['memo'], '', 'L', false, 1, '', '', true, 0);
		
    }


}
