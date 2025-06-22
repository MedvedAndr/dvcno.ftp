//  Кастомные события:
//      changed             - срабатывает после явного измения знячения поля input[number] (стандартное поведение input[number] работает не так как нужно, поэтому оно было перезаписано (файл form.js) и создано событие)
//      item-added          - срабатывает после добавление элемента data-item в список data-items
//      before-item-deleted - срабатывает перед удалением элемента data-item из список data-items
//      item-deleted        - срабатывает после удаления элемента data-item из список data-items
const globals = {};

jQuery(document).on('DOMContentLoaded', function() {
    // События при скролле
    jQuery(window).on('scroll', function (eventObject) {
        const $this_document = jQuery(eventObject.currentTarget);

        // Прописываем отдельный класс 'stuck' всем заголовкам '.group__head' когда они зафиксированы через position: sticky
        jQuery('.group__head').each(function(i, item) {
            let $block = jQuery(item);
            let rect = item.getBoundingClientRect(); // Получаем положение элемента относительно окна
            let offsetTop = parseInt(getComputedStyle(item).top, 10); // Получаем значение top в sticky
            
            if(rect.top <= offsetTop && rect.bottom > offsetTop) {
                $block.addClass('stuck');
            }
            else {
                $block.removeClass('stuck');
            }
        });

        // При некотором скролле вниз добавляем сласс 'bg-dark' в шапку
        const $header = jQuery('header');

        if($this_document.scrollTop() >= 30) {
            $header.addClass('bg-dark');
        }
        else {
            $header.removeClass('bg-dark');
        }

        // При некотором скролле вниз отображаем кнопку 'Вверх'
        const $go_to_top = jQuery('#go_to_top');

        if($this_document.scrollTop() >= 100) {
            $go_to_top.addClass('visible');
        }
        else {
            $go_to_top.removeClass('visible');
        }
    }).trigger('scroll');

    // После загрузки страници инициируем сбор индексов для 'data-items'
    jQuery('[data-items]').each(function (list_i, list_item) {
        const $this_item_list = jQuery(list_item);
        const list_key = $this_item_list.attr('data-items');
        const $this_items = $this_item_list.find('[data-item]');

        if($this_items.length === 0) {
            setGlobal('items_index.'+ String(list_key), 0);
            return;
        }

        $this_items.each(function (i, item) {
            const $this_item = jQuery(item);
            const item_index = Number($this_item.attr('data-index'));
            const current_max = getGlobal('items_index.'+ String(list_key), 0);

            if (item_index > current_max) {
                setGlobal('items_index.'+ String(list_key), item_index);
            }
        });
    });

    // Перерасчет родителей и позиций после добавления пункта меню
    jQuery(document).on('item-added', function(customEventObject, data) {
        const component = data.component;

        switch(component) {
            case 'add-permalink':
                const $menu_layouts = jQuery('[data-items="menu_layout"]');
                
                calculateMenuOrder($menu_layouts);
                
                constructMenuOptions($menu_layouts);
                break;
        }
    });

    // Перерасчет позиций перед удалением пункта меню
    jQuery(document).on('before-item-deleted', function(customEventObject, data) {
        const component = data.component;
        
        switch(component) {
            case 'add-permalink':
                const $this_del_button = data.target;
                const $this_item = $this_del_button.closest('[data-item]');

                const $menu_layouts = jQuery('[data-items="menu_layout"]');

                $menu_layouts.each(function(i, menu_layout) {
                    const $menu_layout = jQuery(menu_layout);
                    const $item = $menu_layout.find('[data-item][data-id="'+ $this_item.attr('data-id') +'"]');
                    const $item_childs = $item.children('.menu__sub').children('[data-item]');
                    
                    $item_childs.appendTo($menu_layout);
                    $item_childs.children('.menu__item').find('select[name$="[parent_id]"]').val('');
                    calculateMenuOrder($menu_layout);
                });
                
                break;
        }
    });

    // Перерасчет родителей и позиций после удаления пункта меню
    jQuery(document).on('item-deleted', function(customEventObject, data) {
        const component = data.component;
        
        switch(component) {
            case 'add-permalink':
                const $parent_list = data.target;

                const $menu_layouts = jQuery('[data-items="menu_layout"]');

                if($parent_list.is('.menu__sub')) {
                    const $item = $parent_list.closest('[data-item]');
                    const $menu_lists = $menu_layouts.find('[data-item][data-id="'+ $item.attr('data-id') +'"]').children('.menu__sub');
                    calculateMenuOrder($menu_lists);
                }
                constructMenuOptions($menu_layouts);
                break;
        }
    });
    
    // Общий хлам (разобрать)
    jQuery(document)
        // Клик по кнопке "Наверх"
        .on('click', '#go_to_top', function(eventObject) {
            jQuery('html').animate({
                scrollTop: 0,
            }, 0);
        })
        // Перезапись куки при изменении select (частный случай)
        .on('change', 'select[data-cookie]', function(eventObject) {
            const $this_select = jQuery(eventObject.currentTarget);

            // console.log($this_select.val());
            jQuery.ajax({
                url     : '/ajax/cookie/set',
                type    : 'POST',
                headers : {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                },
                data    : {
                    'key'   : $this_select.attr('data-cookie'),
                    'value' : $this_select.val(),
                },
                beforeSend  : function() {
                    $this_select.prop('disabled', true);
                },
                success : function(jquery_result) {
                    $this_select.prop('disabled', false);
                    
                    if(jquery_result.status === 'success') {
                        location.reload();
                    }
                },
                error   : function(report) {
                    $this_select.prop('disabled', false);

                    console.log(report.status, report.statusText);
                    console.log(report.responseJSON);
                }
            });
        })
        // Изменение специального input, который переключает статус обязательных полей (додумать функционал)
        .on('input', '[data-required-group]:not([type="hidden"])', function(eventObject) {
            const group_id = jQuery(eventObject.currentTarget).attr('data-required-group');

            const has_value = jQuery('[data-required-group="' + group_id + '"]:not([type="hidden"]').filter(function(i, item) {
                return jQuery(item).val().trim() !== '';
            }).length > 0;
            
            jQuery('[type="hidden"][data-required="' + group_id + '"]').val(has_value ? 'true' : 'false');
        })
        // Добавление компоненты
        .on('click', '[data-item-add]', function(eventObject) {
            eventObject.preventDefault();
            
            const $this_button = jQuery(eventObject.currentTarget);
            const $this_elements = jQuery('[data-item-data="'+ String($this_button.attr('data-item-add')) +'"]');
            const json_data = {};
            
            $this_elements
                .each(function(i, item) {
                    const $data_item = jQuery(item);
                    
                    const keys = $data_item.attr('name').split('[').map(function(key) {
                        return key.replace(']', '');
                    });

                    let current_level = json_data;
                    keys.forEach(function(key, index) {
                        if(index === keys.length - 1) {
                            current_level[key] = $data_item.val();
                        }
                        else {
                            current_level[key] = current_level[key] || {};
                        }
                        current_level = current_level[key];
                    });
                });
            console.log(json_data);
            jQuery.ajax({
                url     : '/ajax/item/get',
                type    : 'POST',
                headers : {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                },
                data    : {
                    'component' : $this_button.attr('data-item-add'),
                    'index'     : getGlobal('items_index.'+ $this_button.attr('data-item-list'), 0) + 1,
                    'data'      : json_data,
                },
                success : function(jquery_result) {
                    console.log(jquery_result);
                    if(jquery_result.status === 'success') {
                        setGlobal('items_index.'+ String($this_button.attr('data-item-list')), jquery_result.meta.index);
                        if(['add-term', 'add-permalink', 'add-list-link', 'add-list-accordion'].includes(jquery_result.meta.component)) {
                            Object.keys(jquery_result.data).forEach(function(locale) {
                                $list = jQuery('[data-items="'+ $this_button.attr('data-item-list') +'"][data-items-lang="'+ locale +'"]');
                                if($list.length) {
                                    const $jquery_result = jQuery(jquery_result.data[locale]);
                                    jQuery($jquery_result).find('[data-label="ckeditor"] textarea').each(function(i, item) {
                                        const $this_input = jQuery(item);
                                        const $this_label = $this_input.closest('[data-label]');

                                        ClassicEditor
                                            .create(item, {
                                                plugins: Object.values(CKPlugins),
                                                toolbar: [
                                                    'undo', 'redo',
                                                    '|',
                                                    'fontFamily', 'fontSize',
                                                    '|',
                                                    'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                                                    '|',
                                                    'fontColor', 'fontBackgroundColor',
                                                    '|',
                                                    'bulletedList', 'numberedList',
                                                    '|',
                                                    'outdent', 'indent',
                                                    '|',
                                                    'alignment',
                                                    '|',
                                                    'heading',
                                                    '|',
                                                    'insertTable', 'link'
                                                ],
                                                fontSize: {
                                                    options: [
                                                        '10px',
                                                        '12px',
                                                        '14px',
                                                        '16px',
                                                        '18px',
                                                        '20px',
                                                        '22px',
                                                        '24px',
                                                        '26px',
                                                        '28px',
                                                        '30px',
                                                    ]
                                                },
                                                translations: CKTranslations,
                                            })
                                            .then(function(editor) {
                                                setGlobal('editors.'+ $this_input.attr('name'), editor);

                                                editor.editing.view.document.on('focus', function() {
                                                    $this_label.addData('status', 'focused');
                                                });

                                                editor.editing.view.document.on('blur', function() {
                                                    $this_label.eraseData('status', 'focused');
                                                });

                                                editor.model.document.on('change:data', () => {
                                                    if(editor.getData().trim() !== '') {
                                                        $this_label.addData('status', 'not_empty');
                                                    }
                                                    else {
                                                        $this_label.eraseData('status', 'not_empty');
                                                    }
                                                });
                                            })
                                            .catch(function(error) {
                                                console.error(error);
                                            });
                                    });
                                    $list.append($jquery_result);
                                }
                            });
                        }
                        else {
                            $list = jQuery('[data-items="'+ $this_button.attr('data-item-list') +'"]');
                            if($list.length) {
                                const $jquery_result = jQuery(jquery_result.data);
                                jQuery($jquery_result).find('[data-label="ckeditor"] textarea').each(function(i, item) {
                                    const $this_input = jQuery(item);
                                    const $this_label = $this_input.closest('[data-label]');

                                    ClassicEditor
                                        .create(item, {
                                            plugins: Object.values(CKPlugins),
                                            toolbar: [
                                                'undo', 'redo',
                                                '|',
                                                'fontFamily', 'fontSize',
                                                '|',
                                                'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
                                                '|',
                                                'fontColor', 'fontBackgroundColor',
                                                '|',
                                                'bulletedList', 'numberedList',
                                                '|',
                                                'outdent', 'indent',
                                                '|',
                                                'alignment',
                                                '|',
                                                'heading',
                                                '|',
                                                'insertTable', 'link'
                                            ],
                                            fontSize: {
                                                options: [
                                                    '10px',
                                                    '12px',
                                                    '14px',
                                                    '16px',
                                                    '18px',
                                                    '20px',
                                                    '22px',
                                                    '24px',
                                                    '26px',
                                                    '28px',
                                                    '30px',
                                                ]
                                            },
                                            translations: CKTranslations,
                                        })
                                        .then(function(editor) {
                                            setGlobal('editors.'+ $this_input.attr('name'), editor);

                                            editor.editing.view.document.on('focus', function() {
                                                $this_label.addData('status', 'focused');
                                            });

                                            editor.editing.view.document.on('blur', function() {
                                                $this_label.eraseData('status', 'focused');
                                            });

                                            editor.model.document.on('change:data', () => {
                                                if(editor.getData().trim() !== '') {
                                                    $this_label.addData('status', 'not_empty');
                                                }
                                                else {
                                                    $this_label.eraseData('status', 'not_empty');
                                                }
                                            });
                                        })
                                        .catch(function(error) {
                                            console.error(error);
                                        });
                                });
                                $list.append($jquery_result);
                            }
                        }

                        if(['add-permalink'].includes(jquery_result.meta.component)) {
                            $this_elements.val('');
                        }

                        jQuery(document).trigger('item-added', {
                            target      : $this_button,
                            component   : $this_button.attr('data-item-add'),
                        });
                    }
                },
                error   : function (report) {
                    console.log(report.status, report.statusText);
                    console.log(report.responseJSON);
                }
            });
        })
        // Удаление компаненты
        .on('click', '[data-item-del]', function(eventObject) {
            eventObject.preventDefault();

            const $this_button = jQuery(eventObject.currentTarget);
            const $this_item = $this_button.closest('[data-item]');
            const $this_list = $this_item.closest('[data-items]');
            const $items_to_del = jQuery('[data-items="'+ $this_list.attr('data-items') +'"] [data-item="'+ $this_item.attr('data-item') +'"]');

            jQuery(document).trigger('before-item-deleted', {
                target      : $this_button,
                component   : $this_button.attr('data-item-del'),
            });

            if($this_list.children('[data-item]').length == 1 && $this_button.is('[data-item-once]')) {
                jQuery.ajax({
                    url     : '/ajax/item/get',
                    type    : 'POST',
                    headers : {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                    },
                    data    : {
                        'component' : $this_button.attr('data-item-del'),
                        'index'     : getGlobal($this_list.attr('data-items')) + 1,
                    },
                    success : function(jquery_result) {
                        if(jquery_result.status === 'success') {
                            setGlobal($this_list.attr('data-items'), jquery_result.meta.index);
                            if(['add-term', 'add-permalink', 'add-list-link'].includes(jquery_result.meta.component)) {
                                Object.keys(jquery_result.data).forEach(function(locale) {
                                    $list = jQuery('[data-items="'+ $this_list.attr('data-items') +'"][data-items-lang="'+ locale +'"]');
                                    if($list.length) {
                                        $list.append(jquery_result.data[locale]);
                                    }
                                });
                            }
                            else {
                                $list = jQuery('[data-items="'+ $this_list.attr('data-items') +'"]');
                                if($list.length) {
                                    $list.append(jquery_result.data);
                                }
                            }
                        }
                    },
                    error   : function (report) {
                        console.log(report.status, report.statusText);
                        console.log(report.responseJSON);
                    }
                });
            }

            const $parents = $items_to_del.parent();

            if($items_to_del.is('[data-animation]')) {
                $items_to_del.attr('data-animation', 'fade_out').on('animationend', function() {
                    
                    $items_to_del.remove();

                    jQuery(document).trigger('item-deleted', {
                        target      : $parents,
                        component   : $this_button.attr('data-item-del'),
                    });
                });
            }
            else {
                $items_to_del.remove();

                jQuery(document).trigger('item-deleted', {
                    target      : $parents,
                    component   : $this_button.attr('data-item-del'),
                });
            }
        })
        // Изменение названий в select[parent_id] при изменении названий пунктов меню
        .on('change', '[data-item] label[data-label="text"] input[name$="[title]"]', function(eventObject) {
            const $this_name = jQuery(eventObject.currentTarget);
            const $this_item = $this_name.closest('[data-item]');
            const $menu_layout = $this_item.closest('[data-items]');

            updateMenuOptions($this_item.attr('data-id'), $this_name.val(), $menu_layout);
        })
        // Перенос menu-item в sub
        .on('change', '[data-item] label[data-label="select"] select[name$="[parent_id]"]', function(eventObject) {
            const $this_select = jQuery(eventObject.currentTarget);
            const $this_item = $this_select.closest('[data-item]');
            const $this_items = $this_item.closest('[data-items]');

            const $all_items = jQuery('[data-items="'+ $this_items.attr('data-items') +'"]');

            $all_items.each(function(i, items) {
                const $items = jQuery(items);
                const $item = $items.find('[data-item][data-id="'+ $this_item.attr('data-id') +'"]');
                let $from, $to;

                if($item.parent().is('.menu__sub')) {
                    $from = $item.parent();
                }
                else {
                    $from = $items;
                }

                if($this_select.val() !== '') {
                    $to = $items
                        .find('[data-item][data-id="'+ $this_select.val() +'"]')
                        .children('.menu__sub');
                }
                else {
                    $to = $items;
                }

                $item.appendTo($to);

                calculateMenuOrder($from);
                calculateMenuOrder($to);
            });

            constructMenuOptions($all_items);
        })
        // Изменение порядка пунктов меню
        .on('changed', '[data-item] label[data-label="number"] input[name$="[order]"]', function(customEventObject) {
            const $this_input = jQuery(customEventObject.currentTarget);
            const $this_item = $this_input.closest('[data-item]');
            const $this_item_list = $this_item.parent();
            const $this_items = $this_item.closest('[data-items]');
            const this_index = $this_item.index();
            const target_index = parseInt($this_input.val(), 10) - 1;
            let $menu_depth;

            if($this_item_list.is('.menu__sub')) {
                $menu_depth = jQuery('[data-items="'+ $this_items.attr('data-items') +'"]')
                    .find('[data-item][data-id="'+ $this_item.parent().closest('[data-item]').attr('data-id') +'"]')
                    .children('.menu__sub');
            }
            else {
                $menu_depth = jQuery('[data-items="'+ $this_items.attr('data-items') +'"]');
            }
            
            $menu_depth.each(function(i, menu_list) {
                const $items = jQuery(menu_list).children('[data-item]');
                const $current_item = $items.eq(this_index);
                const $target_item = $items.eq(target_index);

                if(this_index > target_index) {
                    $target_item.before($current_item);
                }
                else {
                    $target_item.after($current_item);
                }
            });
            
            calculateMenuOrder($menu_depth);
            constructMenuOptions($menu_depth);
        })
        // Копирование ссылки на файл (пока не работает)
        .on('click', '[data-copy]', function(eventObject) {
            // const $this_copy = jQuery(eventObject.currentTarget);
            console.log(navigator.clipboard);
            // const $tempInput = jQuery('<input type="text" hidden />').val($this_copy.attr('data-copy'));
            // $tempInput.appendTo('body');
            // $tempInput[0].select();
            // document.execCommand('copy');
            // $tempInput.remove();
            // openPopUp({data: {
            //     component: 'system_messages.success',
            //     text: 'Ссылка на файл скопирована в буффер обмена',
            // }});

            // navigator.clipboard.writeText($this_copy.attr('data-copy'))
            //     .then(function() {
            //         openPopUp({data: {
            //             component: 'system_messages.success',
            //             text: 'Ссылка на файл скопирована в буффер обмена',
            //         }});
            //     })
            //     .catch(function(error) {
            //         console.log(error);
            //     });
        })
        .on('change', '[data-slugifier]', function(eventObject) {
            const $this_input = jQuery(eventObject.currentTarget);
            const [slugify_input, db_table = null, db_col = null] = $this_input.attr('data-slugifier').split('.');
            const $slugify_input = jQuery('[name="'+ slugify_input +'"]');
            const $slugify_label = $slugify_input.closest('label[data-label]');

            if($this_input.val() !== '' && $slugify_input.val() === '' && db_table !== null && db_col !== null) {
                jQuery.ajax({
                    url     : '/ajax/slugify',
                    type    : 'POST',
                    headers : {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                    },
                    data    : {
                        'table'   : db_table +'.'+ db_col,
                        'value' : $this_input.val(),
                        'limit' : 255,
                    },
                    beforeSend  : function() {
                        
                    },
                    success : function(jquery_result) {
                        $slugify_input.val(jquery_result.data).trigger('change');
                        $slugify_label.find('.label__input .label__text').text(jquery_result.data);
                    },
                    error   : function(report) {
                        console.log(report.status, report.statusText);
                        console.log(report.responseJSON);
                    }
                });
            }
        });
});

