<?php
require_once('./actions/core/DataBaseTableAction.php');

class SettlementCalculations extends DataBaseAction
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Action]
    public function GetConsumptionsData($dataInput)
    {
        $t = $this->db->run(
            'select doc_counter.id, settlement_id, electricity_all, water_all, boiler_all, electricity_app2, electricity_app3
             from doc_counter
             join doc_settlement on doc_settlement.id = doc_counter.settlement_id
             where doc_settlement.id = ' . $dataInput['settlement_id']
        )->fetch();

        $p = $this->db->run(
            'select doc_counter.id, settlement_id, electricity_all, water_all, boiler_all, electricity_app2, electricity_app3
             from doc_counter
             join doc_settlement on doc_settlement.id = doc_counter.settlement_id
             where doc_settlement.id < ' . $dataInput['settlement_id'] . '
             order by doc_settlement.position desc
             limit 1'
        )->fetch();

        //calculations
        $c = array(
            'electricity_all' => $t['electricity_all'] - $p['electricity_all'],
            'water_all' => $t['water_all'] - $p['water_all'],
            'boiler_all' => $t['boiler_all'] - $p['boiler_all'],
            'electricity_app3' => $t['electricity_app3'] - $p['electricity_app3'],
            'electricity_app2' => $t['electricity_app2'] - $p['electricity_app2'],
        );
        $c['water_one'] = $c['water_all'] / 3;
        $c['boiler_one'] = $c['boiler_all'] / 3;
        $c['electricity_app1'] = $c['electricity_all'] - $c['boiler_all'] - $c['electricity_app3'] - $c['electricity_app2'];

        //rounds
        $r = Utils::RoundArrayValues($c);

        //tooltips
        $r['electricity_all_tooltip'] = "{$t['electricity_all']} - {$p['electricity_all']} = {$r['electricity_all']}";
        $r['water_all_tooltip'] = "{$t['water_all']} - {$p['water_all']} = {$r['water_all']}";
        $r['water_one_tooltip'] = "{$r['water_all']} : 3 = {$r['water_one']}";
        $r['boiler_all_tooltip'] = "{$t['boiler_all']} - {$p['boiler_all']} = {$r['boiler_all']}";
        $r['boiler_one_tooltip'] = "{$r['boiler_all']} / 3 = {$r['boiler_one']}";
        $r['electricity_app1_tooltip'] = "{$r['electricity_all']} - {$r['boiler_all']} - {$r['electricity_app3']} - {$r['electricity_app2']} = {$r['electricity_app1']}";
        $r['electricity_app2_tooltip'] = "{$t['electricity_app2']} - {$p['electricity_app2']} = {$r['electricity_app2']}";
        $r['electricity_app3_tooltip'] = "{$t['electricity_app3']} - {$p['electricity_app3']} = {$r['electricity_app3']}";

        return $r;
    }

    #[Action]
    public function GetPriceListData($dataInput)
    {
        $t = $this->db->run(
            'select id, settlement_id, water_price, electricity_price, electricity_power_price, garbage_price, other_expenses, rent_app1, recourse_assistance_price_app1, tax_app1, rent_app2, rent_app3
             from doc_pricelist
             where settlement_id = ' . $dataInput['settlement_id']
        )->fetch();

        return $t;
    }
    #[Action]
    public function GetCostsData($dataInput)
    {
        $t = self::GetConsumptionsData($dataInput);
        $p = self::GetPriceListData($dataInput);

        $t = array_merge((array)$t, (array)$p);

        //calculations
        $c = array(
            'electricity_app3_payment' => (($t['electricity_app3'] + $t['boiler_one']) * $t['electricity_price']) + $t['electricity_power_price'],
            'electricity_app2_payment' => (($t['electricity_app2'] + $t['boiler_one']) * $t['electricity_price']) + $t['electricity_power_price'],
            'electricity_app1_payment' => (($t['electricity_app1'] + $t['boiler_one']) * $t['electricity_price']) + $t['electricity_power_price'],
            'water_payment' => $t['water_one'] * $t['water_price']
        );
        $c['water_garbage_other_payment'] = $c['water_payment'] + $t['garbage_price'] + $t['other_expenses'];

        //rounds
        $r = Utils::RoundArrayValues($c);

        //tooltips
        $r['water_payment_tooltip'] = "{$t['water_one']} * {$t['water_price']} = {$r['water_payment']}";
        $r['water_garbage_other_payment_tooltip'] = "{$r['water_payment']} + {$t['garbage_price']} + {$t['other_expenses']} = {$r['water_garbage_other_payment']}";
        $r['electricity_app1_payment_tooltip'] = "(({$t['electricity_app1']} + {$t['boiler_one']}) * {$t['electricity_price']}) + {$t['electricity_power_price']} =  {$r['electricity_app1_payment']}";
        $r['electricity_app2_payment_tooltip'] = "(({$t['electricity_app2']} + {$t['boiler_one']}) * {$t['electricity_price']}) + {$t['electricity_power_price']}  =  {$r['electricity_app2_payment']}";
        $r['electricity_app3_payment_tooltip'] = "(({$t['electricity_app3']} + {$t['boiler_one']}) * {$t['electricity_price']}) + {$t['electricity_power_price']} =  {$r['electricity_app3_payment']}";

        return $r;
    }

    //Poraba
    #[Action]
    public function Consumptions($dataInput)
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForName('Consumptions'),
            data: self::GetConsumptionsData($dataInput),
            form: array(
                'title' => 'Poraba',
                'template' =>
                '<table class="table-results">
                    <tbody>
                        <tr>
                            <td>Elektrika skupaj</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_all_tooltip}}">
                                    {{electricity_all}} kw
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Voda skupaj</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{water_all_tooltip}}">
                                    {{water_all}} m&sup3;
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Voda vsak</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{water_one_tooltip}}">
                                    {{water_one}} m&sup3;
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Boiler skupaj</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{boiler_all_tooltip}}">
                                    {{boiler_all}} kw
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Boiler vsak</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{boiler_one_tooltip}}">
                                    {{boiler_one}} kw
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Elektrika Apartma 1</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_app1_tooltip}}">
                                    {{electricity_app1}} kw
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Elektrika Apartma 2</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_app2_tooltip}}">
                                    {{electricity_app2}} kw
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Elektrika Apartma 3</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_app3_tooltip}}">
                                    {{electricity_app3}} kw
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>'
            )
        );
    }

    //Poraba
    #[Action]
    public function Costs($dataInput)
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForName('Costs'),
            data: self::GetCostsData($dataInput),
            form: array(
                'title' => 'Stroški',
                'template' =>
                '<table class="table-results">
                    <tbody>
                        <tr>
                            <td>Voda</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{water_payment_tooltip}}">
                                    {{water_payment}} €
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Voda, Smeti, Ostalo</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{water_garbage_other_payment_tooltip}}">
                                    {{water_garbage_other_payment}} €
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Elektrika Apartma 1</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_app1_payment_tooltip}}">
                                    {{electricity_app1_payment}} €
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Elektrika Apartma 2</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_app2_payment_tooltip}}">
                                    {{electricity_app2_payment}} €
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Elektrika Apartma 3</td>
                            <td>
                                <a class="tooltipped" data-position="left" data-tooltip="{{electricity_app3_payment_tooltip}}">
                                    {{electricity_app3_payment}} €
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>'
            )
        );
    }


    #[Action]
    public function GetPaymentsData($dataInput)
    {
        $c1 = self::GetConsumptionsData($dataInput);
        $c2 = self::GetCostsData($dataInput);
        $c3 = self::GetPriceListData($dataInput);

        $t = array_merge((array)$c1, (array)$c2, (array)$c3);

        //calculations
        $c = array(
            'app3_final_payment' => $t['electricity_app3_payment'] + $t['water_garbage_other_payment'] + $t['rent_app3'],
            'app2_final_payment' => $t['electricity_app2_payment'] + $t['water_garbage_other_payment'] + $t['rent_app2'],
            'app1_final_payment' => $t['electricity_app1_payment'] + $t['water_garbage_other_payment'] + $t['rent_app1'] + $t['tax_app1'] - $t['recourse_assistance_price_app1']
        );

        //rounds
        $r = Utils::RoundArrayValues($c);

        //tooltips
        $r['app1_final_payment_tooltip'] = "(({$t['electricity_app1_payment']} + {$t['water_garbage_other_payment']}) + {$t['rent_app1']}) + {$t['tax_app1']} - {$t['recourse_assistance_price_app1']} =  {$r['app1_final_payment']}";
        $r['app2_final_payment_tooltip'] = "(({$t['electricity_app2_payment']} + {$t['water_garbage_other_payment']}) + {$t['rent_app2']}) =  {$r['app2_final_payment']}";
        $r['app3_final_payment_tooltip'] = "(({$t['electricity_app3_payment']} + {$t['water_garbage_other_payment']}) + {$t['rent_app3']}) =  {$r['app3_final_payment']}";

        return $r;
    }
    //Poraba
    #[Action]
    public function Payments($dataInput)
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForName('Payments'),
            data: self::GetPaymentsData($dataInput),
            form: array(
                'title' => 'Plačila',
                'template' =>
                '<table class="table-results">
                    <tr>
                        <td>Apartma 1</td>
                        <td>
                            <a class="tooltipped" data-position="left" data-tooltip="{{app1_final_payment_tooltip}}">
                                {{app1_final_payment}} €
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Apartma 2</td>
                        <td>
                            <a class="tooltipped" data-position="left" data-tooltip="{{app2_final_payment_tooltip}}">
                                {{app2_final_payment}} €
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Apartma 3</td>
                        <td>
                            <a class="tooltipped" data-position="left" data-tooltip="{{app3_final_payment_tooltip}}">
                                {{app3_final_payment}} €
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>'
            )
        );
    }

    #[Action]
    public function Instructions()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForName('Instructions'),
            data: array(),
            form: array(
                'title' => 'Navodila',
                'template' =>
                '<div>1. Vzunaj zgoraj levo je elektrika za celotno hišo. Izpišemo porabo za L1 1.80</div>
                <img src="./images/image01.png" />
                <div>2. Vzunaj spodaj, čisto spodnji števec je poraba vode za celotno hišo.</div>
                <img src="./images/image02.png" />
                <div>3. Števec v 1/1 (Albina) je poraba bojlerja za celotno hišo.</div>
                <div>4. Števec v 2/1 (Radi) zgornji je poraba elektrika samo zanj.</div>
                <div>5. Števec v 3/1 (Jernej) je poraba elektrike samo zanj.</div>
                <img src="./images/image03.png" />
                <div>6. Vse števce beremo samo cele vrednosti, brez decimalk, rdečih vrednosti.</div>'
            )
        );
    }
}
