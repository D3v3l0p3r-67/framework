<?php
require_once('./actions/core/DataBaseTableSessionAction.php');
require_once('./core/Session.php');

class Application extends DataBaseTableSessionAction
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'fw_application';
    }

    public function ColdStart($dataInput)
    {
        if(!isset($dataInput['id'])){
            new Exception('Id parameter is mandatory for action Application.ColdStart');
        }

        $data = $this->db->run('select A.name as application_name, AM.link, AM.icon, AM.name 
                               from fw_application A
                               left join fw_application_menu AM on AM.application_id = A.id
                               where A.id = :id
                               order by AM.Pos', 
                               ['id' => $dataInput['id']]
                               )->fetchAll(PDO::FETCH_ASSOC);

        $application_name = $data[0]['application_name'];

        $username = $this->session->getUsername();
        $email = $this->session->getEmail();
        $avatar = '../server/images/avatar/'.$this->session->getUserId().'.png';

        return ResponseFactory::CreateOk(
            message: MessageLog::GetForGrid(),
            data: $data,
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
                                        <a href="internal:User.Profile">
                                            <img class="circle" src="'.$avatar.'">
                                        </a>
                                        <div>
                                            <div class="white-text name fancy-text-shadow">'.$username.'</div>
                                            <div class="white-text email fancy-text-shadow">'.$email.'</div>
                                        </div>
                                    </div>
                                </li>
                                <li class="sidenav-link">
                                    <a class="subheader">
                                        '.$application_name.'
                                    </a>
                                </li>
                                <li class="divider" tabindex="-1"></li>
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