<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;

use App\Services\Dictionary;

use Illuminate\Support\Facades\DB;
use App\Models\Languages;
use App\Models\Menus;
use App\Models\Events;
use App\Models\News;
use App\Models\Pages;
use App\Models\Files;

class ApiEventsController extends Controller {
    public function getLanguages(Request $request): JsonResponse {
        $response = [
            'status' => 'success',
            'data' => [],
        ];
        
        $languages = Languages::query();

        $orderby = $request->get('orderby');
        $order = $request->get('order');
        
        if($orderby || $order) {
            $fields = array_map('trim', explode(',', $orderby ?? 'id'));
            $orders = array_map('trim', explode(',', $order ?? ''));

            $response['meta']['order'] = [];

            foreach ($fields as $index => $field) {
                $direction = strtolower($orders[$index] ?? '');
                $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

                $languages->orderBy($field, $direction);

                $response['meta']['order'][] = [
                    'orderby' => $field,
                    'order'   => $direction,
                ];
            }
        }

        $enabled = $request->get('enabled');

        if (!is_null($enabled)) {
            if ($enabled === 'true') {
                $languages->where('enabled', '=', 1);
                $response['meta']['filter']['enabled'] = true;
            }
            elseif ($enabled === 'false') {
                $languages->where('enabled', '=', 0);
                $response['meta']['filter']['enabled'] = false;
            }
        }

        $languages = $languages->get();

        foreach($languages as $language) {
            $response['data'][] = [
                'id'            => $language->id,
                'aid'           => $language->aid,
                'name'          => $language->name,
                'native_name'   => $language->native_name,
                'locale'        => $language->locale,
                'locale_code'   => $language->locale_code,
                'order'         => $language->order,
                'enabled'       => $language->enabled,
                'created_at'    => $language->created_at,
                'updated_at'    => $language->updated_at,
            ];
        }

        return response()->json($response);
    }

    public function getDictionaries(Request $request) {
        return $this->handleDictionaryRequest($request);
    }

    public function getDictionaryByParameter(Request $request, $parameter) {
        return $this->handleDictionaryRequest($request, $parameter);
    }

