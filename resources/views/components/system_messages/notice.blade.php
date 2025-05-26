@props([
    'text' => '',
])

<div class="message" data-status="notice">
    <div class="message__box">
        <div class="message__icon">
            <span data-icon="alert-triangle"></span>
        </div>
        <div class="message__body">
            <div class="message__text">Предупреждение</div>
            <div class="message__content">{{ $text }}</div>
        </div>
    </div>
    <div class="message__progress_bar"></div>
</div>