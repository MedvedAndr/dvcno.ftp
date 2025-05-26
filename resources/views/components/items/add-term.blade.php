@props([
    'index' => 1,
    'locale',
    'form_data'  => []
])

<div class="item add-term" data-item="item_{{ $index }}" data-index="{{ $index }}">
    <x-form.text
        id="term_name_{{ $index }}_{{ $locale }}"
        name="terms[{{ $index }}][{{ $locale }}][name]"
        value="{{ $form_data['name']['value'] ?? '' }}"
        disabled="{{ isset($form_data['name']['disabled']) ? true : false }}"
        title="{{ app('dictionary')->dictionary('form_labels')->key('name')->get() }}"
    />

    <x-form.textarea
        id="term_description_{{ $index }}_{{ $locale }}"
        name="terms[{{ $index }}][{{ $locale }}][description]"
        value="{{ $form_data['description']['value'] ?? '' }}"
        disabled="{{ isset($form_data['description']['disabled']) ? true : false }}"
        title="{{ app('dictionary')->dictionary('form_labels')->key('description')->get() }}"
    />

    <x-form.text
        id="term_alias_{{ $index }}_{{ $locale }}"
        name="terms[{{ $index }}][{{ $locale }}][alias]"
        value="{{ $form_data['alias']['value'] ?? '' }}"
        disabled="{{ isset($form_data['alias']['disabled']) ? true : false }}"
        title="{{ app('dictionary')->dictionary('form_labels')->key('alias')->get() }}"
        :data="[
            'sync' => 'term_alias_'. $index
        ]"
    />
    
    <div class="flex__row_center">
        <div class="button__line error" data-item-del="add-term" data-item-once>
            <span class="button__title">{{ app('dictionary')->dictionary('buttons')->key('term_remove')->get() }}</span>
            <span class="button__icon"><span data-icon="trash-2"></span></span>
        </div>
    </div>
</div>