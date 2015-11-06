<?php
/**
 * class Nutex_Validate_EmailWithDomains
 *
 * ドメイン制限つきメールアドレスバリデータ
 *
 * @package Nutex
 * @subpackage Nutex_Validate
 */
class Nutex_Validate_EmailWithDomains extends Nutex_Validate_EmailAddress
{
    const NOT_ALLOWED_DOMAIN = 'notAllowedDomain';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ALLOWED_DOMAIN        => "使用できないメールアドレスです",
        self::INVALID            => "メールアドレスの形式で入力して下さい",
        self::INVALID_FORMAT     => "メールアドレスの形式で入力して下さい",
        self::INVALID_HOSTNAME   => "存在しないメールアドレスのようです",
        self::INVALID_MX_RECORD  => "存在しないメールアドレスのようです",
        self::INVALID_SEGMENT    => "存在しないメールアドレスのようです",
        self::DOT_ATOM           => "メールアドレスの形式で入力して下さい",
        self::QUOTED_STRING      => "メールアドレスの形式で入力して下さい",
        self::INVALID_LOCAL_PART => "メールアドレスの形式で入力して下さい",
        self::LENGTH_EXCEEDED    => "メールアドレスとして長すぎます",
    );

    /**
     * @var array
     */
    protected $_allowedDomains = array(
        'docomo.ne.jp',
        'ezweb.ne.jp',
        'softbank.ne.jp',
        'i.softbank.jp',
        'disney.ne.jp',
        'gmail.com',

        'mopera.net',
        'dwmail.jp',

        'd.vodafone.ne.jp',
        'h.vodafone.ne.jp',
        't.vodafone.ne.jp',
        'c.vodafone.ne.jp',
        'r.vodafone.ne.jp',
        'k.vodafone.ne.jp',
        'n.vodafone.ne.jp',
        's.vodafone.ne.jp',
        'q.vodafone.ne.jp',

        'jp-d.ne.jp',
        'jp-h.ne.jp',
        'jp-t.ne.jp',
        'jp-c.ne.jp',
        'jp-r.ne.jp',
        'jp-k.ne.jp',
        'jp-n.ne.jp',
        'jp-s.ne.jp',
        'jp-q.ne.jp',
        'jp-d.ne.jp',

        '*.biz.ezweb.ne.jp',
        'ido.ne.jp',
        'sky.tkk.ne.jp',
        'sky.tkc.ne.jp',
        'sky.tu-ka.ne.jp',

        'pdx.ne.jp',
        'di.pdx.ne.jp',
        'dj.pdx.ne.jp',
        'dk.pdx.ne.jp',
        'wm.pdx.ne.jp',

        'willcom.com',
        'emnet.ne.jp',
        'vertuclub.ne.jp',
    );

    /**
     * isValid
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if ($value === '') {
            return true;
        }

        if (!parent::isValid($value)) {
            return false;
        }

        list($user, $domain) = explode('@', $value);
        $domainParts = explode('.', $domain);
        foreach ($this->_allowedDomains as $allowed) {
            $match = true;
            foreach (explode('.', $allowed) as $pos => $part) {
                if (!isset($domainParts[$pos]) || ($part !== '*' && $part !== $domainParts[$pos]) ) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return true;
            }
        }
        $this->_error(self::NOT_ALLOWED_DOMAIN);
        return false;
    }

    /**
     * getAllowedDomains
     *
     * @param void
     * @return mixed
     */
    public function getAllowedDomains()
    {
        return $this->_allowedDomains;
    }

    /**
     * setAllowedDomains
     *
     * @param array $domains
     * @return $this
     */
    public function setAllowedDomains(array $domains)
    {
        $this->_allowedDomains = $domains;
        return $this;
    }
}
