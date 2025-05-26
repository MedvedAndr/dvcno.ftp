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

    <div class="alert" data-status="info">
        {{ app('dictionary')->dictionary('technical_terms')->key('func_dev')->get() }}
    </div>

    <div class="tabs" data-tabs="landuages">
        @php $lang_flag = true; @endphp
        @foreach(app('languages') as $language)
        @php
        $lang_status = [];
        if(isset($menu['title'][$language->locale_code]) && $lang_flag) {
            $lang_status[] = 'active';
            $lang_flag = false;
        }
        elseif(!isset($menu['title'][$language->locale_code])) {
            $lang_status[] = 'disabled';
        }
        @endphp
        <a class="tab" @if(!in_array('active', $lang_status) && !in_array('disabled', $lang_status)) href="" @endif data-tab="{{ $language->locale_code }}" @if(!empty($lang_status)) data-status="{{ implode(' ', $lang_status) }}" @endif>
            <span clas="tab__text">{{ app('dictionary')->dictionary('languages')->key($language->locale_code)->get() }}</span>
        </a>
        @endforeach
    </div>
</div>