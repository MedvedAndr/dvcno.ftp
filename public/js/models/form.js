jQuery(document).on('DOMContentLoaded', function() {
    // Очищием события фокуса (предотвращает дублирование события)
    jQuery(document).off('focusin focusout');


    // Общие события
    jQuery(document)
        // Смена типа поля при клике на "глазик"
        .on('click', 'label[data-label="password"] .label__toggle_eye', toggleEye)
        // Первичная валидация формы перед отправкой на сервер
        .on('click', 'form[data-form] [type="submit"]', primeValidation);


    // Обработка событий для присвоения статуса
    jQuery(document)
        // Получение фокуса
        .on(
            'focusin',
            'label[data-label="text"] input, label[data-label="password"] input, label[data-label="number"] input, label[data-label="textarea"] textarea, label[data-label="select"] select',
            {'status': 'focused'},
            toggleStatus
        )
        // Потеря фокуса
        .on(
            'focusout',
            'label[data-label="text"] input, label[data-label="password"] input, label[data-label="number"] input, label[data-label="textarea"] textarea, label[data-label="select"] select',
            {'status': 'focused'},
            toggleStatus
        )
        // Ввод данных
        .on(
            'input',
            'label[data-label="text"] input, label[data-label="password"] input, label[data-label="number"] input, label[data-label="textarea"] textarea',
            {'status': 'not_empty'},
            toggleStatus
        )
        // Изменение
        .on(
            'change',
            'label[data-label="togglebox"] input',
            {'status': 'checked'},
            toggleStatus
        );

    // Связь элементов через data-sync
    jQuery(document)
        // Связывание checkbox
        .on('change', '[type="checkbox"][data-sync]', {type: 'checkbox'}, syncInput)
        // Связывание text
        .on('input change', '[type="text"][data-sync]', {type: 'text'}, syncInput)
        // Связывание number
        .on('changed', '[type="number"][data-sync]', {type: 'number'}, syncInput)
        // Связывание select
        .on('change', 'select[data-sync]', {type: 'select'}, syncInput);


    // Компонента 'datetime'
    jQuery(document)
        // Клик внутри 'label'
        .on('click', 'label[data-label="datetime"]', function(eventObject) {
            const $this_target = jQuery(eventObject.target);
            const $this_label = $this_target.closest('[data-label="datetime"]');

            if(
                !$this_target.is('[data-segment]') &&
                !$this_target.is('.label__calendar_icon') &&
                !$this_target.closest('.label__calendar').length
            ) {
                $this_label.find('[data-segment]:first').focus();
            }
        })
        // Клик на иконку календаря
        .on('click', 'label[data-label="datetime"] .label__calendar_icon', function(eventObject) {
            const $this_calendar_icon = jQuery(eventObject.currentTarget);
            const $this_label = $this_calendar_icon.closest('[data-label="datetime"]');
            const $date_input = $this_label.find('input[type="hidden"]');
            const $this_calendar = $this_label.find('.label__calendar');
            let selected_date;
            const current_date = new Date();
            const edit = [6, 0, 1, 2, 3, 4, 5];
            
            if($date_input.val() !== '') {
                selected_date = new Date($date_input.val());
            }
            else {
                selected_date = new Date();
            }

            const calendar_date = setGlobal('datetime', {
                'year'      : selected_date.getFullYear(),
                'month'     : selected_date.getMonth(),
                'day'       : selected_date.getDate(),
                'hour'      : selected_date.getHours(),
                'minute'    : selected_date.getMinutes(),
                'second'    : selected_date.getSeconds()
            });
            const date = new Date(calendar_date.year, calendar_date.month, calendar_date.day, calendar_date.hour, calendar_date.minute, calendar_date.second);
            date.setDate(1);
            date.setDate(1 - edit[date.getDay()]);

            const $mounth_display = $this_calendar.find('.calendar__month_display').children();
            $mounth_display.eraseData('status', 'active');
            $mounth_display.eq(calendar_date.month).addData('status', 'active');
            
            const $year_display = $this_calendar.find('.calendar__year_display');
            $year_display.text(calendar_date.year);
            
            let $list = jQuery();
            for(i = 0; i < 42; i++) {
                const $option = jQuery('<span class="calendar__day">'+ date.getDate() +'</span>');
                if(calendar_date.month == date.getMonth()) {
                    if(calendar_date.day == date.getDate()) {
                        $option.addData('status', 'selected');
                    }
                    // Подправить ошибку на текущую дату
                    if(current_date.getDate() == date.getDate()) {
                        $option.addData('status', 'current');
                    }
                }
                else {
                    $option.addData('status', 'other_month');
                }
                $list = $list.add($option);
                
                date.setDate(date.getDate() + 1);
            }

            const $days = $this_calendar.find('.calendar__days');
            $days.empty().append($list);

            $this_label.addData('status', 'open');
        })
        // Активность внутри календаря
        .on('click', 'label[data-label="datetime"] .label__calendar', function(eventObject) {
            const $this_calendar = jQuery(eventObject.currentTarget);
            const $action_target = jQuery(eventObject.target);
        })
        // Фокус на сегменте даты
        .on('focusin', 'label[data-label="datetime"] [data-segment]', function(eventObject) {
            const $this_segment = jQuery(eventObject.currentTarget);
            const $this_label = $this_segment.closest('[data-label="datetime"]');
            
            $this_label.addData('status', 'focused');
            const segment_state = createSegmentState();

            $this_segment.off('keydown').on('keydown', function(eventObject) {
                const $active_segment = jQuery(eventObject.currentTarget);
                const $focused_label = $active_segment.closest('[data-label="datetime"]');
                
                const segment_name = $active_segment.attr('data-segment');
                
                const limits = {
                    day     : [1, 31],
                    month   : [1, 12],
                    year    : [1000, 9999],
                    hour    : [0, 23],
                    minute  : [0, 59],
                    second  : [0, 59],
                }
                const order = ['day', 'month', 'year', 'hour', 'minute', 'second'];
                let min, max, num;
                const pad_length = segment_name === 'year' ? 4 : 2;

                function changeValue(increment) {
                    [min, max] = limits[segment_name];
                    num = parseInt($active_segment.text(), 10);

                    if(isNaN(num)) {
                        if(segment_name === 'year') {
                            num = new Date().getFullYear();
                        }
                        else {
                            num = increment > 0 ? min : max;
                        }
                    }
                    else {
                        num += increment;
                        num = loopValue(num);
                    }

                    segment_state.reset();
                    $active_segment.text(String(num).padStart(pad_length, '0'));
                }

                function moveFocus(direction) {
                    const index = order.indexOf(segment_name);

                    let new_index = index + direction;
                    if(new_index < 0) {
                        new_index = 0;
                    }

                    if(new_index > order.length - 1) {
                        new_index = order.length - 1;
                    }

                    const next_segment = order[new_index];
                    if(next_segment !== segment_name) {
                        $focused_label.find('[data-segment="' + next_segment + '"]').focus();
                    }
                }

                function limitValue(val) {
                    if (val < min) return min;
                    if (val > max) return max;
                    return val;
                }

                function loopValue(val) {
                    if (val < min) return max;
                    if (val > max) return min;
                    return val;
                }

                switch(eventObject.key) {
                    case 'ArrowUp':
                        eventObject.preventDefault();

                        changeValue(1);
                        break;
                    case 'ArrowDown':
                        eventObject.preventDefault();

                        changeValue(-1);
                        break;
                    case 'ArrowLeft':
                        eventObject.preventDefault();

                        moveFocus(-1);
                        break;
                    case 'ArrowRight':
                        eventObject.preventDefault();

                        moveFocus(1);
                        break;
                    case 'Backspace':
                    case 'Delete':
                        eventObject.preventDefault();

                        segment_state.reset();
                        $active_segment.text($active_segment.attr('data-default'));
                        break;
                    default:
                        if(eventObject.key.match(/^\d$/)) {
                            eventObject.preventDefault();

                            const digit = eventObject.key;
                            [min, max] = limits[segment_name];

                            // Увеличиваем счетчик
                            const count = segment_state.increment();
                            // Получаем содержимое сегмента
                            let text = $active_segment.text();
                            // Если в сегменте placeholder, то обнуляем, иначе парсим в число
                            let int = !/^\d+$/.test(text) ? 0 : parseInt(text, 10);
                            let max_first;

                            if(count === 1) {
                                max_first = Math.floor(max / Math.pow(10, pad_length - 1));
                                text = digit;
                            }
                            else {
                                max_first = false;
                                text = String(int) + digit;
                            }
                            
                            if(count >= pad_length || (max_first !== false && parseInt(digit, 10) > max_first)) {
                                text = String(limitValue(parseInt(text, 10)));
                                moveFocus(1);
                            }
                            
                            text = text.padStart(pad_length, '0');
                            $active_segment.text(text);
                        }
                        break;
                }
            });
        })
        // Потеря фокуса с сегмента даты
        .on('focusout', 'label[data-label="datetime"] [data-segment]', function(eventObject) {
            const $this_segment = jQuery(eventObject.currentTarget);
            const $this_label = $this_segment.closest('[data-label="datetime"]');
            const $all_segments = $this_label.find('[data-segment]');
            const $date_input = $this_label.find('input[type="hidden"]');

            $this_label.eraseData('status', 'focused');

            let text = $this_segment.text();
            if(/^\d+$/.test(text)) {
                const segment_name = $this_segment.attr('data-segment');
                const limits = {
                    day     : [1, 31],
                    month   : [1, 12],
                    year    : [1000, 9999],
                    hour    : [0, 23],
                    minute  : [0, 59],
                    second  : [0, 59],
                }
                const pad_length = segment_name === 'year' ? 4 : 2;
                const [min, max] = limits[segment_name];
    
                let val = parseInt(text, 10);
                if (val < min) {
                    val = min;
                }
                else if (val > max) {
                    val = max;
                }
                
                text = String(val).padStart(pad_length, '0');
                $this_segment.text(text);
            }

            const data = {};
            let insert_to_input = true;
            $all_segments.each(function(i, segment) {
                const $segment = jQuery(segment);
                if(/^\d+$/.test($segment.text())) {
                    data[$segment.attr('data-segment')] = $segment.text();
                }
                else {
                    data[$segment.attr('data-segment')] = null;
                    insert_to_input = false;
                }
            });

            if(insert_to_input) {
                $date_input.val(data['year'] +'-'+ data['month'] +'-'+ data['day'] +' '+ data['hour'] +':'+ data['minute'] +':'+ data['second']);
            }
            else {
                $date_input.val('');
            }
        });
    // jQuery(document)
        //     .on('click', '#now, #year_prev, #year_next, #month_prev, #month_next', function(e) {
        //         const $this_button = jQuery(e.currentTarget);
        //         const edit = [6, 0, 1, 2, 3, 4, 5];

        //         switch($this_button.attr('id')) {
        //             case 'now':
        //                 const date_input = jQuery('[name="event[rtus4qme0uf][date_to]"]');
        //                 let curr;

        //                 if(date_input.val() !== '') {
        //                     curr = new Date(date_input.val());
        //                 }
        //                 else {
        //                     curr = new Date();
        //                 }

        //                 setGlobal('test_date', {
        //                     'year': curr.getFullYear(),
        //                     'month': curr.getMonth(),
        //                     'day': curr.getDate(),
        //                     'hour': curr.getHours(),
        //                     'minute': curr.getMinutes(),
        //                     'second': curr.getSeconds()
        //                 });
        //                 break;
        //             case 'year_prev':
        //                 setGlobal('test_date.year', getGlobal('test_date.year') - 1);
        //                 break;
        //             case 'year_next':
        //                 setGlobal('test_date.year', getGlobal('test_date.year') + 1);
        //                 break;
        //             case 'month_prev':
        //                 setGlobal('test_date.month', getGlobal('test_date.month') - 1);
        //                 break;
        //             case 'month_next':
        //                 setGlobal('test_date.month', getGlobal('test_date.month') + 1);
        //                 break;
        //         }
        //         const calendar_data = getGlobal('test_date');
                
        //         const date = new Date(calendar_data.year, calendar_data.month, calendar_data.day, calendar_data.hour, calendar_data.minute, calendar_data.second);
        //         date.setDate(1);
        //         date.setDate(1 - edit[date.getDay()]);
                
        //         let $list = jQuery();
        //         for(i = 0; i < 42; i++) {
        //             const $option = jQuery('<span>'+ date.getDate() +'</span>');
        //             if(calendar_data.month == date.getMonth()) {
        //                 if(calendar_data.day == date.getDate()) {
        //                     $option.addData('status', 'active');
        //                 }
        //             }
        //             else {
        //                 $option.addData('status', 'other_month');
        //             }
        //             $list = $list.add($option);
                    
        //             date.setDate(date.getDate() + 1);
        //         }
                
        //         jQuery('#generated_list').empty().append($list);
        //     });
        // });

    // Компонента 'number'
    jQuery(document)
        // Изменение input[type="number"] кастомными кнопками
        .on('click', 'label[data-label="number"] .number_chevron_up, label[data-label="number"] .number_chevron_down', changeInputNumber)
        // Изменение input[type="number"] напрямую руками
        .on('focusin', 'label[data-label="number"] input[type="number"]', function(eventObject) {
            const $this_input = jQuery(eventObject.currentTarget);
            
            setGlobal('input_values.' + $this_input.attr('name'), Number($this_input.val()));
        })
        .on('change', 'label[data-label="number"] input[type="number"]', onChangeInputNumber);
});

