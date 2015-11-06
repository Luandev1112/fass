<?php
/**
 * class Nutex_Request
 *
 * Zend_Controller_Request_Http拡張
 *
 * @package Nutex
 * @subpackage Nutex_Request
 */
class Nutex_Request extends Zend_Controller_Request_Http
{
    /**
     * @var string
     */
    const PARAM_EXECUTION = '__execute';
    const PARAM_BACK = '__back';

    /**
     * Constructor
     *
     * If a $uri is passed, the object will attempt to populate itself using
     * that information.
     *
     * @param string|Zend_Uri $uri
     * @return void
     * @throws Zend_Controller_Request_Exception when invalid URI passed
     */
    public function __construct($uri = null)
    {
        parent::__construct($uri);

        if ($this->isPut()) {
            $this->_handlePutParams();
        }
    }

    /**
     * 実行用フラグがリクエストに含まれるかどうか調べる
     *
     * @param void
     * @return boolean
     */
    public function isExecution()
    {
        return $this->_specialParamExists(self::PARAM_EXECUTION);
    }

    /**
     * 戻る用フラグがリクエストに含まれるかどうか調べる
     *
     * @param void
     * @return boolean
     */
    public function isBack()
    {
        return $this->_specialParamExists(self::PARAM_BACK);
    }

    /**
     * 特別なパラメータがリクエストに含まれるかどうか調べる
     *
     * @param string $name
     * @return boolean
     */
    protected function _specialParamExists($name)
    {
        $method = 'getParam';
        if ($this->isPost()) {
            $method = 'getPost';
        }

        if ($this->$method($name) === $name) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * _handlePutParams
     * @param void
     * @return void
     */
    protected function _handlePutParams()
    {
        if (strpos($this->getHeader('Content-Type'), 'multipart/form-data') === 0) {
            $this->_handleMultipart();
        } else {
            $params = array();
            parse_str($this->getRawBody(), $params);
            $this->setParams($params);
        }
    }

    /**
     * multipart/form-dataのbody部をハンドリングする
     * @param void
     * @return void
     */
    protected function _handleMultipart()
    {
        $eol = "\r\n";
        $boundary = substr($this->getRawBody(), 0, strpos($this->getRawBody(), $eol));

        $params = array();
        foreach (array_slice(explode($boundary, $this->getRawBody()), 1) as $part) {
            // If this is the last part, break
            if ($part == '--' . $eol) {
                break;
            }

            // Separate content from headers
            $part = ltrim($part, $eol);
            list($rawHeaders, $body) = explode($eol . $eol, $part, 2);

            // Parse the headers list
            $rawHeaders = explode($eol, $rawHeaders);
            $headers = array();
            foreach ($rawHeaders as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $parsed = $this->_parseContentDisposition($headers['content-disposition']);

                $body = substr($body, 0, strlen($body) - 2);
                if ($parsed['name']) {
                    if (isset($headers['content-type']) && $parsed['filename']) {
                        $this->_handleUploadedFile($parsed['name'], $parsed['filename'], $headers['content-type'], $body);
                    } else {
                        $params[$parsed['name']] = $body;
                    }
                }
            }
        }

        $this->setParams($params);
    }

    /**
     * multipart/form-dataに含まれるContent-Dispositionをパースする
     * @param string $contentDisposition
     * @return array $parsed
     */
    protected function _parseContentDisposition($contentDisposition)
    {
        $parsed = array(
            'type' => null,
            'name' => null,
            'filename' => null,
        );

        if (preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $contentDisposition, $matches)) {
            $parsed['type'] = (isset($matches[1])) ? $matches[1] : null;
            $parsed['name'] = (isset($matches[2])) ? $matches[2] : null;
            $parsed['filename'] = (isset($matches[4])) ? $matches[4] : null;
        }

        return $parsed;
    }

    /**
     * アップロードされたファイルをハンドリングする
     *
     *  ※サポートしている $_FILES[name]['error']
     *  UPLOAD_ERR_OK
     *  UPLOAD_ERR_CANT_WRITE
     *
     *  ※サポートしていない $_FILES[name]['error']
     *  UPLOAD_ERR_INI_SIZE
     *  UPLOAD_ERR_FORM_SIZE
     *  UPLOAD_ERR_PARTIAL
     *  UPLOAD_ERR_NO_FILE
     *  UPLOAD_ERR_NO_TMP_DIR
     *  UPLOAD_ERR_EXTENSION
     *
     *  ※配列形式のアップロードも未サポート
     *
     * @param string $name
     * @param string $fileName
     * @param string $contentType
     * @param string $body
     * @return boolean $result
     */
    protected function _handleUploadedFile($name, $fileName, $contentType, &$body)
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'PUT');
        if (file_put_contents($tmpPath, $body)) {
            $error = UPLOAD_ERR_OK;
            $result = true;
        } else {
            $tmpPath = null;
            $error = UPLOAD_ERR_CANT_WRITE;
            $result = false;
        }

        $_FILES[$name] = array(
            'name' => $fileName,
            'type' => $contentType,
            'tmp_name' => $tmpPath,
            'error' => $error,
            'size' => strlen($body),
        );

        return $result;
    }
}