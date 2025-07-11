@php
$menus = [
    [
        'items'     => [
            [
                'title'     => app('dictionary')->dictionary('menu')->key('home')->get(),
                'route'     => 'admin.index',
                'icon'      => 'home',
            ],
            // [
            //     'title'     => app('dictionary')->dictionary('menu')->key('users')->get(),
            //     'icon'      => 'users',
            //     'submenu'   => [
            //         [
            //             'title'     => app('dictionary')->dictionary('menu')->key('users_all')->get(),
            //             'route'     => 'admin.users',
            //             'icon'      => 'list',
            //         ],
            //         [
            //             'title'     => app('dictionary')->dictionary('menu')->key('roles_all')->get(),
            //             'route'     => 'admin.roles',
            //             'icon'      => 'list',
            //         ],
            //     ],
            // ],
        ],
        'visible'   => true,
    ],
    [
        'title'     => app('dictionary')->dictionary('menu')->key('site_management')->get(),
        'items'     => [
            [
                'title'     => app('dictionary')->dictionary('menu')->key('file_manager')->get(),
                'icon'      => 'file',
                'route'     => 'admin.file.manager',
            ],
            [
                'title'     => app('dictionary')->dictionary('menu')->key('menu')->get(),
                'icon'      => 'menu',
                'submenu'   => [
                    [
                        'title'     => app('dictionary')->dictionary('menu')->key('menu_all')->get(),
                        'route'     => 'admin.menus',
                        'icon'      => 'list',
                    ],
                    [
                        'title'     => app('dictionary')->dictionary('menu')->key('menu_add')->get(),
                        'route'     => 'admin.menus.create',
                        'icon'      => 'plus',
                    ],
                ],
            ],
            [
                'title'     => 'Страницы',
                'icon'      => 'file-text',
                'submenu'   => [
                    [
                        'title'     => 'Все страницы',
                        'route'     => 'admin.pages',
                        'icon'      => 'list',
                    ],
                ],
            ],
            [
                'title'     => 'Мероприятия',
                'icon'      => 'calendar',
                'submenu'   => [
                    [
                        'title'     => 'Все мероприятия',
                        'route'     => 'admin.events',
                        'icon'      => 'list',
                    ],
                    [
                        'title'     => 'Создать мероприятие',
                        'route'     => 'admin.events.create',
                        'icon'      => 'plus',
                    ],
                ],
            ],
            [
                'title'     => 'Новости',
                'icon'      => 'cast',
                'submenu'   => [
                    [
                        'title'     => 'Все новости',
                        'route'     => 'admin.news',
                        'icon'      => 'list',
                    ],
                    [
                        'title'     => 'Создать новость',
                        'route'     => 'admin.news.create',
                        'icon'      => 'plus',
                    ],
                ],
            ],
            [
                'title'     => 'Подразделения',
                'icon'      => 'cast',
                'submenu'   => [
                    [
                        'title'     => 'Все подразделения',
                        'route'     => 'admin.departments',
                        'icon'      => 'list',
                    ],
                    [
                        'title'     => 'Создать подразделение',
                        'route'     => 'admin.departments.create',
                        'icon'      => 'plus',
                    ],
                ],
            ],
        ],
        'visible'   => true,
    ],
    [
        'title'     => app('dictionary')->dictionary('menu')->key('settings')->get(),
        'items'     => [
            [
                'title'     => app('dictionary')->dictionary('menu')->key('site_settings')->get(),
                'route'     => 'admin.settings',
                'icon'      => 'settings',
            ],
            [
                'title'     => app('dictionary')->dictionary('menu')->key('dictionaries')->get(),
                // 'route'     => 'admin.dictionaries',
                'icon'      => 'book',
                'submenu'   => [
                    [
                        'title'     => app('dictionary')->dictionary('menu')->key('dictionaries_all')->get(),
                        'route'     => 'admin.dictionaries',
                        'icon'      => 'list',
                    ],
                    [
                        'title'     => app('dictionary')->dictionary('menu')->key('dictionary_create')->get(),
                        'route'     => 'admin.dictionaries.create',
                        'icon'      => 'plus',
                    ],
                ],
            ],
            [
                'title'     => app('dictionary')->dictionary('menu')->key('languages')->get(),
                'route'     => 'admin.languages',
                'icon'      => 'flag',
            ],
        ],
        'visible'   => true,
    ],
];
@endphp

<nav id="navigation_panel" data-staus="open">
    <div class="nav__head"></div>

    <div class="nav__body">
        @foreach ($menus as $menu)
            @if ($menu['visible'])
            <div class="nav__menu">
                @if (isset($menu['title']) && !is_null($menu['title']))
                <div class="nav__menu_title">{{ $menu['title'] }}</div>
                @endif

                @if (isset($menu['items']) && !empty($menu['items']))
                <ul class="nav__menu_items">
                    @foreach ($menu['items'] as $item)
                        @php
                        $routes     = [];
                        $submenu    = '';
                        @endphp

                        @if (isset($item['submenu']) && !empty($item['submenu']))
                        @php
                            $submenu    .=  '<ul class="nav__submenu_items">';
                            foreach ($item['submenu'] as $subitem) {
                                if (isset($subitem['route']) && !is_null($subitem['route'])) {
                                    $icon       = isset($subitem['icon']) && !is_null($subitem['icon']) ? '<span data-icon="'. $subitem['icon'] .'"></span>' : '<span data-icon="marker-circle"></span>';
                                    $routes[]   = $subitem['route'];

                                    $submenu    .=  '<li class="nav__submenu_item"'. (in_array(request()->route()->getName(), array($subitem['route'])) ? 'data-status=active' : '') .'>';
                                    $submenu    .=      '<a class="nav__item_title" href="'. route($subitem['route']) .'">'. $icon .'<span class="text">'. $subitem['title'] .'</span></a>';
                                    $submenu    .=  '</li>';
                                }
                            }
                            $submenu    .=  '</ul>';
                        @endphp
                        @endif

                        @php $icon = isset($item['icon']) && !is_null($item['icon']) ? '<span data-icon="'. $item['icon'] .'"></span>' : '<span data-icon="marker-circle"></span>'; @endphp
                        @if (isset($item['route']) && !is_null($item['route']))
                            @php
                            $routes[]   = $item['route'];
                            $item_title = '<a class="nav__item_title" href="'. route($item['route']) .'">'. $icon .'<span class="text">'. $item['title'] .'</span></a>';
                            @endphp
                        @else
                            @php
                            $item_title = '<span class="nav__item_title">'. $icon .'<span class="text">'. $item['title'] .'</span>'. (!empty($submenu) ? '<span class="nav__item_chevron" data-icon="chevron-left"></span>' : '') .'</span>';
                            @endphp
                        @endif
                        
                        @php
                        $data_status = array();
                        if(in_array(request()->route()->getName(), $routes)) {
                            $data_status[] = 'active';
                            $data_status[] = 'open';
                        }
                        @endphp

                        <li class="nav__menu_item" {!! !empty($data_status) ? 'data-status="'. implode(' ', $data_status) .'"' : '' !!}>
                            {!! $item_title !!}
                            
                            {!! $submenu !!}
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endif
        @endforeach
    </div>
</nav>