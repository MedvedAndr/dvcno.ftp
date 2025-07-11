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
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="create_departments">
            @foreach(app('languages') as $language)
            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif>
                <div class="page__content __with_right_col">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">Контент</div>
                            <div class="group__body">
                                <div class="fields">
                                    <x-form.hidden
                                        name="departments[{{ $language->aid }}][required]"
                                        value="false"
                                        :data="[
                                            'required' => $language->locale_code .'_group'
                                        ]"
                                    />

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                id="name_{{ $language->locale_code }}"
                                                name="departments[{{ $language->aid }}][title]"
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('name')->get() }}"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group',
                                                    'slugifier' => 'departments['. $language->aid .'][slug].departments.title',
                                                ]"
                                            />
                                            <x-form.edited_string
                                                id="slug_{{ $language->locale_code }}"
                                                name="departments[{{ $language->aid }}][slug]"
                                                before="Постоянная ссылка: /departments/"
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
                                                name="departments[{{ $language->aid }}][subtitle]"
                                                title="Краткое название " class="h100"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][site]"
                                                title="Сайт"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.ckeditor
                                                name="departments[{{ $language->aid }}][address]"
                                                title="Адрес" class="h100"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__head">
                                            <x-form.label title="Телефоны:"/>
                                        </div>
                                        <div class="field__body">

                                            <div data-list="add_phone" data-list-lang="{{ $language->locale_code }}">
                                                <x-items.phone locale="{{ $language->aid }}" :form_data="['phone'=> 'dfsdfsdfsdfsd']" />
                                            </div>
                                            <div data-action="add" data-component="items.phone" data-target-container="add_phone" data-multi-language="true">Добавить</div>

                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__head">
                                            <x-form.label title="Электронная почта:"/>
                                        </div>
                                        <div class="field__body">
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][emails][0]"
                                            />{{--  --}}
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][emails][1]"
                                            />{{--  --}}
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][emails][2]"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.ckeditor
                                                name="departments[{{ $language->aid }}][shedule]"
                                                title="График" class="h100"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__head">
                                            <x-form.label title="Социальные сети:"/>
                                        </div>
                                        <div class="field__body">
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][social][whatsup]"
                                            />{{--  --}}
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][social][telegram]"
                                            />{{--  --}}
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][social][vk]"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                name="departments[{{ $language->aid }}][link_to_place]"
                                                title="Ссылка на карте"
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
                                                <x-form.togglebox
                                                    name="departments[{{ $language->aid }}][enabled]"
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

                                        <a class="button error" href="{{ route('admin.departments') }}">
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
                                    <div class="file__panel" data-file="departments_images">
                                        {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                                        <x-form.hidden
                                            class="file__input"
                                            name="departments[{{ $language->aid }}][images]"
                                            value=""
                                        />
                                        <div class="file__body">
                                            <div class="file_info">

                                            </div>
                                        </div>
                                        
                                        {{-- TODO: вынести кнопку в отдельную компоненту --}}
                                        <div class="button" data-file-manager="" data-type="multiple" data-extensions="png jpg jpeg webp svg">Выбрать</div>
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
<x-file-manager />