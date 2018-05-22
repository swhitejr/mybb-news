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
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    require_once MYBB_ROOT . 'inc/plugins/news/install/tables.php';
    require_once MYBB_ROOT . 'inc/plugins/news/install/settings.php';

    foreach ($tables as $name => $columns) {
        news_create_table($name, $columns);
    }

    news_create_template_group();
    news_create_template();

    $gid = news_create_settings_group($settinggroup);
    foreach ($settings as $name => $setting) {
        news_create_setting($name, $setting, $gid);
    }

    find_replace_templatesets(
        "index",
        "#" . preg_quote('{$header}') . "#i",
        "{\$header}\n\n        {\$latest_news}"
    );

    rebuild_settings();
}

function news_is_installed()
{
    global $db;

    return $db->table_exists('news');
}

function news_uninstall()
{
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    global $db;

    $db->drop_table('news');
    $db->delete_query('settinggroups', "name = 'newsgroup'");
    $db->delete_query('settings', "name LIKE 'news_%'");
    $db->delete_query('templategroups', "prefix = 'news'");
    $db->delete_query('templates', "title LIKE 'news_%' OR title = 'news'");

    find_replace_templatesets(
        "index",
        "#" . preg_quote('{$latest_news}') . "#i",
        ""
    );
}

function news_activate()
{}

function news_deactivate()
{}

/**
 * @return void
 */
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

/**
 * @return void
 */
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

/**
 * Create templates from files in `./install/templates`
 *
 * Uses file name (minus the .html extension) as template name
 * and escaped file contents as template's html.
 *
 * @return void
 */
function news_create_template()
{
    global $db;

    $url = MYBB_ROOT . 'inc/plugins/news/install/templates';
    $files = array_slice(scandir($url), 2);

    foreach ($files as $file) {
        $template = file_get_contents($url . '/' . $file, true);
        $name = substr($file, 0, -5); // trim off .html from file name
        $db->insert_query('templates', array(
            'title' => $db->escape_string($name),
            'template' => $db->escape_string($template),
            'sid' => '-2',
            'version' => '',
            'dateline' => time(),
        ));
    }
}

/**
 * @param  array $group Data for setting group
 * @return int   GID of new setting group
 */
function news_create_settings_group($group)
{
    global $db;

    return $db->insert_query("settinggroups", $group);
}

/**
 * @param str   $name    Name of setting
 * @param array $setting Data for setting
 * @param int   $gid     Setting group ID
 * @return void
 */
function news_create_setting($name, $setting, $gid)
{
    global $db;

    $setting["name"] = $name;
    $setting["gid"] = $gid;

    $db->insert_query("settings", $setting);
}
