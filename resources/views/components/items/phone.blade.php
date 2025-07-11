@props([
    'index' => 1,
    'locale' => null,
    'form_data'  => []
])

<div class="item" data-item="item_{{ $index }}" data-index="{{ $index }}">
    <x-form.text
        id="department_{{ $index }}_{{ $locale }}"
        name="departments{{$locale ? '['.$locale.']' : ''}}[phones][{{ $index }}]"
        value="{{ $form_data['phone'] ?? '' }}"
    />

    <div class="button" data-action="delete" data-component="items.phone">
        <span class="button__icon"><span data-icon="trash-2"></span></span>
    </div>
</div>