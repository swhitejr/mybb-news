<?php
/**
 * Theme Preview
 * Allows user to preview themes in their UCP before selecting one.
 *
 * Adds Preview field to theme settings in Admin CP.
 *
 * @category MyBB Plugins
 * @package Theme Preview
 * @author  Kalyn Robinson <dev@shinkarpg.com>
 * @license http://unlicense.org/ Unlicense
 * @version 1.0.0
 * @link https://github.com/ShinkaDev-MyBB/mybb-theme-preview
 */

$plugins->add_hook('admin_formcontainer_end', 'themepreview_admin');
function themepreview_admin()
{
    global $mybb, $form, $form_container, $lang, $theme;

    if (!$lang->edit_theme_properties) {
        $lang->load('style_themes');
    }

    if ($form_container->_title == $lang->edit_theme_properties &&
        $mybb->input['action'] == 'edit') {
        if (!$lang->themepreview) {
            $lang->load('themepreview');
        }

        $form_container->output_row($lang->themepreview_setting,
            $lang->themepreview_setting_description,
            $form->generate_text_box('preview', $theme['preview'],
                array('id' => 'preview')),
            'preview');
    }
}

$plugins->add_hook('admin_style_themes_edit_commit', 'themepreview_update');
function themepreview_update()
{
    global $mybb, $db;

    $db->update_query('themes',
        array('preview' => $db->escape_string($mybb->input['preview'])),
        "tid = '" . (int) $mybb->input['tid'] . "'");
}
