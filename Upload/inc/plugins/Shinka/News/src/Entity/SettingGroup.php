<?php

class Shinka_SettingGroup
{
    /** @var string */
    private static $table = "settinggroups";

    /** @var string */
    public $name;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    public function __construct($name, $title, $description)
    {
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * @return void
     */
    public function create()
    {
        global $db;

        $this->gid = $db->insert_query(self::$table, array(
            "name" => $this->name,
            "title" => $this->title,
            "description" => $this->description,
        ));

        Shinka_Setting::create($this->gid);
    }

    /**
     * @return void
     */
    public function destroy()
    {
        global $db;

        $db->delete_query('templategroups', "prefix = '{$this->prefix}'");

        Shinka_Template::destroy($this->prefix);
    }
}
