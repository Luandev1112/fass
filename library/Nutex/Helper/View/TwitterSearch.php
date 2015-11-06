<?php
/**
 * class Nutex_Helper_View_TwitterProfile
 *
 * twitterの公式サーチウィジェットの表示
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_TwitterSearch extends Nutex_Helper_View_TwitterAbstract
{
    /**
     * ウィジェットの幅
     * @var int
     */
    protected $_width = 250;

    /**
     * ウィジェットの高さ
     * @var int
     */
    protected $_height = 300;

    /**
     * tweetsの表示数
     * @var int
     */
    protected $_numberOfTweets = 4;

    /**
     * スクロールバーを表示するかどうか
     * @var boolean
     */
    protected $_isScrollBar = FALSE;

    /**
     * 新しいついーとを取得するか
     * @var boolean
     */
    protected $_isLive = TRUE;

    /**
     * ループさせるかどうか
     * @var boolean
     */
    protected $_isLoop = TRUE;

    /**
     * ウィジェットの振る舞いの設定 all or default
     * @var string
     */
    protected $_behavior = 'default';

    /**
     * ツイートの取得間隔設定 behaviorがdefaultの時のみ有効
     * マイクロ秒
     * @var int
     */
    protected $_tweetInterval = 30000; // 3秒

    /**
     * shell background color
     * @var string
     */
    protected $_shellBackgroundColor = '#8ec1da';

    /**
     * shell text color
     * @var string
     */
    protected $_shellTextColor = '#ffffff';

    /**
     * tweet background color
     * @var string
     */
    protected $_tweetBackgroundColor = '#ffffff';

    /**
     * tweet text color
     * @var string
     */
    protected $_tweetTextColor = '#444444';

    /**
     * tweet link color
     * @var string
     */
    protected $_tweetLinkColor = '#1985b5';

    /**
     * twitterの公式profileウィジェットの表示
     * @param string $twitterName
     */
    public function twitterSearch($serachWord, $title, $subject) {
        $result = NULL;
        if($serachWord && $title && $subject) {
            $result = $this->_getWidgetScript($serachWord, $title, $subject);
        }
        return $result;
    }

    /**
     * 公式twitterプロフィールウィジェットスクリプト作成
     * @param String $twitterName
     */
    protected function _getWidgetScript($serachWord, $title, $subject) {
        $isScrollBar = ($this->_isScrollBar) ? 'true' : 'false';
        $isLive      = ($this->_isLive)      ? 'true' : 'false';
        $isLoop      = ($this->_isLoop)      ? 'true' : 'false';
        return <<<EOS
<script src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
    version: 2,
    type: 'search',
    search: '{$serachWord}',
    title: '{$title}',
    subject: '{$subject}',
    interval: {$this->_tweetInterval},
    width: {$this->_width},
    height: {$this->_height},
    theme: {
        shell: {
          background: '{$this->_shellBackgroundColor}',
          color: '{$this->_shellTextColor}'
        },
        tweets: {
          background: '{$this->_tweetBackgroundColor}',
          color: '{$this->_tweetTextColor}',
          links: '{$this->_tweetLinkColor}'
        }
    },
    features: {
        scrollbar: {$isScrollBar},
        loop: {$isLoop},
        live: {$isLive},
        behavior: '{$this->_behavior}'
    }
}).render().start();
</script>
EOS;
    }
}
