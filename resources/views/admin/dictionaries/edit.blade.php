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
            $lang_status[] = 'not_set';
        }
        @endphp
        <a class="tab" @if(!in_array('active', $lang_status) && !in_array('not_set', $lang_status)) href="" @endif data-tab="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @endif>
            <span clas="tab__text">{{ app('dictionary')->dictionary('languages')->key($language->locale_code)->get() }}</span>
        </a>
        @endforeach
    </div>

    <div class="tabs__box" data-tabs-box="landuages">
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="edit_dictionary">
            @php $lang_flag = true; @endphp
            @foreach(app('languages') as $language)
            @php
            $lang_status = [];
            if(isset($dictionary['name'][$language->locale_code]) && $lang_flag) {
                $lang_status[] = 'active';
                $lang_flag = false;
            }
            @endphp
            
            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @else style="display: none;" @endif>
                <div class="page__content">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('main_parameters')->get() }}</div>
                            <div class="group__body">
                                <div class="fields">
                                    <x-form.hidden
                                        name="dictionary[{{ $language->aid }}][required]"
                                        value="{{ isset($dictionary['name'][$language->locale_code]) || isset($dictionary['description'][$language->locale_code]) ? 'true' : 'false' }}"
                                        :data="[
                                            'required' => $language->locale_code .'_group',
                                        ]"
                                    />
                                    
                                    <x-form.hidden
                                        name="dictionary[{{ $language->aid }}][aid]"
                                        value="{{ $dictionary['aid'] }}"
                                    />

                                    <div class="field">
                                        <div class="field__head">
                                            <x-form.label
                                                for="name_{{ $language->locale_code }}"
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('name')->get() }}"
                                            />
                                        </div>
                                        <div class="field__body">
                                            <x-form.text
                                                id="name_{{ $language->locale_code }}"
                                                name="dictionary[{{ $language->aid }}][name]"
                                                value="{{ $dictionary['name'][$language->locale_code] ?? '' }}"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group'
                                                ]"
                                            />
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__head">
                                            <x-form.label
                                                for="description_{{ $language->locale_code }}"
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('description')->get() }}"
                                            />
                                        </div>
                                        <div class="field__body">
                                            <x-form.textarea
                                                id="description_{{ $language->locale_code }}"
                                                name="dictionary[{{ $language->aid }}][description]"
                                                value="{{ $dictionary['description'][$language->locale_code] ?? '' }}"
                                                required_group="{{ $language->locale_code }}_group"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group'
                                                ]"
                                            />
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__head">
                                            <x-form.label
                                                for="alias_{{ $language->locale_code }}"
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('alias')->get() }}"
                                            />
                                        </div>
                                        <div class="field__body">
                                            <x-form.text
                                                id="alias_{{ $language->locale_code }}"
                                                name="dictionary[{{ $language->aid }}][alias]"
                                                value="{{ $dictionary['alias'] }}"
                                                disabled
                                                :data="[
                                                    'sync' => 'alias',
                                                    'required-group' => $language->locale_code .'_group'
                                                ]"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="group__panel_footer">
                                    <x-form.submit
                                        id="save"
                                        class="success"
                                        name="save"
                                        title="{{ app('dictionary')->dictionary('buttons')->key('save')->get() }}"
                                        icon="save"
                                    />

                                    <x-form.submit
                                        id="save_and_return"
                                        class="success"
                                        name="save_and_return"
                                        title="{{ app('dictionary')->dictionary('buttons')->key('save_and_close')->get() }}"
                                        icon="save"
                                    />

                                    <a class="button error" href="{{ route('admin.dictionaries.view', ['aid' => $dictionary['aid']]) }}">
                                        <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('cancel')->get() }}</span>
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
                                <div class="group__grid_list" data-items="term_list" data-items-lang="{{ $language->locale_code }}">
                                    @forelse($dictionary['items'] as $item)
                                    <x-form.hidden
                                        name="terms[{{ $loop->iteration }}][{{ $language->aid }}][aid]"
                                        value="{{ $item['aid'] }}"
                                    />
                                    <x-items.add-term
                                        index="{{ $loop->iteration }}"
                                        locale="{{ $language->aid }}"
                                        :form_data="[
                                            'name' => [
                                                'value' => $item['item_value'][$language->locale_code] ?? '',
                                            ],
                                            'description' => [
                                                'value' => $item['description'][$language->locale_code] ?? '',
                                            ],
                                            'alias' => [
                                                'value' => $item['item_key'],
                                                'disabled' => true,
                                            ]
                                        ]"
                                    />
                                    @empty
                                    <x-items.add-term locale="{{ $language->aid }}" />
                                    @endforelse
                                </div>

                                <div class="group__panel_footer flex__row_center">
                                    <div class="button" data-item-add="add-term" data-item-list="term_list">
                                        <span class="button__icon"><span data-icon="plus"></span></span>
                                        <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('term_add')->get() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </form>
    </div>
</div>