<?php
/**
 * class Nutex_Util_ResizeImage
 *
 * 画像リサイズユーティリティ
 * @see GD http://php.net/manual/ja/book.image.php
 *
 * @package Nutex
 * @subpackage Nutex_Util
 */
class Nutex_Util_ResizeImage
{
    const TRIM_START_CENTER = -1;

    /**
     * 伸縮ありでリサイズする
     * @param string $data
     * @param string $dstPath
     * @param int $width
     * @param int $height
     * @param boolean
     */
    public static function elastic($data, $dstPath, $width, $height)
    {
        $image = imagecreatefromstring($data);
        $dstImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($dstImage, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        $result = self::saveImage($dstImage, $dstPath);
        imagedestroy($image);
        imagedestroy($dstImage);
        return $result;
    }

    /**
     * リサイズ＋トリムを合わせて伸縮せずにリサイズする
     * @param string $data
     * @param string $dstPath
     * @param int $width
     * @param int $height
     * @param int $trimStartX
     * @param int $trimStartY
     * @param boolean
     */
    public static function notElastic($data, $dstPath, $width, $height, $trimStartX = self::TRIM_START_CENTER, $trimStartY = self::TRIM_START_CENTER)
    {
        $image = imagecreatefromstring($data);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        //縦横どちらかをリサイズ後のサイズにあわせてリサイズ
        if ($width / $imageWidth > $height / $imageHeight) {
            $resizedWidth = $width;
            $resizedHeight = (int) ($imageHeight * ($width / $imageWidth));
        } else {
            $resizedWidth = (int) ($imageWidth * ($height / $imageHeight));
            $resizedHeight = $height;
        }

        //リサイズ
        $resizedImage = imagecreatetruecolor($resizedWidth, $resizedHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $imageWidth, $imageHeight);
        imagedestroy($image);
        $image = $resizedImage;
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        //中央を切り出す場合
        if ($trimStartX === self::TRIM_START_CENTER) {
            $trimStartX = (int) (($imageWidth - $width) / 2);
        }
        if ($trimStartY === self::TRIM_START_CENTER) {
            $trimStartY = (int) (($imageHeight - $height) / 2);
        }

        //トリムして書き出し
        $dstImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($dstImage, $image, 0, 0, $trimStartX, $trimStartY, $width, $height, $width, $height);
        $result = self::saveImage($dstImage, $dstPath);
        imagedestroy($image);
        imagedestroy($dstImage);
        return $result;
    }

    /**
     * saveImage
     * @param resource $image
     * @param string $path
     * @param float $rotate
     * @param int $rotateColor
     * @return boolean
     */
    public static function saveImage($image, $path, $rotate = null, $rotateColor = 0)
    {
        $ext = preg_replace('/^[^\.]\./', '', $path);
        $result = false;

        if ($rotate !== null) {
            $image = imagerotate($image, $rotate, $rotateColor);
        }
        switch (strtolower($ext)) {

            case 'jpeg':
            case 'jpg':
                $result = imagejpeg($image, $path, 50);
                break;

            case 'gif':
                $result = imagegif($image, $path);
                break;

            case 'png':
            default:
                $result = imagepng($image, $path);
                break;

        }

        return $result;
    }
}
