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
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="create_event">
        {{-- <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="create_event" style="display: flex;"> --}}
            @foreach(app('languages') as $language)
            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif>
            {{-- <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif style="display: block;"> --}}
                <div class="page__content __with_right_col">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">Контент</div>
                            <div class="group__body">
                                <div class="fields">
                                    <x-form.hidden
                                        name="event[{{ $language->aid }}][required]"
                                        value="false"
                                        :data="[
                                            'required' => $language->locale_code .'_group'
                                        ]"
                                    />

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                id="name_{{ $language->locale_code }}"
                                                name="event[{{ $language->aid }}][title]"
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('name')->get() }}"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group',
                                                    'slugifier' => 'event['. $language->aid .'][slug].events.title',
                                                ]"
                                            />
                                            <x-form.edited_string
                                                id="slug_{{ $language->locale_code }}"
                                                name="event[{{ $language->aid }}][slug]"
                                                before="Постоянная ссылка: /event/"
                                                after="/"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group',
                                                    'sync' => 'slug',
                                                ]"
                                                status="hidden"
                                            />
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.ckeditor
                                                name="event[{{ $language->aid }}][content]"
                                                title="Контент"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.ckeditor
                                                name="event[{{ $language->aid }}][description]"
                                                title="Краткое описание"
                                            />{{--  --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex__col">
                        <div class="group__box">
                            <div class="group__container container">
                                <div class="group__head">Панель управления</div>
                                <div class="group__body">
                                    <div class="fields">
                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.datetime
                                                    name="event[{{ $language->aid }}][date_from]"
                                                    title="Начало публикации"
                                                />
                                            </div>
                                        </div>
                                    
                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.datetime
                                                    name="event[{{ $language->aid }}][date_to]"
                                                    title="Окончание публикации"
                                                />
                                            </div>
                                        </div>
                                    
                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.togglebox
                                                    name="event[{{ $language->aid }}][enabled]"
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

                                        <a class="button error" href="{{ route('admin.events') }}">
                                            <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('cancel')->get() }}</span>
                                            <span class="button__icon"><span data-icon="x"></span></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="group__box">
                            <div class="group__container container">
                                <div class="group__head">Изображение</div>
                                <div class="group__body">
                                    В разработке
                                </div>
                            </div>
                        </div>

                        <div class="group__box">
                            <div class="group__container container">
                                <div class="group__head">Информация о мероприятии</div>
                                <div class="group__body">
                                    <div class="fields">
                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.datetime
                                                    name="event[{{ $language->aid }}][date_event]"
                                                    title="Дата проведения"
                                                    message
                                                />
                                            </div>
                                        </div>

                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.text
                                                    name="event[{{ $language->aid }}][address]"
                                                    title="Место проведения"
                                                    :data="[
                                                        'required-group' => $language->locale_code .'_group'
                                                    ]"
                                                />
                                            </div>
                                        </div>

                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.text
                                                    name="event[{{ $language->aid }}][link_to_map]"
                                                    title="Ссылка на карту"
                                                    :data="[
                                                        'required-group' => $language->locale_code .'_group',
                                                        'sync' => 'enabled'
                                                    ]"
                                                />
                                            </div>
                                        </div>
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