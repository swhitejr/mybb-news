<?php
/**
 * Database table
 */
class Shinka_Table
{
    public $name;
    public $definitions;

    /**
     * Stores name and table definitions
     */
    public function __construct($name, $definitions)
    {
        $this->name = $name;
        $this->definitions = $definitions;
    }

    /**
     * Creates table
     * @return void
     */
    public function create()
    {
        global $db;

        $definition_strs = join(', ', $this->definition);
        if (!$db->table_exists($this->name)) {
            $db->write_query(
                'CREATE TABLE ' . TABLE_PREFIX . $name .
                "($definition_strs)"
            );
        }
    }

    /**
     * Drops table
     */
    public function drop()
    {
        global $db;

        $db->drop_table($this->name);
    }
}
