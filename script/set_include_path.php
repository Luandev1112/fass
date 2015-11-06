<?php
/**
 * include_pathのセット
 *
 */
$includes = explode(PATH_SEPARATOR, get_include_path());

$additionals = array();
$additionals[] = defined('LIBRARY_PATH') ? LIBRARY_PATH : realpath(dirname(__FILE__) . '/../library');

foreach ($additionals as $add) {
    if (!in_array($add, $includes)) {
        array_unshift($includes, $add);
    }
}

set_include_path(implode(PATH_SEPARATOR, $includes));