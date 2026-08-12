<?php
require_once('./actions/core/DataBaseTableAction.php');

class Joke extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'doc_joke';
    }
    //todo: Self => this. Self samo za klic statičnih metod znotraj razreda.
    public function DeleteAndShowGrid($Data)
    {
        self::Delete($Data);
        return self::Grid();
    }

    //TODO: Pass different Message
    public function InsertAndShowGrid($dataInput)
    {
        self::Insert($dataInput);
        return self::Grid();
    }

    public function UpdateAndShowGrid($dataInput)
    {
        self::Update($dataInput);
        return self::Grid();
    }

    function Edit($dataInput)
    {
        $data = $this->db->run('select id, title, description from ' . $this->table . ' where id = '.$dataInput['id'])->fetch();

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForEdit(),
            data: array(
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description']
            ),
            form: Form::Get(
                title: 'Uredi vic',
                href: 'internal:Joke.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:Joke.UpdateAndShowGrid?form-id=joke-edit',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="joke-edit">
                    <input id="id" name="id" type="hidden" value="{{id}}" class="form-field">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="title" name="title" type="text" placeholder="" value="{{title}}" class="form-field">
                            <label for="title">Naslov</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="description" name="description" class="materialize-textarea form-field">{{description}}</textarea>
                            <label for="description">Opis</label>
                        </div>
                    </div>
                </form>'
            )
        );
    }

    function New()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForNew(),
            data: array(),
            form: Form::Get(
                title: 'Dodaj vic',
                href: 'internal:Joke.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Shrani',
                            href: 'internal:Joke.InsertAndShowGrid?form-id=joke-new',
                            icon: Icon::SAVE
                        )
                    ),
                template: '<form class="col s12" id="joke-new">
                    <div class="row">
                        <div class="input-field col s12">
                            <input id="title" name="title" type="text" placeholder="" value="{{title}}" class="form-field">
                            <label for="title">Naslov</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field col s12">
                            <textarea id="description" name="description" class="materialize-textarea form-field">{{description}}</textarea>
                            <label for="description">Opis</label>
                        </div>
                    </div>
                </form>'
            )
        );
    }

    public function Grid()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForGrid(),
            data: $this->db->getAll($this->table, 'id desc'),
            form: Form::Get(
                title: 'Vici',
                href: 'internal:Joke.Grid',
                menu: MenuEntryArray::Empty()
                    ->Add(
                        MenuEntry::Get(
                            title: 'Dodaj',
                            href: 'internal:Joke.New',
                            icon: Icon::ADD
                        )
                    )
                    ->Add(
                        MenuEntry::Get(
                            title: 'Osveži',
                            href: 'internal:Joke.Grid',
                            icon: Icon::REFRESH
                        )
                    ),
                template:  '{{#data}}
                                <div class="row">
                                    <div class="col s12">
                                        <div class="card">
                                            <div class="card-content">
                                                <div class="card-title"> 
                                                     <div >
                                                        {{title}}
                                                        <a class="dropdown-trigger right" href="#" data-target="joke-dropdown-{{id}}">
                                                            <i class="material-icons teal-text text-lighten-1">more_vert</i>
                                                        </a>
                                                        <ul id="joke-dropdown-{{id}}" class="dropdown-content">
                                                            <li>
                                                                <a href="internal:Joke.Edit?id={{id}}">
                                                                    <i class="material-icons teal-text text-lighten-1">edit</i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="internal:Joke.DeleteAndShowGrid?must-confirm=true&id={{id}}">
                                                                    <i class="material-icons teal-text text-lighten-1">delete</i> Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div> 
                                                    {{description}} 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{/data}}'
            )
        );
    }
}