<?php

use Framework\Core\Database;

require_once('./actions/core/DataBaseAction.php');

class SqlEditor extends DataBaseAction
{
    protected $queryResultContainerId = 'query-result-container';

    public function __construct()
    {
        parent::__construct();
    }

    #[Action]
    public function Show($dataInput)
    {
        $name = "SqlEditor.Show";
        $query = "";

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: array('query' => $query),
            form: Form::Get(
                title: 'Sql Editor',
                href: '/',
                menu: MenuEntryArray::Empty(),
                template:  '<form class="col s12" id="sqleditor-show">
                                <div class="row">
                                    <div class="input-field col s12">
                                        <textarea id="query" name="query" class="form-field materialize-textarea">{{query}}</textarea>
                                        <label for="query">Query</label>
                                    </div>
                                    <div class="input-field col s12">
                                        <a href="internal:SqlEditor.ExecuteQuery?form-id=sqleditor-show" class="teal lighten-2 waves-effect waves-light white-text btn w100">execute query</a>
                                    </div>
                                </div>
                            </form>
                            <div id="'.$this->queryResultContainerId.'">
                                <include actionkey="SqlEditor.GetAllTablesInDatabase" />
                            </div>'
            )
        );
    }

    #[Action]
    public function ExecuteQuery($dataInput)
    {
        $query = $dataInput['query'];

        $data = $this->db->run($query)->fetchAll(PDO::FETCH_ASSOC);

        $HtmlResult = $this->QueryResultToHtmlTable($data);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: array('query'=>''),
            form: Form::Get(
                title: 'SQl Editor',
                target: $this->queryResultContainerId,
                href: '/',
                menu: MenuEntryArray::Empty(),
                template:  $HtmlResult
            )
        );
    }

    #[Action]
    public function GetAllTablesInDatabase()
    {
        $query = "SELECT name as 'Table name'
                  FROM sqlite_master 
                  WHERE type='table' 
                  ORDER BY name";
        $data = $this->db->run($query)->fetchAll(PDO::FETCH_ASSOC);
        $htmlResult = $this->QueryResultToHtmlTable($data);
        $htmlResult = $this->AddLinksToHtmlTable($htmlResult);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: array('query'=>''),
            form: Form::Get(
                title: 'SQl Editor',
                target: $this->queryResultContainerId,
                href: '/',
                menu: MenuEntryArray::Empty(),
                template:  $htmlResult
            )
        );
    }

    private function AddLinksToHtmlTable($htmlTable)
    {
        $htmlTable = preg_replace_callback(
            '/<td>(.*?)<\/td>/i',
            function ($matches) {
                $content = $matches[1];
                return "<td><a href='internal:SqlEditor.ExecuteQuery?query=select * from ".$content."'>$content</a></td>";
            },
            $htmlTable
        );

        return $htmlTable;
    }

    private function QueryResultToHtmlTable($results)
    {
        try {
            if (empty($results)) {
                return '<p>No data found.</p>';
            }

            // Start building the HTML table table.class="striped"
            $html = '<br/>
                        <div class="row">
                            <div class="input-field col s12">
                                <table class="highlight responsive-table">';
            $html .= '<thead>
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
                    $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
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