function primeValidation(eventObject) {
    eventObject.preventDefault();
    
    const $this_submit      = jQuery(eventObject.currentTarget);
    const $this_form        = $this_submit
        .closest('form');
    let form_data           = new FormData();

    let $elements = $this_form
        .find('input:not([type="submit"]), textarea, select');
    // if(this_form.attr('id') !== undefined && this_form.attr('id') !== '') {
    //     elements = elements.add(jQuery('[form="'+ this_form.attr('id') +'"]'));
    // }

    $elements.each(function(iteration, current_form_element) {
        const $form_element     = jQuery(current_form_element);
        const form_element_name = $form_element.attr('name');
        
        if(form_element_name !== undefined) {
            const form_element_cut_name = form_element_name.endsWith('[]') ? form_element_name.slice(0, -2) : form_element_name;

            if(form_element_name !== undefined) {
                const type = $form_element.closest('[data-label]').attr('data-label');

                // Не адекватно добавляется инфа в массивах, нужно предусмотреть этот момент
                switch(type) {
                    case 'ckeditor':
                        if($form_element.prop('required') && getGlobal('editors.'+ form_element_name).getData().trim() === '') {
                            form_data.set('__form_errors['+ form_element_cut_name +']', 'required_field');
                        }

                        const editor_value = getGlobal('editors.'+ form_element_name).getData();

                        if(form_element_name.endsWith('[]')) {
                            form_data.append(form_element_name, editor_value);
                        }
                        else {
                            form_data.set(form_element_name, editor_value);
                        }
                        break;
                    case 'checkbox':
                    case 'togglebox':
                        if(!form_data.has(form_element_name) && !form_data.has(form_element_cut_name)) {
                            form_data.set(form_element_cut_name, 'false');

                            if($form_element.prop('required')) {
                                form_data.set('__form_errors['+ form_element_cut_name +']', 'required_checkbox');
                            }
                        }

                        if($form_element.prop('checked')) {
                            form_data.delete('__form_errors['+ form_element_cut_name +']');

                            const value = $form_element.attr('value') !== undefined ? $form_element.val() : 'true';
                            
                            if(form_data.get(form_element_cut_name) === 'false') {
                                form_data.delete(form_element_cut_name);
                                form_data.set(form_element_name, value);
                            }
                            else {
                                form_data.append(form_element_name, value);
                            }
                        }
                        break;
                    case 'text':
                    case 'password':
                    case 'hidden':
                    case 'number':
                    case 'textarea':
                    case 'select':
                    default:
                        if($form_element.prop('required') && $form_element.val().trim() === '') {
                            form_data.set('__form_errors['+ form_element_cut_name +']', 'required_field');
                        }

                        const value = $form_element.val();

                        if(form_element_name.endsWith('[]')) {
                            form_data.append(form_element_name, value);
                        }
                        else {
                            form_data.set(form_element_name, value);
                        }
                        break;
                }
            }
        }
    });

    form_data.set('__form_name', $this_form.attr('data-form'));
    if($this_submit.attr('name') !== undefined && $this_submit.attr('name') !== '') {
        form_data.set('__send_name', $this_submit.attr('name'));
    }
    
    jQuery.ajax({
        url         : $this_form.attr('action'),
        type        : $this_form.attr('method'),
        headers     : {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content'),
        },
        data        : form_data,
        processData : false,
        contentType : false,
        beforeSend  : function() {
            $this_submit.prop('disabled', true);

            const $all_labels = $elements.closest('label[data-label]');
            const $all_message = $all_labels.find('.label__message');

            $all_labels.eraseData('status', 'succces').eraseData('status', 'notice').eraseData('status', 'error');
            $all_message.text('');
        },
        success     : function(jquery_result) {
            console.log(jquery_result);
            
            // Установка новых значений элементам формы, если есть запрос
            if(Object.hasOwn(jquery_result['meta'], '__set_data')) {
                Object.keys(jquery_result['meta']['__set_data']).forEach(function(key) {
                    jQuery('[data-form="'+ jquery_result['meta']['__form_name'] +'"] [name="'+ key +'"]').val(jquery_result['meta']['__set_data'][key]);
                });
            }

            // Вывод ошибок в элементы формы
            if(Object.hasOwn(jquery_result['meta'], '__form_errors')) {
                const errors = jquery_result['meta']['__form_errors'];

                Object.keys(errors).forEach(function(error_key) {
                    const $current_label = $this_form.find('[name="'+ error_key +'"]').closest('label[data-label]');
                    let $current_message = $current_label.find('.label__message');

                    if(!$current_message.length) {
                        $current_message = jQuery('<span class="label__message"></span>').insertAfter($current_label.find('.label__input'));
                    }

                    $current_label.addData('status', 'error');
                    $current_message.text(errors[error_key]);
                });
            }

            // Вывод системных сообщений
            if(Object.hasOwn(jquery_result['meta'], '__system_messages')) {
                jQuery.each(jquery_result['meta']['__system_messages'], function(component, messages) {
                    jQuery.each(messages, function(message_name, message) {
                        openPopUp({data: {
                            component: 'system_messages.'+ String(component),
                            text: message,
                        }});
                    });
                });
            }

            // Выполнение перенаправления
            if(Object.hasOwn(jquery_result['meta'], '__redirect')) {
                let delay = 0;
                
                if(Object.hasOwn(jquery_result['meta'], '__redirect_delay')) {
                    delay = jquery_result['meta']['__redirect_delay'];
                }

                setTimeout(function() {
                    if(jquery_result['meta']['__redirect'] !== '') {
                        window.location.href = jquery_result['meta']['__redirect'];
                    }
                    else {
                        location.reload();
                    }
                }, delay);
            }
            else {
                $this_submit.prop('disabled', false);
            }
        },
        error       : function(report) {
            console.log(report.status, report.statusText);
            console.log(report.responseJSON);

            $this_submit.prop('disabled', false);
        }
    });
}

