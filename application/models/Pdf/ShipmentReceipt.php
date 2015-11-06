<?php
/**
 * class Shared_Model_Pdf_ShipmentReceipt
 * PDF生成 出荷 明細表
 *
 * @package Shared
 * @subpackage Shared_Model
 * @version
 */
class Shared_Model_Pdf_ShipmentReceipt
{

    /**
     * デフォルトパラメータ作成
     * @param none
     * @return array $params
     */
    public static function createDefaultParams()
    {
    	$params = array(
    		'order_no'              => '00000000',
    		'order_date'            => '2000/01/01',
    		'payment_method'        => '〇〇〇〇〇〇〇〇',
    		
    		'shop_info'             => "オンラインショップ　サンプル\n\n〒000-0000\n〇〇県〇〇〇市〇〇〇〇 〇丁目○番○号 〇〇〇ビル 0F\n[電話] 000-000-0000\n[URL] https://example.com/\n[Mail] sample@example.com",
    		
    		'delivery_method'       => '〇〇〇〇〇〇〇〇',
    		'delivery_zipcode'      => '000-0000',
    		'delivery_full_address' => '〇〇県〇〇〇市〇〇〇〇 〇丁目○番○号 〇〇〇ビル 0F',
    		'delivery_name'         => '〇〇〇 〇〇〇',
    		
    		'order_zipcode'         => '000-0000',
    		'order_full_address'    => '〇〇県〇〇〇市〇〇〇〇 〇丁目○番○号 〇〇〇ビル 0F',
    		'order_name'            => '〇〇〇 〇〇〇',
    		'tax'                   => '99,999',
    		'delivery_fee'          => '99,999',
    		'charge'                => '99,999',
    		'discount'              => '99,999',
    		'total'                 => '999,999',
    	);
    	
    	$items = array();
    	
    	for($i = 0; $i < 4; $i++) {
	    	$items[] = array(
				'product_code'        => '00000000',
				'product_name'        => '〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇',
				'amount'              => '99',
				'product_unit_price'  => '99,999',
				'row_price'           => '99,999',
	    	);
    	}
    	$params['product_items'] = $items;
    	$params['template_1']    = "テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。\n\nテンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。テンプレート1が入ります。";
    	$params['template_2']    = "テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。\n\nテンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。テンプレート2が入ります。";
		$params['template_3']    = "テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。\n\nテンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。テンプレート3が入ります。";
		
		return $params;
    }

    public static function createQRWithOrderId($orderId)
    {
		require_once('Qrcode/Img.php');
		
		$fileName = 'qr_' . uniqid() . '.png';
		
		$tempFilePath = Shared_Model_Resource_Temporary::getResourceObjectPath($fileName);
		
        // QRコード用URL
        $target_url = 'order://' . $orderId;
        
        $z = new Qrcode_Img();
        
        // 生成するQRコードの設定
        $z->set_qrcode_version(3);           # set qrcode version 1
        $z->set_qrcode_error_correct("R");   # set ecc level H
        $z->set_module_size(4);              # set module size 3pixel
        $z->set_quietzone(5);                # set quietzone width 5 modules

        // 画像データの吐き出し
        $z->qrcode_image_out($target_url, "png", $tempFilePath);
        
    	return $fileName;
    }
    
    
    /**
     * PDF作成(シングルページ)
     * @param $data[]
     */
    public static function makeSingle($data, $helper)
    {
        $tmpFileName = uniqid() . '.pdf';
        $savePath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR .  $tmpFileName;

        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');

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
        
        $pdf = new TCPDF("P", "pt", "A4", true, "UTF-8", false);
        
        $pdf->SetDefaultMonospacedFont('ipagp');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // フォント・サブセットを使用するか（初期はtrueなので呼ばなくてもいい）  
        $pdf->setFontSubsetting(false);  
        
        
        // 内容描画
        if ($data['order_from_site'] === (string)Shared_Model_Code::ORDER_FROM_SITE_GOOSCA) {
            self::makeGOOSCAContent($pdf, $data, $helper);
        } else {
            self::makeHFCContent($pdf, $data, $helper);
        }
        
        
        /* PDF を出力します */
        $pdf->Output($savePath, 'I');
        exit;
        
    }

