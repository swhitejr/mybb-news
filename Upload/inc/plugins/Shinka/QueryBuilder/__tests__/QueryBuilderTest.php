<?php

namespace Shinka\QueryBuilder;

require_once "../QueryBuilderHandler.php";

global $db;
$qb = new Shinka_QueryBuilder_QueryBuilderHandler($db, "mybbyl_");
$news = $qb->table('news')
    ->leftJoin('threads', 'threads.tid', '=', 'news.tid')
    ->where('name', '=', 'Klin')
    ->orWhere('age', '>', 81)
    ->getQueryWithData();

echo "\n YOUR QUERY: \n";
echo ($news);
