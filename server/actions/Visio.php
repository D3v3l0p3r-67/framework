<?php
require_once('./actions/core/DataBaseTableSessionAction.php');

class Visio extends DataBaseTableSessionAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_visio';
    }
}
