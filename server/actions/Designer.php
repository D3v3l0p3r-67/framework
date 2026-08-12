<?php
require_once('./actions/core/DataBaseTableSessionAction.php');

class Designer extends DataBaseTableSessionAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_designer';
    }

    public function List()
    {
        $products = $this->db->get($this->table, "id, name, preview");

        return ResponseFactory::CreateOk(
            message: new MessageLog('Record retrieved from the database'),
            data: $products
        );
    }
}