function setGlobal(path, value) {
    const keys = path.split('.');
    let current = globals;

    while (keys.length > 1) {
        const key = keys.shift();
        current[key] = current[key] || {};
        current = current[key];
    }

    current[keys[0]] = value;

    return value;
}

function getGlobal(path, defaultValue = undefined) {
    const keys = path.split('.');
    let current = globals;

    for (const key of keys) {
        if (current && Object.prototype.hasOwnProperty.call(current, key)) {
            current = current[key];
        } else {
            return defaultValue;
        }
    }

    return current;
}

function hasGlobal(path) {
    const keys = path.split('.');
    let current = globals;

    for (const key of keys) {
        if (current && Object.prototype.hasOwnProperty.call(current, key)) {
            current = current[key];
        } else {
            return false;
        }
    }

    return true;
}

function openPopUp(eventObject = {}) {
    // eventObject.data.text
    const json_data = {};
    const $system_messages = jQuery('#system_messages');

    json_data['component'] = (eventObject.data && eventObject.data.component) ? eventObject.data.component : 'system_messages.default';
    json_data['data'] = {};
    if(eventObject.data && eventObject.data.text) {
        json_data['data']['text'] = eventObject.data.text;
    }
    
    // const $message = jQuery('<div class="message"><div class="progress_bar"></div></div>');

    jQuery.ajax({
        url     : '/ajax/component/get',
        type    : 'POST',
        headers : {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
        },
        data    : json_data,
        success : function(jquery_result) {
            console.log(jquery_result);
            const $message = jQuery(jquery_result.data);
            
            $system_messages
                .append($message);
            
            $message.on('animationend', function(event) {
                if(event.originalEvent.animationName === 'fade_in_down') {
                    setTimeout(function() {
                        $message
                            .addClass('fade_out')
                            .on('animationend', function(event) {
                                if (event.originalEvent.animationName === 'fade_out_down') {
                                    $message.remove();
                                }
                            });
                    }, 3500);
                }
            });
        },
        error   : function (report) {
            console.log(report.status, report.statusText);
            console.log(report.responseJSON);
        }
    });

    // $message.on('animationend', function(event) {
    //     if(event.originalEvent.animationName === 'fade_in_down') {
    //         setTimeout(function() {
    //             $message
    //                 .addClass('fade_out')
    //                 .on('animationend', function(event) {
    //                     if (event.originalEvent.animationName === 'fade_out_down') {
    //                         $message.remove();
    //                     }
    //                 });
    //         }, 3500);
    //     }
    // });
}

