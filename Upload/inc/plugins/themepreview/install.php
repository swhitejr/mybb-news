<?php
/**
 * Theme Preview
 * Allows user to preview themes in their UCP before selecting one.

 * Install, activate, and deactivate scripts for the Admin CP -> Plugins page.
 *
 * @category MyBB Plugins
 * @package Theme Preview
 * @author  Kalyn Robinson <dev@shinkarpg.com>
 * @license http://unlicense.org/ Unlicense
 * @version 1.0.0
 * @link https://github.com/ShinkaDev-MyBB/mybb-theme-preview
 */

if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

function themepreview_info()
{
    global $lang;

    if (!$lang->themepreview) {
        $lang->load('themepreview');
    }

    return array(
        'name' => $lang->themepreview,
        'description' => $lang->themepreview_description,
        'website' => 'https://github.com/ShinkaDev-MyBB/mybb-theme-preview',
        'author' => 'Shinka',
        'authorsite' => 'https://github.com/ShinkaDev-MyBB',
        'version' => '1.0.0',
        'compatibility' => '18*',
    );
}

function themepreview_install()
{
    global $db, $lang;
    require_once MYBB_ROOT . 'inc/plugins/themepreview/templates.php';

    if (!$lang->themepreview) {
        $lang->load('themepreview');
    }

    if (!$db->field_exists('preview', 'themes')) {
        $db->add_column('themes', 'preview', 'VARCHAR(255)');
    }

    $db->insert_query('templategroups', array(
        'prefix' => 'themepreview',
        'title' => $lang->themepreview,
        'isdefault' => 1,
    ));

    foreach ($templates as $name => $template) {
        $db->insert_query('templates', array(
            'title' => 'themepreview_' . $name,
            'template' => $db->escape_string($template),
            'sid' => '-2',
            'version' => '',
            'dateline' => time(),
        ));
    }
}

function themepreview_is_installed()
{
    global $db;

    return $db->field_exists('preview', 'themes');
}

function themepreview_uninstall()
{
    global $db;

    $db->drop_column('themes', 'preview');
    $db->delete_query('templategroups', "prefix = 'themepreview'");
    $db->delete_query('templates', "title LIKE 'themepreview_%'");
}

function themepreview_activate()
{}

function themepreview_deactivate()
{}
