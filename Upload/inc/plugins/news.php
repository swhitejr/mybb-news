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

global $plugins;

$plugins->add_hook('fetch_wol_activity_end', 'news_wol');
function news_wol($user_activity) {
    if(strpos($user_activity['location'], "news.php"))
    {
        $user_activity['activity'] = "news";
    }
    return $user_activity;
}

$plugins->add_hook('build_friendly_wol_location_end', 'news_friendly_loc');
function news_friendly_loc($array) {
    if($array['user_activity']['activity'] == "news")
    {
        $array['location_name'] = "Viewing <a href='/news.php'>News</a>";
    }
    return $array;
}