function toggleEye(eventObject) {
    eventObject.preventDefault();
        
    const $this_eye = jQuery(this);
    const $this_label = $this_eye.closest('label[data-label="password"]');
    const $this_input = $this_label.find('.label__input input');

    // Переключаем иконку и тип input
    const is_password = $this_input.attr('type') === 'password';

    $this_input.attr('type', is_password ? 'text' : 'password');
    $this_eye.attr('data-icon', is_password ? 'eye-off' : 'eye');
}

function toggleStatus(eventObject) {
    const $this_input   = jQuery(eventObject.currentTarget);
    const $this_label   = $this_input.closest('label[data-label]');
    const eventType     = eventObject.type;
    const data          = eventObject.data;
    
    switch(data.status) {
        case 'focused':
            if(eventType === 'focusin' || eventType === 'focus'){
                $this_label.addData('status', 'focused');
            }
            else if(eventType === 'focusout' || eventType === 'blur') {
                $this_label.eraseData('status', 'focused');
            }
            break;
        case 'not_empty':
            if($this_input.val().length !== 0) {
                $this_label.addData('status', 'not_empty');
            }
            else {
                $this_label.eraseData('status', 'not_empty');
            }
            break;
        case 'checked':
            if($this_input.prop('checked')) {
                $this_label.addData('status', 'checked');
            }
            else {
                $this_label.eraseData('status', 'checked');
            }
            break;
    }
}

