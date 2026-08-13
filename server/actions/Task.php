<?php

use Framework\Core\Logger;

require_once('./actions/core/DataBaseTableAction.php');
require_once __DIR__ . '/../core/FormFactory.php';
require_once __DIR__ . '/../core/vendor/tcpdf/tcpdf.php'; //todo: instead include core/Report.php

class Task extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_task';
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
    public function DataGrid()
    {
        $data = $this->db->getAll($this->table, 'id desc');

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForDataGrid(),
            data: $data,
            form: Form::Get(
                title: 'Task.DataGrid',
                template: '<nav>
                                <div class="nav-wrapper" id="main-toolbar">
                                    <a href="#" data-target="slide-out" class="sidenav-trigger white-text">
                                        <i class="material-icons">menu</i>
                                    </a>
                                    <span class="toolbar-title white-text fancy-text-shadow">
                                        Task.DataGrid
                                    </span>
                                    <ul class="right menu">
                                        <li>
                                            <a href="internal:Task.New">
                                                <i class="material-icons white-text">add</i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <div class="row">
                                <div class="col s12">
                                    <div id="Task-DataGrid">
                                        <table class="responsive-table highlight">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th style="width:130px;">Updated</th>
                                                    <th style="width:130px;">Created</th>
                                                    <th style="width:10px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{#data}}
                                                <tr>
                                                    <td> 
                                                        <a href="internal:Task.Show?id={{id}}">
                                                            {{name}}
                                                        </a> 
                                                    </td>
                                                    <td> {{description}} </td>
                                                    <td> {{status}} </td>
                                                    <td> {{#formatDate}} {{updated}} {{/formatDate}} </td>
                                                    <td> {{#formatDate}} {{created}} {{/formatDate}} </td>
                                                    <td> 
                                                        <a class="dropdown-trigger right" href="javascript:void(0);" data-target="Task.DataGridDropDown{{id}}">
                                                            <i class="material-icons teal-text text-lighten-1">more_vert</i>
                                                        </a>
                                                        <ul id="Task.DataGridDropDown{{id}}" class="dropdown-content">
                                                            <li>
                                                                <a href="internal:Task.Show?id={{id}}">
                                                                    <i class="material-icons teal-text text-lighten-1">visibility</i> Show
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="internal:Task.Edit?id={{id}}">
                                                                    <i class="material-icons teal-text text-lighten-1">edit</i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="internal:Task.Duplicate?id={{id}}">
                                                                    <i class="material-icons teal-text text-lighten-1">content_copy</i> Duplicate
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="https://dev.tittlus.com/framework/server/?actionKey=Task.Report&id={{id}}" target="_blank">
                                                                    <i class="material-icons teal-text text-lighten-1">picture_as_pdf</i> Report
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="internal:Task.DeleteAndShowGrid?must-confirm=true&id={{id}}">
                                                                    <i class="material-icons teal-text text-lighten-1">delete</i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </td>
                                                </tr>
                                                {{/data}}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>'
            )
        );
    }

    /**
     * /
     * @param mixed $dataInput
     * @return Response
     */
    #[Action]
    public function Show($dataInput): Response
    {
        $id = $dataInput['id'];
        $data = $this->db->getById($this->table, $id);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: $data,
            form: Form::Get(
                title: 'Task.Show',
                template: '<nav>
                                <div class="nav-wrapper" id="main-toolbar">
                                    <a href="#" data-target="slide-out" class="sidenav-trigger white-text">
                                        <i class="material-icons">menu</i>
                                    </a>
                                    <span class="toolbar-title white-text fancy-text-shadow">
                                        Task.Show
                                    </span>
                                    <ul class="right menu">
                                        <li>
                                            <a href="internal:Task.Edit?id={{id}}">
                                                <i class="material-icons white-text">edit</i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="internal:Task.Duplicate?id={{id}}">
                                                <i class="material-icons white-text">content_copy</i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="internal:Task.Report?id={{id}}">
                                                <i class="material-icons white-text">picture_as_pdf</i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <form class="col s12" id="Task-Show">
                                <input id="id" name="id" type="hidden" class="form-field" value="{{id}}" />
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="name" name="name" type="text" class="form-field validate" value="{{name}}" disabled>
                                        <label for="name">Name</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <textarea id="description" name="description" class="form-field materialize-textarea" disabled>{{description}}</textarea>
                                        <label for="description">Description</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="status" name="status" type="text" class="form-field validate" value="{{status}}" disabled>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </form>'
            )
        );
    }

    /**
     * /
     * @return Response
     */
    #[Action]
    public function New()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForNew(),
            data: array(),
            form: Form::Get(
                title: 'Task.New',
                template: '<nav>
                                <div class="nav-wrapper" id="main-toolbar">
                                    <a href="#" data-target="slide-out" class="sidenav-trigger white-text">
                                        <i class="material-icons">menu</i>
                                    </a>
                                    <span class="toolbar-title white-text fancy-text-shadow">
                                        Task.New
                                    </span>
                                    <ul class="right menu">
                                        <li>
                                            <a href="internal:Task.InsertAndShowGrid?form-id=Task-New">
                                                <i class="material-icons white-text">save</i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <form class="col s12" id="Task-New">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="name" name="name" type="text" class="form-field validate" required>
                                        <label for="name">Name</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <textarea id="description" name="description" class="form-field materialize-textarea validate"></textarea>
                                        <label for="description">Description</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="status" name="status" type="text" class="form-field validate" required>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </form>'
            )
        );
    }

    /**
     * /
     * @param mixed $dataInput
     * @return Response
     */
    function Edit($dataInput)
    {
        $id = $dataInput['id'];
        $data = $this->db->getById($this->table, $id);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForEdit(),
            data: $data,
            form: Form::Get(
                title: 'Task.Edit',
                template: '<nav>
                                <div class="nav-wrapper" id="main-toolbar">
                                    <a href="#" data-target="slide-out" class="sidenav-trigger white-text">
                                        <i class="material-icons">menu</i>
                                    </a>
                                    <span class="toolbar-title white-text fancy-text-shadow">
                                        Task.Edit
                                    </span>
                                    <ul class="right menu">
                                        <li>
                                            <a href="internal:Task.UpdateAndShowGrid?form-id=Task-Edit">
                                                <i class="material-icons white-text">save</i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <form class="col s12" id="Task-Edit">
                                <input id="id" name="id" type="hidden" class="form-field" value="{{id}}">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="name" name="name" type="text" class="form-field validate" value="{{name}}" required>
                                        <label for="name">Name</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <textarea id="description" name="description" class="form-field materialize-textarea validate">{{description}}</textarea>
                                        <label for="description">Description</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="status" name="status" type="text" class="form-field validate" value="{{status}}" required>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </form>'
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
                title: 'Task.Duplicate',
                template: '<nav>
                                <div class="nav-wrapper" id="main-toolbar">
                                    <a href="#" data-target="slide-out" class="sidenav-trigger white-text">
                                        <i class="material-icons">menu</i>
                                    </a>
                                    <span class="toolbar-title white-text fancy-text-shadow">
                                        Task.Duplicate
                                    </span>
                                    <ul class="right menu">
                                        <li>
                                            <a href="internal:Task.InsertAndShowGrid?form-id=Task-Duplicate">
                                                <i class="material-icons white-text">save</i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                            <form class="col s12" id="Task-Duplicate">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="name" name="name" type="text" class="form-field validate" value="{{name}}" required>
                                        <label for="name">Name</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <textarea id="description" name="description" class="form-field materialize-textarea">{{description}}</textarea>
                                        <label for="description">Description</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="input-field col s12">
                                        <input id="status" name="status" type="text" class="form-field validate" value="{{status}}" required>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </form>'
            )
        );
    }



    #[Action]
    public function Report($dataInput)
    {
        $id = $dataInput['id'];
        $data = $this->db->getById($this->table, $id);


        $pdf = new TCPDF();
        // Dodajte stran
        $pdf->AddPage();

        // Dodajte besedilo
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'Name:' . $data->name);
        $pdf->Ln();
        $pdf->Write(0, 'Description:' . $data->description);
        $pdf->Ln();
        $pdf->Write(0, 'Status:' . $data->status);
        $pdf->Ln();

        $fileName = $this->name . '_' . $id . '.pdf';
        $pdf->Output($fileName, 'D');

        /*
        'I': Display the PDF inline in the browser.
        'D': Force download.
        'F': Save to a file on the server.
        'S': Return the PDF as a string.
        */
        exit();
    }
}
