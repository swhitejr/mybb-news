<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'news.php');

require_once "./global.php";

$templatelist = "news, news_important, news_item, news_latest, news_mark_as, news_no_news, ";
$templatelist .= "news_submit_important, news_submit, news_tag, news_delete";

global $mybb, $lang, $templates, $plugins, $db;

if (!$lang->news) {
    $lang->load('news');
}

$plugins->run_hooks("news_start");

add_breadcrumb($lang->news, "news.php");

process_action();

$nid = (int) $_GET['nid'];
$query = $nid ? news_get(0, 1, $nid) : news_get_paged();
$news = news_build_items($query);

$item = null;
if ($nid) {
    $db->data_seek($query, 0);
    $item = $db->fetch_array($query);
    add_breadcrumb($item['title']);
}
$news_submit = build_submit_form($item);

$page = eval($templates->render('news'));
output_page($page);

/**
 * Determine which action to perform by
 * query param `action`.
 */
function process_action()
{
    global $mybb;

    $action = $mybb->input['_method'];
    if ($action === "post") {
        news_submit();
        header("Location: news.php", true);
        die();
    } elseif ($action === "put") {
        news_mark();
        header("Location: news.php", true);
        die();
    } elseif ($action === "delete") {
        news_delete();
        header("Location: news.php", true);
        die();
    }
}

function build_tag_options($news_tags = "")
{
    $news_tags = explode(',', $news_tags);
    $taglist = news_explode_settings('news_tags');
    $tag_options = '';
    foreach ($taglist as $key => $value) {
        $selected = in_array($key, $news_tags) ? "selected" : "";
        $tag = array('key' => $key, 'value' => $value);
        $tag_options .= '<option value="' . $tag['key'] . '" ' . $selected . '>' . $tag['value'] . '</option>';
    }
    return $tag_options;
}

function build_submit_form($item = array())
{
    global $mybb, $templates, $lang;

    if (isset($item['tid']) &&
        !(news_allowed($mybb->settings['news_canedit'], news_usergroups()) ||
            ($mybb->settings['news_caneditown'] && $item['uid'] == $mybb->user['uid']))) {
        return '';
    }

    $tag_options = build_tag_options($item['tags']);
    $news_submit = '';
    if (news_allowed($mybb->settings['news_groups'], news_usergroups())) {
        $canflag = news_allowed($mybb->settings['news_canflag'], news_usergroups());
        $important = $canflag && !isset($item['tid']) ? eval($templates->render('news_submit_important')) : '';
        $news_submit = eval($templates->render('news_submit'));
    }
    return $news_submit;
}
