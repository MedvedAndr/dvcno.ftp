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
        {{-- app('dictionary')->dictionary('technical_terms')->key('page_dev')->get() --}}
        <div class="group__box">
            <div class="group__container container">
                <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('dictionaries_list')->get() }}</div>
                <div class="group__body">
                    <div class="group__panel">
                        <a class="button" href="{{ route('admin.dictionaries.create') }}">
                            <span class="button__icon"><span data-icon="plus"></span></span>
                            <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('create')->get() }}</span>
                        </a>
                    </div>
                    
                    @if(!empty($dictionaries_list))
                    <table data-table="main">
                        <thead>
                            <tr>
                                {{-- <td></td> --}}
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('name')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('description')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('alias')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('languages')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('management')->get() }}</td>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($dictionaries_list as $dictionary)
                            <tr>
                                {{-- <td></td> --}}
                                <td>
                                    @if(isset($dictionary['name'][app('locale')]))
                                    {{ $dictionary['name'][app('locale')] }}
                                    @else
                                    {{ reset($dictionary['name']) }}
                                    @endif
                                </td>
                                <td>
                                    @if(isset($dictionary['description'][app('locale')]))
                                    {!! nl2br($dictionary['description'][app('locale')]) !!}
                                    @else
                                    {!! nl2br(reset($dictionary['description'])) !!}
                                    @endif
                                </td>
                                <td>{{ $dictionary['alias'] }}</td>
                                <td class="center">
                                    @foreach(app('languages') as $language)
                                    <span class="lang"
                                        @if(!isset($dictionary['name'][$language->locale_code])) data-status="enabled" @endif
                                    >{{ $language->locale_code }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="flex">
                                        <a class="button" href="{{ route('admin.dictionaries.view', ['aid' => $dictionary['aid']]) }}" title="Просмотреть"><span data-icon="search"></span></a>
                                        <a class="button" href="{{ route('admin.dictionaries.edit', ['aid' => $dictionary['aid']]) }}" title="Редактировать"><span data-icon="edit"></span></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty_content">{!! app('dictionary')->dictionary('technical_terms')->key('dictionaries_list_empty')->get() !!}</div>
                    @endif

                    <div class="group__panel_footer">
                        <a class="button" href="{{ route('admin.dictionaries.create') }}">
                            <span class="button__icon"><span data-icon="plus"></span></span>
                            <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('create')->get() }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>