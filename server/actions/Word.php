<?php
require_once('./actions/core/DataBaseTableSessionAction.php');

class Word extends DataBaseTableSessionAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_word';
    }
}
