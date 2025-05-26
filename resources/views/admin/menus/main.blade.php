<div class="container">
    @if(isset($title) || isset($breadcrumbs))
    <div class="page__head">
        @isset($title)
        <h1>{{ $title }}</h1>
        @endisset

        @isset($breadcrumbs)
        <x-breadcrumbs :list="$breadcrumbs" />
        @endisset
    </div>
    @endif

    <div class="page__content">
        <div class="group__box">
            <div class="group__container container">
                <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('menu_list')->get() }}</div>
                <div class="group__body">
                    @if(!empty($menu_list))
                    <table data-table="main">
                        <thead>
                            <tr>
                                <td>Название</td>
                                <td>Описание</td>
                                <td>Псевдоним</td>
                                <td>Статус</td>
                                <td>Языки</td>
                                <td>Управление</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menu_list as $menu)
                            <tr>
                                <td>
                                    @if(isset($menu['title'][app('locale')]))
                                    {{ $menu['title'][app('locale')] }}
                                    @else
                                    {{ reset($menu['title']) }}
                                    @endif
                                </td>
                                <td>
                                    @if(isset($menu['description'][app('locale')]))
                                    {!! nl2br($menu['description'][app('locale')]) !!}
                                    @else
                                    {!! nl2br(reset($menu['description'])) !!}
                                    @endif
                                </td>
                                <td>{{ $menu['alias'] }}</td>
                                <td class="center">{{ app('dictionary')->dictionary('technical_terms')->key('enabled_'. $menu['enabled'])->get() }}</td>
                                <td class="center">
                                    @foreach(app('languages') as $language)
                                    <span class="lang"
                                        @if(!isset($menu['title'][$language->locale_code])) data-status="enabled" @endif
                                    >{{ $language->locale_code }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="flex">
                                        <a class="button" href="{{ route('admin.menus.edit', ['aid' => $menu['aid']]) }}" title="Редактировать"><span data-icon="edit"></span></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty_content">{!! app('dictionary')->dictionary('technical_terms')->key('menu_list_empty')->get() !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>