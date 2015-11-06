<?php
/**
 * class Nutex_Helper_View_ShowQueriesFromException
 *
 * 例外からクエリを出力
 *
 * @package Nutex
 * @subpackage Nutex_Helper_View
 */
class Nutex_Helper_View_ShowQueriesFromException extends Nutex_Helper_View_Abstract
{
    /**
     * showQueriesFromException
     *
     * @param Exception $exception
     * @return string
     */
    public function showQueriesFromException(Exception $exception)
    {
        $htmls = array();

        foreach ($exception->getTrace() as $trace) {
            if (isset($trace['class']) === false || $trace['class'] !== 'Zend_Db_Adapter_Abstract') {
                continue;
            }

            if (isset($trace['args']) === false || is_array($trace['args']) === false) {
                continue;
            }

            foreach ($trace['args'] as $obj) {
                if (is_object($obj) === false || method_exists($obj, 'getAdapter') === false) {
                    continue;
                }

                $adapter = $obj->getAdapter();
                if (!$adapter instanceof Zend_Db_Adapter_Abstract) {
                    continue;
                }

                $profiles = $adapter->getProfiler()->getQueryProfiles(null, true);
                if (is_array($profiles) === false) {
                    continue;
                }

                $num = count($profiles);
                foreach (array_reverse($profiles) as $profile) {
                    if ($htmls === array()) {
                        $htmls[] = '<table class="listTable">';
                    }
                    $htmls[] = '<tr>';
                    $htmls[] = '<th style="width: 10%;">' . $num . '</th>';
                    $htmls[] = '<td style="width: 90%; word-break: break-all; word-wrap:break-word; white-space: pre-wrap;">' . $this->getView()->escape($profile->getQuery()) . '</td>';
                    $htmls[] = '</tr>';
                    $num--;
                }
            }
            if ($htmls !== array()) {
                $htmls[] = '</table>';
            }
        }

        return implode("\n", $htmls);
    }
}
