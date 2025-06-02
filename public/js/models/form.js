jQuery(document).on('DOMContentLoaded', function() {
    // Очищием события фокуса (предотвращает дублирование события)
    jQuery(document)
        .off('focusin focusout')
        .on('click', function(eventObject) {
            const $this_target = jQuery(eventObject.target);
            
            // Закрытие календаря в datetime
            if(
                !$this_target.closest('.label__calendar').length &&
                !$this_target.closest('.label__calendar_icon').length
            ) {
                const $datetime_labels = jQuery('label[data-label="datetime"][data-status~="open"]');
                $datetime_labels.eraseData('status', 'open');
            }
        });


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
        .on('change', '[type="hidden"][data-sync]', {type: 'hidden'}, syncInput)
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
                !$this_target.is('.label__calendar_icon [data-icon]') &&
                !$this_target.closest('.label__calendar').length
            ) {
                $this_label.find('[data-segment]:first').focus();
            }
        })
        // Клик на иконку календаря
        .on('click', 'label[data-label="datetime"] .label__calendar_icon', {type: 'icon'}, calendarGenerator)
        .on('click', 'label[data-label="datetime"] .label__calendar .calendar__month_prev', {type: 'month_prev'}, calendarGenerator)
        .on('click', 'label[data-label="datetime"] .label__calendar .calendar__month_next', {type: 'month_next'}, calendarGenerator)
        .on('click', 'label[data-label="datetime"] .label__calendar .calendar__year_prev', {type: 'year_prev'}, calendarGenerator)
        .on('click', 'label[data-label="datetime"] .label__calendar .calendar__year_next', {type: 'year_next'}, calendarGenerator)
        .on('click', 'label[data-label="datetime"] .label__calendar .calendar__day', {type: 'day'}, calendarGenerator)
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
    
    jQuery(document)
        .on('click', 'label[data-label="edited_string"] .label__button', function(eventObject) {
            const $this_button = jQuery(eventObject.currentTarget);
            const $this_label = $this_button.closest('label[data-label]');
            const $this_input = $this_label.find('.label__input input[type="hidden"]');
            const $this_text = $this_label.find('.label__input .label__text');
            let $edit_input;
            
            switch($this_button.attr('data-button')) {
                case 'edit':
                    $this_label.addData('status', 'edited');
                    $this_text.empty().append('<input type="text" value="'+ $this_input.val() +'" />');
                    break;
                case 'save':
                    $this_label.eraseData('status', 'edited');
                    $edit_input = $this_label.find('.label__input .label__text input');
                    $this_text.empty().text($edit_input.val());
                    $this_input.val($edit_input.val()).trigger('change');
                    break;
                case 'cancel':
                    $this_label.eraseData('status', 'edited');
                    $this_text.empty().text($this_input.val());
                    break;
            }
        });
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
        case 'hidden':
            const $this_hidden = jQuery(eventObject.currentTarget);
            const $this_label = $this_hidden.closest('[data-label]');
            const $sync_hiddens = jQuery('[type="hidden"][data-sync="'+ $this_hidden.attr('data-sync') +'"]').not($this_hidden);
            const $sync_hiddens_label = $sync_hiddens.closest('[data-label]');
            const $sync_hiddens_text = $sync_hiddens_label.find('.label__input .label__text');
            
            $sync_hiddens_text.text($this_hidden.val());
            $sync_hiddens.val($this_hidden.val());
            $sync_hiddens_label.eraseData('status', 'hidden');
            $this_label.eraseData('status', 'hidden');
            break;
        case 'text':
        default:
            const $this_text = jQuery(eventObject.currentTarget);
            // const $this_label = $this_text.closest('[data-label]');
            const $sync_texts = jQuery('[type="text"][data-sync="'+ $this_text.attr('data-sync') +'"]').not($this_text);
            const $sync_texts_label = $sync_texts.closest('[data-label]');
            const $sync_text_boxes = jQuery('[data-sync="'+ $this_text.attr('data-sync') +'"]:is(div, span, p)');

            $sync_texts.val($this_text.val());
            $sync_text_boxes.text($this_text.val());
            // $this_label.eraseData('status', 'hidden');
            // $sync_texts_label.eraseData('status', 'hidden');
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

