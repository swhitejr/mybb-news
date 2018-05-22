<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'news.php');

require_once "./global.php";
// require_once MYBB_ROOT . 'inc/plugins/news/functionality.php';

$templatelist = "news, news_important, news_item, news_latest, news_mark_as, news_no_news, ";
$templatelist .= "news_submit_important, news_submit, news_tag";

global $mybb, $lang, $templates, $plugins, $db;

if (!$lang->news) {
    $lang->load('news');
}

$plugins->run_hooks("news_start");

add_breadcrumb($lang->news);

$action = $mybb->get_input('action');
if ($action === "POST") {
    news_submit();
} elseif ($action === "PUT") {
    news_mark();
}

$query = news_get_paged();
$news = news_build_items($query);

$taglist = news_explode_settings('news_tags');
$tag_options = '';
foreach ($taglist as $key => $value) {
    $tag = array('key' => $key, 'value' => $value);
    $tag_options .= '<option value="' . $tag['key'] . '">' . $tag['value'] . '</option>';
}

if (news_allowed($mybb->settings['news_groups'], news_usergroups())) {
    $canflag = news_allowed($mybb->settings['news_canflag'], news_usergroups());
    $important = $canflag ? eval($templates->render('news_submit_important')) : '';
    $news_submit = eval($templates->render('news_submit'));
}

$page = eval($templates->render('news'));
output_page($page);
