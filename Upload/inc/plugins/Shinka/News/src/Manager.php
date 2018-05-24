<?php

/**
 * Manages the creating, fetching and manipulation of objects in the database.
 *
 * @package Shinka\News
 */
class Shinka_News_Manager extends Shinka_Core_Manager
{
    /** @var string */
    private static $table = "news";

    /** @var string Base query for news */
    private static $query = 'SELECT news.nid, news.title, news.text, news.tid, news.uid, news.tags, news.important, ' .
        'user.uid, user.username, user.usergroup, user.displaygroup, thread.subject ' .
        'FROM ' . TABLE_PREFIX . 'news news ' .
        'LEFT JOIN ' . TABLE_PREFIX . 'threads thread ON thread.tid = news.tid ' .
        'INNER JOIN ' . TABLE_PREFIX . 'users user ON user.uid = news.uid ';

    /**
     * @return
     */
    public function createItem(Shinka_News_Entity_News $news)
    {
        return $this->db->insert_query(self::$table, $news->toArray());
    }

    public function destroyItem(integer $nid)
    {
        return $this->db->delete_query(self::$table, "nid = $nid", 1);
    }

    public function createItems(array $news)
    {
        foreach ($news as $n) {
            $this->createItem($n);
        }
    }

    public function destroyItems(array $news)
    {
        foreach ($news as $n) {
            $this->destroyItem($n);
        }
    }

    public function findSimple(integer $id, string $fields = "*")
    {
        return $this->db->simple_select(self::$table, $fields, "nid = $nid", array(
            "limit" => 1,
        ));
    }

    public function find(integer $nid)
    {
        $query = self::$query . " WHERE news.nid = $nid";
        return $this->db->write_query(self::$table, self::$query);
    }

    public function findByTag(integer $id, string $fields = "*")
    {
        $query = self::$query . " WHERE news.nid = $nid";
        return $this->db->write_query(self::$table, self::$query);
    }

    public function count()
    {
        return $this->db->simple_select(self::$table, "COUNT(news.nid)", array());
    }
}