    /**
     * PDF作成(複数枚)
     * @param $items[][]
     */
    public static function makeMultiple($items, $helper)
    {
        $tmpFileName = uniqid() . '.pdf';
        $savePath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR .  $tmpFileName;

        require_once(LIBRARY_PATH . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php');

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
        
        $pdf = new TCPDF("P", "pt", "A4", true, "UTF-8", false);
        
        $pdf->SetDefaultMonospacedFont('ipagp');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // フォント・サブセットを使用するか（初期はtrueなので呼ばなくてもいい）  
        $pdf->setFontSubsetting(false);  
        
        
        // 内容描画
        foreach ($items as $data) {
            if ($data['order_from_site'] === (string)Shared_Model_Code::ORDER_FROM_SITE_GOOSCA) {
                self::makeGOOSCAContent($pdf, $data, $helper);
            } else {
                self::makeHFCContent($pdf, $data, $helper);
            }
        }
        
        /* PDF を出力します */
        $pdf->Output($savePath, 'I');
        exit;
        
    }
    
    
    /**
     * GOOSCA PDF明細書1件描画
     * @param $data[]
     */
    public static function makeGOOSCAContent($pdf, $data, $helper)
    {
        $pdf->AddPage();
     	$pdf->SetTextColor(0,0,0);
		$pdf->SetDrawColor (0, 0, 0);
		$pdf->SetLineWidth(0.5);  //0.8は線の太さ
		$pdf->setCellPaddings(5,5,5,5);
		
        $pdf->SetFont('ipagp', 'U', 14);
        $pdf->Cell(0, 20, "　納品明細書　", 0, 2, 'C', false);
        
        $pdf->SetFont('ipagp', '', 8);
        $pdf->Cell(0, 3, "", '', 1, 'C', false);
        
		$height0 = $pdf->getY();
		
		$fullAddress = '';
		$prefectureList    = Shared_Model_Code::codes('prefecture');
		
		//if ($orderData['country_select'] === (string)Shared_Model_Code::COUNTTRY_JP) {
			$fullAddress .= '〒' . substr($data['delivery_zipcode'], 0, 3) . '-' . substr($data['delivery_zipcode'], 3, 4) . "\n";
		
		/*
		} else {
			$fullAddress .= $orderData['country_other'] . "\n" .$data['order_zipcode'] . "\n";
		}
		*/
								
		$fullAddress .= $data['delivery_full_address'] . "\n";
		$fullAddress .= $data['delivery_name'];
		$fullAddress .= ' 様' . "\n";


        $pdf->MultiCell(340, 0, "お届け先：\n\n" . $fullAddress, '1', 'L', 0, 1);
        $height1 = $pdf->getY();
        

        
    	$logoPath = RESOURCE_PATH . DIRECTORY_SEPARATOR . 'goosca_logo.png';
    	$pdf->Image($logoPath, 400, 65, 0, 24, 'PNG', '', '', true, 300, '', false, false, 0);
    	
    	$pdf->MultiCell(0, 0, "グースカ 楽しいネット通販モール\n[URL] https://goosca.jp", '0', 'L', 0, 2, 380, $height0+20);

    	//$pdf->Image($data['logo_path'], 380, $height0, 0, 40, 'PNG', '', '', true, 300, '', false, false, 0);
    	
		$height2 = $pdf->getY();

		if ($height1 > $height2) {
			$pdf->setY($height1);
		} else {
			$pdf->setY($height2);
		}
		
    	$pdf->Cell(0, 20, '', '', 1, 'L', false);
    	
		$prefectureList    = Shared_Model_Code::codes('prefecture');
		$paymentMethodList = Shared_Model_Code::codes('payment_method');

    	$pdf->MultiCell(180, 0, "■ご注文番号　" . $data['order_no'], '0', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(180, 0, "■ご注文日　" . $data['order_date'], '0', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(180, 0, "■お支払い方法　" . $data['payment_method'], '0', 'L', false, 1, '', '', true, 1);
    	
    	//$pdf->MultiCell(150, 0, "■お届け方法　" . $data['delivery_method'], '0', 'L', false, 1, '', '', true, 1);

    	$fullAddress = '';
    	
    	$buyerName = '';
    	if (!empty($data['customer_id'])) {
    		$buyerName . $data['customer_id'] . '　';
    	}
    	$buyerName .= $data['order_customer_name'];
    	

    	$pdf->MultiCell(180, 0, "■ご注文者\n" . $buyerName . ' 様', '0', 'L', false, 0, '', '', true, 0);
		$pdf->MultiCell(180, 0, "■販売店舗名\n" . 'SP1900004' . '　' . 'フレスコ・ヘルスケア', '0', 'L', false, 1, '', '', true, 0);
		
		$pdf->SetFont('ipagp', '', 8);
		$pdf->Cell(0, 10, '', '', 1, 'L', false);

		$pdf->SetFont('ipagp', '', 7);
    	$pdf->SetFillColor(241, 241, 241);


		$pdf2 = clone $pdf;
		$pdf2->setCellPaddings(3,3,3,3);
		$pdf2->AddPage();
		$pdf2->SetFont('ipagp', '', 7);
	
		$cell1 = array(
			'L' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 1.3, 'color' => array(0, 0, 0)),
        	'R' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 1.3, 'color' => array(0, 0, 0)),
        	'B' => array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
		$cell1['T'] = array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$pdf->setCellPaddings(3,3,3,3);
		
    	$pdf->MultiCell(280, 0, "商品名/商品コード", $cell1, 'L', true, 0, '', '', true, 0);
    	$pdf->MultiCell(45, 0, "数量", $cell1, 'C', true, 0, '', '', true, 0);
    	$pdf->MultiCell(70, 0, "金額(税別)", $cell1, 'C', true, 0, '', '', true, 0);
    	$pdf->MultiCell(40, 0, "税率(%)", $cell1, 'C', true, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, "金額(税込)", $cell1, 'C', true, 1, '', '', true, 0);

		foreach ($data['product_items'] as $each) {
			$height0 = $pdf2->getY();
			$pdf2->MultiCell(280, 0, $each['product_name'] . "\n" . $each['product_code'], $cell1, 'L', false, 1, '', '', true, 0);
			$height1 = $pdf2->getY();
			$rowHeight = $height1 - $height0;
			
	    	$pdf->MultiCell(280, $rowHeight, $each['product_name'] . "\n" . $each['product_code'], $cell1, 'L', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(45, $rowHeight, $each['amount'], $cell1, 'C', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(70, $rowHeight, $helper->numberFormat($each['item_total']) , $cell1, 'R', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(40, $rowHeight, $helper->numberFormat($each['item_tax_rate']), $cell1, 'C', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(0, $rowHeight, $helper->numberFormat($each['item_total_with_tax']), $cell1, 'R', false, 1, '', '', true, 0);
            
		}

	    	
		$cell2 = array(
        	'B' => array('width' => 0.3, 'cap' => 'square', 'join' => 'miter', 'dash' => 1, 'color' => array(0, 0, 0)),
        );
		
		$pdf->SetFont('ipagp', '', 8);
		$pdf->Cell(0, 9, '', '', 1, 'L', false);
		$pdf->setCellPaddings(0,0,0,0);
		
		$pdf->MultiCell(310, 0, "", '0', 'R', false, 0, '', '', true, 0);
    	$pdf->MultiCell(110, 0, "送料(円・税込)", $cell2, 'C', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, $helper->numberFormat($data['delivery_fee']), $cell2, 'R', false, 1, '', '', true, 0);
    	
		$pdf->MultiCell(310, 0, "", '0', 'R', false, 0, '', '', true, 0);
    	$pdf->MultiCell(110, 0, "消費税(円)", $cell2, 'C', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, $helper->numberFormat($data['tax']), $cell2, 'R', false, 01, '', '', true, 0);	
		
    	$pdf->MultiCell(310, 0, "", '0', 'R', false, 0, '', '', true, 0);
    	$pdf->MultiCell(110, 0, "合計金額(円)", $cell2, 'C', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, $helper->numberFormat($data['total']), $cell2, 'R', false, 1, '', '', true, 0);

    	$pdf->MultiCell(310, 0, "", '0', 'R', false, 0, '', '', true, 0);
    	$pdf->MultiCell(110, 0, "クーポン利用額(円)", $cell2, 'C', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, $helper->numberFormat(0), $cell2, 'R', false, 1, '', '', true, 0);    	

    	$pdf->MultiCell(310, 0, "", '0', 'R', false, 0, '', '', true, 0);
    	$pdf->MultiCell(110, 0, "ポイント利用額(円)", $cell2, 'C', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, $helper->numberFormat(0), $cell2, 'R', false, 1, '', '', true, 0);
    	
    	$pdf->MultiCell(310, 0, "", '0', 'R', false, 0, '', '', true, 0);
    	$pdf->MultiCell(110, 0, "支払合計金額(円)", $cell2, 'C', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, $helper->numberFormat($data['total']), $cell2, 'R', false, 1, '', '', true, 0);
    	
    	/*
    	if (!empty($data['has_liquor'])) {
    		$pdf->SetFont('ipagp', '', 8);
    		$pdf->Cell(0, 9, '', '', 1, 'L', false);
            $pdf->MultiCell(0, 0, "法律により20歳未満の酒類の購入や飲酒は禁止されています。", '0', 'L', false, 1, '', '', true, 0);
    	}
    	*/
    	
		$pdf->SetFont('ipagp', '', 8);
		$pdf->MultiCell(0, 0, "", '0', 'L', false, 1, '', '', true, 0);
		$pdf->Cell(0, 9, '', '', 1, 'L', false);
		
		
		$pdf->setCellPaddings(3,3,3,3);
        $pdf->MultiCell(0, 0, "■店舗からのご案内", '0', 'L', false, 1, '', '', true, 0);
    	$pdf->MultiCell(450, 0, $data['template_2'], '1', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(10, 0, "", '0', 'C', false, 0, '', '', true, 0);
    	$pdf->SetFillColor(255, 255, 255);
    	
    	
    	$height1 = $pdf->getY();
    	$qrFile = self::createQRWithOrderId($data['order_no']);
    	$qrPath = Shared_Model_Resource_Temporary::getResourceObjectPath($qrFile);
    	$pdf->Image($qrPath, 490, $height1, 0, 60, 'PNG', '', '', true, 300, '', false, false, 0);
    	
    }
    
    
    
    
    /**
     * PDF明細書1件描画
     * @param $data[]
     */
    public static function makeHFCContent($pdf, $data, $helper)
    {
        $pdf->AddPage();
     	$pdf->SetTextColor(0,0,0);
		$pdf->SetDrawColor (0, 0, 0);
		$pdf->SetLineWidth(0.5);  //0.8は線の太さ
		$pdf->setCellPaddings(3,3,3,3);
		
        $pdf->SetFont('ipagp', 'U', 14);
        $pdf->Cell(0, 20, "　お買い上げ明細書　", 0, 2, 'C', false);
        
        $pdf->SetFont('ipagp', '', 8);
        $pdf->Cell(0, 3, "", '', 1, 'C', false);
        
		$height0 = $pdf->getY();
        $pdf->MultiCell(340, 0, $data['template_1'], '1', 'L', 0, 1);
        $height1 = $pdf->getY();
        
    	$pdf->MultiCell(0, 0, $data['shop_info'], '0', 'L', 0, 2, 380, $height0 + 40);

    	$pdf->Image($data['logo_path'], 380, $height0, 0, 40, 'PNG', '', '', true, 300, '', false, false, 0);
    	
		$height2 = $pdf->getY();

		if ($height1 > $height2) {
			$pdf->setY($height1);
		} else {
			$pdf->setY($height2);
		}
    	
    	$pdf->MultiCell(150, 0, "■ご注文番号　" . $data['order_no'], '0', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(130, 0, "■ご注文日　" . $data['order_date'], '0', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(150, 0, "■お支払い方法　" . $data['payment_method'], '0', 'L', false, 1, '', '', true, 1);
    	
    	$pdf->MultiCell(150, 0, "■お届け方法　" . $data['delivery_method'], '0', 'L', false, 1, '', '', true, 1);

    	$royal = '';
    	if (!empty($data['is_royal_customer'])) {
	    	$royal = '●';
    	}
    	$pdf->MultiCell(280, 0, "■お届け先\n〒" . $data['delivery_zipcode'] . "\n" . $data['delivery_full_address'], '0', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, "■ご依頼主\n" . $data['order_name'] . "様" . $royal, '0', 'L', false, 1, '', '', true, 0);
		
		$pos = $pdf->getY();
		$pdf->setY($pos + 5);
		$pdf->SetFont('ipagp', '', 10);
    	$pdf->MultiCell(280, 0, $data['delivery_name'] . "様", '0', 'L', false, 1, '', '', true, 0);
    	//$pdf->MultiCell(0, 0,  $data['order_name'] . "様", '0', 'L', false, 1, '', '', true, 0);
		
		$pdf->SetFont('ipagp', '', 8);
		$pdf->Cell(0, 0, '', '', 1, 'L', false);
		
        $pdf->SetFont('ipagp', '', 12);
        $pdf->Cell(0, 20, "お買い上げ明細", 0, 2, 'C', false);

		$pdf->SetFont('ipagp', '', 7);

    	$pdf->SetFillColor(241, 241, 241);


		$pdf2 = clone $pdf;
		$pdf2->setCellPaddings(3,3,3,3);
		$pdf2->AddPage();
		$pdf2->SetFont('ipagp', '', 7);
		
		$cell1 = array(
			'L' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 1.3, 'color' => array(0, 0, 0)),
        	'R' => array('width' => 0.1, 'cap' => 'square', 'join' => 'miter', 'dash' => 1.3, 'color' => array(0, 0, 0)),
        	'B' => array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)),
        );
        
		$cell1['T'] = array('width' => 0.5, 'cap' => 'square', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$pdf->setCellPaddings(3,3,3,3);
    	$pdf->MultiCell(280, 0, "商品名/商品コード", $cell1, 'L', true, 0, '', '', true, 0);
    	$pdf->MultiCell(45, 0, "数量", $cell1, 'C', true, 0, '', '', true, 0);
    	$pdf->MultiCell(70, 0, "金額(税別)", $cell1, 'C', true, 0, '', '', true, 0);
    	$pdf->MultiCell(40, 0, "税率(%)", $cell1, 'C', true, 0, '', '', true, 0);
    	$pdf->MultiCell(0, 0, "金額(税込)", $cell1, 'C', true, 1, '', '', true, 0);
		
		
		foreach ($data['product_items'] as $each) {
			$height0 = $pdf2->getY();
			$pdf2->MultiCell(280, 0, $each['product_name'] . "\n" . $each['product_code'], $cell1, 'L', false, 1, '', '', true, 0);
			$height1 = $pdf2->getY();
			$rowHeight = $height1 - $height0;

	    	$pdf->MultiCell(280, $rowHeight, $each['product_name'] . "\n" . $each['product_code'], $cell1, 'L', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(45, $rowHeight, $each['amount'], $cell1, 'C', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(70, $rowHeight, $helper->numberFormat($each['item_total']) , $cell1, 'R', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(40, $rowHeight, $helper->numberFormat($each['item_tax_rate']), $cell1, 'C', false, 0, '', '', true, 0);
	    	$pdf->MultiCell(0, $rowHeight, $helper->numberFormat($each['item_total_with_tax']), $cell1, 'R', false, 1, '', '', true, 0);
		}
		
		$pdf->Cell(0, 10, '', '', 1, 'L', false);



		$height0 = $pdf2->getY();
		$pdf2->MultiCell(290, 0, $data['template_2'], '1', 'L', false, 1, '', '', true, 0);
		$height1 = $pdf2->getY();
		$rowHeight = $height1 - $height0;
			
    	$pdf->MultiCell(290, 0, $data['template_2'], '1', 'L', false, 0, '', '', true, 0);
    	$pdf->MultiCell(10, 0, "", '0', 'C', false, 0, '', '', true, 0);
    	$pdf->SetFillColor(255, 255, 255);
    	
    	$height1 = $pdf->getY();
    	//var_dump($height1);
    	
    	$right_column = '';
    	
    	
    	$template3 = nl2br($data['template_3']);
    	
    	$right_column = <<< EOF
<style>
body {padding:0;}
table {padding:0; margin:0;width:100%;}
table tr td {border:0.5px solid #000;padding:3px;}
</style>

<table cellpadding="3">
	<tr>
		<td bgcolor="#f1f1f1" align="center">消費税</td>
		<td bgcolor="#f1f1f1" align="center">送料</td>
		<td bgcolor="#f1f1f1" align="center">手数料</td>
		<td bgcolor="#f1f1f1" align="center">値引額</td>
	</tr>
	<tr>
		<td align="center">{$data['tax']} 円</td>
		<td align="center">{$data['delivery_fee']} 円</td>
		<td align="center">{$data['charge']} 円</td>
		<td align="center">{$data['discount']} 円</td>
	</tr>
	<tr>
		<td colspan="4" align="right">お支払い合計金額&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$data['total']} 円</td>
	</tr>
</table>
EOF;

//	<tr>
//		<td colspan="4" align="left">{$template3}</td>
//	</tr>

		$pdf->setCellPaddings(0,0,0,0);
    	$pdf->writeHTMLCell(0, 0, '', '', $right_column, 0, 1, 1, true, '', true);
    	
    	$height2 = $pdf->getY();
    	//var_dump($height2);exit;
    	
    	$qrFile = self::createQRWithOrderId($data['order_no']);
    	$qrPath = Shared_Model_Resource_Temporary::getResourceObjectPath($qrFile);
    	$pdf->Image($qrPath, 330, $height2 + 5, 0, 60, 'PNG', '', '', true, 300, '', false, false, 0);
    	//$pdf->MultiCell(10, 0, "", '0', 'C', false, 0, '', '', true, 0);

		$pdf->SetFont('ipagp', '', 7);
		
		$height3 = $pdf->getY();
		
		//var_dump($height3);
		//var_dump($height1 + $rowHeight);exit;
		
		//$data['payment_method']
		
		if (!empty($data['jaccs_with_package'])) {
			$pdf->MultiCell(0, 0, "お支払い用紙を同封しております。\n内容をご確認の上、コンビニまたは金融機関で\n期限日までにお支払いをお願いいたします。", '', 'L', true, 1, '', $height1 + $rowHeight + 5, true, 0);
		} else {
			
		}

/*
if (strtotime(date('Y-m-d')) < strtotime('2020-04-16 00:00:00')) {


		$important = <<< EOF
★★★重要連絡事項★★★
4/16のフレスコ株式会社本社の東京移転に伴い、お問い合せ商品の発送元住所およびお問い合わせ先電話番号が変更となります。

◎発送元住所：　〒111-0051 東京都台東区蔵前１-７-７

◎フレスコ・ヘルスケアお問合せ窓口：　050-5370-4160

※公式販売サイトおよびお問合せメールアドレスに変更はありません。	
EOF;

} else {
	
		$important = <<< EOF
★★★重要連絡事項★★★
【１．フレスコヘルスケアのお問い合わせ連絡先・発送元住所の変更】
4/16のフレスコ株式会社本社の東京移転に伴い、商品の発送元住所・お問い合わせ先電話番号が変更となります。

◎発送元住所：　〒111-0051 東京都台東区蔵前１-７-７

◎フレスコ・ヘルスケアお問合せ窓口：　050-5370-4160 
（訂正：商品記載のフレスコヘルスケアの連絡先も同じく変更となります）

※公式販売サイトおよびお問合せメールアドレスに変更はありません。

【２．フレスコ株式会社の商品記載の住所の変更】
上記１と同様に、商品記載のフレスコ株式会社の住所も東京都へ変更となります。
商品のパッケージ裏面の記載につきましては、商品またはパッケージの在庫がなくなり次第、順次差し替えとなるため、「製造者・加工者名・販売元名」の欄が旧住所（名古屋市丸の内）となっている場合がございますこと、ご了承願います。以下の通り、訂正をご連絡いたします。

＜○正・新＞フレスコ株式会社　〒111-0051 東京都台東区蔵前１-７-７
　←＜×誤・旧＞フレスコ株式会社　〒453-0002名古屋市中区丸の内1ー１３－１１
EOF;


}
*/
        
		//$pdf->setCellPaddings(3,3,3,3);
        //$pdf->MultiCell(0, 0, $important, '1', 'L', 0, 1, '', $height1 + 70);  
    }


}
