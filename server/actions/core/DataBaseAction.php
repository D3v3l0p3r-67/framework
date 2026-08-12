<?php
require_once('./core/Database.php');
require_once('./actions/core/BaseAction.php');

use Framework\Core\Database;

class DataBaseAction extends BaseAction
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
    }
}
