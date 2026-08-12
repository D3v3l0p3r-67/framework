<?php
require_once('./core/Session.php');
require_once('./actions/core/DataBaseTableAction.php');

use Framework\Core\Database;
use Framework\Core\Session;

class DataBaseTableSessionAction extends DataBaseTableAction
{
    protected $session;

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session();
    }

    public function Insert($data)
    {
        $data['user_id'] = $this->session->getUserId();
        parent::Insert($data);
    }

    public function Update($data)
    {
        $data['user_id'] = $this->session->getUserId();
        parent::Update($data);
    }
}
