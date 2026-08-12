<?php
require_once('./actions/core/DataBaseTableAction.php');

class Settlement extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_settlement';
    }

    public function DeleteAndShowGrid($Data)
    {
        self::Delete($Data);
        return self::Grid();
    }

    //TODO: Pass different Message
    public function CreateAndShowGrid($dataInput)
    {
        self::Create($dataInput);
        return self::Grid();
    }

    //TODO: Pass different Message
    public function CreateAndShow($dataInput)
    {
        $dataOutput = self::Create($dataInput);
        return self::Show($dataOutput);
    }

    public function Create($dataInput)
    {
        //fet data from db
        $data = $this->db->run('select id, year, month from ' . $this->table . ' order by position desc limit 1')->fetch();
        $last_id = $data['id'];

        //get data from input
        $this_year = $dataInput['year'];
        $this_month =  $dataInput['month'];
        $this_title = $dataInput['title'];
        $this_position = $this_year * 100 + $this_month;

        //create document
        $this_id = $this->db->insert(
            $this->table,
            [
                'title' => $this_title,
                'month' => $this_month,
                'year' => $this_year,
                'position' => $this_position
            ]
        );

        //copy pricelist
        $this->db->run('insert into doc_pricelist (settlement_id, water_price, electricity_price, electricity_power_price, garbage_price, other_expenses, rent_app1, recourse_assistance_price_app1, tax_app1, rent_app2, rent_app3)
                  select ' . $this_id . ', water_price, electricity_price, electricity_power_price, garbage_price, other_expenses, rent_app1, recourse_assistance_price_app1, tax_app1, rent_app2, rent_app3
                  from doc_pricelist
                  where settlement_id = ' . $last_id);

        return array('id' => $this_id);
    }

    function New()
    {
        $data = $this->db->run('select id, year, month from ' . $this->table . ' order by position desc limit 1')->fetch();

        $last_year = $data['year'];
        $last_month = $data['month'];
        $this_year = $last_year;
        $this_month =  $last_month + 1;
        if ($this_month == 13) {
            $this_year = $this_year + 1;
            $this_month = 1;
        }
        $this_title = "Obračun " . Utils::addLeadingZero($this_month) . "/" . $this_year;

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForNew(),
            data: array(
                'year' => strval($this_year),
                'month' => strval($this_month),
                'title' => $this_title
            ),
            form: Form::Get(
                title: 'Nov obračun',
                href: 'internal:Settlement.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:Settlement.CreateAndShowGrid?form-id=settlement-new',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="settlement-new">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="title" name="title" type="text" placeholder="" value="{{title}}">
                            <label for="title">Naslov</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s6">
                            <input id="year" name="year" type="number" placeholder="" value="{{year}}">
                            <label for="year">Leto</label>
                        </div>
                        <div class="input-field col s6">
                        <input id="month" name="month" type="number" placeholder="" value="{{month}}">
                        <label for="month">Mesec</label>
                        </div>
                    </div>
                </form>'
            )
        );
    }

    public function Show($data)
    {
        $dataOutput = $this->db->getById($this->table, $data['id']);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: $dataOutput,
            form: Form::Get(
                title: $dataOutput->title,
                href: 'internal:Settlement.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Izbriši',
                            href: 'internal:Settlement.DeleteAndShowGrid?must-confirm=true&id=' . $dataOutput->id,
                            icon: Icon::DELETE
                        )
                    )/*
                    ->Add(
                        MenuEntry::Get(
                            title: 'Osveži',
                            href: 'internal:Settlement.Show?id=' . $dataOutput->id,
                            icon: Icon::REFRESH
                        )
                    )*/,
                template: '<ul id="collapsible" class="collapsible expandable">
                                <li class="active">
                                    <div class="collapsible-header section-header">
                                        <i class="material-icons">av_timer</i>
                                        <span class="page-title width-full"></span>
                                        <a href="" class="page-action right">
                                            <i class="small material-icons form-submit-icon right"></i>
                                        </a>
                                    </div>
                                    <div class="collapsible-body">
                                        <include actionkey="Counter.Show?settlement_id={{id}}" />
                                    </div>
                                </li>
                            </ul>
                            <ul id="collapsible" class="collapsible expandable">
                                <li class="active">
                                    <div class="collapsible-header section-header">
                                        <i class="material-icons">av_timer</i>
                                        <span class="page-title width-full"></span>
                                        <a href="" class="page-action right">
                                            <i class="small material-icons form-submit-icon right"></i>
                                        </a>
                                    </div>
                                    <div class="collapsible-body">
                                        <include actionkey="PriceList.Show?settlement_id={{id}}" />
                                    </div>
                                </li>
                            </ul>
                            <ul id="collapsible" class="collapsible expandable">
                                <li class="active">
                                    <div class="collapsible-header section-header">
                                        <i class="material-icons">av_timer</i>
                                        <span class="page-title width-full"></span>
                                        <a href="" class="page-action right">
                                            <i class="small material-icons form-submit-icon right"></i>
                                        </a>
                                    </div>
                                    <div class="collapsible-body collapsible-body-compact">
                                        <include actionkey="SettlementCalculations.Consumptions?settlement_id={{id}}" />
                                    </div>
                                </li>
                            </ul>
                            <ul id="collapsible" class="collapsible expandable">
                                <li class="active">
                                    <div class="collapsible-header section-header">
                                        <i class="material-icons">av_timer</i>
                                        <span class="page-title width-full"></span>
                                        <a href="" class="page-action right">
                                            <i class="small material-icons form-submit-icon right"></i>
                                        </a>
                                    </div>
                                    <div class="collapsible-body collapsible-body-compact">
                                        <include actionkey="SettlementCalculations.Costs?settlement_id={{id}}" />
                                    </div>
                                </li>
                            </ul>
                            <ul id="collapsible" class="collapsible expandable">
                                <li class="active">
                                    <div class="collapsible-header section-header">
                                        <i class="material-icons">av_timer</i>
                                        <span class="page-title width-full"></span>
                                        <a href="" class="page-action right">
                                            <i class="small material-icons form-submit-icon right"></i>
                                        </a>
                                    </div>
                                    <div class="collapsible-body collapsible-body-compact">
                                        <include actionkey="SettlementCalculations.Payments?settlement_id={{id}}" />
                                    </div>
                                </li>
                            </ul>
                            <ul id="collapsible" class="collapsible expandable">
                                <li class="active">
                                    <div class="collapsible-header section-header">
                                        <i class="material-icons">av_timer</i>
                                        <span class="page-title width-full"></span>
                                        <a href="" class="page-action right">
                                            <i class="small material-icons form-submit-icon right"></i>
                                        </a>
                                    </div>
                                    <div class="collapsible-body collapsible-images padding-1rem">
                                        <include actionkey="SettlementCalculations.Instructions" />
                                    </div>
                                </li>
                            </ul>'
            )
        );
    }

    public function Grid()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForGrid(),
            data: $this->db->getAll($this->table, 'position desc'),
            form: Form::Get(
                title: 'Obračuni',
                href: 'internal:Settlement.New',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Dodaj',
                            href: 'internal:Settlement.New',
                            icon: Icon::ADD
                        )
                    )
                    ->Add(
                        MenuEntry::Get(
                            title: 'Osveži',
                            href: 'internal:Settlement.Grid',
                            icon: Icon::REFRESH
                        )
                    ),
                template: '<table class="highlight white">
                    <tbody>
                        {{#data}}
                            <tr>
                                <td>
                                    <a href="internal:Settlement.Show?id={{id}}">
                                        <div class="valign-wrapper">
                                            <i class="material-icons">chevron_right</i>  
                                            {{title}}
                                        </div>
                                    </a>
                                </td>
                            </tr>
                        {{/data}}
                    </tbody>
                </table>'
            )
        );
    }
}
