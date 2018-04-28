<?php
/**
 * Theme Preview
 * Allows user to preview themes in their UCP before selecting one.
 *
 * Template list.
 *
 * @category MyBB Plugins
 * @package Theme Preview
 * @author  Kalyn Robinson <dev@shinkarpg.com>
 * @license http://unlicense.org/ Unlicense
 * @version 1.0.0
 * @link https://github.com/ShinkaDev-MyBB/mybb-theme-preview
 */

$templates = array(
    'ucp_theme' => '<tr>
    <td class="trow1">
        <label>
            <input type="radio" name="style" value="${theme[\'tid\']}" {$checked}>
            <img src="{$theme[\'preview\']}" class="theme-preview" />
            {$theme[\'name\']}
        </label>
    </td>
</tr>',
    'ucp_nav' => '<tr>
    <td class="trow1 smalltext">
        <a href="usercp.php?action=changetheme" class="usercp_nav_item usercp_nav_changetheme">
            {$lang->themepreview_change_theme}
        </a>
    </td>
</tr>',
    'ucp' => '<html>
	<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->themepreview_change_theme}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<form action="usercp.php?action=changetheme&act=POST" method="post">
			<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
			<table width="100%" border="0" align="center">
				<tr>
					{$usercpnav}
					<td valign="top">
						{$errors}
						<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
							<tr>
								<td class="thead"><strong>{$lang->themepreview_change_theme}</strong></td>
							</tr>
                            {$themes}
						</table>
						<br />
						<div align="center">
							<input type="submit" class="button" name="submit" value="{$lang->themepreview_change_theme}" />
						</div>
					</td>
				</tr>
			</table>
		</form>
		{$footer}
	</body>
</html>',
);