    public function getMenus(Request $request): JsonResponse {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $data = Menus::query()
                ->select(
                      'm.id as menu_id',
                              'm.aid as menu_aid',
                            'm.title as menu_title',
                      'm.description as menu_description',
                            'm.alias as menu_alias',
                          'm.enabled as menu_enabled',
                       'm.created_at as menu_created_at',
                       'm.updated_at as menu_updated_at',
                    
                              'mi.id as menu_item_id',
                             'mi.aid as menu_item_aid',
                         'mi.menu_id as menu_item_menu_id',
                       'mi.parent_id as menu_item_parent_id',
                       'mi.item_type as menu_item_item_type',
                         'mi.item_id as menu_item_item_id',
                           'mi.title as menu_item_title',
                             'mi.url as menu_item_url',
                            'mi.icon as menu_item_icon',
                    'mi.access_roles as menu_item_access_roles',
                           'mi.order as menu_item_order',
                         'mi.enabled as menu_item_enabled',
                      'mi.created_at as menu_item_created_at',
                      'mi.updated_at as menu_item_updated_at',

                      'l.locale_code as language_locale',
                )
                ->from('menus as m')
                ->leftJoin('menu_items as mi', function($join) {
                    $join
                        ->on('m.aid', '=', 'mi.menu_id')
                        ->on('m.language_id', '=', 'mi.language_id');
                })
                ->join('languages as l', function($join) {
                    $join
                        ->on('m.language_id', '=', 'l.aid');
                });

            $lang = $request->get('lang');
            
            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $orderby = $request->get('orderby');
            $order = $request->get('order');
            
            if($orderby || $order) {
                $fields = array_map('trim', explode(',', $orderby ?? 'menu_item_order'));
                $orders = array_map('trim', explode(',', $order ?? ''));
    
                foreach ($fields as $index => $field) {
                    $direction = strtolower($orders[$index] ?? '');
                    $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
    
                    $data
                        ->orderBy($field, $direction);
    
                    $response['meta']['order'][] = [
                        'orderby' => $field,
                        'order'   => $direction,
                    ];
                }
            }
            else {
                $data
                    ->orderBy('menu_item_order', 'asc');
            }

            $data = $data->get();

            $menus      = [];
            $menu_items = [];

            foreach($data as $item) {
                if(!isset($menus[$item['menu_aid']])) {
                    $menus[$item['menu_aid']] = [
                        'id'            => [],
                        'aid'           => $item['menu_aid'],
                        'title'         => [],
                        'description'   => [],
                        'alias'         => $item['menu_alias'],
                        'enabled'       => $item['menu_enabled'],
                        'created_at'    => $item['menu_created_at'],
                        'updated_at'    => $item['menu_updated_at'],
                    ];
                }

                $menus[$item['menu_aid']]['id'][$item['menu_id']] = true;
                $menus[$item['menu_aid']]['title'][$item['language_locale']] = $item['menu_title'];
                $menus[$item['menu_aid']]['description'][$item['language_locale']] = $item['menu_description'];

                if($item['menu_item_aid']) {
                    if(!isset($menu_items[$item['menu_item_aid']])) {
                        $menu_items[$item['menu_item_aid']] = [
                            'id'            => [],
                            'aid'           => $item['menu_item_aid'],
                            'menu_id'       => $item['menu_item_menu_id'],
                            'parent_id'     => $item['menu_item_parent_id'],
                            'title'         => [],
                            'link'          => $item['menu_item_url'],
                            'icon'          => $item['menu_item_icon'],
                            'access_roles'  => $item['menu_item_access_roles'],
                            'order'         => $item['menu_item_order'],
                            'enabled'       => $item['menu_item_enabled'],
                            'created_at'    => $item['menu_item_created_at'],
                            'updated_at'    => $item['menu_item_updated_at'],
                        ];
                    }
                    
                    $menu_items[$item['menu_item_aid']]['id'][$item['menu_item_id']] = true;
                    $menu_items[$item['menu_item_aid']]['title'][$item['language_locale']] = $item['menu_item_title'];
                }
            }

            $menus = array_map(function($value) use ($lang) {
                $value['id'] = array_keys($value['id']);
                if($lang) {
                    $value['id'] = $value['id'][0];
                    $value['title'] = $value['title'][$lang];
                    $value['description'] = $value['description'][$lang];
                }
                return $value;
            }, $menus);
            $menu_items = array_map(function($value) use ($lang) {
                $value['id'] = array_keys($value['id']);
                if($lang) {
                    $value['id'] = $value['id'][0];
                    $value['title'] = $value['title'][$lang];
                }

                return $value;
            }, $menu_items);
            
            $max_depth = $request->get('depth');

            if($max_depth) {
                $response['meta']['filter']['depth'] = $max_depth;
            }

            foreach($menus as &$menu) {
                $items = $this->prepareChildren($menu_items, $menu['aid'], $max_depth);
                
                if(!empty($items)) {
                    $menu['items'] = $items;
                }
            }

            $response['data'] = array_values($menus);

            return response()->json($response);
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function getMenusByParameter(Request $request, $parameter): JsonResponse {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $data = Menus::query()
                ->select(
                      'm.id as menu_id',
                              'm.aid as menu_aid',
                            'm.title as menu_title',
                      'm.description as menu_description',
                            'm.alias as menu_alias',
                          'm.enabled as menu_enabled',
                       'm.created_at as menu_created_at',
                       'm.updated_at as menu_updated_at',
                    
                              'mi.id as menu_item_id',
                             'mi.aid as menu_item_aid',
                         'mi.menu_id as menu_item_menu_id',
                       'mi.parent_id as menu_item_parent_id',
                       'mi.item_type as menu_item_item_type',
                         'mi.item_id as menu_item_item_id',
                           'mi.title as menu_item_title',
                             'mi.url as menu_item_url',
                            'mi.icon as menu_item_icon',
                    'mi.access_roles as menu_item_access_roles',
                           'mi.order as menu_item_order',
                         'mi.enabled as menu_item_enabled',
                      'mi.created_at as menu_item_created_at',
                      'mi.updated_at as menu_item_updated_at',

                      'l.locale_code as language_locale',
                )
                ->from('menus as m')
                ->leftJoin('menu_items as mi', function($join) {
                    $join
                        ->on('m.aid', '=', 'mi.menu_id')
                        ->on('m.language_id', '=', 'mi.language_id');
                })
                ->join('languages as l', function($join) {
                    $join
                        ->on('m.language_id', '=', 'l.aid');
                })
                ->whereAny([
                    'm.id',
                    'm.aid',
                    'm.alias'
                ], '=', $parameter);
            
            $lang = $request->get('lang');
            
            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $orderby = $request->get('orderby');
            $order = $request->get('order');
            
            if($orderby || $order) {
                $fields = array_map('trim', explode(',', $orderby ?? 'menu_item_order'));
                $orders = array_map('trim', explode(',', $order ?? ''));
    
                foreach ($fields as $index => $field) {
                    $direction = strtolower($orders[$index] ?? '');
                    $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
    
                    $data
                        ->orderBy($field, $direction);
    
                    $response['meta']['order'][] = [
                        'orderby' => $field,
                        'order'   => $direction,
                    ];
                }
            }
            else {
                $data
                    ->orderBy('menu_item_order', 'asc');
            }

            $data = $data->get();

            $menu      = [];
            $menu_items = [];

            foreach($data as $item) {
                if(empty($menu)) {
                    $menu['id']             = [];
                    $menu['aid']            = $item['menu_aid'];
                    $menu['title']          = [];
                    $menu['description']    = [];
                    $menu['alias']          = $item['menu_alias'];
                    $menu['enabled']        = $item['menu_enabled'];
                    $menu['created_at']     = $item['menu_created_at'];
                    $menu['updated_at']     = $item['menu_updated_at'];
                }

                $menu['id'][$item['menu_id']]                   = true;
                $menu['title'][$item['language_locale']]        = $item['menu_title'];
                $menu['description'][$item['language_locale']]  = $item['menu_description'];

                if($item['menu_item_aid']) {
                    if(!isset($menu_items[$item['menu_item_aid']])) {
                        $menu_items[$item['menu_item_aid']] = [
                            'id'            => [],
                            'aid'           => $item['menu_item_aid'],
                            'menu_id'       => $item['menu_item_menu_id'],
                            'parent_id'     => $item['menu_item_parent_id'],
                            'title'         => [],
                            'link'          => $item['menu_item_url'],
                            'icon'          => $item['menu_item_icon'],
                            'access_roles'  => $item['menu_item_access_roles'],
                            'order'         => $item['menu_item_order'],
                            'enabled'       => $item['menu_item_enabled'],
                            'created_at'    => $item['menu_item_created_at'],
                            'updated_at'    => $item['menu_item_updated_at'],
                        ];
                    }
                    
                    $menu_items[$item['menu_item_aid']]['id'][$item['menu_item_id']] = true;
                    $menu_items[$item['menu_item_aid']]['title'][$item['language_locale']] = $item['menu_item_title'];
                }
            }

            $menu['id'] = array_keys($menu['id']);
            if($lang) {
                $menu['id'] = $menu['id'][0];
                $menu['title'] = $menu['title'][$lang];
                $menu['description'] = $menu['description'][$lang];
            }

            $menu_items = array_map(function($value) use ($lang) {
                $value['id'] = array_keys($value['id']);
                if($lang) {
                    $value['id'] = $value['id'][0];
                    $value['title'] = $value['title'][$lang];
                }

                return $value;
            }, $menu_items);

            $max_depth = $request->get('depth');

            if($max_depth) {
                $response['meta']['filter']['depth'] = $max_depth;
            }

            $items = $this->prepareChildren($menu_items, $menu['aid'], $max_depth);
            
            if(!empty($items)) {
                $menu['items'] = $items;
            }

            $response['data'] = $menu;

            return response()->json($response);
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function getEvents(Request $request): JsonResponse {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $dictionary = [
                'week'          => [
                    'Monday'    => ['понедельник', 'понедельника', 'понедельнику', 'понедельник', 'понедельником', 'понедельнике'],
                    'Tuesday'   => ['вторник', 'вторника', 'вторнику', 'вторник', 'вторником', 'вторнике'],
                    'Wednesday' => ['среда', 'среды', 'среде', 'среду', 'средой', 'среде'],
                    'Thursday'  => ['четверг', 'четверга', 'четвергу', 'четверг', 'четвергом', 'четверге'],
                    'Friday'    => ['пятница', 'пятницы', 'пятнице', 'пятницу', 'пятницей', 'пятнице'],
                    'Saturday'  => ['суббота', 'субботы', 'субботе', 'субботу', 'субботой', 'субботе'],
                    'Sunday'    => ['воскресенье', 'воскресенья', 'воскресенью', 'воскресенье', 'воскресеньем', 'воскресенье'],
                ],
                'week_short'    => [
                    'Mon'   => ['пн', 'пнд'],
                    'Tue'   => ['вт', 'втр'],
                    'Wed'   => ['ср', 'срд'],
                    'Thu'   => ['чт', 'чвт'],
                    'Fri'   => ['пт', 'птн'],
                    'Sat'   => ['сб', 'сбт'],
                    'Sun'   => ['вс', 'вск'],
                ],
                'month'         => [
                    'January'   => ['январь', 'января', 'январю', 'январь', 'январем', 'январе'],
                    'February'  => ['февраль', 'февраля', 'февралю', 'февраль', 'февралем', 'феврале'],
                    'March'     => ['март', 'марта', 'марту', 'март', 'мартом', 'марте'],
                    'April'     => ['апрель', 'апреля', 'апрелю', 'апрель', 'апрелем', 'апреле'],
                    'May'       => ['май', 'мая', 'маю', 'май', 'маем', 'мае'],
                    'June'      => ['июнь', 'июня', 'июню', 'июнь', 'июнем', 'июне'],
                    'July'      => ['июль', 'июля', 'июлю', 'июль', 'июлем', 'июле'],
                    'August'    => ['август', 'августа', 'августу', 'август', 'августом', 'августе'],
                    'September' => ['сентябрь', 'сентября', 'сентябрю', 'сентябрь', 'сентябрём', 'сентябре'],
                    'October'   => ['октябрь', 'октября', 'октябрю', 'октябрь', 'октябрём', 'октябре'],
                    'November'  => ['ноябрь', 'ноября', 'ноябрю', 'ноябрь', 'ноябрем', 'ноябре'],
                    'December'  => ['декабрь', 'декабря', 'декабрю', 'декабрь', 'декабрем', 'декабре'],
                ],
                'month_short'   => [
                    'Jan'   => ['янв'],
                    'Feb'   => ['фев'],
                    'Mar'   => ['мар'],
                    'Apr'   => ['апр'],
                    'May'   => ['май'],
                    'Jun'   => ['июн'],
                    'Jul'   => ['июл'],
                    'Aug'   => ['авг'],
                    'Sep'   => ['сен'],
                    'Oct'   => ['окт'],
                    'Nov'   => ['ноя'],
                    'Dec'   => ['дек'],
                ],
            ];

            $data = Events::query()
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
                           
                           'f.path as file_path',

                    'l.locale_code as language_locale',
                )
                ->from('events as e')
                ->join('languages as l', function($join) {
                    $join
                        ->on('e.language_id', '=', 'l.aid');
                })
                ->leftJoin('files as f', function($join) {
                    $join
                        ->on('e.thumbnail', '=', 'f.aid');
                });
                // ->where('e.enabled', '=', 1);

            $lang = $request->get('lang');

            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $page_current = $request->get('page_current');
            $items_per_page = $request->get('items_per_page');

            if($page_current || $items_per_page) {
                $response['meta']['pagination']['page_current'] = $page_current ? (int) $page_current : 1;
                $response['meta']['pagination']['items_per_page'] = $items_per_page ? (int) $items_per_page : 25;

                $subQuery = Events::query()
                    ->select('e.aid')
                    ->from('events as e')
                    ->join('languages as l', 'e.language_id', '=', 'l.aid')
                    ->when($lang, fn($query) => $query->where('l.locale_code', $lang))
                    ->groupBy('e.aid')
                    ->orderBy(DB::raw('MAX(e.date_event)'), 'desc');

                $response['meta']['pagination']['total_items'] = (clone $subQuery)->pluck('aid')->count();

                $subQuery
                    ->offset(($response['meta']['pagination']['page_current'] - 1) * $response['meta']['pagination']['items_per_page'])
                    ->limit($response['meta']['pagination']['items_per_page']);
                
                $data
                    ->joinSub($subQuery, 'le', function($join) {
                        $join->on('e.aid', '=', 'le.aid');
                    });
                
                $response['meta']['pagination']['pages_count'] = ceil($response['meta']['pagination']['total_items'] / $response['meta']['pagination']['items_per_page']);
            }

            $orderby = $request->get('orderby');
            $order = $request->get('order');
            
            if($orderby || $order) {
                $fields = array_map('trim', explode(',', $orderby ?? 'event_date_event'));
                $orders = array_map('trim', explode(',', $order ?? ''));
    
                foreach ($fields as $index => $field) {
                    $direction = strtolower($orders[$index] ?? '');
                    $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
    
                    $data
                        ->orderBy($field, $direction);
    
                    $response['meta']['order'][] = [
                        'orderby' => $field,
                        'order'   => $direction,
                    ];
                }
            }
            else {
                $data
                    ->orderBy('event_date_event', 'desc');
            }
            
            $data = $data->get();

            $events = [];

            foreach($data as $item) {
                if(!isset($events[$item['event_aid']])) {
                    $datetime = strtotime($item['event_date_event']);
                    $events[$item['event_aid']] = [
                        'id'            => [],
                        'aid'           => $item['event_aid'],
                        'slug'          => $item['event_slug'],
                        'title'         => [],
                        'description'   => [],
                        'content'       => [],
                        'thumbnail'     => $item['file_path'],
                        'address'       => [],
                        'link_to_map'   => $item['event_link_to_map'],
                        'enabled'       => $item['event_enabled'],
                        'date_event'    => [
                            'day_d'     => date('d', $datetime),
                            'day_D'     => $dictionary['week_short'][date('D', $datetime)],
                            'month_F'   => $dictionary['month'][date('F', $datetime)],
                            'year_Y'    => date('Y', $datetime),
                            'time_H'    => date('H', $datetime),
                            'time_i'    => date('i', $datetime),
                        ],
                        'date_from'     => $item['event_date_from'],
                        'date_to'       => $item['event_date_to'],
                        'created_at'    => $item['event_created_at'],
                        'updated_at'    => $item['event_updated_at'],
                    ];
                }

                $events[$item['event_aid']]['id'][$item['event_id']]                    = true;
                $events[$item['event_aid']]['title'][$item['language_locale']]          = $item['event_title'];
                $events[$item['event_aid']]['description'][$item['language_locale']]    = $item['event_description'];
                $events[$item['event_aid']]['content'][$item['language_locale']]        = $item['event_content'];
                $events[$item['event_aid']]['address'][$item['language_locale']]        = $item['event_address'];
            }

            $events = array_map(function($value) use ($lang) {
                $value['id'] = array_keys($value['id']);
                if($lang) {
                    $value['id']            = $value['id'][0];
                    $value['title']         = $value['title'][$lang];
                    $value['description']   = $value['description'][$lang];
                    $value['content']       = $value['content'][$lang];
                    $value['address']       = $value['address'][$lang];
                }
                return $value;
            }, $events);

            $response['data'] = array_values($events);

            return response()->json($response);
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function getEventsByParameter(Request $request, $parameter) {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $dictionary = [
                'week'          => [
                    'Monday'    => ['понедельник', 'понедельника', 'понедельнику', 'понедельник', 'понедельником', 'понедельнике'],
                    'Tuesday'   => ['вторник', 'вторника', 'вторнику', 'вторник', 'вторником', 'вторнике'],
                    'Wednesday' => ['среда', 'среды', 'среде', 'среду', 'средой', 'среде'],
                    'Thursday'  => ['четверг', 'четверга', 'четвергу', 'четверг', 'четвергом', 'четверге'],
                    'Friday'    => ['пятница', 'пятницы', 'пятнице', 'пятницу', 'пятницей', 'пятнице'],
                    'Saturday'  => ['суббота', 'субботы', 'субботе', 'субботу', 'субботой', 'субботе'],
                    'Sunday'    => ['воскресенье', 'воскресенья', 'воскресенью', 'воскресенье', 'воскресеньем', 'воскресенье'],
                ],
                'week_short'    => [
                    'Mon'   => ['пн', 'пнд'],
                    'Tue'   => ['вт', 'втр'],
                    'Wed'   => ['ср', 'срд'],
                    'Thu'   => ['чт', 'чвт'],
                    'Fri'   => ['пт', 'птн'],
                    'Sat'   => ['сб', 'сбт'],
                    'Sun'   => ['вс', 'вск'],
                ],
                'month'         => [
                    'January'   => ['январь', 'января', 'январю', 'январь', 'январем', 'январе'],
                    'February'  => ['февраль', 'февраля', 'февралю', 'февраль', 'февралем', 'феврале'],
                    'March'     => ['март', 'марта', 'марту', 'март', 'мартом', 'марте'],
                    'April'     => ['апрель', 'апреля', 'апрелю', 'апрель', 'апрелем', 'апреле'],
                    'May'       => ['май', 'мая', 'маю', 'май', 'маем', 'мае'],
                    'June'      => ['июнь', 'июня', 'июню', 'июнь', 'июнем', 'июне'],
                    'July'      => ['июль', 'июля', 'июлю', 'июль', 'июлем', 'июле'],
                    'August'    => ['август', 'августа', 'августу', 'август', 'августом', 'августе'],
                    'September' => ['сентябрь', 'сентября', 'сентябрю', 'сентябрь', 'сентябрём', 'сентябре'],
                    'October'   => ['октябрь', 'октября', 'октябрю', 'октябрь', 'октябрём', 'октябре'],
                    'November'  => ['ноябрь', 'ноября', 'ноябрю', 'ноябрь', 'ноябрем', 'ноябре'],
                    'December'  => ['декабрь', 'декабря', 'декабрю', 'декабрь', 'декабрем', 'декабре'],
                ],
                'month_short'   => [
                    'Jan'   => ['янв'],
                    'Feb'   => ['фев'],
                    'Mar'   => ['мар'],
                    'Apr'   => ['апр'],
                    'May'   => ['май'],
                    'Jun'   => ['июн'],
                    'Jul'   => ['июл'],
                    'Aug'   => ['авг'],
                    'Sep'   => ['сен'],
                    'Oct'   => ['окт'],
                    'Nov'   => ['ноя'],
                    'Dec'   => ['дек'],
                ],
            ];

            $data = Events::query()
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
                           
                           'f.path as file_path',

                    'l.locale_code as language_locale',
                )
                ->from('events as e')
                ->join('languages as l', function($join) {
                    $join
                        ->on('e.language_id', '=', 'l.aid');
                })
                ->leftJoin('files as f', function($join) {
                    $join
                        ->on('e.thumbnail', '=', 'f.aid');
                })
                ->where(function($query) use ($parameter) {
                    $query
                        ->where(DB::raw("CAST(e.id AS CHAR)"), '=', $parameter)
                        ->orWhere('e.aid', '=', (string) $parameter)
                        ->orWhere('e.slug', '=', (string) $parameter);
                });
                // ->whereAny([
                //     'e.id',
                //     'e.aid',
                //     'e.slug',
                // ], '=', $parameter);

            $lang = $request->get('lang');

            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $data = $data->get();

            $event = [];

            foreach($data as $item) {
                if(empty($event)) {
                    $datetime = strtotime($item['event_date_event']);
                    $event['id']            = [];
                    $event['aid']           = $item['event_aid'];
                    $event['slug']          = $item['event_slug'];
                    $event['title']         = [];
                    $event['description']   = [];
                    $event['content']       = [];
                    $event['thumbnail']     = $item['file_path'];
                    $event['address']       = [];
                    $event['link_to_map']   = $item['event_link_to_map'];
                    $event['enabled']       = $item['event_enabled'];
                    $event['date_event']    = [
                        'day_d'     => date('d', $datetime),
                        'day_D'     => $dictionary['week_short'][date('D', $datetime)],
                        'month_F'   => $dictionary['month'][date('F', $datetime)],
                        'year_Y'    => date('Y', $datetime),
                        'time_H'    => date('H', $datetime),
                        'time_i'    => date('i', $datetime)
                    ];
                    $event['date_from']     = $item['event_date_from'];
                    $event['date_to']       = $item['event_date_to'];
                    $event['created_at']    = $item['event_created_at'];
                    $event['updated_at']    = $item['event_updated_at'];
                }

                $event['id'][$item['event_id']]                    = true;
                $event['title'][$item['language_locale']]          = $item['event_title'];
                $event['description'][$item['language_locale']]    = $item['event_description'];
                $event['content'][$item['language_locale']]        = $item['event_content'];
                $event['address'][$item['language_locale']]        = $item['event_address'];
            }

            $event['id'] = array_keys($event['id']);

            if($lang) {
                $event['id']            = $event['id'][0];
                $event['title']         = $event['title'][$lang];
                $event['description']   = $event['description'][$lang];
                $event['content']       = $event['content'][$lang];
                $event['address']       = $event['address'][$lang];
            }
            
            $response['data'] = $event;

            return response()->json($response);
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function getNews(Request $request): JsonResponse {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $data = News::query()
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

                     'l.locale_code as language_locale',
                )
                ->from('news as n')
                ->join('languages as l', function($join) {
                    $join
                        ->on('n.language_id', '=', 'l.aid');
                });
            
            $lang = $request->get('lang');

            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $page_current = $request->get('page_current');
            $items_per_page = $request->get('items_per_page');

            if($page_current || $items_per_page) {
                $response['meta']['pagination']['page_current'] = $page_current ? (int) $page_current : 1;
                $response['meta']['pagination']['items_per_page'] = $items_per_page ? (int) $items_per_page : 25;

                $subQuery = News::query()
                    ->select('n.aid')
                    ->from('news as n')
                    ->join('languages as l', 'n.language_id', '=', 'l.aid')
                    ->when($lang, fn($query) => $query->where('l.locale_code', $lang))
                    ->groupBy('n.aid')
                    ->orderBy(DB::raw('MAX(n.created_at)'), 'desc');
                
                $response['meta']['pagination']['total_items'] = (clone $subQuery)->pluck('aid')->count();
                $subQuery
                    ->offset(($response['meta']['pagination']['page_current'] - 1) * $response['meta']['pagination']['items_per_page'])
                    ->limit($response['meta']['pagination']['items_per_page']);
                
                $data
                    ->joinSub($subQuery, 'le', function($join) {
                        $join->on('n.aid', '=', 'le.aid');
                    });
                
                $response['meta']['pagination']['pages_count'] = ceil($response['meta']['pagination']['total_items'] / $response['meta']['pagination']['items_per_page']);
            }

            $orderby = $request->get('orderby');
            $order = $request->get('order');
            
            if($orderby || $order) {
                $fields = array_map('trim', explode(',', $orderby ?? 'news_created_at'));
                $orders = array_map('trim', explode(',', $order ?? ''));
    
                foreach ($fields as $index => $field) {
                    $direction = strtolower($orders[$index] ?? '');
                    $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
    
                    $data
                        ->orderBy($field, $direction);
    
                    $response['meta']['order'][] = [
                        'orderby' => $field,
                        'order'   => $direction,
                    ];
                }
            }
            else {
                $data
                    ->orderBy('news_created_at', 'desc');
            }

            $data = $data->get();

            $news = [];

            foreach($data as $item) {
                if(!isset($news[$item['news_aid']])) {
                    if(!is_null($item['news_thumbnail'])) {
                        $images_aid = json_decode($item['news_thumbnail'], true);
                        $images = Files::query()
                            ->whereIn('aid', $images_aid)
                            ->pluck('path', 'aid');
                        $images_json = array_map(function($aid) use ($images) {
                            return ['slide' => $images[$aid] ?? null];
                        }, $images_aid);
                    }
                    else {
                        $images_json = null;
                    }
                    
                    $news[$item['news_aid']] = [
                        'id'            => [],
                        'aid'           => $item['news_aid'],
                        'slug'          => $item['news_slug'],
                        'title'         => [],
                        'subtitle'      => [],
                        'description'   => [],
                        'content'       => [],
                        'images'        => $images_json,
                        'time_to_read'  => [],
                        'enabled'       => $item['news_enabled'],
                        'date_from'     => $item['news_date_from'],
                        'date_to'       => $item['news_date_to'],
                        'created_at'    => $item['news_created_at'],
                        'updated_at'    => $item['news_updated_at'],
                    ];
                }
                
                $news[$item['news_aid']]['id'][$item['news_id']]                    = true;
                $news[$item['news_aid']]['title'][$item['language_locale']]         = $item['news_title'];
                $news[$item['news_aid']]['subtitle'][$item['language_locale']]      = $item['news_subtitle'];
                $news[$item['news_aid']]['description'][$item['language_locale']]   = $item['news_description'];
                $news[$item['news_aid']]['content'][$item['language_locale']]       = $item['news_content'];
                $news[$item['news_aid']]['time_to_read'][$item['language_locale']]  = $item['news_time_to_read'];
            }

            $news = array_map(function($value) use ($lang) {
                $value['id'] = array_keys($value['id']);
                if($lang) {
                    $value['id']            = $value['id'][0];
                    $value['title']         = $value['title'][$lang];
                    $value['subtitle']      = $value['subtitle'][$lang];
                    $value['description']   = $value['description'][$lang];
                    $value['content']       = $value['content'][$lang];
                    $value['time_to_read']  = $value['time_to_read'][$lang];
                }

                return $value;
            }, $news);

            $response['data'] = array_values($news);
            
            return response()->json($response);
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function getNewsByParameter(Request $request, $parameter): JsonResponse {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $data = News::query()
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

                     'l.locale_code as language_locale',
                )
                ->from('news as n')
                ->join('languages as l', function($join) {
                    $join
                        ->on('n.language_id', '=', 'l.aid');
                })
                ->whereAny([
                    'n.id',
                    'n.aid',
                    'n.slug',
                ], '=', $parameter);
            
            $lang = $request->get('lang');

            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $data = $data->get();

            $news_once = [];

            foreach($data as $item) {
                if(empty($news_once)) {
                    if(!is_null($item['news_thumbnail'])) {
                        $images_aid = json_decode($item['news_thumbnail'], true);
                        $images = Files::query()
                            ->whereIn('aid', $images_aid)
                            ->pluck('path', 'aid');
                        $imsges_json = array_map(function($aid) use ($images) {
                            return ['slide' => $images[$aid] ?? null];
                        }, $images_aid);
                    }
                    else {
                        $imsges_json = null;
                    }
                    
                    $news_once['id']            = [];
                    $news_once['aid']           = $item['news_aid'];
                    $news_once['slug']          = $item['news_slug'];
                    $news_once['title']         = [];
                    $news_once['subtitle']      = [];
                    $news_once['description']   = [];
                    $news_once['content']       = [];
                    $news_once['images']        = $imsges_json;
                    $news_once['time_to_page']  = [];
                    $news_once['enabled']       = $item['news_enabled'];
                    $news_once['date_from']     = $item['news_date_from'];
                    $news_once['date_to']       = $item['news_date_to'];
                    $news_once['created_at']    = $item['news_created_at'];
                    $news_once['updated_at']    = $item['news_updated_at'];
                }
                
                $news_once['id'][$item['news_id']]                      = true;
                $news_once['title'][$item['language_locale']]           = $item['news_title'];
                $news_once['subtitle'][$item['language_locale']]        = $item['news_subtitle'];
                $news_once['description'][$item['language_locale']]     = $item['news_description'];
                $news_once['content'][$item['language_locale']]         = $item['news_content'];
                $news_once['time_to_page'][$item['language_locale']]    = $item['news_time_to_page'];
            }

            $news_once['id'] = array_keys($news_once['id']);
            if($lang) {
                $news_once['id']            = $news_once['id'][0];
                $news_once['title']         = $news_once['title'][$lang];
                $news_once['subtitle']      = $news_once['subtitle'][$lang];
                $news_once['description']   = $news_once['description'][$lang];
                $news_once['content']       = $news_once['content'][$lang];
                $news_once['time_to_page']  = $news_once['time_to_page'][$lang];
            }

            $response['data'] = $news_once;
            
            return response()->json($response);
        }
        catch(\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }

    public function getPages(Request $request) {
        return '';
    }

    public function getPagesByParameter(Request $request, $parameter) {
        try {
            $response = [
                'status' => 'success',
                'data' => [],
            ];

            $data = Pages::query()
                ->select(
                     'p.id as page_id',
                             'p.aid as page_aid',
                            'p.slug as page_slug',
                           'p.title as page_title',
                     'p.description as page_description',
                         'p.enabled as page_enabled',
                      'p.created_at as page_created_at',
                      'p.updated_at as page_updated_at',

                              's.id as section_id',
                             's.aid as section_aid',
                            's.type as section_type',
                         's.content as section_content',
                           's.group as section_group',
                           's.order as section_order',
                      's.created_at as section_created_at',
                      's.updated_at as section_updated_at',

                     'l.locale_code as language_locale',
                )
                ->from('pages as p')
                ->join('sections as s', function($join) {
                    $join
                        ->on('p.aid', '=', 's.page_id')
                        ->on('p.language_id', '=', 's.language_id');
                })
                ->join('languages as l', function($join) {
                    $join
                        ->on('p.language_id', '=', 'l.aid');
                })
                // ->whereAny([
                //     'p.id',
                //     'p.aid',
                //     'p.slug',
                // ], '=', $parameter)
                ->where(function($query) use ($parameter) {
                    $query
                        ->where(DB::raw("CAST(p.id AS CHAR)"), '=', $parameter)
                        ->orWhere('p.aid', '=', (string) $parameter)
                        ->orWhere('p.slug', '=', (string) $parameter);
                })
                ->orderBy('section_order', 'asc');
            
            $lang = $request->get('lang');

            if($lang) {
                $data
                    ->where('l.locale_code', '=', $lang);
                
                $response['meta']['filter']['language'] = $lang;
            }

            $data = $data->get();

            $page = [];

            foreach($data as $item) {
                if(empty($page)) {
                    $page['id']             = [];
                    $page['aid']            = $item['page_aid'];
                    $page['slug']           = $item['page_slug'];
                    $page['title']          = [];
                    $page['description']    = [];
                    $page['enabled']        = $item['page_enabled'];
                    $page['created_at']     = $item['page_created_at'];
                    $page['updated_at']     = $item['page_updated_at'];
                    $page['sections']       = [];
                }

                $page['id'][$item['page_id']] = true;
                $page['title'][$item['language_locale']] = $item['page_title'];
                $page['description'][$item['language_locale']] = $item['page_description'];

                if(!isset($page['sections'][$item['section_group']][$item['section_aid']])) {
                    $page['sections'][$item['section_group']][$item['section_aid']]['id']         = [];
                    $page['sections'][$item['section_group']][$item['section_aid']]['aid']        = $item['section_aid'];
                    $page['sections'][$item['section_group']][$item['section_aid']]['type']       = $item['section_type'];
                    $page['sections'][$item['section_group']][$item['section_aid']]['content']    = [];
                    $page['sections'][$item['section_group']][$item['section_aid']]['group']      = $item['section_group'];
                    $page['sections'][$item['section_group']][$item['section_aid']]['order']      = $item['section_order'];
                    $page['sections'][$item['section_group']][$item['section_aid']]['created_at'] = $item['section_created_at'];
                    $page['sections'][$item['section_group']][$item['section_aid']]['updated_at'] = $item['section_updated_at'];
                }

                $page['sections'][$item['section_group']][$item['section_aid']]['id'][$item['section_id']] = true;
                $page['sections'][$item['section_group']][$item['section_aid']]['content'][$item['language_locale']] = json_decode($item['section_content'], true);
            }

            $page['id'] = array_keys($page['id']);
            if($lang) {
                $page['id'] = $page['id'][0];
                $page['title'] = $page['title'][$lang];
                $page['description'] = $page['description'][$lang];
            }

            foreach($page['sections'] as $group_name => &$group) {
                $group = array_values($group);
                $group = array_map(function($section) use ($lang) {
                    $section['id'] = array_keys($section['id']);

                    array_walk_recursive($section['content'], function(&$value, $key) {
                        if(in_array($key, ['document', 'image', 'big', 'medium', 'small'])) {
                            $file = Files::where('aid', '=', $value)->first();
                            if($file) {
                                $value = $file->path;
                            }
                        }
                    });

                    if($lang) {
                        $section['id'] = $section['id'][0];
                        $section['content'] = $section['content'][$lang];
                    }
                    return $section;
                }, $group);
                // dump($group);
            }

            $response['data'] = $page;
            // dump($page);

            return response()->json($response);
        }
        catch(\Throwable $error) {
dump($error);
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage(),
            ]);
        }
    }





    private function getMeta(Request $request, array $allowedParams): array {
        $data = $request->query();
        $meta = [];

        foreach ($allowedParams as $param => $label) {
            if(isset($data[$param])) {
                if($param === 'full') {
                    $meta[$label] = filter_var($data['full'], FILTER_VALIDATE_BOOLEAN) ? 'full' : 'brief';
                }
                else {
                    $meta['filter'][$label] = $data[$param];
                }
            }
        }

        return $meta;
    }

    private function prepareDictionary(Request $request, Dictionary $dictionary): array|string {
        foreach (['lang' => 'locale', 'key' => 'key', 'full' => 'full'] as $param => $method) {
            if ($request->has($param)) {
                $dictionary->$method($param === 'full'
                    ? filter_var($request->get($param), FILTER_VALIDATE_BOOLEAN)
                    : $request->get($param)
                );
            }
        }
    
        return $dictionary->get();
    }

    private function handleDictionaryRequest(Request $request, ?string $parameter = null): JsonResponse {
        try {
            $dictionary = new Dictionary();
            if($parameter !== null) {
                $dictionary->dictionary($parameter);
            }
    
            $data = $this->prepareDictionary($request, $dictionary);
            $meta = $this->getMeta($request, ['lang' => 'language', 'key' => 'key', 'full' => 'format']);
    
            $response = [
                'status' => 'success',
                'data' => $data
            ];
    
            if(!empty($meta)) {
                $response['meta'] = $meta;
            }
    
            return response()->json($response);
        }
        catch (\Throwable $error) {
            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage()
            ]);
        }
    }

    private function prepareChildren(&$items, $menu_id, $max_depth = null, $parent_id = null) {
        $tree = [];
        
        if(is_null($max_depth) || $max_depth > 0) {
            $next_max_depth = is_null($max_depth) ? null : $max_depth - 1;

            foreach($items as $item) {
                if($item['parent_id'] === $parent_id && $item['menu_id'] == $menu_id) {
                    $children = $this->prepareChildren($items, $menu_id, $next_max_depth, $item['aid']);
                    if(!empty($children)) {
                        $item['submenu'] = $children;
                    }

                    unset($item['menu_id']);
                    unset($item['parent_id']);
                    $tree[] = $item;
                }
            }
        }

        return $tree;
    }
}
