<?php
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

function news_info()
{
    global $lang;

    if (!$lang->news) {
        $lang->load('news');
    }

    return array(
        'name' => $lang->news,
        'description' => $lang->news_description,
        'website' => 'https://github.com/ShinkaDev-MyBB/mybb-news',
        'author' => 'Shinka',
        'authorsite' => 'https://github.com/ShinkaDev-MyBB',
        "codename" => "news",
        'version' => '1.0.0',
        'compatibility' => '18*',
    );
}

function news_install()
{
    require_once MYBB_ROOT . 'inc/plugins/news/install/templates.php';
    require_once MYBB_ROOT . 'inc/plugins/news/install/tables.php';
    require_once MYBB_ROOT . 'inc/plugins/news/install/settings.php';

    foreach ($tables as $name => $columns) {
        news_create_table($name, $columns);
    }

    news_create_template_group();
    $gid = news_create_settings_group($settinggroup);

    foreach ($templates as $name => $template) {
        news_create_template($name, $template);
    }

    foreach ($settings as $name => $setting) {
        news_create_setting($name, $setting, $gid);
    }

    rebuild_settings();
}

function news_is_installed()
{
    global $db;

    return $db->table_exists('news');
}

function news_uninstall()
{
    global $db;

    $db->drop_table('news');
    $db->delete_query('settinggroups', "name = 'newsgroup'");
    $db->delete_query('settings', "name LIKE 'news_%'");
    $db->delete_query('templategroups', "prefix = 'news'");
    $db->delete_query('templates', "title LIKE 'news_%'");
}

function news_activate()
{}

function news_deactivate()
{}

function news_create_table($name, $columns)
{
    global $db;

    $column_strs = join(', ', $columns);
    if (!$db->table_exists($name)) {
        $db->write_query('CREATE TABLE ' . TABLE_PREFIX . $name . '('
            . $column_strs .
            ')');
    }
}

function news_create_template_group()
{
    global $lang, $db;

    if (!$lang->news) {
        $lang->load('news');
    }

    $db->insert_query('templategroups', array(
        'prefix' => 'news',
        'title' => $lang->news,
        'isdefault' => 1,
    ));
}

function news_create_template($name, $template)
{
    global $db;

    $db->insert_query('templates', array(
        'title' => 'news_' . $name,
        'template' => $db->escape_string($template),
        'sid' => '-2',
        'version' => '',
        'dateline' => time(),
    ));
}

function news_create_settings_group($group)
{
    global $db;

    return $db->insert_query("settinggroups", $group);
}

function news_create_setting($name, $setting, $gid)
{
    global $db;

    $setting["name"] = $name;
    $setting["gid"] = $gid;

    $db->insert_query("settings", $setting);
}
