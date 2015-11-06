<?php
/**
 * class Nutex_Helper_View_TwitterProfile
 *
 * twitterの公式profileウィジェットの表示
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_TwitterProfile extends Nutex_Helper_View_TwitterAbstract
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
    protected $_isLive = FALSE;

    /**
     * ループさせるかどうか
     * @var boolean
     */
    protected $_isLoop = FALSE;

    /**
     * ウィジェットの振る舞いの設定 all or default
     * @var string
     */
    protected $_behavior = 'all';

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
    protected $_shellBackgroundColor = '#333333';

    /**
     * shell text color
     * @var string
     */
    protected $_shellTextColor = '#ffffff';

    /**
     * tweet background color
     * @var string
     */
    protected $_tweetBackgroundColor = '#000000';

    /**
     * tweet text color
     * @var string
     */
    protected $_tweetTextColor = '#ffffff';

    /**
     * tweet link color
     * @var string
     */
    protected $_tweetLinkColor = '#4aed05';

    /**
     * twitterの公式profileウィジェットの表示
     * @param string $twitterName
     */
    public function twitterProfile($twitterName) {
        $result = NULL;
        if($twitterName) {
            $result = $this->_getWidgetScript($twitterName);
        }
        return $result;
    }

    /**
     * 公式twitterプロフィールウィジェットスクリプト作成
     * @param String $twitterName
     */
    protected function _getWidgetScript($twitterName) {
        $isScrollBar = ($this->_isScrollBar) ? 'true' : 'false';
        $isLive      = ($this->_isLive)      ? 'true' : 'false';
        $isLoop      = ($this->_isLoop)      ? 'true' : 'false';
        return <<<EOS
<script src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
    version: 2,
    type: 'profile',
    rpp: {$this->_numberOfTweets},
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
}).render().setUser('{$twitterName}').start();
</script>
EOS;
    }
}