function changeInputNumber(eventObject) {
    eventObject.preventDefault();

    const $change_button    = jQuery(eventObject.currentTarget);
    const $this_label       = $change_button.closest('label[data-label="number"]');
    const $this_input       = $this_label.find('input[type="number"]');

    const input_value       = $this_input.val() !== '' ? Number($this_input.val()) : null;
    const input_min         = $this_input.attr('min') !== undefined && $this_input.attr('min') !== '' ? Number($this_input.attr('min')) : null;
    const input_max         = $this_input.attr('max') !== undefined && $this_input.attr('max') !== '' ? Number($this_input.attr('max')) : null;
    const input_step        = $this_input.attr('step') !== undefined && $this_input.attr('step') !== '' ? Number($this_input.attr('step')) : 1;
    
    const direction   = $change_button.hasClass('number_chevron_up') ? 1 : -1;

    let new_value;

    if(input_value !== null) {
        new_value = input_value + input_step * direction;

        if(input_max !== null && new_value > input_max) {
            new_value = input_max;
        }

        if(input_min !== null && new_value < input_min) {
            new_value = input_min;
        }
    }
    else {
        new_value = input_min !== null ? input_min : 0;
    }

    if(new_value !== input_value) {
        $this_input.val(String(new_value)).trigger('change');
    }
}

