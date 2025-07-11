@props([
    'index' => 1,
    'form_data'  => []
])

<div class="item add-file-type" data-item="item_{{ $index }}" data-index="{{ $index }}">
    <x-form.text
        id="file_type_{{ $index }}"
        name="settings[file_types][setting_value][{{ $index }}][type]"
        value="{{ $form_data['file_type']['value'] ?? '' }}"
        disabled="{{ isset($form_data['file_type']['disabled']) ? true : false }}"
        title="{{ app('dictionary')->dictionary('form_labels')->key('file_type')->get() }}"
    />

    <x-form.text
        id="file_size_{{ $index }}"
        name="settings[file_types][setting_value][{{ $index }}][size]"
        value="{{ $form_data['file_size']['value'] ?? '' }}"
        disabled="{{ isset($form_data['file_size']['disabled']) ? true : false }}"
        title="{{ app('dictionary')->dictionary('form_labels')->key('file_size')->get() }}"
    />
    
    <div class="button__line error" data-action="delete" data-component="items.file-type">
        <span class="button__icon"><span data-icon="trash-2"></span></span>
    </div>
</div>