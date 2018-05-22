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

$forceAll = process_action();
$query = determine_query();
$news = news_build_items($query);
$news_submit = build_submit_form();

$page = eval($templates->render('news'));
output_page($page);

/**
 * Determine which action to perform by
 * query param `action`.
 */
function process_action()
{
    global $mybb;

    $action = $mybb->get_input('action');
    if ($action === "POST") {
        news_submit();
    } elseif ($action === "PUT") {
        news_mark();
    } elseif ($action === "DELETE") {
        news_delete();
    } else {
        return false;
    }
    return true;
}

function determine_query()
{
    global $mybb, $db;

    $nid = $mybb->get_input('nid', MyBB::INPUT_INT);
    if (!$forceAll && $nid) {
        $query = news_get(0, 1, $nid);
        add_breadcrumb($db->fetch_field($query, 'title'));
        $db->data_seek($query, 0);
    } else {
        $query = news_get_paged();
    }

    return $query;
}

function build_tag_options()
{
    $taglist = news_explode_settings('news_tags');
    $tag_options = '';
    foreach ($taglist as $key => $value) {
        $tag = array('key' => $key, 'value' => $value);
        $tag_options .= '<option value="' . $tag['key'] . '">' . $tag['value'] . '</option>';
    }
    return $tag_options;
}

function build_submit_form()
{
    global $mybb, $templates, $lang;

    $tag_options = build_tag_options();
    $news_submit = '';
    if (news_allowed($mybb->settings['news_groups'], news_usergroups())) {
        $canflag = news_allowed($mybb->settings['news_canflag'], news_usergroups());
        $important = $canflag ? eval($templates->render('news_submit_important')) : '';
        $news_submit = eval($templates->render('news_submit'));
    }
    return $news_submit;
}
