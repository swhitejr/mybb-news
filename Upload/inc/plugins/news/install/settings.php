<?php

global $lang;

if (!$lang->news) {
    $lang->load('news');
}

$settinggroup = array(
    "name" => "newsgroup",
    "title" => $lang->news_settings_title,
    "description" => $lang->news_settings_description,
    "disporder" => 5,
    "isdefault" => 0,
);

$settings = array(
    "news_forums" => array(
        "title" => $lang->news_forums,
        "description" => $lang->news_forums_description,
        "optionscode" => "forumselect",
        "value" => "-1",
        "disporder" => 1,
    ),
    "news_groups" => array(
        "title" => $lang->news_groups,
        "description" => $lang->news_groups_description,
        "optionscode" => "groupselect",
        "value" => "-1",
        "disporder" => 2,
    ),
    "news_canflag" => array(
        "title" => $lang->news_canflag,
        "description" => $lang->news_canflag_description,
        "optionscode" => "groupselect",
        "value" => 4,
        "disporder" => 3,
    ),
    "news_tags" => array(
        "title" => $lang->news_tags,
        "description" => $lang->news_tags_description,
        "optionscode" => "textarea",
        "value" => "personal=Personal\nsite_wide=Site Wide",
        "disporder" => 4,
    ),
    "news_perpage" => array(
        "title" => $lang->news_perpage,
        "description" => $lang->news_perpage_description,
        "optionscode" => "numeric",
        "value" => 10,
        "disporder" => 5,
    ),
    "news_onindex" => array(
        "title" => $lang->news_onindex,
        "description" => $lang->news_onindex_description,
        "optionscode" => "numeric",
        "value" => 5,
        "disporder" => 6,
    ),
);
