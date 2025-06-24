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
        @foreach(app('languages') as $language)
        <a class="tab" href="" data-tab="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif>
            <span clas="tab__text">{{ app('dictionary')->dictionary('languages')->key($language->locale_code)->get() }}</span>
        </a>
        @endforeach
    </div>

    <div class="tabs__box" data-tabs-box="landuages">
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="create_menu">
        {{-- <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="create_menu" style="display: flex; gap: 20px;"> --}}
            @foreach(app('languages') as $language)
            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif>
            {{-- <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif style="display: block;"> --}}
                <div class="page__content">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('main_parameters')->get() }}</div>
                            <div class="group__body">
                                <div class="group__panel">
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

                                    <a class="button error" href="{{ route('admin.menus') }}">
                                        <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('cancel')->get() }}</span>
                                        <span class="button__icon"><span data-icon="x"></span></span>
                                    </a>
                                </div>

                                <div class="fields">
                                    <x-form.hidden
                                        name="menu[{{ $language->aid }}][required]"
                                        value="false"
                                        :data="[
                                            'required' => $language->locale_code .'_group'
                                        ]"
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
                                                name="menu[{{ $language->aid }}][title]"
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
                                                name="menu[{{ $language->aid }}][description]"
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
                                                name="menu[{{ $language->aid }}][alias]"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group',
                                                    'sync' => 'alias'
                                                ]"
                                            />
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__head"></div>
                                        <div class="field__body">
                                            <x-form.togglebox
                                                name="menu[{{ $language->aid }}][enabled]"
                                                checked
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('enabled')->get() }}"
                                                title_checked="{{ app('dictionary')->dictionary('form_labels')->key('disabled')->get() }}"
                                                :data="[
                                                    'sync' => 'enabled'
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

                                    <a class="button error" href="{{ route('admin.menus') }}">
                                        <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('cancel')->get() }}</span>
                                        <span class="button__icon"><span data-icon="x"></span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="page__content __with_left_col">
                        <div class="group__box">
                            <div class="group__container container">
                                <div class="group__head">Добавьте элементы меню</div>
                            </div>    
                            <div class="accordions" data-accordion="menu_elements">
                                <div class="accordion" data-status="active">
                                    <div class="accordion__head">
                                        <div class="accordion__head_title">Произвольная ссылка</div>
                                        <div class="accordion__head_icon"><span data-icon></span></div>
                                    </div>
                                    <div class="accordion__body" style="display: block;">
                                        <div class="flex__col">    
                                            <x-form.text
                                                name="elements[{{ $language->locale_code }}][menu_url]"
                                                :data="[
                                                    'item-data' => 'add-permalink',
                                                    'sync' => 'permalink_url'
                                                ]"
                                                title="URL"
                                            />
                                            <x-form.text
                                                name="elements[{{ $language->locale_code }}][menu_name]"
                                                :data="[
                                                    'item-data' => 'add-permalink'
                                                ]"
                                                title="Текст ссылки"
                                            />
                                            <div class="button" data-item-add="add-permalink" data-item-list="menu_layout">Добавить</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="group__box">
                            <div class="group__container container">
                                <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('menu_structure')->get() }}</div>
                                <div class="group__body">
                                    <div class="items" data-items="menu_layout" data-items-lang="{{ $language->locale_code }}"></div>
                                    <div class="empty">Добавьте элементы меню из столбца слева.</div>
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