function onChangeInputNumber(eventObject) {
    const $this_input = jQuery(eventObject.currentTarget);
    const input_value = $this_input.val();

    if(input_value === '') {
        return;
    }

    let value       = Number(input_value);
    const input_min = $this_input.attr('min') !== undefined && $this_input.attr('min') !== '' ? Number($this_input.attr('min')) : null;
    const input_max = $this_input.attr('max') !== undefined && $this_input.attr('max') !== '' ? Number($this_input.attr('max')) : null;

    const original_value = getGlobal('input_values.' + $this_input.attr('name'));

    if(input_max !== null && value > input_max) {
        value = input_max;
    }

    if(input_min !== null && value < input_min) {
        value = input_min;
    }

    $this_input.val(value);
    
    if(value !== original_value) {
        $this_input.trigger('changed');
    }
}

function syncInput(eventObject) {
    switch(eventObject.data.type) {
        case 'checkbox':
            const $this_checkbox = jQuery(eventObject.currentTarget);
            const $sync_checkboxes = jQuery('[type="checkbox"][data-sync="'+ $this_checkbox.attr('data-sync') +'"]').not($this_checkbox);

            $sync_checkboxes.each(function(i, checkbox) {
                jQuery(checkbox).prop('checked', $this_checkbox.prop('checked'));
                toggleStatus({
                    'currentTarget' : checkbox,
                    'type'          : 'change',
                    'data'          : {
                        'status'    : 'checked',
                    },
                });
            });
            break;
        case 'select':
            const $this_select = jQuery(eventObject.currentTarget);
            const $sync_selects = jQuery('select[data-sync="'+ $this_select.attr('data-sync') +'"]').not($this_select);

            $sync_selects.prop('selectedIndex', $this_select.prop('selectedIndex'));
            break;
        case 'number':
            const $this_number = jQuery(eventObject.currentTarget);
            const $sync_numbers = jQuery('[type="number"][data-sync="'+ $this_number.attr('data-sync') +'"]').not($this_number);
            const $sync_numbers_label = $sync_numbers.closest('[data-label]');
            const $sync_number_boxes = jQuery('[data-sync="'+ $this_number.attr('data-sync') +'"]:is(div, span, p)');

            $sync_numbers.val($this_number.val());
            $sync_number_boxes.text($this_number.val());
            if($this_number.val() !== '') {
                $sync_numbers_label.addData('status', 'not_empty');
            }
            else {
                $sync_numbers_label.eraseData('status', 'not_empty');
            }
            break;
        case 'text':
        default:
            const $this_text = jQuery(eventObject.currentTarget);
            const $sync_texts = jQuery('[type="text"][data-sync="'+ $this_text.attr('data-sync') +'"]').not($this_text);
            const $sync_texts_label = $sync_texts.closest('[data-label]');
            const $sync_text_boxes = jQuery('[data-sync="'+ $this_text.attr('data-sync') +'"]:is(div, span, p)');

            $sync_texts.val($this_text.val());
            $sync_text_boxes.text($this_text.val());
            if($this_text.val() !== '') {
                $sync_texts_label.addData('status', 'not_empty');
            }
            else {
                $sync_texts_label.eraseData('status', 'not_empty');
            }
            break;
    }    
}

function createSegmentState() {
    let counter = 0;

    return {
        increment: function(i = 1) {
            return counter += i;
        },
        get: function() {
            return counter;
        },
        reset: function(i = 0) {
            counter = i;
        }
    };
}

// ----------------------------------------------------------

// ----------------------------------------------------------