function constructMenuOptions($menu_layouts) {
    $menu_layouts.each(function(i, menu_layout) {
        const $points   = jQuery(menu_layout).find('[data-item]');
        let $options    = jQuery('<option value="">Нет родителя</option>');
        
        $points.each(function(i, item) {
            const $item = jQuery(item);
            const id    = String($item.attr('data-id'));
            const name  = String($item.find('[name$="[title]"]').val());
            $options    = $options.add('<option value="'+ id +'">'+ name +'</option>');
        });
        
        $points.each(function(i, item) {
            const $item             = jQuery(item);
            const $childrens_list   = $item.find('.menu__sub').children('[data-item]');
            const id                = String($item.attr('data-id'));
            const $parents_list     = $item.children('.menu__item').find('[name$="[parent_id]"]');

            let excluded_ids = [id];
            $childrens_list.each(function(i, child) {
                const child_id = String(jQuery(child).attr('data-id'));
                excluded_ids.push(child_id);
            });
            
            $parents_list.each(function(i, select) {
                const $select = jQuery(select);
                
                // Сохраняем текущее выбранное значение
                const selected_value = $select.val();
                // console.log($select, selected_value);
                // Очищаем и заполняем select новыми опциями, исключая текущий пункт и его дочерние элементы
                $select.empty().append(
                    $options
                        .filter(function(i, option) {
                            return !excluded_ids.includes(jQuery(option).val());
                        })
                        .clone()
                );
                
                // Восстанавливаем выбранное значение
                $select.val(selected_value);
            });
        });
    });
}

