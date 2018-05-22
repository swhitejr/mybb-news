<?php

/**
 * Construct news item templates
 *
 * Available template params:
 * @var array  $item       News item with indices `created_at` (formatted per MyBB settings), `nid`, `text`,
 *                        `tid`, `uid`, `tags`, `important`, `username` (styled appropriately), `usergroup`,
 *                        `displaygroup`, and `subject`
 * @var str    $important  Label for news items marked as important
 * @var str    $status     "Important" or "Unimportant" (subject to $lang)
 * @var str    $mark_as    Button to mark news item as important or unimportant
 */
function news_build_items($query)
{
    global $mybb, $db, $templates, $lang;

    if (!$lang->news) {
        $lang->load('news');
    }

    $news = '';
    while ($item = $db->fetch_array($query)) {
        $item['created_at'] = my_date($mybb->settings['dateformat'], $item['created_at']);
        $item['username'] = format_name($item['username'], $item['usergroup'], $item['displaygroup']);
        $item['tags'] = news_build_tags($item['tags']);
        $important = $item['important'] ? eval($templates->render('news_important')) : '';

        $status = $item['important'] ? $lang->news_unimportant : $lang->news_important;
        $mark_as = news_allowed($mybb->settings['news_canflag'], news_usergroups()) ?
        eval($templates->render('news_mark_as')) :
        '';

        $delete = news_allowed($mybb->settings['news_candelete'], news_usergroups()) ?
        eval($templates->render('news_delete')) :
        '';
        $news .= eval($templates->render('news_item'));
    }

    if ($news === '') {
        $news = eval($templates->render('news_no_news'));
    }

    return $news;
}

/**
 * Build news item tags template
 *
 * Available template params:
 * @var  array  $tag  Tag item with available indices `key` and `value`
 */
function news_build_tags($given)
{
    global $mybb, $templates;

    if ($given === '') {
        return;
    }

    $taglist = news_explode_settings('news_tags');
    $given = explode(',', $given);

    $tags = '';
    foreach ($given as $item) {
        $split = explode('=', $item);
        $tag = array('key' => $item, 'value' => $taglist[$item]);
        $tags .= eval($templates->render('news_tag'));
    }

    return $tags;
}

/**
 * @return int Total number of news
 */
function news_get_count()
{
    global $db;

    $query = $db->simple_select("news", "COUNT(nid) as news", "", array('limit' => 1));
    return $db->fetch_field($query, "news");
}

/**
 * Retrieves a page of news
 *
 * Retrieves page number as input param.
 *
 * @return array
 */
function news_get_paged()
{
    global $mybb, $multipage;

    $page = $mybb->get_input('page', MyBB::INPUT_INT);
    $count = news_get_count();
    $perpage = $mybb->settings['news_perpage'] ?: 10;

    if ($page > 0) {
        $start = ($page - 1) * $perpage;
        $pages = $count / $perpage;
        $pages = ceil($pages);
        if ($page > $pages || $page <= 0) {
            $start = 0;
            $page = 1;
        }
    } else {
        $start = 0;
        $page = 1;
    }

    $end = $start + $perpage;
    $lower = $start + 1;
    $upper = $end;

    if ($upper > $count) {
        $upper = $count;
    }

    $page_url = str_replace("{page}", $page, "news.php");
    $multipage = multipage($count, $perpage, $page, $page_url);

    return news_get($start, $perpage);
}

/**
 * Queries for news records with optional limit
 *
 * @param  int   $start   Start of limit
 * @param  int   $perpage Number of records to return, end of limit
 * @return array List of news
 */
function news_get($start = null, $perpage = 5)
{
    global $db;

    $query = 'SELECT news.nid, news.title, news.text, news.tid, news.uid, news.tags, news.important, ' .
        'user.uid, user.username, user.usergroup, user.displaygroup, thread.subject ' .
        'FROM ' . TABLE_PREFIX . 'news news ' .
        'INNER JOIN ' . TABLE_PREFIX . 'threads thread ON thread.tid = news.tid ' .
        'INNER JOIN ' . TABLE_PREFIX . 'users user ON user.uid = news.uid ' .
        'ORDER BY important DESC, created_at DESC ';

    if ($start !== null) {
        $query .= 'LIMIT ' . $start . ', ' . $perpage;
    }

    return $db->write_query($query);
}

