<div data-drag-drop-back="Переместите файл сюда"></div>

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
        <div class="group__box">
            <div class="group__container container">
            <div class="group__head">{{ app('dictionary')->dictionary('headers')->key('files_list')->get() }}</div>
            <div class="group__body">
                <div class="files">
                    <label class="add__file">
                        <input type="file" name="drop_file" multiple hidden />
                        <span class="add__icon"><span data-icon="plus"></span></span>
                        <span class="add__title">{{ app('dictionary')->dictionary('buttons')->key('add_or_drag')->get() }}</span>
                    </label>
                    
                    <div class="files_list">
                        @foreach($files as $file)
                        <div class="file">
                            @if(str_starts_with($file['mime_type'], 'image/'))
                            <div class="preview image">
                                <img src="{{ $file['path'] }}" />
                            </div>
                            @else
                            <div class="preview document">
                                <div class="document__icon"><span data-icon="file"></span></div>
                                <div class="document__title">{{ $file['name'] }}.{{ $file['extension'] }}</div>
                            </div>
                            @endif
                            <!-- <span class="copy_link" data-copy="{{ $file['path'] }}"><span data-icon="copy"></span></span> -->
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>