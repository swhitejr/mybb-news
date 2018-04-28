<?php
/**
 * Theme Preview
 * Allows user to preview themes in their UCP before selecting one.
 *
 * @category MyBB Plugins
 * @package Theme Preview
 * @author  Kalyn Robinson <dev@shinkarpg.com>
 * @license http://unlicense.org/ Unlicense
 * @version 1.0.0
 * @link https://github.com/ShinkaDev-MyBB/mybb-theme-preview
 */

if (!defined('IN_MYBB')) {
    die('You Cannot Access This File Directly. Please Make Sure IN_MYBB Is Defined.');
}

if (defined('IN_ADMINCP')) {
    require_once MYBB_ROOT . 'inc/plugins/themepreview/install.php';
    require_once MYBB_ROOT . 'inc/plugins/themepreview/admin.php';
} else {
    require_once MYBB_ROOT . 'inc/plugins/themepreview/forum.php';
}
