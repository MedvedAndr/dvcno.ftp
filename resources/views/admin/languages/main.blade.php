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
                <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('languages_list')->get() }}</div>
                <div class="group__body">
                    @if(!empty($languages_list))
                    <table data-table="main">
                        <thead>
                            <tr>
                                {{-- <td></td> --}}
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('name')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('name_original')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('locale')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('locale_code')->get() }}</td>
                                <td>{{ app('dictionary')->dictionary('table_headers')->key('status')->get() }}</td>
                                {{-- <td>Управление</td> --}}
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($languages_list as $language)
                            <tr @if(!$language->enabled) data-status="enabled" @endif>
                                {{-- <td></td> --}}
                                <td>{{ $language->name }}</td>
                                <td>{{ $language->native_name }}</td>
                                <td class="center">{{ $language->locale }}</td>
                                <td class="center">{{ $language->locale_code }}</td>
                                <td class="center">{{ app('dictionary')->dictionary('technical_terms')->key('enabled_'. $language->enabled)->get() }}</td>
                                {{-- <td></td> --}}
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty_content">{!! app('dictionary')->dictionary('technical_terms')->key('languages_list_empty')->get() !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>