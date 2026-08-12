<?php
require_once('./actions/core/DataBaseTableAction.php');

class Link extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_link';
    }
}
