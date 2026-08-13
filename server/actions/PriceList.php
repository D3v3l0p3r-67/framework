<?php
require_once('./actions/core/DataBaseTableAction.php');

class PriceList extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_pricelist';
    }

    //access by Pk or Fk
    #[Action]
    public function Show($dataInput)
    {
        //New or Edit: Todo rename method!
        $dataOutput = array();
        if ($this->db->filter($this->table, $dataInput)) {
            $dataOutput = $this->db->filter($this->table, $dataInput)[0];
        }
        //add input parameters to output bag
        $dataOutput = array_merge((array)$dataOutput, (array)$dataInput);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: $dataOutput,
            form: Form::Get(
                title: 'Cenik',
                href: 'internal:Settlement.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:PriceList.Upsert?form-id=pricelist-upsert',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="pricelist-upsert">
                    <div class="row">
                        <div class="input-field col s12">
                            <input type="hidden" name="id" value="{{id}}">
                            <input type="hidden" name="settlement_id" value="{{settlement_id}}">
                            <input id="water_price" name="water_price" type="number" placeholder="" value="{{water_price}}">
                            <label for="water_price">Cena vode na m3</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="electricity_price" name="electricity_price" type="number" placeholder="" value="{{electricity_price}}">
                            <label for="electricity_price">Cena elektrike na kw</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="electricity_power_price" name="electricity_power_price" type="number" placeholder="" value="{{electricity_power_price}}">
                            <label for="electricity_power_price">Cena moči elektike na osebo</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="garbage_price" name="garbage_price" type="number" placeholder="" value="{{garbage_price}}">
                            <label for="garbage_price">Cena smeti na osebo</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="other_expenses" name="other_expenses" type="number" placeholder="" value="{{other_expenses}}">
                            <label for="other_expenses">Ostali stroški na osebo</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="rent_app1" name="rent_app1" type="number" placeholder="" value="{{rent_app1}}">
                            <label for="rent_app1">Najemnina apartma 1</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="recourse_assistance_price_app1" name="recourse_assistance_price_app1" type="number" placeholder="" value="{{recourse_assistance_price_app1}}">
                            <label for="recourse_assistance_price_app1">Regres apartma 1</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="tax_app1" name="tax_app1" type="number" placeholder="" value="{{tax_app1}}">
                            <label for="tax_app1">Pribitek apartma 1</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="rent_app2" name="rent_app2" type="number" placeholder="" value="{{rent_app2}}">
                            <label for="rent_app2">Najemnina apartma 2</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="rent_app3" name="rent_app3" type="number" placeholder="" value="{{rent_app3}}">
                            <label for="rent_app3">Najemnina apartma 3</label>
                        </div>
                    </div>
                </form>'
            )
        );
    }
    #[Action]
    public function Upsert($data)
    {
        if ($data['id']) {
            $this->db->update(
                $this->table,
                [
                    'settlement_id' => $data['settlement_id'],
                    'water_price' => $data['water_price'],
                    'electricity_price' => $data['electricity_price'],
                    'electricity_power_price' => $data['electricity_power_price'],
                    'garbage_price' => $data['garbage_price'],
                    'other_expenses' => $data['other_expenses'],
                    'rent_app1' => $data['rent_app1'],
                    'recourse_assistance_price_app1' => $data['recourse_assistance_price_app1'],
                    'tax_app1' => $data['tax_app1'],
                    'rent_app2' => $data['rent_app2'],
                    'rent_app3' => $data['rent_app3']
                ],
                [
                    'id' => $data['id']
                ]
            );
        } else {
            $this->db->insert(
                $this->table,
                [
                    'settlement_id' => $data['settlement_id'],
                    'water_price' => $data['water_price'],
                    'electricity_price' => $data['electricity_price'],
                    'electricity_power_price' => $data['electricity_power_price'],
                    'garbage_price' => $data['garbage_price'],
                    'other_expenses' => $data['other_expenses'],
                    'rent_app1' => $data['rent_app1'],
                    'recourse_assistance_price_app1' => $data['recourse_assistance_price_app1'],
                    'tax_app1' => $data['tax_app1'],
                    'rent_app2' => $data['rent_app2'],
                    'rent_app3' => $data['rent_app3']
                ]
            );
        }

        return ResponseFactory::CreateOk(
            message: MessageUser::GetForUpsert(),
        );
    }
}
