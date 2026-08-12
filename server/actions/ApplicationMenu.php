<?php
require_once('./actions/core/DataBaseTableAction.php');

class ApplicationMenu extends DataBaseTableAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'fw_application_menu';
    }

    public function Test()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForGrid(),
            data: '',
            form: Form::Get(
                title: 'AppMenu',
                target: 'test',
                href: '/',
                menu: MenuEntryArray::Empty(),
                template:  'Response from server' 
            )
        );
    }

    public function Grid()
    {
        return ResponseFactory::CreateOk(
            message: MessageLog::GetForGrid(),
            data: $this->db->getAll($this->table, 'id'),
            form: Form::Get(
                title: 'AppMenu',
                target: 'sidenav', //To vrača napako!!!
                href: '/',
                menu: MenuEntryArray::Empty(),
                template:  '<ul id="slide-out" class="sidenav sidenav-fixed">
                                <li>
                                    <div class="user-view">
                                        <div class="background">
                                            <img src="images/sidebar-bcg.png">
                                        </div>
                                        <a href="#user">
                                            <img class="circle" src="images/avatar.jpg">
                                            </a>
                                        <a href="#name">
                                            <span class="white-text name">Boštjan Tittl</span>
                                        </a>
                                        <a href="#email">
                                            <span class="white-text email">btittl@gmail.com</span>
                                        </a>
                                    </div>
                                </li>
                                <li class="sidenav-link">
                                    <a class="subheader">
                                        AppMenu
                                    </a>
                                </li>
                                {{#data}}
                                    <li class="sidenav-link">
                                        <a href="{{link}}" class="waves-effect">
                                            <i class="material-icons">{{icon}}</i>
                                            {{name}}
                                        </a>
                                    </li>
                                {{/data}}
                                <li class="divider" tabindex="-1"></li>
                                <li class="sidenav-link">
                                    <a href="internal:User.Logout" class="waves-effect">
                                        <i class="material-icons">power_settings_new</i>
                                        Odjava
                                    </a>
                                </li>
                            </ul>'
            )
        );
    }
}