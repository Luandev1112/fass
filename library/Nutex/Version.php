<?php
/**
 * class Nutex_Version
 *
 * バージョン情報
 * Nutexライブラリを変更したら必ず変更すること
 *
 * @package Nutex
 * @subpackage Nutex_Version
 */
final class Nutex_Version
{
    /**
     * バージョン情報
     * @var string
     */
    const VERSION = '1.1.0';

    /**
     * version
     *
     * @param  void
     * @return string
     */
    public static function version()
    {
        return self::VERSION;
    }
}
