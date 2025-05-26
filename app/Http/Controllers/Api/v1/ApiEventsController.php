<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;

use App\Services\Dictionary;

use App\Models\Languages;
use App\Models\Menus;

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

            if($lang) {
                $menus = array_map(function($value) use ($lang) {
                    $value['id'] = $value['id'][0];
                    $value['title'] = $value['title'][$lang];
                    $value['description'] = $value['description'][$lang];
                    return $value;
                }, $menus);
                $menu_items = array_map(function($value) use ($lang) {
                    $value['title'] = $value['title'][$lang];
                    return $value;
                }, $menu_items);
            }

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