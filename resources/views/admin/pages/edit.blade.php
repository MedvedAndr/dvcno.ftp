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
        if(isset($page['title'][$language->locale_code]) && $lang_flag) {
            $lang_status[] = 'active';
            $lang_flag = false;
        }
        elseif(!isset($page['title'][$language->locale_code])) {
            $lang_status[] = 'not_set';
        }
        @endphp
        <a class="tab" @if(!in_array('active', $lang_status) && !in_array('not_set', $lang_status)) href="" @endif data-tab="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @endif>
            <span clas="tab__text">{{ app('dictionary')->dictionary('languages')->key($language->locale_code)->get() }}</span>
        </a>
        @endforeach
    </div>

    <div class="tabs__box" data-tabs-box="landuages">
        <form action="{{ route('ajax.form.validation') }}" method="POST" data-form="edit_page">
            @php $lang_flag = true; @endphp
            @foreach(app('languages') as $language)
            @php
            $lang_status = [];
            if(isset($page['title'][$language->locale_code]) && $lang_flag) {
                $lang_status[] = 'active';
                $lang_flag = false;
            }
            @endphp

            <div class="tab__box" data-tab-box="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @else style="display: none;" @endif>
                <div class="page__content">
                    <div class="group__box">
                        <div class="group__container container">
                            <div class="group__head">Секции</div>
                            <div class="group__body flex__col">
                                <div class="group__panel">
                                    <x-form.submit
                                        id="save"
                                        class="success"
                                        name="save"
                                        title="{{ app('dictionary')->dictionary('buttons')->key('save')->get() }}"
                                        icon="save"
                                    />
                                </div>
                                @php $index = 1 @endphp
                                @foreach($page['sections'] as $section)
                                @switch($section['type'])
                                    @case('header')
                                        <x-sections.header
                                            index="{{ $index }}"
                                            locale="{{ $language->aid }}"
                                            :data="[
                                                'aid' => $section['aid'],
                                                'title' => $section['title'][$language->locale_code] ?? '',
                                                'content' => $section['content'][$language->locale_code] ?? '',
                                            ]"
                                        />
                                        @break
                                    @case('format_text')
                                        <x-sections.format-text
                                            index="{{ $index }}"
                                            locale="{{ $language->aid }}"
                                            :data="[
                                                'aid' => $section['aid'],
                                                'title' => $section['title'][$language->locale_code] ?? '',
                                                'content' => $section['content'][$language->locale_code] ?? '',
                                            ]"
                                        />
                                        @break
                                    @case('banner')
                                        <x-sections.banner
                                            index="{{ $index }}"
                                            locale="{{ $language->aid }}"
                                            :data="[
                                                'aid' => $section['aid'],
                                                'content' => $section['content'][$language->locale_code] ?? [],
                                            ]"
                                        />
                                        @break
                                    {{-- @case('banner_docs')
                                        @break --}}
                                    {{-- @case('slider')
                                        @break --}}
                                    {{-- @case('gallery')
                                        @break --}}
                                    {{-- @case('section')
                                        @break --}}
                                    @case('list_links')
                                        <x-sections.list-links
                                            index="{{ $index }}"
                                            :locale="$language"
                                            :data="[
                                                'aid' => $section['aid'],
                                                'title' => $section['title'][$language->locale_code] ?? '',
                                                'content' => $section['content'][$language->locale_code] ?? [],
                                            ]"
                                        />
                                        @break
                                    {{-- @case('list_block')
                                        @break --}}
                                    @case('list_docs')
                                        <x-sections.list-docs
                                            index="{{ $index }}"
                                            :locale="$language"
                                            :data="[
                                                'aid' => $section['aid'],
                                                'title' => $section['title'][$language->locale_code] ?? '',
                                                'content' => $section['content'][$language->locale_code] ?? [],
                                            ]"
                                        />
                                        @break
                                    @case('accordion')
                                        {{--
                                        @dump($section)
                                        --}}
                                        <x-sections.accordion
                                            index="{{ $index }}"
                                            :locale="$language"
                                            :data="[
                                                'aid' => $section['aid'],
                                                'title' => $section['title'][$language->locale_code] ?? '',
                                                'content' => $section['content'][$language->locale_code] ?? [],
                                            ]"
                                        />
                                        @break
                                @endswitch
                                @php $index++ @endphp
                                @endforeach
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