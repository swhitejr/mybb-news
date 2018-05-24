<?php

class Shinka_TemplateGroup
{
    /** @var string */
    private static $table = "templategroups";

    /** @var string Prefix templates should be grouped under */
    public $prefix;

    /** @var string */
    public $title;

    /** @var integer */
    public $is_default = 1;

    /** @var string */
    public $asset_dir;

    /**
     * Store name and table definitions
     */
    public function __construct($asset_dir, $prefix, $title, $is_default = 1)
    {
        $this->asset_dir = $asset_dir;
        $this->prefix = $prefix;
        $this->title = $title;
        $this->is_default = $is_default;
    }

    /**
     * Create table
     *
     * @return void
     */
    public function create()
    {
        global $db;

        $db->insert_query(self::$table, array(
            'prefix' => $this->prefix,
            'title' => $this->title,
            'isdefault' => $this->is_default,
        ));

        Shinka_Template::create($this->asset_dir);
    }

    /**
     * Delete template group and templates
     */
    public function destroy()
    {
        global $db;

        $db->delete_query(self::$table, "prefix = '{$this->prefix}'");

        Shinka_Template::destroy($this->prefix);
    }
}
