<?php
/**
 * class Shared_Model_Utility_Image
 *
 * イメージユーティリティ
 *
 * @package Shared
 * @subpackage Shared_Model
 */
class Shared_Model_Utility_Image
{

    const EXTENSON_TYPE_PNG  = 'png'; 
    const EXTENSON_TYPE_JPEG = 'jpeg';
    
   	/**
	 * makePngImageWithWidth
	 *
	 * 横幅を$widthに合わせてpngイメージを作成
	 */
    public static function makePngImageWithWidth($resourceFilePath, $width, $fileNameWithExt)
    {
	    $lotated = false;
	    
	    if (self::isJpeg($resourceFilePath)) {
		    $lotated = true;
		    $orientationTempPath = Shared_Model_Resource_TemporaryPrivate::getResourceObjectPath('fix_' . $fileNameWithExt);
	        self::orientationFixedImage($orientationTempPath, $resourceFilePath); 
	    } else {
		    $orientationTempPath = $resourceFilePath;
	    }
	    
        $img = self::imageCreateFromAny($orientationTempPath);
        
        $baseWidth = ImageSx($img);
        $baseHeight = ImageSy($img);
        
        if ($baseWidth > $width) {
	        // 指定サイズを超える場合は縮小
        	$out = ImageCreateTrueColor($width, $baseHeight / $baseWidth * $width);
        	$white = imagecolorallocate($out, 255, 255, 255);
        	imagefilledrectangle($out, 0, 0, $width, floor($baseHeight / $baseWidth * $width), $white);
			imagecopyresampled($out, $img, 0,0,0,0, $width, floor($baseHeight / $baseWidth * $width), $baseWidth, $baseHeight);
        } else {
	        // 指定サイズ未満はそのまま保存
        	$out = ImageCreateTrueColor($baseWidth, $baseHeight);
        	$white = imagecolorallocate($out, 255, 255, 255);
        	imagefilledrectangle($out, 0, 0, $baseWidth, $baseHeight, $white);
			imagecopy($out, $img, 0,0,0,0, $baseWidth, $baseHeight);
        }
        
        $filePath = Shared_Model_Resource_Temporary::getResourceObjectPath($fileNameWithExt);

        imagepng($out, $filePath);
        
        
        if ($lotated === true) {
	        Shared_Model_Resource_TemporaryPrivate::removeResource('fix_' . $fileNameWithExt);
        }

        return $filePath;
    }
    
   	/**
	 * makeSquareImageWithWidth
	 *
	 * 縦幅・横幅を$sizeに合わせて正方形のイメージを作成
	 */
    public static function makeSquareImageWithWidth($resourceFilePath, $size, $fileName, $ext, $quality = 100)
    {
        $ImageResource = self::imageCreateFromAny($resourceFilePath);
        
        $width = ImageSx($ImageResource);
        $height = ImageSy($ImageResource);
        
        if ($width >= $height) {
		    // 横長
		    $side = $height;
		    $x = floor(($width - $height) / 2);
		    $y = 0;
		    $width = $side;
		} else {
		    // 縦長
		    $side = $width;
		    $y = floor(($height - $width) / 2);
		    $x = 0;
		    $height = $side;
		}

		$square_new = imagecreatetruecolor($size, $size);
		imagecopyresized($square_new, $ImageResource, 0, 0, $x, $y, $size, $size, $width, $height);
		
		$filePath = Shared_Model_Resource_Temporary::getResourceObjectPath($fileName . '.' . $ext);
		
		if ($ext == self::EXTENSON_TYPE_JPEG) {
			imagejpeg($square_new, $filePath, $quality);
        } else {
        	ImagePNG($square_new, $filePath);
        }
        return $filePath;
    }
    
    
	/**
	 * triimImageFromTop
	 *
	 * 画像の上からheight(pixel)で切り取る
	 */
    public static function triimImageFromTop($resourceFilePath, $height, $fileNameWithExt)
    {
        $img = self::imageCreateFromAny($resourceFilePath);
        
        $baseWidth = ImageSx($img);
        // $out = imagecrop($img, array('x' => 0, 'y' => 0, 'width' => $baseWidth, 'height' => $height)); // PHP5.5以上
        $out = self::imageCropping($img, array('x' => 0, 'y' => 0, 'width' => $baseWidth, 'height' => $height));
        
        $filePath = Shared_Model_Resource_Temporary::getResourceObjectPath($fileNameWithExt);
        ImagePNG($out, $filePath);
        
        return $filePath;
    }  
    
    

