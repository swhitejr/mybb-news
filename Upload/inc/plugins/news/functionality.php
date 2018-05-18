<?php

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

        $news .= eval($templates->render('news_item'));
    }

    if ($news === '') {
        $news = eval($templates->render('news_no_news'));
    }

    return $news;
}

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

function news_get_count()
{
    global $db;

    $query = $db->simple_select("news", "COUNT(nid) as news", "", array('limit' => 1));
    return $db->fetch_field($query, "news");
}

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

function news_get($start = null, $perpage = 1)
{
    global $db;

    $query = 'SELECT news.nid, news.text, news.tid, news.uid, news.tags, news.important, ' .
        'user.username, user.usergroup, user.displaygroup, thread.subject ' .
        'FROM ' . TABLE_PREFIX . 'news news ' .
        'INNER JOIN ' . TABLE_PREFIX . 'threads thread ON thread.tid = news.tid ' .
        'INNER JOIN ' . TABLE_PREFIX . 'users user ON user.uid = news.uid ' .
        'ORDER BY important DESC, created_at DESC ' .
        'LIMIT ' . $start . ', ' . $perpage;

    return $db->write_query($query);
}

function news_submit()
{
    global $mybb, $db, $templates, $lang, $errors;

    if (!news_allowed($mybb->settings['news_groups'], news_usergroups())) {
        return;
    }

    $data = array(
        'text' => $_POST['text'],
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

function news_usergroups()
{
    global $mybb;

    if ($mybb->user['additionalgroups']) {
        $groups = explode(',', $mybb->user['additionalgroups']);
    }

    $groups[] = $mybb->user['usergroup'];
    return $groups;
}

function news_allowed($allowed, $groups)
{
    return ($allowed == -1 || array_intersect($groups, explode(',', $allowed)));
}

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
 * Fetches value of setting $name and explodes into array delimited by '='.
 *
 * @param  string  $name  Case-sensitive name of the setting.
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
