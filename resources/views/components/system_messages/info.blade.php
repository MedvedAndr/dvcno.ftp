@props([
    'text' => '',
])

<div class="message" data-status="info">
    <div class="message__box">
        <div class="message__icon">
            <span data-icon="alert-circle"></span>
        </div>
        <div class="message__body">
            <div class="message__text">Информация</div>
            <div class="message__content">{{ $text }}</div>
        </div>
    </div>
    <div class="message__progress_bar"></div>
</div>