<?php

$templates = array(
    'latest' => '<table class="tborder" cellspacing="0" cellpadding="5" border="0">
	<thead>
		<tr>
			<td class="thead" colspan="5">
				<div><strong>{$lang->news_latest}</strong></div>
			</td>
		</tr>
	</thead>
	<tbody>
		{$news}
		<tr class="trow1">
			<td>
				<a href="news.php">View more</a>
			</td>
		</tr>
	</tbody>
</table>',

    'no_news' => '<tr class="trow1">
	<td>{$lang->news_no_news}</td>
</tr>',

    'item' => '<tr class="trow1 important-{$item[\'important\']}">
	<td>
		<b>{$item[\'title\']}</b><br/>
		by {$item[\'username\']} on {$item[\'created_at\']}</b> {$mark_as}<br/>
		{$important}
		{$item[\'tags\']}
		{$item[\'text\']}<br/>
		(<a href="/showthread.php?tid={$item[\'tid\']}">{$item[\'subject\']}</a>)
	</td>
</tr>',

    'tag' => '<span class="news-tag {$tag[\'key\']}">{$tag[\'value\']}</span>',

    'important' => '<span class="news-tag tag-important">{$lang->news_important}</span>',

    'mark_as' => '<form method="POST" action="news.php?action=PUT" style="display:inline-block;">
	<input type="hidden" name="nid" value="{$item[\'nid\']}" />
	<input type="hidden" name="important" value="{$item[\'important\']}" />
	<input class="button" type="submit" value="Mark as {$status}" />
</form>',

    'submit_important' => '<tr class="trow1">
	<td></td>
	<td>
		<label for="important">
			<input type="checkbox" name="important" id="important" /> {$lang->news_important}?
		</label>
	</td>
</tr>',

    'page' => '<html>
	<head>
		<title>{$mybb->settings[\'bbname\']} - {$lang->news}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}

		{$errors}

		{$multipage}
		<table class="tborder" cellspacing="0" cellpadding="5" border="0">
			<thead>
				<tr>
					<td class="thead" colspan="1">
						<div><strong>{$lang->news}</strong></div>
					</td>
				</tr>
			</thead>
			<tbody>
				{$news}
			</tbody>
		</table>
		{$multipage}

		<br/>

		{$news_submit}

		{$footer}
	</body>
</html>',

    'submit' => '<form method="POST" action="news.php?action=POST">
	<table class="tborder" cellspacing="0" cellpadding="5" border="0">
		<thead>
			<tr>
				<td class="thead" colspan="2">
					<div><strong>{$lang->news_submit}</strong></div>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr class="trow1">
				<td>
					<label for="text">Title</label>
				</td>
				<td>
					<input class="textbox" name="title" id="title" maxlength="255" required />
				</td>
			</tr>
			<tr class="trow1">
				<td>
					<label for="text">Text</label>
				</td>
				<td>
					<input class="textbox" name="text" id="text" maxlength="255" required />
				</td>
			</tr>
			<tr class="trow1">
				<td>
					<label for="tid">Thread ID</label>
				</td>
				<td>
					<input class="textbox" name="tid" id="tid" maxlength="255" required />
				</td>
			</tr>
			<tr class="trow1">
				<td>
					<label for="tags">Tags</label>
				</td>
				<td>
					<select class="input" name="tags[]" id="tags" multiple size="5">
						{$tag_options}
					</select>
				</td>
			</tr>
			<tr class="trow1">
				<td></td>
				<td>
					<input type="submit" value="Submit" class="button" />
				</td>
			</tr>
			{$important}
		</tbody>
	</table>
</form>',
);
