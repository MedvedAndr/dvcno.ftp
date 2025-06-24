<div data-drag-drop-back="Переместите файл сюда"></div>
@php
//phpinfo();
@endphp
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
                            <div class="file" data-aid="{{$file['aid']}}">
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

                                <div class='edit_button'>
                                    <a class='button' data-popup="popup__edit_img">
                                        <span class='button__icon'>
                                        <span data-icon='edit'></span>
                                        </span>
                                        <span class='button__title'>Редактировать</span>
                                    </a>
                                </div>

                            </div>
                            @endforeach
                        </div>

                    </div>
                </div> 
            </div>           
        </div>

        {{--
        <x-filemanager />
        --}}
        
    </div>
</div>

<div id="popup__edit_img" class="popup">
    <div class="popup__container">
        <div class="group__edit_head">Редактирование
            <div class="popup__close"><span data-icon='x-circle'></span></div>
        </div>
        <div class="group__edit_body">

            <form action="{{route("ajax.form.validation")}}" method="POST" data-form="file_edit">
            <x-form.hidden name="file_aid" value="" />
            <div class="page__content __with_right_col">
                <div class="group__box">
                    <div class="group__container container">
                        <div class="group__head">Изображение</div>
                        <div class="group__edit_body">
                            <div class="items_empty">
                                <img src="" alt="" class="group__edit_img">
                            </div>
                        </div>
                    </div>    

                </div>

                <div class="group__box">
                    <div class="group__container container">
                        <div class="group__head">Инфо</div>
                        <div class="group__edit_body">
                            <div class="items" data-items="menu_layout" data-items-lang="ru"></div>
                            <div class="items_empty">
                                <div>Расширение: <span id="group__edit_ext"></span></div>
                            </div>
                            
                            <div class="items_empty">Имя: <input type="text" value="" name="name" id="group__edit_name"></div>
                            <div class="items_empty">Атрибут alt: <input type="text" value="" name="alt" id="group__edit_alt"></div>
                            
                            <div class="items_empty">
                                <div>Размер: <span id="group__edit_size"></span> байт</div>
                            </div>

                            <div class="items_empty">
                                <div>Дата загрузки: <span id="group__edit_updated"></span></div>
                            </div>

                            {{--
                            <button type="submit" class='button' id="group__edit_save" value="save">
                                <span class='button__icon'>
                                    <span data-icon='save'></span>
                                </span>
                                <span class='button__title'>Сохранить</span>
                            </button>
                            --}}

                            <x-form.submit
                            id="group__edit_save"
                            class="success"
                            name="save"
                            title="{{ app('dictionary')->dictionary('buttons')->key('save')->get() }}"
                            icon="save"
                            />

                            <x-form.submit
                            id="group__edit_delete"
                            class="error"
                            name="delete"
                            title="Удалить"
                            icon="trash"
                            />

                        </div>
                    </div>
                </div>
            </div>
            </form>

        </div> 
    </div>            

</div>