    // Private
    
    
    
    static function imageCropping($src, array $rect)
    {
        $dest = imagecreatetruecolor($rect['width'], $rect['height']);
        imagecopy(
            $dest,
            $src,
            0,
            0,
            $rect['x'],
            $rect['y'],
            $rect['width'],
            $rect['height']
        );
    
        return $dest;
    }

    static function isJpeg($filepath)
    {
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );
        
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        if ($type === 2) { 
                return true;
        }   
        return false; 
    }
        
    static function imageCreateFromAny($filepath)
    {
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
        $allowedTypes = array(
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6   // [] bmp
        );
        if (!in_array($type, $allowedTypes)) {
            return false;
        }
        switch ($type) {
            case 1 :
                $im = imageCreateFromGif($filepath);
            break;
            case 2 :
                $im = imageCreateFromJpeg($filepath);
            break;
            case 3 :
                $im = imageCreateFromPng($filepath);
            break;
            case 6 :
                $im = imageCreateFromBmp($filepath);
            break;
        }   
        return $im; 
    }
    
   	/**
	 * orientationFixedImage
	 *
	 * スマホなどの画像向きを修正
	 */
	static function orientationFixedImage($output, $input)
	{
	    
	    $image = self::imageCreateFromAny($input);
	    $exif = @exif_read_data($input);
	    
	    if(isset($exif['Orientation'])){
	        $orientation = $exif['Orientation'];

			if ($image) {
                // 未定義
                if ($orientation == 0) {
                
                // 通常
                } else if ($orientation == 1) {
                // 左右反転
                } else if ($orientation == 2) {
                    $image = self::imageFlop($image);
                        
                // 180°回転
                } else if ($orientation == 3) {
                    $image = self::imageRotate($image,180, 0);
                        
                // 上下反転
                } else if ($orientation == 4) {
                	$image = imageFlip($image);
                        
                // 反時計回りに90°回転 上下反転
                } else if ($orientation == 5) {
                    $image = self::imageRotate($image,90, 0);
                    $image = self::imageFlip($image);
                        
                // 時計回りに90°回転
                } else if ($orientation == 6) {
                    $image = self::imageRotate($image,270, 0);
                        
                // 時計回りに90°回転 上下反転
                } else if ($orientation == 7) {
                    $image = self::imageRotate($image,270, 0);
                    $image = self::imageFlip($image);
                        
                // 反時計回りに90°回転
                } else if ($orientation == 8) {
                    $image = self::imageRotate($image,90, 0);
                        
                }
	        }
	    }
	    // 画像の書き出し
	    ImageJPEG($image ,$output);
	    return false;
	}


	// 画像の左右反転
	static function imageFlop($image) 
	{
	    // 画像の幅を取得
	    $w = imagesx($image);
	    // 画像の高さを取得
	    $h = imagesy($image);
	    // 変換後の画像の生成（元の画像と同じサイズ）
	    $destImage = @imagecreatetruecolor($w,$h);
	    // 逆側から色を取得
	    for($i=($w-1);$i>=0;$i--){
	        for($j=0;$j<$h;$j++){
	            $color_index = imagecolorat($image,$i,$j);
	            $colors = imagecolorsforindex($image,$color_index);
	            imagesetpixel($destImage,abs($i-$w+1),$j,imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
	        }
	    }
	    return $destImage;
	}
	
	// 上下反転
	static function imageFlip($image)
	{
	    // 画像の幅を取得
	    $w = imagesx($image);
	    // 画像の高さを取得
	    $h = imagesy($image);
	    // 変換後の画像の生成（元の画像と同じサイズ）
	    $destImage = @imagecreatetruecolor($w,$h);
	    // 逆側から色を取得
	    for($i=0;$i<$w;$i++){
	        for($j=($h-1);$j>=0;$j--){
	            $color_index = imagecolorat($image,$i,$j);
	            $colors = imagecolorsforindex($image,$color_index);
	            imagesetpixel($destImage,$i,abs($j-$h+1),imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
	        }
	    }
	    return $destImage;
	}
	
	// 画像を回転
	static function imageRotate($image, $angle, $bgd_color)
	{
	     return imagerotate($image, $angle, $bgd_color, 0);
	}
    
}