<?php

global $plugins;

$plugins->add_hook('index_start', 'news_index');
require_once MYBB_ROOT . 'inc/plugins/news/functionality.php';

/**
 * Display latest news on index
 */
function news_index()
{
    global $mybb, $lang, $templates, $latest_news;

    if (!$lang->news) {
        $lang->load('news');
    }

    $onindex = $mybb->settings['news_onindex'] ?: 5;
    $query = news_get(0, $onindex);
    $news = news_build_items($query);

    $latest_news = eval($templates->render('news_latest'));
}
