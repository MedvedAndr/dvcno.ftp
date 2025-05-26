jQuery(document).on('DOMContentLoaded', function() {
    setGlobal('flags.drag_and_drop', true);

    jQuery(document)
        // Переносим файл на 'body', чтобы инициализировать интерфейс выгрузки файла
        .on('dragover', 'body', {type: 'over'}, drag_and_drop)
        // Уводим файл из интерфейса выгрузки файла, чтобы закрыть его
        .on('dragleave', '[data-drag-drop-back]', {type: 'leave'}, drag_and_drop)
        // Бросаем файл для выгрузки
        .on('drop', '[data-drag-drop-back]', {type: 'drop'}, drag_and_drop)
        // Добавляем по кнопке
        .on('change', 'input[type="file"][name="drop_file"]', {type: 'add'}, drag_and_drop);
});

function drag_and_drop(eventObject) {
    const dnd_box = jQuery('[data-drag-drop-back]');
    const download = jQuery('.add__file').find('.add__icon [data-icon]');
    let errors = [];

    if(eventObject.data && eventObject.data.type) {
        switch(eventObject.data.type) {
            case 'drop':
                eventObject.preventDefault();
    
                setGlobal('flags.drag_and_drop', true);
                dnd_box.fadeOut(300);
                download.attr('data-icon', 'spinner-light').attr('data-animation', 'spin_step12').addData('status', 'downloaded');
    
                const dropped_files = eventObject.originalEvent.dataTransfer.files;
    
                getAllowedFileTypes(function(result) {
                    errors = validateFiles(dropped_files, result);
                    download.attr('data-icon', 'plus').removeAttr('data-animation').eraseData('status', 'downloaded');
                    
                    if(errors.length === 0) {
                        fileUploadQueue(dropped_files);
                    } else {
                        errorsReport(errors);
                    }
                });
    
                break;
            case 'add' :
                download.attr('data-icon', 'spinner-light').attr('data-animation', 'spin_step12').addData('status', 'downloaded');
                const files = eventObject.currentTarget.files;
    
                getAllowedFileTypes(function(result) {
                    errors = validateFiles(files, result);
                    download.attr('data-icon', 'plus').removeAttr('data-animation').eraseData('status', 'downloaded');
                    
                    if(errors.length === 0) {
                        fileUploadQueue(files);
                    } else {
                        errorsReport(errors);
                    }
                });
                
                break;
            case 'over':
                eventObject.preventDefault();
                
                if(hasGlobal('flags.drag_and_drop') && getGlobal('flags.drag_and_drop')) {
                    setGlobal('flags.drag_and_drop', false);
                    dnd_box.fadeIn(300);
                }
                break;
            case 'leave':
                setGlobal('flags.drag_and_drop', true);
                dnd_box.fadeOut(300);
                break;
        }
    }
}

function getAllowedFileTypes(callback) {
    jQuery.ajax({
        url         : '/ajax/settings/get',
        type        : 'POST',
        headers     : {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        },
        data        : {
            'setting_keys': [
                'file_types',
            ]
        },
        dataType    : 'json',
        success     : function(result) {
            callback(result);
        },
        error       : function(report) {
            console.log(report.status, report.statusText);
            console.log(report.responseJSON);
        }
    });
}

function validateFiles(files, allowed_file_types = []) {
    const errors = [];

    jQuery.each(files, function(i, file) {
        const ext = file.name.split('.').pop().toLowerCase();
        const match_item = allowed_file_types.find(function(item) {
            return item.type === ext;
        });

        if(!match_item) {
            errors.push(String(file.name) +': формат .'+ String(ext) +' не поддерживается');
            return;
        }

        if(file.size > match_item.size) {
            const maxMb = (match_item.size / 1024 / 1024).toFixed(1);
            const fileMb = (file.size / 1024 / 1024).toFixed(1);
            errors.push(String(file.name) +': размер '+ String(fileMb) +' МБ превышает лимит '+ String(maxMb) +' МБ');
        }
    });

    return errors;
}

function fileUploadQueue(files, index = 0) {
    if(index >= files.length) {
        return;
    }

    if(index === 0) {
        let elements = [];

        for(let i = files.length - 1; i >= 0; i--) {
            elements.push(jQuery('<div class="file" data-index="'+ String(i) +'"><div class="download"><span data-icon="spinner-light" data-animation="spin_step12"></span></div></div>'));
        }

        jQuery('.files_list').prepend(...elements);
    }
    
    const file = files[index];
    const form_data = new FormData();
    form_data.append('file', file);
    const file_box = jQuery('.file[data-index="'+ String(index) +'"]');
    
    jQuery.ajax({
        url: '/ajax/upload_files',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        },
        data: form_data,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
            file_box.empty().append('<div class="progress_bar"><div class="progress"></div></div>');
        },
        xhr: function() {
            const xhr = jQuery.ajaxSettings.xhr();
            const progress_line = file_box.find('.progress_bar .progress');
            
            if(xhr.upload) {
                xhr.upload.addEventListener('progress', function(xhrObject) {
                    if(xhrObject.lengthComputable) {
                        const percent = Math.round((xhrObject.loaded / xhrObject.total) * 100);
                        // Интерфейс будет позже, пока в консоль
                        // console.log(String(file.name) +': '+ String(percent) +'%');
                        progress_line.css({'width': String(percent) +'%'});
                    }
                }, false);
            }

            return xhr;
        },
        success: function(result) {
            console.log(result);
            file_box.removeAttr('data-index').empty();

            if(result.data.mime_type.startsWith('image/')) {
                file_box.append('<div class="preview image"><img src="'+ String(result.data.path) +'"></div>');
            }
            else {
                file_box.append('<div class="preview document"><div class="document__icon"><span data-icon="file"></span></div><div class="document__title">'+ String(result.data.name) +'.'+ String(result.data.extension) +'</div></div>');
            }
            //file_box.append('<span class="copy_link" data-copy="'+ String(result.data.path) +'"><span data-icon="copy"></span></span>');

            fileUploadQueue(files, index + 1);
        },
        error: function(report) {
            console.log(report.status, report.statusText);
            console.log(report.responseJSON);
        }
    });
}

function errorsReport(errors) {
    jQuery.each(errors, function(i, error) {
        openPopUp({data: {
            component: 'system_messages.error',
            text: error,
        }});
    });
}