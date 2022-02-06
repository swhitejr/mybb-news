<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'news.php');

require_once "./global.php";

$templatelist = "news, news_important, news_item, news_latest, news_mark_as, news_no_news, ";
$templatelist .= "news_submit_important, news_submit, news_tag, news_delete, news_tag_filter";

global $mybb, $lang, $templates, $plugins, $db;

if (!$lang->news) {
    $lang->load('news');
}

add_breadcrumb($lang->news, "news.php");

$plugins->run_hooks("news_start");

$nid = (int) $_GET['nid'];
$taglist = news_explode_settings('news_tags');
$filters = $mybb->get_input('tags');
$years = santize_years_input($mybb->get_input('years'));
$options = array('start' => 0, 'perpage' => 1, 'nid' => $nid);

$forceAll = process_action();
$query = $nid ? news_get($options) : news_get_paged($filters, $years);

$item = null;
if ($nid) {
    $item = $db->fetch_array($query);
    add_breadcrumb($item['title']);
    $db->data_seek($query, 0);
}

$news = news_build_items($query);
$news_submit = build_submit_form($item, $taglist);
$tags = build_tag_filters($filters, $taglist, $years);

$plugins->run_hooks("news_end");

$page = eval($templates->render('news'));
output_page($page);

/**
 * Determine which action to perform by query param `action`.
 */
function process_action()
{
    global $mybb, $errors;

    $action = $mybb->input['_method'];
    if ($action === "post") {
        news_submit();
        if(!empty(!$errors)) {
            header("Location: news.php", true);
            die();
        }
    } elseif ($action === "put") {
        news_mark();
        if(!empty(!$errors)) {
            header("Location: news.php", true);
            die();
        }
    } elseif ($action === "delete") {
        news_delete();
        if(!empty(!$errors)) {
            header("Location: news.php", true);
            die();
        }
    }
}

/**
 * @param str  $news_tags Comma-delimited string of already selected tags
 * @param str  $taglist   Defined tags
 * @return str HTML options
 */
function build_tag_options($news_tags = "", $taglist = array())
{
    $news_tags = explode(',', $news_tags);
    $tag_options = '';
    foreach ($taglist as $key => $value) {
        $selected = in_array($key, $news_tags) ? "selected" : "";
        $tag = array('key' => $key, 'value' => $value);
        $tag_options .= '<option value="' . $tag['key'] . '" ' . $selected . '>' . $tag['value'] . '</option>';
    }
    return $tag_options;
}

/**
 * @param  array $item     Item being edited
 * @param  array $taglist  Defined tags
 * @return str   Evaluated template
 */
function build_submit_form($item = array(), $taglist = array())
{
    global $mybb, $templates, $lang;

    if (isset($item['tid']) &&
        !(news_allowed($mybb->settings['news_canedit'], news_usergroups()) ||
            ($mybb->settings['news_caneditown'] && $item['uid'] == $mybb->user['uid']))) {
        return '';
    }

    $tag_options = build_tag_options($item['tags'], $taglist);
    $news_submit = '';
    if (news_allowed($mybb->settings['news_groups'], news_usergroups())) {
        $canflag = news_allowed($mybb->settings['news_canflag'], news_usergroups());
        $important = $canflag && !isset($item['tid']) ? eval($templates->render('news_submit_important')) : '';
        $required = $mybb->settings['news_requirethread'] ? 'required' : '';
        $news_submit = eval($templates->render('news_submit'));
    }
    return $news_submit;
}

/**
 * @param  array $taglist Defined tags
 * @return string   Evaluated template
 */
function build_tag_filters($filters = null, $taglist = array(), $years = null)
{
    global $mybb, $templates;

    $og_filters = $filters ? explode(',', $filters) : array();
    $tags = '';
    foreach ($taglist as $key => $value) {
        $filters = compute_filters($key, $og_filters);
        $tag = array('key' => $key, 'value' => $value, 'status' => in_array($key, $og_filters) ? 'on' : 'off');
        $tagUrl = "news.php?tags={$filters}" . ($years ? "&years={$years}" : "");
        $tags .= eval($templates->render('news_tag_filter'));
    }
    return $tags;
}

/**
 * Build query string for tag filters
 *
 * If $key is already in $og_filters, removes it. Otherwise, adds
 * key to list.
 *
 * @param  str   $key        Tag to check
 * @param  array $og_filters List of filters
 * @return str   Comma-delimited string of tags to use as query param
 */
function compute_filters($key, $og_filters)
{
    if (in_array($key, $og_filters)) {
        $filters = array_diff($og_filters, array($key));
    } else {
        $filters = $og_filters;
        $filters[] = $key;
    }
    return implode(',', $filters);
}

/**
 * Sanitize a comma separated list of numbers by removing any values from the string
 * that aren't numbers
 *
 * @param $input string Comma separated string of values
 * @return string Comma separated string of values with non-numbers removed
 */
function santize_years_input($input) {
    if(!empty($input)) {
        $input = explode(',', $input);
        $input = array_filter($input, function($year) {
            return is_numeric($year) && strtotime("31-12-".$year." 11:59:59");
        });

        return implode(',', $input);
    }

    return $input;
}
