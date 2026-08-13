<?php

require_once  __DIR__ .'/core/DataBaseTableAction.php';
require_once __DIR__ . '/../core/vendor/tcpdf/tcpdf.php';

class Test extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_test';
    }


    #[Action]
    public function Report()
    {
        header('Content-Type: application/pdf');

        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => true,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );

        $pdf = new TCPDF();
        // Dodajte stran
        $pdf->AddPage();

        // Dodajte besedilo
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'Hello, TCPDF!');

        $pdf->Ln();

        $pdf->Cell(0, 0, 'EAN 13', 0, 1);
        $pdf->write1DBarcode('1234567890128', 'EAN13', '', '', '', 18, 0.4, $style, 'N');
        $pdf->Cell(0, 0, '1234567890128', 0, 1);

        // Izpišite ali prenesite PDF
        $pdf->Output('example.pdf', 'I');

        /*
        'I': Display the PDF inline in the browser.
        'D': Force download.
        'F': Save to a file on the server.
        'S': Return the PDF as a string.
        */
        exit();
    }


    #[Action]
    public function Grid()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForGrid(),
            data: $this->db->getAll($this->table, 'id desc'),
            form: Form::Get(
                title: 'Test.Grid',
                href: 'internal:Test.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Dodaj',
                            href: 'internal:Test.New',
                            icon: Icon::ADD
                        )
                    )
                    ->Add(
                        MenuEntry::Get(
                            title: 'Osveži',
                            href: 'internal:Test.Grid',
                            icon: Icon::REFRESH
                        )
                    ),
                template:  '<a href="https://dev.tittlus.com/framework/server/?actionKey=Test.Report" target="_blank"> Report </a>
                            <iframe id="pdf-viewer" src="https://dev.tittlus.com/framework/server/?actionKey=Test.Report"
                                    width="300px"
                                    height="600px"
                                    style="border: none;">
                            </iframe>
                            <div class="row">
                                <div class="col s12">
                                    <table>
                                        <thead class="sticky-html-header">
                                            <tr>
                                            <th>ID</th>
                                            <th>Varchar Field</th>
                                            <th>Date Field</th>
                                            <th>Datetime Field</th>
                                            <th>Boolean Field</th>
                                            <th>Decimal Field</th>
                                            <th>Created Datetime</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{#data}}
                                            <tr>
                                                <td> {{id}} </td>
                                                <td> {{varchar_field}} </td>
                                                <td> {{date_field}} </td>
                                                <td> {{datetime_field}} </td>
                                                <td> {{bool_field}} </td>
                                                <td> {{decimal_field}} </td>
                                                <td> {{created_datetime}} </td>
                                            </tr>
                                            {{/data}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>'
            )
        );
    }

    #[Action]
    public function InsertAndShowGrid($dataInput)
    {
        $this->Insert($dataInput);
        return $this->Grid();
    }

    #[Action]
    public function New()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForNew(),
            data: array(),
            form: Form::Get(
                title: 'Dodaj test',
                href: 'internal:Test.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:Test.InsertAndShowGrid?form-id=test-new',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="test-new">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="varchar_field" name="varchar_field" type="text" class="validate" required>
                            <label for="varchar_field">Varchar Field</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="date_field" name="date_field" type="date" required>
                            <label for="date_field">Date Field</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="datetime_field" name="datetime_field" type="datetime-local" required>
                            <label for="datetime_field">Datetime Field</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input type="text" id="bool_field" name="bool_field" />
                            <label for="bool_field">Bool Field</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="decimal_field" name="decimal_field" type="number" step="1" class="validate" required>
                            <label for="decimal_field">Decimal Field (19,6)</label>
                        </div>
                    </div>
                </form>'
            )
        );
    }
}
