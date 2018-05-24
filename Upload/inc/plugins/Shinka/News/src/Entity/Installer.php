<?php
/**
 * Common install functions
 */
class ShinkaInstaller
{
    /**
     * @return void
     */
    public function create_tables()
    {
        foreach ($this->tables as $name => $definition) {
            self::create_table($name, $definition);
        }
    }

    /**
     * @return void
     */
    public static function create_table($name, $definition)
    {
        global $db;

        $definition_strs = join(', ', $definition);
        if (!$db->table_exists($name)) {
            $db->write_query(
                'CREATE TABLE ' . TABLE_PREFIX . $name .
                "($definition_strs)"
            );
        }
    }
}
