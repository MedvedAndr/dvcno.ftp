<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItems;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

use App\Helpers\AssetsManager;
use App\Services\Dictionary;
use App\Services\GenerateID;

use App\Models\Files;
use App\Models\Menus;
use App\Models\Pages;
use App\Models\News;
use App\Models\Events;
use App\Models\Departments;
use App\Models\Settings;
use App\Models\Languages;

class AdminController extends Controller
{
    public function index(Request $request) {
        // dump((new GenerateID())->table("pages")->get());
        // dump((new GenerateID())->table("sections")->get());
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
        //
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

    public function pages() {
        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);
        
        $view_data = [];
        $template = [];

        $view_data['title'] = 'Страницы';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Страницы',
            ],
        ];

        $view_data['pages_list'] = [];
        $pages_query = Pages::query()
            ->select(
                'p.id as page_id',
                        'p.aid as page_aid',
                       'p.slug as page_slug',
                  'p.front_url as page_front_url',
                      'p.title as page_title',
                'p.description as page_description',
                    'p.enabled as page_enabled',
                 'p.created_at as page_created_at',
                 'p.updated_at as page_updated_at',

                'l.locale_code as locale_code',
            )
            ->from('pages as p')
            ->join('languages as l', function($join) {
                $join
                    ->on('p.language_id', '=', 'l.aid');
            })
            ->orderByRaw("l.locale_code = ? DESC", [app('locale')])
            ->orderBy('page_title', 'asc');

        $pages = $pages_query->get();

        foreach($pages as $page) {
            if(!isset($view_data['pages_list'][$page->page_aid])) {
                $view_data['pages_list'][$page->page_aid] = [
                    'id'            => [],
                    'aid'           => $page->page_aid,
                    'slug'          => $page->page_slug,
                    'front_url'     => $page->page_front_url,
                    'title'         => [],
                    'description'   => [],
                    'enabled'       => $page->page_enabled,
                    'created_at'    => strtotime($page->page_created_at),
                    'updated_at'    => strtotime($page->page_updated_at),
                ];
            }
            
            $view_data['pages_list'][$page->page_aid]['id'][$page->page_id] = true;
            $view_data['pages_list'][$page->page_aid]['title'][$page->locale_code] = $page->page_title;
            $view_data['pages_list'][$page->page_aid]['description'][$page->locale_code] = $page->page_description;
        }

        $view_data['pages_list'] = array_values($view_data['pages_list']);

        $view_data['pages_list'] = array_map(function($value) {
            $value['id'] = array_keys($value['id']);

            return $value;
        }, $view_data['pages_list']);

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.pages.main', $view_data);
        $template[] = view('admin.footer', $view_data);
        
        return implode('', $template);
    }

    public function editPage($aid) {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('accordions');
        AssetsManager::useBundle('form');
        AssetsManager::useBundle('ckeditor');

        $view_data = [];
        $template = [];

        $view_data['page'] = [];

        $page_query = Pages::query()
            ->select(
                'p.id as page_id',
                        'p.aid as page_aid',
                       'p.slug as page_slug',
                  'p.front_url as page_front_url',
                      'p.title as page_title',
                'p.description as page_description',
                    'p.enabled as page_enabled',
                 'p.created_at as page_created_at',
                 'p.updated_at as page_updated_at',

                         's.id as section_id',
                        's.aid as section_aid',
                       's.type as section_type',
                      's.title as section_title',
                    's.content as section_content',
                      's.group as section_group',
                      's.order as section_order',
                 's.created_at as section_created_at',
                 's.updated_at as section_updated_at',

                'l.locale_code as locale_code',
            )
            ->from('pages as p')
            ->join('languages as l', function($join) {
                $join
                    ->on('p.language_id', '=', 'l.aid');
            })
            ->join('sections as s', function($join) {
                $join
                    ->on('p.aid', '=', 's.page_id')
                    ->on('p.language_id', '=', 's.language_id');
            })
            ->where('p.aid', '=', $aid)
            ->orderBy('section_order', 'asc');

        $pages = $page_query->get();

        foreach($pages as $page) {
            if(empty($view_data['page'])) {
                $view_data['page']['id']            = [];
                $view_data['page']['aid']           = $page['page_aid'];
                $view_data['page']['slug']          = $page['page_slug'];
                $view_data['page']['front_url']     = $page['page_front_url'];
                $view_data['page']['title']         = [];
                $view_data['page']['description']   = [];
                $view_data['page']['enabled']       = $page['page_enabled'];
                $view_data['page']['created_at']    = $page['page_created_at'];
                $view_data['page']['updated_at']    = $page['page_updated_at'];
                $view_data['page']['sections']      = [];
            }

            $view_data['page']['id'][$page['page_id']] = true;
            $view_data['page']['title'][$page['locale_code']] = $page['page_title'];
            $view_data['page']['description'][$page['locale_code']] = $page['page_description'];
            
            if(!isset($view_data['page']['sections'][$page['section_aid']])) {
                $view_data['page']['sections'][$page['section_aid']] = [
                    'id'            => [],
                    'aid'           => $page['section_aid'],
                    'type'          => $page['section_type'],
                    'title'         => [],
                    'content'       => [],
                    'group'         => $page['section_group'],
                    'order'         => $page['section_order'],
                    'created_at'    => $page['section_created_at'],
                    'updated_at'    => $page['section_updated_at'],
                ];
            }

            $view_data['page']['sections'][$page['section_aid']]['id'][$page['section_id']] = true;
            $content = json_decode($page['section_content'], true);
//             $fileIds = array_unique(array_filter(array_merge(
//                 array_column($content, 'document'),
//                 array_column($content, 'image'),
//                 array_column($content, 'big'),
//                 array_column($content, 'medium'),
//                 array_column($content, 'small')
//             )));
// dump($content);
            $fileIds = [];

            array_walk_recursive($content, function($value, $key) use (&$fileIds) {
                if(in_array($key, ['document', 'video_mp4', 'video_webm', 'image', 'big', 'medium', 'small'])) {
                    $fileIds[$value] = true;
                }
            });

            $files = Files::whereIn('aid', array_keys($fileIds))->get()->keyBy('aid');

            // Затем обрабатываем контент
            array_walk_recursive($content, function(&$value, $key) use ($files) {
                if(in_array($key, ['document', 'video_mp4', 'video_webm', 'image', 'big', 'medium', 'small'])) {
                    if(isset($files[$value])) {
                        $value = [
                            'aid' => $files[$value]->aid,
                            'name' => $files[$value]->name,
                            'extension' => $files[$value]->extension,
                            'path' => $files[$value]->path
                        ];
                    }
                    else {
                        $value = null;
                    }
                }
            });

            if($page['section_type'] === 'header' || $page['section_type'] === 'sub_header' || $page['section_type'] === 'format_text') {
                $content = $content[0];
            }

            $view_data['page']['sections'][$page['section_aid']]['title'][$page['locale_code']] = $page['section_title'];
            $view_data['page']['sections'][$page['section_aid']]['content'][$page['locale_code']] = $content;
        }

        $view_data['page']['id'] = array_keys($view_data['page']['id']);
        $view_data['page']['sections'] = array_values($view_data['page']['sections']);
        $view_data['page']['sections'] = array_map(function($value) {
            $value['id'] = array_keys($value['id']);
            return $value;
        }, $view_data['page']['sections']);

        $view_data['title'] = 'Редактирование страницы';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Страницы',
                'href'  => 'admin.pages'
            ],
            [
                'title' => 'Редактирование страницы ""',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.pages.edit', $view_data);
        $template[] = view('admin.footer', $view_data);
        
        return implode('', $template);
    }

    public function events() {
        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['title'] = 'Мероприятия';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Мероприятия',
            ],
        ];

        $view_data['events_list'] = [];
        $events_query = Events::query()
            ->select(
                'e.id as event_id',
                        'e.aid as event_aid',
                       'e.slug as event_slug',
                      'e.title as event_title',
                'e.description as event_description',
                    'e.content as event_content',
                  'e.thumbnail as event_thumbnail',
                    'e.address as event_address',
                'e.link_to_map as event_link_to_map',
                    'e.enabled as event_enabled',
                 'e.date_event as event_date_event',
                  'e.date_from as event_date_from',
                    'e.date_to as event_date_to',
                 'e.created_at as event_created_at',
                 'e.updated_at as event_updated_at',

                'l.locale_code as locale_code',
            )
            ->from('events as e')
            ->join('languages as l', function($join) {
                $join
                    ->on('e.language_id', '=', 'l.aid');
            })
            ->orderByRaw("l.locale_code = ? DESC", [app('locale')])
            ->orderBy('event_created_at', 'desc');

        $events = $events_query->get();

        $thumbnail_aids = [];

        foreach($events as $event) {
            // Проверяем, что значение thumbnail не пустое и не равно null
            if (!empty($event->event_thumbnail)) {
                $thumbnail_aids[$event->event_thumbnail] = true;
            }
        }

        $thumbnails = Files::whereIn('aid', array_keys($thumbnail_aids))->get()->keyBy('aid');
        
        foreach($events as $event) {
            if(!isset($view_data['events_list'][$event->event_aid])) {
                $view_data['events_list'][$event->event_aid] = [
                    'id'            => [],
                    'aid'           => $event->event_aid,
                    'slug'          => $event->event_slug,
                    'title'         => [],
                    'description'   => [],
                    'content'       => [],
                    'thumbnail'     => $event->event_thumbnail ? $thumbnails[$event->event_thumbnail]->toArray() : null,
                    'address'       => [],
                    'link_to_map'   => $event->link_to_map,
                    'enabled'       => $event->event_enabled,
                    'date_event'    => $event->event_date_event,
                    'date_from'     => $event->event_date_from,
                    'date_to'       => $event->event_date_to,
                    'created_at'    => strtotime($event->event_created_at),
                    'updated_at'    => strtotime($event->event_updated_at),
                ];
            }
            
            $view_data['events_list'][$event->event_aid]['id'][$event->event_id] = true;
            $view_data['events_list'][$event->event_aid]['title'][$event->locale_code] = $event->event_title;
            $view_data['events_list'][$event->event_aid]['description'][$event->locale_code] = $event->event_description;

            $view_data['events_list'][$event->event_aid]['content'][$event->locale_code] = $event->event_content;
            $view_data['events_list'][$event->event_aid]['address'][$event->locale_code] = $event->event_address;
        }

        $view_data['events_list'] = array_values($view_data['events_list']);

        $view_data['events_list'] = array_map(function($value) {
            $value['id'] = array_keys($value['id']);

            return $value;
        }, $view_data['events_list']);
        
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
                'title' => 'Мероприятия',
                'href' => 'admin.events'
            ],
            [
                'title' => 'Создание мероприятия',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.events.create', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function editEvents($aid) {
        
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');
        AssetsManager::useBundle('ckeditor');
        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['events'] = [];
        $events_query = Events::query()
            ->select(
                         'n.id as events_id',
                        'n.aid as events_aid',
                       'n.slug as events_slug',
                      'n.title as events_title',
                'n.description as events_description',
                    'n.content as events_content',
                  'n.thumbnail as events_thumbnail',
               'n.address as events_address',
               'n.link_to_map as events_link_to_map',
               'n.date_event as events_date_event',
                    'n.enabled as events_enabled',
                  'n.date_from as events_date_from',
                    'n.date_to as events_date_to',
                 'n.created_at as events_created_at',
                 'n.updated_at as events_updated_at',

                'l.locale_code as locale_code',
            )
            ->from('events as n')
            ->join('languages as l', function($join) {
                $join
                    ->on('n.language_id', '=', 'l.aid');
            })
            ->where('n.aid', '=', $aid)
            ->orderBy('events_created_at', 'desc');

        $events = $events_query->get();

        foreach($events as $event) {
            if(empty($view_data['events'])) {
                $view_data['events']['id']            = [];
                $view_data['events']['aid']           = $event['events_aid'];
                $view_data['events']['slug']          = $event['events_slug'];
                $view_data['events']['content']       = [];
                $view_data['events']['description']   = [];
                $view_data['events']['address']  = $event['events_address'];
                $view_data['events']['link_to_map']  = $event['events_link_to_map'];
                $view_data['events']['date_event']  = $event['events_date_event'];
                $view_data['events']['title']         = [];
                $view_data['events']['enabled']       = $event['events_enabled'];
                $view_data['events']['date_from']     = $event['events_date_from'];
                $view_data['events']['date_to']       = $event['events_date_to'];
                $view_data['events']['created_at']    = $event['events_created_at'];
                $view_data['events']['updated_at']    = $event['events_updated_at'];
            }

            $view_data['events']['id'][$event['events_id']] = true;
            $view_data['events']['title'][$event['locale_code']] = $event['events_title'];
            $view_data['events']['content'][$event['locale_code']] = $event['events_content'];
            $view_data['events']['description'][$event['locale_code']] = $event['events_description'];
        }

        $view_data['title'] = 'Мероприятия';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Мероприятия',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.events.edit', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function news() {
        AssetsManager::setStyle([
            'href' => asset('/css/models/tables.css'),
            'priority' => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['title'] = 'Новости';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Новости',
            ],
        ];

        $view_data['news_list'] = [];
        $news_query = News::query()
            ->select(
                 'n.id as news_id',
                         'n.aid as news_aid',
                        'n.slug as news_slug',
                       'n.title as news_title',
                    'n.subtitle as news_subtitle',
                 'n.description as news_description',
                     'n.content as news_content',
                   'n.thumbnail as news_thumbnail',
                'n.time_to_read as news_time_to_read',
                     'n.enabled as news_enabled',
                   'n.date_from as news_date_from',
                     'n.date_to as news_date_to',
                  'n.created_at as news_created_at',
                  'n.updated_at as news_updated_at',

                 'l.locale_code as locale_code',
            )
            ->from('news as n')
            ->join('languages as l', function($join) {
                $join
                    ->on('n.language_id', '=', 'l.aid');
            })
            ->orderByRaw("l.locale_code = ? DESC", [app('locale')])
            ->orderBy('news_created_at', 'desc');

        $news = $news_query->get();

        $thumbnail_aids = [];

        foreach($news as $news_ones) {
            // Проверяем, что значение thumbnail не пустое и не равно null
            if (!empty($news_ones->news_thumbnail)) {
                foreach(json_decode($news_ones->news_thumbnail, true) as $thumbnail_aid) {
                    $thumbnail_aids[$thumbnail_aid] = true;
                }
            }
        }

        $thumbnails = Files::whereIn('aid', array_keys($thumbnail_aids))->get()->keyBy('aid');

        foreach($news as $news_ones) {
            if(!isset($view_data['news_list'][$news_ones->news_aid])) {
                $view_data['news_list'][$news_ones->news_aid] = [
                    'id'            => [],
                    'aid'           => $news_ones->news_aid,
                    'slug'          => $news_ones->news_slug,
                    'title'         => [],
                    'subtitle'      => [],
                    'description'   => [],
                    'content'       => [],
                    'thumbnail'     => $news_ones->news_thumbnail ? $thumbnails[json_decode($news_ones->news_thumbnail, true)[0]]->toArray() : null,
                    'time_to_read'  => [],
                    'enabled'       => $news_ones->news_enabled,
                    'date_from'     => $news_ones->news_date_from,
                    'date_to'       => $news_ones->news_date_to,
                    'created_at'    => strtotime($news_ones->news_created_at),
                    'updated_at'    => strtotime($news_ones->news_updated_at),
                ];
            }

            $view_data['news_list'][$news_ones->news_aid]['id'][$news_ones->news_id] = true;
            $view_data['news_list'][$news_ones->news_aid]['title'][$news_ones->locale_code] = $news_ones->news_title;
            $view_data['news_list'][$news_ones->news_aid]['subtitle'][$news_ones->locale_code] = $news_ones->news_subtitle;
            $view_data['news_list'][$news_ones->news_aid]['description'][$news_ones->locale_code] = $news_ones->news_description;

            $view_data['news_list'][$news_ones->news_aid]['content'][$news_ones->locale_code] = $news_ones->news_content;
            $view_data['news_list'][$news_ones->news_aid]['time_to_read'][$news_ones->locale_code] = $news_ones->news_time_to_read;
        }

        $view_data['news_list'] = array_values($view_data['news_list']);

        $view_data['news_list'] = array_map(function($value) {
            $value['id'] = array_keys($value['id']);

            return $value;
        }, $view_data['news_list']);
        
        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.news.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function editNews($aid) {
        
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');
        AssetsManager::useBundle('ckeditor');
        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['news'] = [];
        $news_query = News::query()
            ->select(
                         'n.id as news_id',
                        'n.aid as news_aid',
                       'n.slug as news_slug',
                      'n.title as news_title',
                   'n.subtitle as news_subtitle',
                'n.description as news_description',
                    'n.content as news_content',
                  'n.thumbnail as news_thumbnail',
               'n.time_to_read as news_time_to_read',
                    'n.enabled as news_enabled',
                  'n.date_from as news_date_from',
                    'n.date_to as news_date_to',
                 'n.created_at as news_created_at',
                 'n.updated_at as news_updated_at',

                'l.locale_code as locale_code',
            )
            ->from('news as n')
            ->join('languages as l', function($join) {
                $join
                    ->on('n.language_id', '=', 'l.aid');
            })
            ->where('n.aid', '=', $aid)
            ->orderBy('news_created_at', 'desc');

        $news = $news_query->get();

        foreach($news as $new) {
            if(empty($view_data['news'])) {
                $view_data['news']['id']            = [];
                $view_data['news']['aid']           = $new['news_aid'];
                $view_data['news']['slug']          = $new['news_slug'];
                $view_data['news']['content']       = [];
                $view_data['news']['description']   = [];
                $view_data['news']['subtitle']      = [];
                $view_data['news']['time_to_read']  = [];
                $view_data['news']['title']         = [];
                $view_data['news']['enabled']       = $new['news_enabled'];
                $view_data['news']['date_from']     = $new['news_date_from'];
                $view_data['news']['date_to']       = $new['news_date_to'];
                $view_data['news']['created_at']    = $new['news_created_at'];
                $view_data['news']['updated_at']    = $new['news_updated_at'];
            }

            $view_data['news']['id'][$new['news_id']] = true;
            $view_data['news']['title'][$new['locale_code']] = $new['news_title'];
            $view_data['news']['content'][$new['locale_code']] = $new['news_content'];
            $view_data['news']['description'][$new['locale_code']] = $new['news_description'];
            $view_data['news']['subtitle'][$new['locale_code']] = $new['news_subtitle'];
            $view_data['news']['time_to_read'][$new['locale_code']] = $new['news_time_to_read'];
        }

        $view_data['title'] = 'Новости';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Новости',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.news.edit', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function createNews() {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');
        AssetsManager::useBundle('ckeditor');
        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['title'] = 'Новости';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Новости',
                'href' => 'admin.news'
            ],
            [
                'title' => 'Создание новости',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.news.create', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function departments() {
        // AssetsManager::useBundle('tabs');
        // AssetsManager::useBundle('form');
        // AssetsManager::useBundle('ckeditor');
        // AssetsManager::setStyle([
        //     'href'      => asset('/css/models/fields.css'),
        //     'priority'  => 500,
        // ]);

        $view_data = [];
        $template = [];

        $view_data['title'] = 'Подразделения';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Подразделения',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.departments.main', $view_data);
        $template[] = view('admin.footer', $view_data);

        return implode('', $template);
    }

    public function createDepartments() {
        AssetsManager::useBundle('tabs');
        AssetsManager::useBundle('form');
        AssetsManager::useBundle('ckeditor');
        AssetsManager::setStyle([
            'href'      => asset('/css/models/fields.css'),
            'priority'  => 500,
        ]);

        $view_data = [];
        $template = [];

        $view_data['title'] = 'Подразделения';
        $view_data['breadcrumbs'] = [
            [
                'title' => 'Подразделения',
                'href' => 'admin.departments'
            ],
            [
                'title' => 'Создание подразделения',
            ],
        ];

        $template[] = view('admin.header', $view_data);
        $template[] = view('admin.departments.create', $view_data);
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

    public function files_select() {

        $view_data  = [];
        $template   = [];

        $view_data['files'] = Files::orderBy('created_at', 'desc')->get();

        $template[] = view('admin.file_manager.select', $view_data);

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

    public function generator_id() {
        $ids = [
            'sections' => (new GenerateID())->table('sections')->get(),
        ];

        return $ids;
    }
}
