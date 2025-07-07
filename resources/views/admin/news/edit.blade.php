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
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="edit_news">
            @foreach(app('languages') as $language)
            <x-form.hidden name="news[{{ $language->aid }}][aid]" value="{{$news['aid']}}"/>
            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @endif>
                <div class="page__content __with_right_col">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">Контент</div>
                            <div class="group__body">
                                <div class="fields">
                                    @dump($news)
                                    <x-form.hidden
                                        name="news[{{ $language->aid }}][required]"
                                        value="{{ isset($news['title'][$language->locale_code]) || isset($news['content'][$language->locale_code]) ? 'true' : 'false' }}"
                                        :data="[
                                            'required' => $language->locale_code .'_group'
                                        ]"
                                    />

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                id="name_{{ $language->locale_code }}"
                                                name="news[{{ $language->aid }}][title]"
                                                title="{{ app('dictionary')->dictionary('form_labels')->key('name')->get() }}"
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group',
                                                    'slugifier' => 'news['. $language->aid .'][slug].news.title']"
                                                value="{{ $news['title'][$language->locale_code] ?? ''}}"
                                            />
                                            <x-form.edited_string
                                                id="slug_{{ $language->locale_code }}"
                                                name="news[{{ $language->aid }}][slug]"
                                                before="Постоянная ссылка: /news/"
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
                                                name="news[{{ $language->aid }}][content]"
                                                title="Контент"
                                                value="{{ $news['content'][$language->locale_code] ?? ''}}"
                                            />
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.ckeditor
                                                name="news[{{ $language->aid }}][description]"
                                                title="Краткое описание"
                                                value="{{ $news['description'][$language->locale_code] ?? ''}}"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                name="news[{{ $language->aid }}][subtitle]"
                                                title="Подзаголовок"
                                                value="{{ $news['subtitle'][$language->locale_code] ?? ''}}"
                                            />{{--  --}}
                                        </div>
                                    </div>

                                    <div class="field">
                                        <div class="field__body">
                                            <x-form.text
                                                name="news[{{ $language->aid }}][time_to_read]"
                                                title="Время чтения"
                                                value="{{ $news['time_to_read'][$language->locale_code] ?? ''}}"
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
                                                    name="news[{{ $language->aid }}][date_from]"
                                                    title="Начало публикации"
                                                />
                                            </div>
                                        </div>
                                    
                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.datetime
                                                    name="news[{{ $language->aid }}][date_to]"
                                                    title="Окончание публикации"
                                                />
                                            </div>
                                        </div>
                                    
                                        <div class="field">
                                            <div class="field__body">
                                                <x-form.togglebox
                                                    name="news[{{ $language->aid }}][enabled]"
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
                                    <div class="file__panel" data-file="news_images">
                                        {{-- TODO: переделать на выбор нескольких файлов (сейчас логика лько для одного файла) --}}    
                                        <x-form.hidden
                                            class="file__input"
                                            name="news[{{ $language->aid }}][images]"
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