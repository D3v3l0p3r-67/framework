<?php
require_once('./core/Session.php');
require_once('./core/Database.php');
require_once('./core/Logger.php');
require_once('./core/FormFactory.php');
require_once('./core/ResponseFactory.php');

use Framework\Core\Database;
use Framework\Core\Logger;
use Framework\Core\Session;

class ApfType
{
    protected $db;
    protected $name;
    protected $table;
    protected $session;

    public function __construct($table = null)
    {
        $this->name = get_class($this);
        $this->table = $table ?? $this->name;
        $this->db = new Database();
        $this->session = new Session();
    }

    #[Action]
    public function DataGrid($dataInput = null)
    {
        $formKind = 'DataGrid';
        $formName = $this->name . '.' . $formKind;

        $type_id = $dataInput['type_id'] ?? '';
        $filter = $type_id !== '' ? ['type_id' => $type_id] : [];
        $data = $this->db->getAll($this->table, 'id desc', $filter);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForDataGrid(),
            data: $data,
            form: Form::Get(
                title: $formName,
                template: FormFactory::GetFormTemplate($formName)
            )

        );
    }

    #[Action]
    public function Show($dataInput): Response
    {
        $id = $dataInput['id'];
        $data = $this->db->getById($this->table, $id);
        $data->random = time();

        if (isset($data->definition)) {
            $data->definition = base64_encode($data->definition);
        }

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: $data,
            form: Form::Get(
                title: $this->name  . '.Show',
                template: FormFactory::ProcessForm(
                    FormFactory::GetFormTemplate($this->name  . '.Show'),
                    'Show'
                )
            )
        );
    }

    #[Action]
    public function New($dataInput = null)
    {
        $dataInput = (object) ($dataInput ?? []);
        $dataInput->random = time();
        $data = $dataInput;

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForNew(),
            data: $data,
            form: Form::Get(
                title: $this->name  . '.New',
                template: FormFactory::ProcessForm(
                    FormFactory::GetFormTemplate($this->name  . '.Show'),
                    'New'
                )
            )
        );
    }

    function Edit($dataInput)
    {
        $id = $dataInput['id'];
        $data = $this->db->getById($this->table, $id);
        $data->random = time();

        if (isset($data->definition)) {
            $data->definition = base64_encode($data->definition);
        }

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForEdit(),
            data: $data,
            form: Form::Get(
                title: $this->name  . '.Edit',
                template: FormFactory::ProcessForm(
                    FormFactory::GetFormTemplate($this->name  . '.Show'),
                    'Edit'
                )
            )
        );
    }

    function Duplicate($dataInput)
    {
        $id = $dataInput['id'];
        $data = $this->db->getById($this->table, $id);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForDuplicate(),
            data: $data,
            form: Form::Get(
                title: $this->name  . '.Duplicate',
                template: FormFactory::ProcessForm(
                    FormFactory::GetFormTemplate($this->name  . '.Show'),
                    'Duplicate'
                )
            )
        );
    }

    #[Action]
    public function DeleteAndShowGrid($Data)
    {
        self::Delete($Data);
        return self::DataGrid();
    }

    #[Action]
    public function InsertAndShowGrid($dataInput)
    {
        self::Insert($dataInput);
        return self::DataGrid();
    }

    #[Action]
    public function UpdateAndShowGrid($dataInput)
    {
        self::Update($dataInput);
        return self::DataGrid();
    }

    #[Action]
    public function Get($dataInput = null)
    {
        if (isset($dataInput['id'])) {
            $products = $this->db->getById($this->table, $dataInput['id']);
        } else {
            $products = $this->db->getAll($this->table);
        }
        return ResponseFactory::CreateOk(
            message: new MessageLog('Record retrieved from the database'),
            data: $products
        );
    }

    #[Action]
    public function Delete($dataInput)
    {
        if (isset($dataInput['id'])) {
            $id = $this->db->deleteById($this->table, $dataInput['id']);
        } else {
            //todo: Error
        }
        return  ResponseFactory::CreateOk(
            message: new MessageUser('Record deleted successfully'),
            data: array("id" => $id)
        );
    }

    #[Action]
    public function Insert($dataInput)
    {
        //$dataInput['user_id'] = $this->session->getUserId();
        $id = $this->db->insert($this->table, $dataInput);

        return  ResponseFactory::CreateOk(
            message: new MessageUser('New record created successfully'),
            data: array("id" => $id)
        );
    }

    #[Action]
    public function Update($dataInput)
    {
        if (isset($dataInput['id'])) {
            //$dataInput['user_id'] = $this->session->getUserId();
            $count = $this->db->update($this->table, $dataInput, ['id' => $dataInput['id']]);
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
