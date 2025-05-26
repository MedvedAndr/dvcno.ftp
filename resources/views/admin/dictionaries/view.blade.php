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
    
    <div class="tabs" data-tabs="landuages">
        @php $lang_flag = true; @endphp
        @foreach(app('languages') as $language)
        @php
        $lang_status = [];
        if(isset($dictionary['name'][$language->locale_code]) && $lang_flag) {
            $lang_status[] = 'active';
            $lang_flag = false;
        }
        elseif(!isset($dictionary['name'][$language->locale_code])) {
            $lang_status[] = 'disabled';
        }
        @endphp
        <a class="tab" @if(!in_array('active', $lang_status) && !in_array('disabled', $lang_status)) href="" @endif data-tab="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @endif>
            <span clas="tab__text">{{ app('dictionary')->dictionary('languages')->key($language->locale_code)->get() }}</span>
        </a>
        @endforeach
    </div>

    <div class="tabs__box" data-tabs-box="landuages">
        @php $lang_flag = true; @endphp
        @foreach(app('languages') as $language)
        @php
        $lang_status = [];
        if(isset($dictionary['name'][$language->locale_code]) && $lang_flag) {
            $lang_status[] = 'active';
            $lang_flag = false;
        }
        @endphp
        @if(isset($dictionary['name'][$language->locale_code]))
        <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @else style="display: none;" @endif>
            <div class="page__content">
                <div class="group__box">
                    <div class="group__container container">
                        <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('main_parameters')->get() }}</div>
                        <div class="group__body">
                            <table data-table="main">
                                <tbody>
                                    <tr>
                                        <td width="25%">{{ app('dictionary')->dictionary('table_headers')->key('name')->get() }}</td>
                                        <td>{{ $dictionary['name'][$language->locale_code] }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('description')->get() }}</td>
                                        <td>{{ $dictionary['description'][$language->locale_code] }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('alias')->get() }}</td>
                                        <td>{{ $dictionary['alias'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('date_create')->get() }}</td>
                                        <td>
                                            @if(!is_null($dictionary['created_at']))
                                            {{ date('d.m.Y H:i:s', $dictionary['created_at']) }}
                                            @else
                                            {{ app('dictionary')->dictionary('technical_terms')->key('no_data')->get() }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('date_update')->get() }}</td>
                                        <td>
                                            @if(!is_null($dictionary['updated_at']))
                                            {{ date('d.m.Y H:i:s', $dictionary['updated_at']) }}
                                            @else
                                            {{ app('dictionary')->dictionary('technical_terms')->key('no_data')->get() }}
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="group__panel_footer">
                                <a class="button" href="{{ route('admin.dictionaries.edit', ['aid' => $dictionary['aid']]) }}">
                                    <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('edit')->get() }}</span>
                                    <span class="button__icon"><span data-icon="edit"></span></span>
                                </a>
                                <a class="button" href="{{ route('admin.dictionaries') }}">
                                    <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('close')->get() }}</span>
                                    <span class="button__icon"><span data-icon="x"></span></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="group__box">
                    <div class="group__container container">
                        <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('dictionary_items_list')->get() }}</div>
                        <div class="group__body">
                            @if(!empty($dictionary['items']))
                            <table data-table="main">
                                <thead>
                                    <tr>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('name')->get() }}</td>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('alias')->get() }}</td>
                                        <td>{{ app('dictionary')->dictionary('table_headers')->key('description')->get() }}</td>
                                    </tr>
                                </thead>    
                                
                                <tbody>
                                    @foreach($dictionary['items'] as $dictionary_item)
                                    @if(isset($dictionary_item['item_value'][$language->locale_code]))
                                    <tr>
                                        <td>{{ $dictionary_item['item_value'][$language->locale_code] }}</td>
                                        <td>{{ $dictionary_item['item_key'] }}</td>
                                        <td>{{ $dictionary_item['description'][$language->locale_code] }}</td>
                                    </tr>
                                    @else
                                    <tr data-status="not_set">
                                        <td>{{ app('dictionary')->dictionary('technical_terms')->key('not_set')->get() }}</td>
                                        <td>{{ $dictionary_item['item_key'] }}</td>
                                        <td>{{ app('dictionary')->dictionary('technical_terms')->key('not_set')->get() }}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div>{{ app('dictionary')->dictionary('technical_terms')->key('terms_list_empty')->get() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>