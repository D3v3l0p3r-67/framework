<?php
require_once('./actions/core/DataBaseTableAction.php');

class Counter extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_counter';
    }

    #[Action]
    public function GetPreviewData($this_settlement_id)
    {
        return $this->db->run(
            'select electricity_all as prev_electricity_all, 
                    water_all as prev_water_all,
                    boiler_all as prev_boiler_all,
                    electricity_app2 as prev_electricity_app2,
                    electricity_app3 as prev_electricity_app3
             from doc_counter
             join doc_settlement on doc_settlement.id = doc_counter.settlement_id
             where doc_settlement.id < ' . $this_settlement_id . '
             order by doc_settlement.position desc
             limit 1'
        )->fetch();
    }

    //access by Fk - settlement_id (so filter)
    #[Action]
    public function Show($dataInput)
    {
        //Get last settlement's data for placeholder
        $settlement_id = $dataInput['settlement_id'];
        $dataOutputPrev = self::GetPreviewData($settlement_id);

        //New or Edit: Todo rename method!
        $dataOutput = array();
        if ($this->db->filter($this->table, $dataInput)) {
            $dataOutput = $this->db->filter($this->table, $dataInput)[0];
        }
        //add input parameters to output bag
        $dataOutput = array_merge((array)$dataOutput, (array)$dataInput, (array) $dataOutputPrev);

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForShow(),
            data: $dataOutput,
            form: Form::Get(
                title: 'Popis števcev',
                href: 'internal:Settlement.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:Counter.Upsert?form-id=counter-upsert',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="counter-upsert">
                    <div class="row">
                        <div class="input-field col s12">
                            <input type="hidden" name="id" value="{{id}}">
                            <input type="hidden" name="settlement_id" value="{{settlement_id}}">
                            <input id="electricity_all" name="electricity_all" type="number" placeholder="{{prev_electricity_all}}" value="{{electricity_all}}">
                            <label for="electricity_all">Elektrika skupaj</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="water_all" name="water_all" type="number" placeholder="{{prev_water_all}}" value="{{water_all}}">
                            <label for="water_all">Voda skupaj</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="boiler_all" name="boiler_all" type="number" placeholder="{{prev_boiler_all}}" value="{{boiler_all}}">
                            <label for="boiler_all">Grelnik vode skupaj</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="electricity_app2" name="electricity_app2" type="number" placeholder="{{prev_electricity_app2}}" value="{{electricity_app2}}">
                            <label for="electricity_app2">Elektrika apartma 2</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="electricity_app3" name="electricity_app3" type="number" placeholder="{{prev_electricity_app3}}" value="{{electricity_app3}}">
                            <label for="electricity_app3">Elektrika apartma 3</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <button id="counter_upsert_submit" class="hide waves-effect waves-light btn btn-submit" type="submit">Shrani</button>
                        </div>
                    </div>
                </form>'
            )
        );
    }

    #[Action]
    public function Upsert($data)
    {
        $desiredFields = ['settlement_id', 'electricity_all', 'water_all', 'boiler_all', 'electricity_app2', 'electricity_app3'];
        $queryData = Utils::FilterOutEmptyData($data, $desiredFields);

        if ($data['id']) {
            $this->db->update(
                $this->table,
                $queryData,
                [
                    'id' => $data['id']
                ]
            );
        } else {
            $this->db->insert(
                $this->table,
                $queryData
            );
        }

        return ResponseFactory::CreateOk(
            message: MessageUser::GetForUpsert(),
        );
    }
}
