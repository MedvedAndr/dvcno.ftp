import {
    ClassicEditor,
    Essentials,
    Heading,
    Paragraph,
    Bold,
    Italic,
    Font,
    Underline,
    Strikethrough,
    Superscript,
    Subscript,
    List,
    Alignment,
    Indent,
    Link,
    Table,
    FontSize,
    FontFamily,
    FontColor,
    FontBackgroundColor,
} from '/js/ckeditor5-43.2.0/ckeditor5/ckeditor5.js';

import ru from '/js/ckeditor5-43.2.0/ckeditor5/translations/ru.js';

jQuery(window).on('DOMContentLoaded', function() {
    document.querySelectorAll('[data-label="ckeditor"] textarea').forEach(function(item) {
        const $this_input = jQuery(item);
        const $this_label = $this_input.closest('[data-label]');
        
        ClassicEditor
            .create(item, {
                plugins: [
                    Essentials,
                    Heading,
                    Paragraph,
                    Bold,
                    Italic,
                    Font,
                    Underline,
                    Strikethrough,
                    Superscript,
                    Subscript,
                    List,
                    Alignment,
                    Indent,
                    Link,
                    Table,
                    FontSize,
                    FontFamily,
                    FontColor,
                    FontBackgroundColor,
                ],
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
                translations: ru,
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
});