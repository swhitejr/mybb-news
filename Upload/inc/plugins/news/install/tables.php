<?php

$tables = array(
    'news' => array(
        'nid INT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
        'tid INT(10) UNSIGNED NOT NULL',
        'uid INT(10) UNSIGNED NOT NULL',
        'text VARCHAR(255) NOT NULL',
        'tags VARCHAR(255) NOT NULL',
        'important BOOL NOT NULL DEFAULT FALSE',
        'updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()',
        'created_at TIMESTAMP NOT NULL DEFAULT NOW()',
        'PRIMARY KEY (nid)',
    ),
);
