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
                <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('site_settings')->get() }}</div>
                <div class="group__body">
                    <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="edit_settings">
                        <div class="group__panel">
                            <x-form.submit
                                id="save"
                                class="success"
                                name="save"
                                title="{{ app('dictionary')->dictionary('buttons')->key('save')->get() }}"
                                icon="save"
                            />
                        </div>

                        <div class="flex__row">
                            <div class="pills __vertical" data-tabs="settings">
                                <a class="pill" href="" data-tab="main" data-status="active">{{ app('dictionary')->dictionary('buttons')->key('main_settings')->get() }}</a>
                                <a class="pill" href="" data-tab="file_system">{{ app('dictionary')->dictionary('buttons')->key('file_management')->get() }}</a>
                            </div>
                            
                            <div class="pills__box __vertical" data-tabs-box="settings">
                                <div class="settings_groups" data-tab-box="main" data-status="active">
                                    @foreach($main_settings as $group_key => $settings)
                                    <div class="settings_group">
                                        <div class="h4">{{ app('dictionary')->dictionary('headers')->key($group_key)->get() }}</div>

                                        <div class="fields">
                                            @foreach($settings as $setting)
                                            <div class="field">
                                                <div class="field__head">
                                                    <x-form.label
                                                        title="{{ app('dictionary')->dictionary('form_labels')->key($setting['setting_key'])->get() }}"
                                                    />
                                                </div>
                                                
                                                <div class="field__body">
                                                    <x-form.hidden
                                                        name="settings[{{ $setting['setting_key'] }}][aid]"
                                                        value="{{ $setting['aid'] }}"
                                                    />
                                                    <x-form.hidden
                                                        name="settings[{{ $setting['setting_key'] }}][setting_key]"
                                                        value="{{ $setting['setting_key'] }}"
                                                    />
                                                @if($setting['type'] === 'toggle')
                                                    <x-form.togglebox
                                                        name="settings[{{ $setting['setting_key'] }}][setting_value]"
                                                        checked="{{ $setting['setting_value'] === '1' ? true : false }}"
                                                        title="{{ app('dictionary')->dictionary('form_labels')->key('flick_on')->get() }}"
                                                        title_checked="{{ app('dictionary')->dictionary('form_labels')->key('flick_off')->get() }}"
                                                    />
                                                @elseif($setting['type'] === 'textarea')
                                                    <x-form.textarea
                                                        name="settings[{{ $setting['setting_key'] }}][setting_value]"
                                                        value="{{ $setting['setting_value'] }}"
                                                    />
                                                @else
                                                    <x-form.text
                                                        name="settings[{{ $setting['setting_key'] }}][setting_value]"
                                                        value="{{ $setting['setting_value'] }}"
                                                    />
                                                @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div data-tab-box="file_system" style="display: none;">
                                    <div class="h5">{{ app('dictionary')->dictionary('headers')->key('supported_files')->get() }}</div>
                                    
                                    <div class="fields">
                                        @foreach($file_system as $item)
                                        <div class="field">
                                            <div class="field__head">
                                                <x-form.label
                                                    title="{{ app('dictionary')->dictionary('form_labels')->key($item['setting_key'])->get() }}"
                                                />
                                            </div>
                                            <div class="field__body">
                                                @if($item['type'] === 'data_set')
                                                <div class="items" data-list="file_types" data-empty="items.file-type">
                                                    {{-- @dump(json_decode($item['value'], true)) --}}
                                                    @foreach (json_decode($item['setting_value'], true) as $file_type)
                                                    <x-form.hidden
                                                        name="settings[{{ $item['setting_key'] }}][aid]"
                                                        value="{{ $item['aid'] }}"
                                                    />

                                                    <x-form.hidden
                                                        name="settings[{{ $item['setting_key'] }}][setting_key]"
                                                        value="{{ $item['setting_key'] }}"
                                                    />

                                                    <x-items.file-type
                                                        index="{{ $loop->iteration }}"
                                                        :form_data="[
                                                            'file_type' => [
                                                                'value' => $file_type['type']
                                                            ],
                                                            'file_size' => [
                                                                'value' => $file_type['size']
                                                            ],
                                                        ]"
                                                    />
                                                    @endforeach
                                                </div>

                                                <div class="flex__row_center">
                                                    <div class="button" data-action="add" data-component="items.file-type" data-target-container="file_types">
                                                        <span class="button__icon"><span data-icon="plus"></span></span>
                                                        <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('file_type_add')->get() }}</span>
                                                    </div>
                                                </div>
                                                @else

                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>