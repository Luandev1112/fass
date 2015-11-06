<?php
/**
 * class Nutex_Helper_Action_BinaryOutput
 *
 * バイナリ出力ヘルパー
 *
 * @package Nutex
 * @subpackage Nutex_Helper_Action
 */
class Nutex_Helper_Action_BinaryOutput extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * option keys
     * @var string
     */
    const OPT_BINARY = 'binary';
    const OPT_CONTENT_TYPE = 'contentType';
    const OPT_CONTENT_DISPOSITION = 'contentDisposition';
    const OPT_FILENAME = 'filename';

    /**
     * 出力するバイナリをプールしておく場所
     * @var string
     */
    protected $_binaryPool = null;

    /**
     * 拡張子とmime-typeの対応
     * @var array
     */
    protected $_mimeTypesByExt = array(

        '.txt' => 'text/plain',
        '.htm' => 'text/html',
        '.html' => 'text/html',
        '.css' => 'text/css',
        '.js' => 'text/javascript',
        '.csv' => 'text/csv',
        '.tsv' => 'text/tab-separated-values',
        '.hdml' => 'text/x-hdml',

        '.doc' => 'application/msword',
        '.xls' => 'application/vnd.ms-excel',
        '.xlsx' => 'application/vnd.ms-excel',
        '.ppt' => 'application/vnd.ms-powerpoint',
        '.xdw' => 'application/vnd.fujixerox.docuworks',

        '.gif' => 'image/gif',
        '.jpg' => 'image/jpg',
        '.jpeg' => 'image/jpg',
        '.png' => 'image/png',
        '.bmp' => 'image/bmp',

        '.mp3' => 'audio/mpeg',
        '.m4a' => 'audio/mp4',
        '.mp4' => 'audio/mp4',
        '.wav' => 'audio/x-wav',
        '.mid' => 'audio/midi',
        '.midi' => 'audio/midi',
        '.mmf' => 'application/x-smaf',

        '.mpg' => 'video/mpeg',
        '.mpeg' => 'video/mpeg',
        '.wmv' => 'video/x-ms-wmv',
        '.avi' => 'video/avi',
        '.3g2' => 'video/3gpp2',

        '.zip' => 'application/zip',
        '.lha' => 'application/x-lzh',
        '.tar' => 'application/x-tar',
        '.tgz' => 'application/x-tar',
        '.tar.gz' => 'application/x-tar',

        '.apk' => 'application/vnd.android.package-archive',
        '.pdf' => 'application/pdf',
        '.swf' => 'application/x-shockwave-flash',
        '.ai' => 'application/postscript',
        '.psd' => 'application/postscript',

    );

    /**
     * inlineで出力した方がよさげなmime-type
     * ワイルドカードが使えます
     *
     * @var array
     */
    protected $_inlineMimeTypes = array(
        'text/html',
        'image/*',
        'audio/*',
        'video/*',
        'application/x-shockwave-flash',
    );

    /**
     * direct
     *
     * @param string $content
     * @param array $options
     * @return void
     */
    public function direct($content, array $options = array())
    {
        $this->_binaryPool = null;
        if (array_key_exists(self::OPT_BINARY, $options) && $options[self::OPT_BINARY]) {
            $this->_binaryPool = $content;
            $this->output(null, $options);
        } else {
            $this->output($content, $options);
        }
    }

    /**
     * output
     *
     * @param string $content
     * @param array $options
     * @return void
     */
    public function output($content = null, array $options = array())
    {
        //binaryオプションが指定されなければ$contentをファイルパスと判断する
        if ($content && (!array_key_exists(self::OPT_BINARY, $options) || !$options[self::OPT_BINARY])) {
            if (is_readable($content)) {
                $this->_binaryPool = file_get_contents($content);

                //ファイル名が指定されてなかったら、読み込んだパスのファイル名をセット
                if (!array_key_exists(self::OPT_FILENAME, $options)) {
                    $options[self::OPT_FILENAME] = basename($content);
                }
            } else {
                throw new Nutex_Exception_Error("cound not open file $content");
            }
        }

        //viewを無効にする
        $this->getActionController()->getHelper('disableView')->direct();

        //各種ヘッダをセットし、bodyにバイナリをセット
        $this->_setHeaders($options);
        $this->getResponse()->setBody($this->_binaryPool);
        unset($this->_binaryPool);//念のためメモリ開放
    }

    /**
     * _setHeaders
     *
     * @param array $options
     * @return void
     */
    protected function _setHeaders(array $options = array())
    {
        $response = $this->getResponse();
        $headers = array();
        $filename = (array_key_exists(self::OPT_FILENAME, $options)) ? $options[self::OPT_FILENAME] : null;

        //Content-Type判定
        $value = 'application/octet-stream';
        if (array_key_exists(self::OPT_CONTENT_TYPE, $options)) {
            $value = $options[self::OPT_CONTENT_TYPE];
        } elseif ($filename) {
            //ファイル名が与えられたら、拡張子から判断してみる
            $ext = strtolower(preg_replace('/^[^\.]+/', '', $filename));
            if ($ext && array_key_exists($ext, $this->_mimeTypesByExt)) {
                $value = $this->_mimeTypesByExt[$ext];
            }
        }
        $headers['Content-Type'] = $value;

        //Content-Disposition判定
        $value = 'attachment';
        if (array_key_exists(self::OPT_CONTENT_DISPOSITION, $options)) {
            if (is_string($options[self::OPT_CONTENT_DISPOSITION])) {
                $value = $options[self::OPT_CONTENT_DISPOSITION];
            }
        } else {
            //inlineの方がよさげなmime-typeかどうか調べてみる
            $parts = explode('/', $headers['Content-Type']);
            foreach ($this->_inlineMimeTypes as $type) {
                $compareParts = explode('/', $type);
                if (($compareParts[0] == '*' || $parts[0] == $compareParts[0]) && ($compareParts[1] == '*' || $parts[1] == $compareParts[1])) {
                    $value = 'inline';
                    break;
                }
            }
        }

        if ($filename) {
            /*
             * @todo マルチバイトファイル名のサポートをするか？
             */
            $value .= '; filename=' . $filename;
        }
        $headers['Content-Disposition'] = $value;

        //サイズ計測
        $headers['Content-Length'] = strlen($this->_binaryPool);

        //キャッシュ制御 - IE + SSL対策
        if (strpos($headers['Content-Disposition'], 'attachment') === 0 && $this->getRequest()->isSecure()) {
            $headers['Cache-Control'] = 'private';
            $headers['Pragma'] = 'private';
        }

        //ヘッダにセット
        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value, true);
        }
    }
}