
                    <div class="files">
                        
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
                                    <a class='button' data-select="">
                                        <span class='button__icon'>
                                        <span data-icon='check'></span>
                                        </span>
                                        <span class='button__title'>Выбрать</span>
                                    </a>
                                </div>

                            </div>
                            @endforeach
                        </div>

                    </div>




