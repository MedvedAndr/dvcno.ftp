//  Кастомные события:
//      changed                     - срабатывает после явного измения знячения поля input[number] (стандартное поведение input[number] работает не так как нужно, поэтому оно было перезаписано (файл form.js) и создано событие)
//      component-added             - срабатывает после добавление элемента data-item в список data-list
//      before-component-deleted    - срабатывает перед удалением элемента data-item из списка data-list
//      component-deleted           - срабатывает после удаления элемента data-item из списка data-list
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

    // После загрузки страници инициируем сбор индексов для 'data-list'
    jQuery('[data-list]').each(function (list_i, list_item) {
        const $this_item_list = jQuery(list_item);
        const list_key = $this_item_list.attr('data-list');
        const $this_items = $this_item_list.find('[data-item]');

        if($this_items.length === 0 && !hasGlobal('items_index.'+ String(list_key))) {
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
    jQuery(document).on('component-added', function(customEventObject, data) {
        const component = data.component;

        switch(component) {
            case 'items.permalink':
                const $menu_layouts = jQuery('[data-list="menu_layout"]');
                
                calculateMenuOrder($menu_layouts);
                
                constructMenuOptions($menu_layouts);
                break;
        }
    });

    // Перерасчет позиций перед удалением пункта меню
    jQuery(document).on('before-component-deleted', function(customEventObject, data) {
        const component = data.component;
        
        switch(component) {
            case 'items.permalink':
                const $this_del_button = data.target;
                const $this_item = $this_del_button.closest('[data-item]');

                const $menu_layouts = jQuery('[data-list="menu_layout"]');

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
    jQuery(document).on('component-deleted', function(customEventObject, data) {
        const component = data.component;
        
        switch(component) {
            case 'items.permalink':
                const $parent_list = data.target;

                const $menu_layouts = jQuery('[data-list="menu_layout"]');

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
        .on('input change', '[data-required-group]:not([type="hidden"])', function(eventObject) {
            const group_id = jQuery(eventObject.currentTarget).attr('data-required-group');
            // TODO: проверить работу функционала обязательной группы полей
            const has_value = jQuery('[data-required-group="' + group_id + '"]:not([type="hidden"]').filter(function(i, item) {
                return jQuery(item).val().trim() !== '';
            }).length > 0;
            
            jQuery('[type="hidden"][data-required="' + group_id + '"]').val(has_value ? 'true' : 'false');
        })
        // Добавление компоненты
        // .on('click', '[data-item-add]', function(eventObject) {
        //     eventObject.preventDefault();
            
        //     const $this_button = jQuery(eventObject.currentTarget);
        //     const $this_elements = jQuery('[data-item-data="'+ String($this_button.attr('data-item-add')) + ($this_button.attr('data-index') !== undefined ? '_'+ String($this_button.attr('data-index')) : '') +'"]');
        //     const json_data = {};
        //     console.log($this_button, $this_elements);
        //     console.log(String($this_button.attr('data-item-add')), ($this_button.attr('data-index') !== undefined ? '_'+ String($this_button.attr('data-index')) : ''));
        //     $this_elements
        //         .each(function(i, item) {
        //             const $data_item = jQuery(item);
                    
        //             const keys = $data_item.attr('name').split('[').map(function(key) {
        //                 return key.replace(']', '');
        //             });

        //             let current_level = json_data;
        //             keys.forEach(function(key, index) {
        //                 if(index === keys.length - 1) {
        //                     current_level[key] = $data_item.val();
        //                 }
        //                 else {
        //                     current_level[key] = current_level[key] || {};
        //                 }
        //                 current_level = current_level[key];
        //             });
        //         });
            
        //     jQuery.ajax({
        //         url     : '/ajax/item/get',
        //         type    : 'POST',
        //         headers : {
        //             'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
        //         },
        //         data    : {
        //             'component' : $this_button.attr('data-item-add'),
        //             'index'     : getGlobal('items_index.'+ $this_button.attr('data-item-list'), 0) + 1,
        //             'data'      : json_data,
        //         },
        //         success : function(jquery_result) {
        //             // console.log(jquery_result);
        //             if(jquery_result.status === 'success') {
        //                 setGlobal('items_index.'+ String($this_button.attr('data-item-list')), jquery_result.meta.index);
        //                 if(['add-term', 'add-permalink', 'add-list-link', 'add-list-block', 'add-list-doc', 'add-list-video', 'add-list-accordion'].includes(jquery_result.meta.component)) {
        //                     Object.keys(jquery_result.data).forEach(function(locale) {
        //                         $list = jQuery('[data-items="'+ $this_button.attr('data-item-list') +'"][data-items-lang="'+ locale +'"]');
        //                         if($list.length) {
        //                             const $jquery_result = jQuery(jquery_result.data[locale]);
        //                             jQuery($jquery_result).find('[data-label="ckeditor"] textarea').each(function(i, item) {
        //                                 const $this_input = jQuery(item);
        //                                 const $this_label = $this_input.closest('[data-label]');

        //                                 ClassicEditor
        //                                     .create(item, {
        //                                         plugins: Object.values(CKPlugins),
        //                                         toolbar: [
        //                                             'undo', 'redo',
        //                                             '|',
        //                                             'fontFamily', 'fontSize',
        //                                             '|',
        //                                             'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
        //                                             '|',
        //                                             'fontColor', 'fontBackgroundColor',
        //                                             '|',
        //                                             'bulletedList', 'numberedList',
        //                                             '|',
        //                                             'outdent', 'indent',
        //                                             '|',
        //                                             'alignment',
        //                                             '|',
        //                                             'heading',
        //                                             '|',
        //                                             'insertTable', 'link'
        //                                         ],
        //                                         fontSize: {
        //                                             options: [
        //                                                 '10px',
        //                                                 '12px',
        //                                                 '14px',
        //                                                 '16px',
        //                                                 '18px',
        //                                                 '20px',
        //                                                 '22px',
        //                                                 '24px',
        //                                                 '26px',
        //                                                 '28px',
        //                                                 '30px',
        //                                             ]
        //                                         },
        //                                         translations: CKTranslations,
        //                                     })
        //                                     .then(function(editor) {
        //                                         setGlobal('editors.'+ $this_input.attr('name'), editor);

        //                                         editor.editing.view.document.on('focus', function() {
        //                                             $this_label.addData('status', 'focused');
        //                                         });

        //                                         editor.editing.view.document.on('blur', function() {
        //                                             $this_label.eraseData('status', 'focused');
        //                                         });

        //                                         editor.model.document.on('change:data', () => {
        //                                             if(editor.getData().trim() !== '') {
        //                                                 $this_label.addData('status', 'not_empty');
        //                                             }
        //                                             else {
        //                                                 $this_label.eraseData('status', 'not_empty');
        //                                             }
        //                                         });
        //                                     })
        //                                     .catch(function(error) {
        //                                         console.error(error);
        //                                     });
        //                             });
        //                             $list.append($jquery_result);
        //                         }
        //                     });
        //                 }
        //                 else {
        //                     $list = jQuery('[data-items="'+ $this_button.attr('data-item-list') +'"]');
        //                     if($list.length) {
        //                         const $jquery_result = jQuery(jquery_result.data);
        //                         jQuery($jquery_result).find('[data-label="ckeditor"] textarea').each(function(i, item) {
        //                             const $this_input = jQuery(item);
        //                             const $this_label = $this_input.closest('[data-label]');

        //                             ClassicEditor
        //                                 .create(item, {
        //                                     plugins: Object.values(CKPlugins),
        //                                     toolbar: [
        //                                         'undo', 'redo',
        //                                         '|',
        //                                         'fontFamily', 'fontSize',
        //                                         '|',
        //                                         'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript',
        //                                         '|',
        //                                         'fontColor', 'fontBackgroundColor',
        //                                         '|',
        //                                         'bulletedList', 'numberedList',
        //                                         '|',
        //                                         'outdent', 'indent',
        //                                         '|',
        //                                         'alignment',
        //                                         '|',
        //                                         'heading',
        //                                         '|',
        //                                         'insertTable', 'link'
        //                                     ],
        //                                     fontSize: {
        //                                         options: [
        //                                             '10px',
        //                                             '12px',
        //                                             '14px',
        //                                             '16px',
        //                                             '18px',
        //                                             '20px',
        //                                             '22px',
        //                                             '24px',
        //                                             '26px',
        //                                             '28px',
        //                                             '30px',
        //                                         ]
        //                                     },
        //                                     translations: CKTranslations,
        //                                 })
        //                                 .then(function(editor) {
        //                                     setGlobal('editors.'+ $this_input.attr('name'), editor);

        //                                     editor.editing.view.document.on('focus', function() {
        //                                         $this_label.addData('status', 'focused');
        //                                     });

        //                                     editor.editing.view.document.on('blur', function() {
        //                                         $this_label.eraseData('status', 'focused');
        //                                     });

        //                                     editor.model.document.on('change:data', () => {
        //                                         if(editor.getData().trim() !== '') {
        //                                             $this_label.addData('status', 'not_empty');
        //                                         }
        //                                         else {
        //                                             $this_label.eraseData('status', 'not_empty');
        //                                         }
        //                                     });
        //                                 })
        //                                 .catch(function(error) {
        //                                     console.error(error);
        //                                 });
        //                         });
        //                         $list.append($jquery_result);
        //                     }
        //                 }

        //                 if(['add-permalink'].includes(jquery_result.meta.component)) {
        //                     $this_elements.val('');
        //                 }

        //                 jQuery(document).trigger('item-added', {
        //                     target      : $this_button,
        //                     component   : $this_button.attr('data-item-add'),
        //                 });
        //             }
        //         },
        //         error   : function (report) {
        //             console.log(report.status, report.statusText);
        //             console.log(report.responseJSON);
        //         }
        //     });
        // })
        .on('click', '[data-action="add"]', function(eventObject) {
            eventObject.preventDefault();

            const $this_button = jQuery(eventObject.currentTarget);
            const $this_fields = jQuery('[data-field-context="'+ String($this_button.attr('data-component')) + ($this_button.attr('data-field-set') !== undefined ? '_'+ String($this_button.attr('data-field-set')) : '') +'"]');
            const is_multi_language = $this_button.attr('data-multi-language') === 'true';
            const json_data = {};

            $this_fields
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
            
            jQuery.ajax({
                url     : '/ajax/component/get',
                type    : 'POST',
                headers : {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                },
                contentType: 'application/json',
                data    : JSON.stringify({
                    'component'         : $this_button.attr('data-component'),
                    'index'             : getGlobal('items_index.'+ String($this_button.attr('data-target-container')), 0) + 1,
                    'data'              : json_data,
                    'multi_language'    : is_multi_language
                }),
                success : function(jquery_result) {
                    console.log(jquery_result);
                    if(jquery_result.status === 'success') {
                        setGlobal('items_index.'+ String($this_button.attr('data-target-container')), jquery_result.meta.index);
                        
                        if(is_multi_language) {
                            Object.entries(jquery_result.data).forEach(function([locale, item]) {
                                jQuery('[data-list="'+ $this_button.attr('data-target-container') +'"][data-list-lang="'+ locale +'"]').append(jQuery(item));
                            });
                        }
                        else {
                            jQuery('[data-list="'+ $this_button.attr('data-target-container') +'"]').append(jQuery(jquery_result.data));
                        }

                        $this_fields
                            .filter('[data-cleanable]')
                            .val('')
                            .trigger('change');

                        jQuery(document).trigger('component-added', {
                            target      : $this_button,
                            component   : $this_button.attr('data-component'),
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
        .on('click', '[data-action="delete"]', function(eventObject) {
            eventObject.preventDefault();

            const $this_button = jQuery(eventObject.currentTarget);
            const $this_item = $this_button.closest('[data-item]');
            const $this_list = $this_item.closest('[data-list]');
            const $lists = jQuery('[data-list="'+ $this_list.attr('data-list') +'"]');
            const is_multi_language = $this_list.attr('data-list-lang') !== undefined;
            // const $items_to_del = jQuery('[data-list="'+ $this_list.attr('data-list') +'"] [data-item="'+ $this_item.attr('data-item') +'"]');

            jQuery(document).trigger('before-component-deleted', {
                target      : $this_button,
                component   : $this_button.attr('data-component'),
            });

            $lists.children('[data-item="'+ $this_item.attr('data-item') +'"]').remove();

            jQuery(document).trigger('component-deleted', {
                target      : $lists,
                component   : $this_button.attr('data-component'),
            });
            
            if($this_list.children('[data-item]').length === 0 && $this_list.is('[data-empty]')) {
                jQuery.ajax({
                    url     : '/ajax/component/get',
                    type    : 'POST',
                    headers : {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                    },
                    contentType: 'application/json',
                    data    : JSON.stringify({
                        'component'         : $this_list.attr('data-empty'),
                        'index'             : getGlobal($this_list.attr('data-list')) + 1,
                        'multi_language'    : is_multi_language
                    }),
                    success : function(jquery_result) {
                        if(jquery_result.status === 'success') {
                            setGlobal($this_list.attr('data-list'), jquery_result.meta.index);

                            if(is_multi_language) {
                                Object.entries(jquery_result.data).forEach(function([locale, item]) {
                                    $lists.filter('[data-list-lang="'+ locale +'"]').append(jQuery(item));
                                });
                            }
                            else {
                                $lists.append(jQuery(jquery_result.data));
                            }
                        }
                    },
                    error   : function (report) {
                        console.log(report.status, report.statusText);
                        console.log(report.responseJSON);
                    }
                });
            }
        })
        // Изменение названий в select[parent_id] при изменении названий пунктов меню
        .on('change', '[data-item] label[data-label="text"] input[name$="[title]"]', function(eventObject) {
            const $this_name = jQuery(eventObject.currentTarget);
            const $this_item = $this_name.closest('[data-item]');
            const $menu_layout = $this_item.closest('[data-list]');

            updateMenuOptions($this_item.attr('data-id'), $this_name.val(), $menu_layout);
        })
        // Перенос menu-item в sub
        .on('change', '[data-item] label[data-label="select"] select[name$="[parent_id]"]', function(eventObject) {
            const $this_select = jQuery(eventObject.currentTarget);
            const $this_item = $this_select.closest('[data-item]');
            const $this_items = $this_item.closest('[data-list]');

            const $all_items = jQuery('[data-list="'+ $this_items.attr('data-list') +'"]');

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
            const $this_items = $this_item.closest('[data-list]');
            const this_index = $this_item.index();
            const target_index = parseInt($this_input.val(), 10) - 1;
            let $menu_depth;

            if($this_item_list.is('.menu__sub')) {
                $menu_depth = jQuery('[data-list="'+ $this_items.attr('data-list') +'"]')
                    .find('[data-item][data-id="'+ $this_item.parent().closest('[data-item]').attr('data-id') +'"]')
                    .children('.menu__sub');
            }
            else {
                $menu_depth = jQuery('[data-list="'+ $this_items.attr('data-list') +'"]');
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
        })
        .on('click', '[data-file-manager]', function(eventObject) {
            const $this_button = jQuery(eventObject.currentTarget);
            const $this_label = $this_button.closest('.file__panel');
            const $this_input = $this_label.find('.file__input');
            const $this_display = $this_label.find('.file__body');
            const $modal = jQuery('#file_manager');
            const $modal_content = $modal.find('.modal__content');
            const $body = jQuery('body');

            const scrollbar_width = jQuery(window).outerWidth() - $body.outerWidth(true);
            
            $body.css({
                'overflow': 'hidden',
                '--scroll-width': String(scrollbar_width) +'px'
            });

            jQuery.ajax({
                url     : '/ajax/files/get',
                type    : 'POST',
                headers : {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
                },
                data    : {
                    'extensions': $this_button.attr('data-extensions').split(' '),
                    'type': $this_button.attr('data-type')
                },
                beforeSend: function() {
                    const $download_box = jQuery('<div></div>')
                        .addClass('download_files');
                    const $download_spinner = jQuery('<div></div>')
                        .addClass('download_spinner');
                    const $download_icon = jQuery('<div></div>')
                        .addData('icon', 'spinner-light')
                        .attr('data-animation', 'spin_step12');
                    
                    $modal_content.empty().append($download_box.append($download_spinner.append($download_icon)));
                },
                success : function(jquery_result) {
                    $modal_content.empty().append(jquery_result.data);
                    $modal_content.find('input[name="for"]').val($this_input.attr('name'));
                },
                error   : function (report) {
                    console.log(report.status, report.statusText);
                    console.log(report.responseJSON);
                }
            });
            
            openModal({
                'currentTarget': $modal,
            });
        })
        .on('click', '.modal', closeModal)
        .on('change', '.files input[name="file"]', function(eventObject) {
            const $this_input = jQuery(eventObject.currentTarget);
            const $this_filesbox = $this_input.closest('.files');
            const $this_panel = $this_filesbox.find('.files__panel');
            const $count_display = $this_panel.find('.files__count .quantity');
            const $this_accept = $this_panel.find('.button.files__accept');
            const $checked_inputs = jQuery('.files input[name="file"]:checked');

            $count_display.text($checked_inputs.length);
            if($checked_inputs.length > 0) {
                $this_accept.eraseData('status', 'disabled');
            }
            else {
                $this_accept.addData('status', 'disabled');
            }
        })
        .on('click', '.files .files__accept', function(eventObject) {
            const $this_accept = jQuery(eventObject.currentTarget);
            const $this_files = $this_accept.closest('.files');
            const $this_for = $this_files.find('input[name="for"]');
            const $this_modal = $this_accept.closest('.modal');
            const $checked_inputs = jQuery('.files input[name="file"]:checked');
            
            const $for = jQuery('[name="'+ $this_for.val() +'"]');
            const $for_panel = $for.closest('.file__panel');
            const $for_updates = jQuery('.file__panel[data-file="'+ $for_panel.attr('data-file') +'"]');
            
            $for_updates.each(function(i, file_panel) {
                const $file_panel = jQuery(file_panel);

                const $for_display = $file_panel.find('.file__body');
                const $for_button = $file_panel.find('[data-file-manager]');

                let $infos = jQuery();

                $checked_inputs.each(function(i, item) {
                    const $item = jQuery(item);
                    let info     =  '<div class="file_info">';
                    info        +=      '<span class="file__icon">';
                    if($item.attr('data-image') !== undefined) {
                        info    +=          '<img src="'+ $item.attr('data-image') +'" />';
                    }
                    else {
                        info    +=          '<span data-icon="file"></span>';
                    }
                    info        +=      '</span>';
                    info        +=      '<span class="file__name">'+ $item.attr('data-name') +'</span>';
                    info        +=  '</div>';
                    $infos = $infos.add(jQuery(info));

                    $for.val($item.val());
                });

                $for_display.empty().append($infos);
                $for_button.text('Изменить');
            });

            closeModal({
                target: $this_modal.get(0),
                currentTarget: $this_modal.get(0)
            });
        });

    // jQuery('.modal').on('click', function(eventObject) {

    // });
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

function openModal(eventObject) {
    const $this_modal = jQuery(eventObject.currentTarget);

    $this_modal
        .show(0, function() {
            $this_modal
                .off('transitionend')
                .css({'display': 'flex'})
                .addData('status', 'opening')
                .on('transitionend', function() {
                    $this_modal
                        .eraseData('status', 'opening')
                        .addData('status', 'active');
                });
        });
}

function closeModal(eventObject) {
    const $this_close = jQuery(eventObject.target).closest('.modal__close');
    const $this_modal = jQuery(eventObject.currentTarget);
    const $body = jQuery('body');
    
    if(eventObject.target === eventObject.currentTarget || $this_close.length !== 0) {
        $this_modal
            .off('transitionend')
            .eraseData('status', 'active')
            .addData('status', 'closing')
            .on('transitionend', function() {
                $body.css({
                    'overflow': 'auto',
                    '--scroll-width': ''
                });

                $this_modal
                    .eraseData('status', 'closing').hide();
            });
    }
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