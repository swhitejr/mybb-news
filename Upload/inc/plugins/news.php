<?php
/**
 * News
 * Adds user-submitted news feed.
 *
 * @category MyBB Plugins
 * @package  News
 * @author   Kalyn Robinson <dev@shinkarpg.com>
 * @license  http://unlicense.org/ Unlicense
 * @version  1.0.0
 * @link https://github.com/ShinkaDev-MyBB/mybb-news
 */

if (!defined('IN_MYBB')) {
    die('You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.');
}

if (defined('IN_ADMINCP')) {
    require_once MYBB_ROOT . 'inc/plugins/news/install.php';
} else {
    require_once MYBB_ROOT . 'inc/plugins/news/forum.php';
}
