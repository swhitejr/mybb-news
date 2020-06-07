<?php
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

/**
 * @return array Plugin info
 */
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

/**
 * @return void
 */
function news_install()
{
    require_once MYBB_ROOT . "inc/adminfunctions_templates.php";
    require_once MYBB_ROOT . 'inc/plugins/news/install/tables.php';
    require_once MYBB_ROOT . 'inc/plugins/news/install/settings.php';

    foreach ($tables as $name => $columns) {
        news_create_table($name, $columns);
    }

    news_create_template_group();
    news_create_templates();
    news_create_stylesheet();

    $gid = news_create_settings_group($settinggroup);
    $i = 1;
    foreach ($settings as $name => $setting) {
        news_create_setting($name, $setting, $gid, $i++);
    }

    find_replace_templatesets(
        "index",
        "#" . preg_quote('{$header}') . "#i",
        "{\$header}\n\n        {\$latest_news}"
    );

    rebuild_settings();
}

/**
 * @return boolean
 */
function news_is_installed()
{
    global $db;

    return $db->table_exists('news');
}

/**
 * @return void
 */
function news_uninstall()
{
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    global $db;

    $db->drop_table('news');
    $db->delete_query('settinggroups', "name = 'newsgroup'");
    $db->delete_query('settings', "name LIKE 'news_%'");
    $db->delete_query('templategroups', "prefix = 'news'");
    $db->delete_query('templates', "title LIKE 'news_%' OR title = 'news'");
    news_delete_stylesheet();

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
function news_create_templates()
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
 * Create news.css stylesheet for master theme and update cache
 *
 * @return void
 */
function news_create_stylesheet()
{
    global $db;
    require_once MYBB_ROOT . "admin/inc/functions_themes.php";

    $url = MYBB_ROOT . 'inc/plugins/news/install/stylesheets/news.css';
    $stylesheet = file_get_contents($url, true);

    $db->insert_query('themestylesheets', array(
        'name' => 'news.css',
        'tid' => 1,
        'attachedto' => 'index.php|news.php',
        'stylesheet' => $db->escape_string($stylesheet),
    ));

    cache_stylesheet(1, "news.css", $stylesheet);
    update_theme_stylesheet_list("1");
}

/**
 * @param  array $group Data for setting group
 * @return int   ID of new setting group
 */
function news_create_settings_group($group)
{
    global $db;

    return $db->insert_query("settinggroups", $group);
}

/**
 * @param  str   $name    Name of setting
 * @param  array $setting Data for setting
 * @param  int   $gid     Setting group ID
 * @return void
 */
function news_create_setting($name, $setting, $gid, $ndx = 1)
{
    global $db;

    $setting["name"] = $name;
    $setting["gid"] = $gid;
    $setting["disporder"] = $ndx;

    $db->insert_query("settings", $setting);
}

/**
 * Deletes stylesheet and removes from cache
 *
 * @return void
 */
function news_delete_stylesheet()
{
    global $db;
    require_once MYBB_ROOT . "admin/inc/functions_themes.php";

    $db->delete_query('themestylesheets', "name = 'news.css'");

    $query = $db->simple_select("themes", "tid");
    while ($tid = $db->fetch_field($query, "tid")) {
        $file = MYBB_ROOT . "cache/themes/theme{$tid}/news.css";
        if (file_exists($file)) {
            unlink($file);
        }

    }

    update_theme_stylesheet_list("1");
}