function compareDates(d1, d2) {
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

// ----------------------------------------------------------
function calendarGenerator(eventObject) {
    eventObject.stopPropagation();
    
    const event_data        = eventObject.data;
    
    const $this_oblect      = jQuery(eventObject.currentTarget);
    const $this_label       = $this_oblect.closest('label[data-label="datetime"]');
    const $this_input       = $this_label.find('input[type="hidden"]');
    const $this_calendar    = $this_label.find('.label__calendar');
    const $all_segments     = $this_label.find('[data-segment]');

    if(event_data.type === 'icon') {
        const input_date = $this_input.val() !== '' ? new Date($this_input.val()) : new Date();

        setGlobal('datetime', {
            'year'      : input_date.getFullYear(),
            'month'     : input_date.getMonth(),
            'day'       : input_date.getDate(),
            'hour'      : input_date.getHours(),
            'minute'    : input_date.getMinutes(),
            'second'    : input_date.getSeconds()
        });

        jQuery('label[data-label="datetime"][data-status~="open"]')
            .not($this_label)
            .eraseData('status', 'open');
        $this_label.addData('status', 'open');
    }

    const calendar_date = getGlobal('datetime');

    if(event_data.type !== 'day') {
        dateList($this_calendar, event_data.type);
    }
    else {
        const $selected_day = $this_oblect.siblings('[data-status~="selected"]');
        $selected_day.eraseData('status', 'selected');
        $this_oblect.addData('status', 'selected');
        calendar_date.day = Number($this_oblect.text());
        setGlobal('datetime.day', calendar_date.day);

        if($this_oblect.hasData('status', 'other_month')) {
            dateList($this_calendar, event_data.type, $this_oblect);
        }
    }

    if(event_data.type !== 'icon') {
        const data = {};
        let insert_to_input = true;
        $all_segments.each(function(i, segment) {
            const $segment = jQuery(segment);
            const segment_name = $segment.attr('data-segment');
            let value = calendar_date[segment_name];

            if(segment_name == 'month') {
                value += 1;
            }

            $segment.text(String(value).padStart(segment_name === 'year' ? 4 : 2, '0'));

            if(/^\d+$/.test($segment.text())) {
                data[segment_name] = $segment.text();
            }
            else {
                data[segment_name] = null;
                insert_to_input = false;
            }
        });
        
        if(insert_to_input) {
            $this_input.val(data['year'] +'-'+ data['month'] +'-'+ data['day'] +' '+ data['hour'] +':'+ data['minute'] +':'+ data['second']);
        }
        else {
            $this_input.val('');
        }
    }
}

function dateList($this_calendar, type, $this_oblect = null) {
    const $mounth_display = $this_calendar.find('.calendar__month_display').children();
    const $year_display = $this_calendar.find('.calendar__year_display');

    const current_date = new Date();
    const calendar_date = getGlobal('datetime');
    
    const edit = [6, 0, 1, 2, 3, 4, 5];

    switch(type) {
        case 'month_prev':
            if(calendar_date.month === 0) {
                calendar_date.month = 11;
                calendar_date.year = calendar_date.year - 1;
            }
            else {
                calendar_date.month = calendar_date.month - 1;
            }
            setGlobal('datetime.month', calendar_date.month);
            setGlobal('datetime.year', calendar_date.year);
            
            const prev_month_last_day = new Date(calendar_date.year, calendar_date.month + 1, 0).getDate();
            if(calendar_date.day > prev_month_last_day) {
                calendar_date.day = prev_month_last_day;
                setGlobal('datetime.day', calendar_date.day);
            }
            break;
        case 'month_next':
            if(calendar_date.month === 11) {
                calendar_date.month = 0;
                calendar_date.year = calendar_date.year + 1;
            }
            else {
                calendar_date.month = calendar_date.month + 1;
            }
            setGlobal('datetime.month', calendar_date.month);
            setGlobal('datetime.year', calendar_date.year);
            
            const next_month_last_day = new Date(calendar_date.year, calendar_date.month + 1, 0).getDate();
            if(calendar_date.day > next_month_last_day) {
                calendar_date.day = next_month_last_day;
                setGlobal('datetime.day', calendar_date.day);
            }
            break;
        case 'year_prev':
            if(calendar_date.year === 1000) {
                calendar_date.year = 9999;
            }
            else {
                calendar_date.year = calendar_date.year - 1;
            }
            setGlobal('datetime.year', calendar_date.year);
            break;
        case 'year_next':
            if(calendar_date.year === 9999) {
                calendar_date.year = 1000;
            }
            else {
                calendar_date.year = calendar_date.year + 1;
            }
            setGlobal('datetime.year', calendar_date.year);
            break;
        case 'day':
            if($this_oblect.hasData('status', 'prev_month')) {
                if(calendar_date.month === 0) {
                    calendar_date.month = 11;
                    calendar_date.year = calendar_date.year - 1;
                }
                else {
                    calendar_date.month = calendar_date.month - 1;
                }
            }
            else if ($this_oblect.hasData('status', 'next_month')) {
                if(calendar_date.month === 11) {
                    calendar_date.month = 0;
                    calendar_date.year = calendar_date.year + 1;
                }
                else {
                    calendar_date.month = calendar_date.month + 1;
                }
            }
            setGlobal('datetime.month', calendar_date.month);
            setGlobal('datetime.year', calendar_date.year);
            break;
    }

    const generate_date = new Date(
        calendar_date.year,
        calendar_date.month,
        calendar_date.day,
        calendar_date.hour,
        calendar_date.minute,
        calendar_date.second
    );
    generate_date.setDate(1);
    generate_date.setDate(1 - edit[generate_date.getDay()]);

    $mounth_display.eraseData('status', 'active');
    $mounth_display.eq(calendar_date.month).addData('status', 'active');
    $year_display.text(calendar_date.year);

    let $list = jQuery();
    for(i = 0; i < 42; i++) {
        const $option = jQuery('<span>'+ generate_date.getDate() +'</span>');
        $option.addClass('calendar__day');
        
        if(generate_date.getDay() === 0 || generate_date.getDay() === 6) {
            $option.addData('status', 'dayoff');
        }
        
        if(calendar_date.month == generate_date.getMonth()) {
            if(calendar_date.day == generate_date.getDate()) {
                $option.addData('status', 'selected');
            }
            
            if(compareDates(current_date, generate_date)) {
                $option.addData('status', 'current');
            }
        }
        else {
            $option.addData('status', 'other_month');
            // if(calendar_date.month < generate_date.getMonth()) {
            if(String(calendar_date.year).padStart(4, '0') + String(calendar_date.month).padStart(2, '0') < String(generate_date.getFullYear()).padStart(4, '0') + String(generate_date.getMonth()).padStart(2, '0')) {
                $option.addData('status', 'next_month');
            }
            else {
                $option.addData('status', 'prev_month');
            }
        }
        $list = $list.add($option);
        
        generate_date.setDate(generate_date.getDate() + 1);
    }

    const $days = $this_calendar.find('.calendar__days');
    $days.empty().append($list);
}
// ----------------------------------------------------------