<?php

use Framework\Core\Database;

require_once('./actions/core/DataBaseAction.php');


class SqlTable extends DataBaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Action]
    public function Show($dataInput)
    {
        $name =  $dataInput['name'];
        $query = "select * from ".$name;

        $data = $this->db->run($query)->fetchAll(PDO::FETCH_ASSOC);

        $HtmlResult = $this->QueryResultToHtmlTable($data);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: array('search'=>''),
            form: Form::Get(
                title: 'SQl Table: '.$name,
                href: 'internal:SqlTable.Grid',
                menu: MenuEntryArray::Empty(),
                template:  $HtmlResult
            )
        );
    }

    #[Action]
    public function Grid($dataInput)
    {
        $search = $dataInput['search'] ?? '';
        $searchSql = '';

        if (isset($search) && !empty($search)) {
            $searchSql = " and name like '%".$search."%'";
        }
        $query = "SELECT name as 'Table name'
                  FROM sqlite_master 
                  WHERE type='table' ". $searchSql."
                  ORDER BY name";

        $data = $this->db->run($query)->fetchAll(PDO::FETCH_ASSOC);
        $htmlResult = $this->QueryResultToHtmlTable($data);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: array('search'=>$search),
            form: Form::Get(
                title: 'Sql Tables',
                href: '/',
                menu: MenuEntryArray::Empty(),
                template:  $htmlResult
            )
        );
    }

    private function QueryResultToHtmlTable($results)
    {
        $html = '<br/>
                 <form class="col s12" id="sqltable-grid-search">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="search" name="search" type="text" placeholder="" value="{{search}}">
                            <label for="search">Išči</label>
                        </div>
                        <div class="input-field col s12">
                            <a href="internal:SqlTable.Grid?form-id=sqltable-grid-search" class="teal lighten-2 waves-effect waves-light white-text btn w100">Išči</a>
                        </div>
                    </div>
                </form>';

        try {
            if (empty($results)) {
                $html .= '<div class="row">
                            <div class="input-field col s12">
                                <p>No data found.</p>
                            </div>
                        </div>';
                return $html;
            }

            // Start building the HTML table table.class="striped"
            $html .= '<div class="row">
                        <div class="input-field col s12">
                            <table class="highlight responsive-table">
                                <thead>
                                    <tr>';

            // Table headers
            foreach (array_keys($results[0]) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }

            $html .= '</tr>
                    </thead>
                <tbody>';

            // Table rows
            foreach ($results as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $table = htmlspecialchars($cell ?? '');
                    $html .= '<td>
                                <a href="internal:SqlTable.Show?name='.$table.'">'
                                    . $table . '
                                </a>
                             </td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody>
                   </table>
                </div>
            </div>';

            return $html;

        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }
}
