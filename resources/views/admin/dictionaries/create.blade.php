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
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="create_dictionary">
            @foreach(app('languages') as $language)
            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if($loop->first) data-status="active" @else style="display: none;" @endif>
                <div class="page__content">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('main_parameters')->get() }}</div>
                            <div class="group__body">
                                <div class="fields">
                                    <x-form.hidden
                                        name="dictionary[{{ $language->aid }}][required]"
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
                                                name="dictionary[{{ $language->aid }}][name]"
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
                                                :data="[
                                                    'required-group' => $language->locale_code .'_group',
                                                    'sync' => 'alias'
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

                                    <a class="button error" href="{{ route('admin.dictionaries') }}">
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
                                <div class="group__grid_list" data-list="term_list" data-list-lang="{{ $language->locale_code }}"  data-empty="items.term">
                                    <x-items.term locale="{{ $language->aid }}" />
                                    {{-- <x-items.add-term index="2" locale="{{ $language->locale_code }}" /> --}}
                                    {{-- <x-items.add-term index="3" locale="{{ $language->locale_code }}" /> --}}
                                    {{-- <x-items.add-term index="4" locale="{{ $language->locale_code }}" /> --}}
                                    {{-- <x-items.add-term index="5" locale="{{ $language->locale_code }}" /> --}}
                                </div>

                                <div class="group__panel_footer flex__row_center">
                                    <div class="button" data-action="add" data-component="items.term" data-target-container="term_list" data-multi-language="true">
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