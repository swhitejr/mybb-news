<?php
/**
 * Theme Preview
 * Allows user to preview themes in their UCP before selecting one.
 *
 * Adds Change Theme page to UCP.
 *
 * @category MyBB Plugins
 * @package Theme Preview
 * @author  Kalyn Robinson <dev@shinkarpg.com>
 * @license http://unlicense.org/ Unlicense
 * @version 1.0.0
 * @link https://github.com/ShinkaDev-MyBB/mybb-theme-preview
 */

$plugins->add_hook('usercp_menu', 'themepreview_usercpmenu', 40);
$plugins->add_hook('usercp_start', 'themepreview_usercp_page');

/**
 * Adds a button to the usercp navigation.
 */
function themepreview_usercpmenu()
{
    global $mybb, $lang, $templates, $theme, $usercpmenu;

    if (!$lang->themepreview) {
        $lang->load('themepreview');
    }

    $usercpmenu .= eval($templates->render('themepreview_ucp_nav'));
}

function themepreview_usercp_page()
{
    global $mybb;

    if ($mybb->input['action'] == 'changetheme') {
        themepreview_update();
        themepreview_show();
    }
}

function themepreview_show()
{
    global $mybb, $db, $lang, $templates, $themes;
    global $headerinclude, $header, $footer, $usercpnav, $usercpmenu;

    if (!$lang->themepreview) {
        $lang->load('themepreview');
    }

    add_breadcrumb($lang->nav_usercp, 'usercp.php');
    add_breadcrumb($lang->themepreview_change_theme);

    $query = $db->simple_select('themes',
        'tid, name, preview, allowedgroups, def',
        'pid != 0',
        array('order_by' => 'name',
            'order_dir' => 'ASC'));

    $groups = themepreview_usergroups();
    $style = $mybb->input['style'] ? (int) $mybb->input['style'] : (int) $mybb->user['style'];
    while ($theme = $db->fetch_array($query)) {
        if (themepreview_allowed($theme, $groups)) {
            if ($style == (int) $theme['tid'] || (!$style && $theme['def'] == '1')) {
                $checked = 'checked';
            } else {
                $checked = null;
            }

            eval('$themes .= "' . $templates->get("themepreview_ucp_theme") . '";');
        }
    }

    output_page(eval($templates->render('themepreview_ucp')));
    exit;
}

function themepreview_update()
{
    global $mybb, $db;

    if ($mybb->input['act'] != 'POST') {
        return;
    }

    $tid = $mybb->input['style'];
    if ($tid === null) {
        return;
    }

    $query = $db->simple_select('themes',
        'tid, allowedgroups',
        'tid = ' . $tid,
        '');

    if (themepreview_allowed($db->fetch_array($query), themepreview_usergroups())) {
        $db->update_query('users',
            array('style' => $db->escape_string($tid)),
            'uid = ' . $mybb->user['uid']);
    }

    header("Location: " . $mybb->settings['bburl'] . '/usercp.php?action=changetheme');
    exit();
}

function themepreview_usergroups()
{
    global $mybb;

    if ($mybb->user['additionalgroups']) {
        $groups = explode(',', $mybb->user['additionalgroups']);
    }

    $groups[] = $mybb->user['usergroup'];
    return $groups;
}

function themepreview_allowed($theme, $groups)
{
    $allowed = $theme['allowedgroups'];
    return ($allowed == 'all' || array_intersect($groups, explode(',', $allowed)));
}
