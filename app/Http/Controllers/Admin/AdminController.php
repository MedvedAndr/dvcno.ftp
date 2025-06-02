<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItems;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

use App\Helpers\AssetsManager;
use App\Services\Dictionary;
use App\Services\GenerateID;

use App\Models\Languages;
use App\Models\Settings;
use App\Models\Files;
use App\Models\Menus;

class AdminController extends Controller
{
    public function index(Request $request) {
        // Получаем блок данных пользователя из текущей сессии и если его нет, то возвращаем false
        $user_session            = $request->session()->get('user');
        // Данные для отправки в шаблон
        $view_data               = [];
        $template = [];
        
        // dump('Шаблон', request()->route());

        $view_data['title']                  = 'Панель администратора';
        // $viewData['user']                   = getUserByAID($userSession['aid']);

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function settings() {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');

        AssetsManager::setStyle([
            'href' => asset('/css/models/fields.css'),
            'priority' => 500,
        ]);

        $view_data = [];
        $template = [];
        // dump((new GenerateID())->table('settings')->get());
        
        $view_data['main_settings'] = [];
        $main_settings_data = Settings::whereIn('group', ['technical_works', 'social_webs'])
            ->orderBy('group_order', 'asc')
            ->orderBy('order', 'asc')
            ->get()
            ->toArray();
        foreach($main_settings_data as $setting_data) {
            if(!isset($view_data['main_settings'][$setting_data['group']])) {
                $view_data['main_settings'][$setting_data['group']] = [];
            }

            $view_data['main_settings'][$setting_data['group']][] = [
                'aid'           => $setting_data['aid'],
                'type'          => $setting_data['type'],
                'setting_key'   => $setting_data['setting_key'],
                'setting_value' => $setting_data['setting_value'],
                'order'         => $setting_data['order'],
                'group'         => $setting_data['group'],
                'group_order'   => $setting_data['group_order'],
                'created_at'    => strtotime($setting_data['created_at']),
                'updated_at'    => strtotime($setting_data['updated_at']),
            ];
        }
        // $view_data['main_settings'] = $main_settings;
        
        $view_data['technical_works'] = Settings::where('group', '=', 'technical_works')
            ->orderBy('order', 'asc')
            ->get()
            ->toArray();
        $view_data['file_system'] = Settings::where('group', '=', 'file_types')
            ->orderBy('order', 'asc')
            ->get()
            ->toArray();

        $view_data['title'] = app('dictionary')->dictionary('headers')->key('site_settings')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('site_settings')->get(),
            ],
        ];
        
        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.settings.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function menus() {
        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        AssetsManager::setStyle([
            'href' => asset('/css/models/form/button.css'),
            'priority' => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['title'] = app('dictionary')->dictionary('headers')->key('menu')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('menu')->get(),
            ],
        ];

        $view_data['menu_list'] = [];
        $menus = Menus::select(
                'm.id as menu_id',
                        'm.aid as menu_aid',
                'm.language_id as menu_language_id',
                      'm.title as menu_title',
                'm.description as menu_description',
                      'm.alias as menu_alias',
                    'm.enabled as menu_enabled',
                 'm.created_at as menu_created_at',
                 'm.updated_at as menu_updated_at',

                'l.locale_code as locale_code',
            )
            ->from('menus as m')
            ->join('languages as l', function($join) {
                $join
                    ->on('m.language_id', '=', 'l.aid');
            })
            ->orderBy('m.alias', 'asc')
            ->get();
        
        foreach($menus as $menu) {
            if(!isset($view_data['menu_list'][$menu->menu_aid])) {
                $view_data['menu_list'][$menu->menu_aid] = [
                    'id'            => [],
                    'aid'           => $menu->menu_aid,
                    'title'         => [],
                    'description'   => [],
                    'alias'         => $menu->menu_alias,
                    'enabled'       => $menu->menu_enabled,
                    'created_at'    => strtotime($menu->menu_created_at),
                    'updated_at'    => strtotime($menu->menu_updated_at),
                ];

            }
            
            $view_data['menu_list'][$menu->menu_aid]['id'][] = $menu->menu_id;

            $view_data['menu_list'][$menu->menu_aid]['title'][$menu->locale_code] = $menu->menu_title;
            $view_data['menu_list'][$menu->menu_aid]['description'][$menu->locale_code] = $menu->menu_description;
            // dump($menu);
        }
        // dump($view_data['menu_list']);
        // $view_data['menu_list'] = [];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.menus.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function viewMenu($aid) {
        AssetsManager::useBundle('tabs');

        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        AssetsManager::setStyle([
            'href' => asset('/css/models/form/button.css'),
            'priority' => 500,
        ]);

        $view_data = [];
        $template   = [];
        $menu = [];

        $menu_data = Menus::select(
                'm.id as menu_id',
                        'm.aid as menu_aid',
                'm.language_id as menu_language_id',
                      'm.title as menu_title',
                'm.description as menu_description',
                      'm.alias as menu_alias',
                    'm.enabled as menu_enabled',
                 'm.created_at as menu_created_at',
                 'm.updated_at as menu_updated_at',
            )
            ->from('menus as m')
            ->where('aid', '=', $aid)->get();
        
        foreach($menu_data as $menu_item) {
            if(empty($menu)) {

            }
        }
        // $view_data['menu'] = ;
        $view_data['title']         = 'Просмотр меню';
        $view_data['breadcrumbs']   = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('menu')->get(),
                'href' => 'admin.menus'
            ],
            [
                'title' => 'Просмотр меню',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.menus.view', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function createMenu() {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('accordions');
        AssetsManager::useBundle('form');

        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);

