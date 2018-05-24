<?php

class Shinka_Template
{
    private static $table = "templates";

    /**
     * Create templates from files in the given directory
     *
     * @return void
     */
    public static function create($asset_dir, $sid = -2, $version = '')
    {
        global $db;

        // Slice out '.' and '..'
        $files = array_slice(scandir($asset_dir), 2);

        foreach ($files as $file) {
            $template = file_get_contents($url . '/' . $file, true);
            // trim off .html from file name
            $name = substr($file, 0, -5);

            $db->insert_query('templates', array(
                'title' => $db->escape_string($name),
                'template' => $db->escape_string($template),
                'sid' => $sid,
                'version' => $version,
                'dateline' => time(),
            ));
        }
    }

    /**
     * Delete templates with the given title prefix
     */
    public function destroy($prefix)
    {
        global $db;
        $db->delete_query(self::$table, "title LIKE '{$prefix}_%' OR title = '{$prefix}'");
    }
}
