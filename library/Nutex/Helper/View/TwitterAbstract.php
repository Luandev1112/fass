<?php
/**
 * class Nutex_Helper_View_TwitterAbstract
 *
 * twitterの公式ウィジェットの表示共通クラス
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_TwitterAbstract extends Nutex_Helper_View_Abstract
{
    /**
     * widgetの幅を設定する
     * @param int $width
     */
    public function setWidth($width) {
        $this->_width = $width;
    }

    /**
     * widgetの高さを設定する
     * @param int $height
     */
    public function setHeight($height) {
        $this->_height = $height;
    }

    /**
     * スクロールバーを表示するかどうか
     * @param boolean $isScroll
     */
    public function setScrollBar($isScroll) {
        $this->_isScrollBar = $isScroll;
    }

    /**
     * 新しいツイートを取得するか
     * @param int $isLive
     */
    public function setLive($isLive) {
        $this->_isLive = $isLive;
    }

    /**
     * ループさせるかどうか
     * @param int $isLoop
     */
    public function setLoop($isLoop) {
        $this->_isLoop = $isLoop;
    }

    /**
     * ウィジェットの振る舞いの設定
     * @param string $behavior all or default
     */
    public function setBehavior($behavior) {
        if (!($behavior == 'all' || $behavior == 'default')) {
            throw new Zend_Exception('behavior is not all or default');
        }
        $this->_behavior = $behavior;
    }

    /**
     * ツイートの表示間隔の設定
     * @param int $tweetInterval マイクロ秒
     */
    public function setTweetInterval($tweetInterval) {
        $this->_tweetInterval = $tweetInterval;
    }

    /**
     * ウィジェットのシェルのバックグランドカラーを指定する
     * @param string $color #FFFFFFとか
     */
    public function setShellBackgroundColor($color) {
        $this->_shellBackgroundColor = $color;
    }

    /**
     * ウィジェットのシェルのテキストカラーを指定する
     * @param string $color #FFFFFFとか
     */
    public function setShellTextColor($color) {
        $this->_shellTextColor = $color;
    }

    /**
     * ツイートのバックグラウンドカラーを指定する
     * @param string $color #FFFFFFとか
     */
    public function setTweetBackgroundColor($color) {
        $this->_tweetBackgroundColor = $color;
    }

    /**
     * ツイートのテキストカラーを指定する
     * @param string $color #FFFFFFとか
     */
    public function setTweetTextColor($color) {
        $this->_tweetTextColor = $color;
    }

    /**
     * ツイートのリンクカラーを指定する
     * @param string $color #FFFFFFとか
     */
    public function setTweetLinkColor($color) {
        $this->_tweetLinkColor = $color;
    }
}