function updateMenuOptions(item_id, new_title, $menu_layout) {
    const $item_selects = $menu_layout.find('[name$="[parent_id]"]');
    
    $item_selects.each(function(i, select) {
        const $select = jQuery(select);
        const $change_option = $select.find('option[value="'+ String(item_id) +'"]');

        if($change_option.length !== 0) {
            $change_option.text(new_title);
        }
    });
}

function calculateMenuOrder($menu_layouts) {
    $menu_layouts.each(function(i, menu_layout) {
        const $item_list = jQuery(menu_layout).children('[data-item]');
        
        $item_list.each(function(index, item) {
            const $item = jQuery(item);
            const $order_input = $item.children('.menu__item').find('[name$="[order]"]');

            $order_input.attr('max', $item_list.length).val(index + 1);
        });
    });
}





//-----------------------------
// function getNextInputIndex(start, val) {
//     for (let i = start; i < val.length; i++) {
//         if ('0123456789_'.includes(val[i])) return i;
//     }
//     return -1;
// }

// function getNextSectionIndex(index) {
//     const sectionBreaks = [2, 5, 10, 13, 16];
//     for (let i = 0; i < sectionBreaks.length; i++) {
//         if (index < sectionBreaks[i]) return sectionBreaks[i] + 1;
//     }
//     return -1;
// }

// function isValidDigit(index, digit, val) {
//     digit = parseInt(digit);
//     const get = i => parseInt(val[i]) || 0;

//     switch (index) {
//         case 0: return digit <= 3;
//         case 1:
//             if (get(0) === 3) return digit <= 1;
//             return true;
//         case 3: return digit <= 1;
//         case 4:
//             if (get(3) === 1) return digit <= 2;
//             return true;
//         case 6: return true; // год, час, минута, секунда — просто числа
//         case 7: return true;
//         case 8: return true;
//         case 9: return true;
//         case 11: return digit <= 2;
//         case 12:
//             if (get(11) === 2) return digit <= 3;
//             return true;
//         case 14: return digit <= 5;
//         case 15: return true;
//         case 17: return digit <= 5;
//         case 18: return true;
//         default: return true;
//     }
// }