/**
 * Inserts valid news submission
 *
 * Builds submission with data from $_POST and current user.
 * Validates that current user has permission to submit news
 * and that news is from a valid forum.
 *
 * @return void
 */
function news_submit()
{
    global $mybb, $db, $templates, $lang, $errors;

    if (!news_allowed($mybb->settings['news_groups'], news_usergroups())) {
        return;
    }

    $data = array(
        'title' => $db->escape_string($_POST['title']),
        'text' => $db->escape_string($_POST['text']),
        'tid' => $_POST['tid'],
        'tags' => implode(',', $_POST['tags'] ?: array()),
        'important' => $_POST['important'] === "on" ? true : false,
        'uid' => $mybb->user['uid'],
    );

    if (news_valid_thread($data['tid'])) {
        $db->insert_query('news', $data);
    } else {
        if (!$lang->news) {
            $lang->load('news');
        }
        $errorlist = '<li>' . $lang->news_invalid_thread . '</li>';
        $errors = eval($templates->render('error_inline'));
    }
}

/**
 * Marks news record as important or unimportant
 *
 * Inverts the current value of record's `important` field.
 */
function news_mark()
{
    global $mybb, $db;

    $nid = $_POST['nid'];
    if (!news_allowed($mybb->settings['news_canflag'], news_usergroups()) ||
        $nid == '') {
        return;
    }

    $db->update_query(
        'news',
        array(
            'important' => !((bool) $_POST['important']),
        ),
        'nid = ' . $nid
    );
}

/**
 * Delete news
 *
 * @return void
 */
function news_delete()
{
    global $mybb, $db;

    $nid = $_POST['nid'];
    if (!news_allowed($mybb->settings['news_candelete'], news_usergroups()) ||
        $nid == '') {
        return;
    }

    $db->delete_query('news', 'nid = ' . $nid, 1);
}

/**
 * Builds array of current user's usergroup and additional usergroups
 *
 * @return array Current user's usergroups
 */
function news_usergroups()
{
    global $mybb;

    if ($mybb->user['additionalgroups']) {
        $groups = explode(',', $mybb->user['additionalgroups']);
    }

    $groups[] = $mybb->user['usergroup'];
    return $groups;
}

/**
 * Compares list of current groups with list of allowed groups
 *
 * @return bool Whether $allowed groups and current $groups intersect
 */
function news_allowed($allowed, $groups)
{
    return ($allowed == -1 || array_intersect($groups, explode(',', $allowed)));
}

/**
 * Validates thread
 *
 * Checks that thread exists and is in an allowed forum.
 *
 * @param  int $tid Thread ID
 * @return int 0 or 1, number of threads that match $tid and are in
 *             a designated forum
 */
function news_valid_thread($tid)
{
    global $mybb, $db;

    if ($tid === '') {
        return false;
    }

    $fids = $mybb->settings['news_forums'];

    if ($fids == -1) {
        $clause = '';
    } elseif ($fids == '') {
        return false;
    } else {
        $clause = "AND FIND_IN_SET(fid, '" . $fids . "') > 0";
    }

    $query = $db->simple_select(
        'threads',
        'COUNT(tid) as thread',
        'tid = ' . $tid . ' ' . $clause,
        array(
        ));
    return $db->fetch_field($query, 'thread');
}

/**
 * Fetches value of setting $name and explodes into array
 *
 * Explodes outer list on newline, explodes inner list on "=".
 *
 * @param string $name Case-sensitive name of the setting.
 */
function news_explode_settings($name)
{
    global $mybb;

    $settings = $mybb->settings[$name];
    $settings = explode("\n", $settings);
    foreach ($settings as $key => $value) {
        $explosion = explode('=', $value);
        $settings[$explosion[0]] = $explosion[1];
        unset($settings[$key]);
    }

    return $settings;
}
