<?php
require_once('./core/Database.php');

use Framework\Core\Database;

class Table
{
    private $db;

    function __construct()
    {
        $this->db = new Database();
    }

    function Create($name, $fields)
    {
        $sql = "CREATE TABLE $name (";
        foreach ($fields as $key => $value) {
            $sql .= $key . " " . $value . ",";
        }
        $sql = rtrim($sql, ",");
        $sql .= ")";
        $this->db->raw($sql);
        echo 1;
    }

    function Drop($name)
    {
        $sql = "DROP TABLE IF EXISTS $name";
        $this->db->raw($sql);
        echo 1;
    }

    function Truncate($name)
    {
        $sql = "TRUNCATE TABLE $name";
        $this->db->raw($sql);
        echo 1;
    }
}
