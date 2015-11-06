<?php
/**
 * class Nutex_Session_Cookie
 *
 * クッキーを利用したセッション
 *
 * @package Nutex
 * @subpackage Nutex_Session
 */
class Nutex_Session_Cookie extends Nutex_Session_Abstract
{
    /**
     * inRequest
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param array $options
     * @return boolean
     */
    public static function inRequest(Zend_Controller_Request_Abstract $request, array $options = array())
    {
        $name = (isset($options[self::OPTION_ID_NAME])) ? $options[self::OPTION_ID_NAME] : self::DEFAULT_ID_NAME;
        if (method_exists($request, 'getCookie') && $request->getCookie($name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * __construct
     * type hintingのみオーバーライドしています
     *
     * @param Nutex_Session_StorageAdapter_Abstract $dataAdapter
     * @param Zend_Controller_Request_Http $request
     * @param Zend_Controller_Response_Http $response
     * @return void
     */
    public function __construct(
        Nutex_Session_StorageAdapter_Abstract $dataAdapter,
        Zend_Controller_Request_Http $request,
        Zend_Controller_Response_Http $response,
        array $options = array()
    )
    {
        parent::__construct($dataAdapter, $request, $response, $options);
    }

    /**
     * sustain
     * セッションを維持するために必要なアクションを継承先で記述
     *
     * @param void
     * @return void
     */
    public function sustain()
    {
        //クッキーにセッションIDをセット
        $this->setCookie();
    }

    /**
     * destroy
     *
     * @param void
     * @return void
     */
    public function destroy()
    {
        parent::destroy();
        $this->removeCookie();
    }

    /**
     * getIdFromRequest
     *
     * @param void
     */
    public function getIdFromRequest()
    {
        return $this->getRequest()->getCookie($this->getOption(self::OPTION_ID_NAME, self::DEFAULT_ID_NAME));
    }

    /**
     * publishNewId
     *
     * @param void
     * @return string
     */
    public function publishNewId()
    {
        $base = $this->getOption(self::OPTION_HASH_SALT, self::DEFAULT_HASH_SALT) . mt_rand() . $this->getRequest()->getServer('HTTP_USER_AGENT') . mt_rand() . uniqid(null, true);
        return $this->getConverter()->convert(hash($this->getOption(self::OPTION_HASH_FUNCTION, self::DEFAULT_HASH_FUNCTION), $base));
    }

    /**
     * removeCookie
     *
     * @param void
     * @return string
     */
    public function removeCookie()
    {
        $name = $this->getOption(self::OPTION_ID_NAME, self::DEFAULT_ID_NAME);
        $value = '';
        $expires = Nutex_Date::getReplaced(0, Zend_Date::COOKIE);
        $header = rawurldecode($name) . '=' . rawurldecode($value) . '; expires=' . $expires .'; path=/;' . (($secure) ? ' secure' : '');
        $this->getResponse()->setHeader('Set-Cookie', $header);
    }

    /**
     * setCookie
     *
     * @param boolean $secure
     * @return string
     */
    public function setCookie($secure = false)
    {
        $name = $this->getOption(self::OPTION_ID_NAME, self::DEFAULT_ID_NAME);
        $value = $this->getId();
        $expires = Nutex_Date::getDiffer($this->getOption(self::OPTION_SESSION_LIFETIME, self::DEFAULT_SESSION_LIFETIME), Zend_Date::COOKIE);
        $header = rawurldecode($name) . '=' . rawurldecode($value) . '; expires=' . $expires .'; path=/;' . (($secure) ? ' secure' : '');
        $this->getResponse()->setHeader('Set-Cookie', $header);
    }
}
