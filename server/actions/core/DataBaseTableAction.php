<?php
require_once('./core/ResponseFactory.php');
require_once('./actions/core/DataBaseAction.php');

class DataBaseTableAction extends DataBaseAction
{
    protected $table;

    public function __construct()
    {
        parent::__construct();
    }

    public function Get($data = null)
    {
        if (isset($data['id'])) {
            $products = $this->db->getById($this->table, $data['id']);
        } else {
            $products = $this->db->getAll($this->table);
        }
        return ResponseFactory::CreateOk(
            message: new MessageLog('Record retrieved from the database'),
            data: $products
        );
    }

    public function Delete($data)
    {
        if (isset($data['id'])) {
            $id = $this->db->deleteById($this->table, $data['id']);
        } else {
            //todo: Error
        }
        return  ResponseFactory::CreateOk(
            message: new MessageUser('Record deleted successfully'),
            data: array("id" => $id)
        );
    }

    public function Insert($data)
    {
        $id = $this->db->insert($this->table, $data);

        return  ResponseFactory::CreateOk(
            message: new MessageUser('New record created successfully'),
            data: array("id" => $id)
        );
    }

    public function Update($data)
    {
        if (isset($data['id'])) {
            $count = $this->db->update($this->table, $data, ['id' => $data['id']]);
            $message = 'Record updated successfully';
        } else {
            $message = 'Error on update, no id found!';
        }
        return  ResponseFactory::CreateOk(
            message: new MessageUser($message),
            data: array("count" => $count ?? '/')
        );
    }
}