        $view_data  = [];
        $template   = [];

        $view_data['title']         = app('dictionary')->dictionary('headers')->key('menu_create')->get();
        $view_data['breadcrumbs']   = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('menu')->get(),
                'href' => 'admin.menus'
            ],
            [
                'title' => app('dictionary')->dictionary('headers')->key('menu_create')->get(),
            ],
        ];
        // $view_data['languages'] = Languages::get();

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.menus.create', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function editMenu($aid) {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('accordions');
        AssetsManager::useBundle('form');

        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);

        $view_data = [];
        $template   = [];

        $menu_data = MenuItems::select(
                'mi.id as item_id',
                'mi.aid as item_aid',
                'mi.language_id as item_language_id',
                'mi.menu_id as item_menu_id',
                'mi.parent_id as item_parent_id',
                'mi.item_type as item_item_type',
                'mi.item_id as item_item_id',
                'mi.title as item_title',
                'mi.url as item_url',
                'mi.icon as item_icon',
                'mi.access_roles as item_access_roles',
                'mi.order as item_order',
                'mi.enabled as item_enabled',
                'mi.created_at as item_created_at',
                'mi.updated_at as item_updated_at',
            )
            ->from('menu_items as mi')
            ->where('menu_id', '=', $aid)
            ->get();

        foreach($menu_data as $item) {
            // dump($item);
        }

        $view_data['title']         = 'Редактирование меню';
        $view_data['breadcrumbs']   = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('menu')->get(),
                'href' => 'admin.menus'
            ],
            [
                'title' => 'Редактирование меню',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.menus.edit', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function events() {
        $view_data = [];
        $template = [];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.events.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function createEvent() {
        AssetsManager::useBundle('tabs');
        // AssetsManager::useBundle('accordions');
        AssetsManager::useBundle('form');
        AssetsManager::useBundle('ckeditor');

        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);
        
        $view_data = [];
        $template = [];

        $view_data['title'] = 'Мероприятия';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Создание мероприятия',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.events.create', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function languages(Request $request) {
        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        $view_data               = [];
        $template   = [];

        $view_data['title'] = app('dictionary')->dictionary('headers')->key('languages')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('languages')->get(),
            ],
        ];
        $view_data['languages_list'] = Languages::get();

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.languages.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function viewLanguage() {
        $view_data               = [];
        $template   = [];

        $view_data['title'] = 'Просмотр языка';
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('languages')->get(),
                'href' => 'admin.languages'
            ],
            [
                'title' => 'Просмотр языка',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.languages.view', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function createLanguage() {
        $view_data               = [];
        $template   = [];

        $view_data['title'] = app('dictionary')->dictionary('headers')->key('language_create')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('languages')->get(),
                'href' => 'admin.languages'
            ],
            [
                'title' => app('dictionary')->dictionary('headers')->key('language_create')->get(),
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.languages.create', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function editLanguage() {
        $view_data               = [];
        $template   = [];

        $view_data['title'] = 'Редактирование языка';
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('languages')->get(),
                'href' => 'admin.languages'
            ],
            [
                'title' => 'Редактирование языка',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.languages.edit', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function dictionaries(Request $request) {
        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        AssetsManager::setStyle([
            'href' => asset('/css/models/form/button.css'),
            'priority' => 500,
        ]);

        $view_data = [];
        $template   = [];

        $view_data['dictionaries_list'] = (new Dictionary())->full()->get();
        // $view_data['dictionaries_list'] = [];
        $view_data['title'] = app('dictionary')->dictionary('headers')->key('dictionaries')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('dictionaries')->get(),
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.dictionaries.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function viewDictionary(Request $request, $aid) {
        AssetsManager::useBundle('tabs');

        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        AssetsManager::setStyle([
            'href' => asset('/css/models/form/button.css'),
            'priority' => 500,
        ]);

        $view_data               = [];
        $template   = [];

        $view_data['dictionary'] = (new Dictionary())->dictionary($aid)->full()->get();
        $view_data['title'] = app('dictionary')->dictionary('headers')->key('dictionary_view')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('dictionaries')->get(),
                'href' => 'admin.dictionaries'
            ],
        ];

        if($view_data['dictionary'] !== '') {
            $view_data['breadcrumbs'][] = [
                'title' => app('dictionary')->dictionary('headers')->key('dictionary_view')->get() .' "'. (isset($view_data['dictionary']['name'][app('locale')]) ? $view_data['dictionary']['name'][app('locale')] : reset($view_data['dictionary']['name'])) .'"',
            ];
            $load_template = view('admin.dictionaries.view', $view_data);
        }
        else {
            $view_data['breadcrumbs'][] = [
                'title' => 'Страница не найдена',
            ];
            $load_template = view('admin.404', $view_data);
        }

        $template[] = view('admin.header', $view_data);
        $template[] = $load_template;
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function createDictionary(Request $request) {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');

        AssetsManager::setStyle([
            'href' => asset('/css/models/fields.css'),
            'priority' => 500,
        ]);

        $view_data  = [];
        $template   = [];

        $view_data['title'] = app('dictionary')->dictionary('headers')->key('dictionary_create')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('dictionaries')->get(),
                'href' => 'admin.dictionaries'
            ],
            [
                'title' => app('dictionary')->dictionary('headers')->key('dictionary_create')->get(),
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.dictionaries.create', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function editDictionary(Request $request, $aid) {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');

        AssetsManager::setStyle([
            'href' => asset('/css/models/fields.css'),
            'priority' => 500,
        ]);

        $view_data  = [];
        $template   = [];

        $view_data['dictionary']    = (new Dictionary())->dictionary($aid)->full()->get();
        $view_data['title']         = app('dictionary')->dictionary('headers')->key('dictionary_edit')->get();
        $view_data['breadcrumbs']   = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('dictionaries')->get(),
                'href'  => 'admin.dictionaries'
            ],
        ];

        if($view_data['dictionary'] !== '') {
            $view_data['breadcrumbs'][] = [
                'title' => app('dictionary')->dictionary('headers')->key('dictionary_view')->get() .' "'. (isset($view_data['dictionary']['name'][app('locale')]) ? $view_data['dictionary']['name'][app('locale')] : reset($view_data['dictionary']['name'])) .'"',
            ];
            $load_template = view('admin.dictionaries.edit', $view_data);
        }
        else {
            $view_data['breadcrumbs'][] = [
                'title' => 'Страница не найдена',
            ];
            $load_template = view('admin.404', $view_data);
        }
        
        $template[] = view('admin.header', $view_data);
        $template[] = $load_template;
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function files() {
        AssetsManager::setStyle([
            'href' => asset('/css/models/form/file.css'),
            'priority' => 500,
        ]);

        AssetsManager::setStyle([
            'href' => asset('/css/models/form/button.css'),
            'priority' => 500,
        ]);

        AssetsManager::setScript([
            'src' => asset('/js/models/drag_n_drop_file.js'),
            'priority' => 500,
        ]);

        AssetsManager::setScript([
            'src' => asset('/js/models/form.js'),
            'priority' => 500,
        ]);

        $view_data  = [];
        $template   = [];

        $view_data['title'] = app('dictionary')->dictionary('headers')->key('file_manager')->get();
        $view_data['breadcrumbs'] = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('file_manager')->get(),
            ],
        ];
        $view_data['files'] = Files::orderBy('created_at', 'desc')->get();

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.file_manager.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function users() {
        $view_data  = [];
        $template   = [];

        $view_data['title']         = app('dictionary')->dictionary('headers')->key('users')->get();
        $view_data['breadcrumbs']   = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('users')->get(),
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.users.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function viewUser() {

    }

    public function createUser() {

    }

    public function editUser() {

    }

    public function roles() {
        $view_data  = [];
        $template   = [];

        $view_data['title']         = app('dictionary')->dictionary('headers')->key('roles')->get();
        $view_data['breadcrumbs']   = [
            [
                'title' => app('dictionary')->dictionary('headers')->key('users')->get(),
                'href' => 'admin.users'
            ],
            [
                'title' => app('dictionary')->dictionary('headers')->key('roles')->get(),
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.users.roles', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function viewRole() {

    }

    public function createRole() {

    }

    public function editRole() {

    }
}
