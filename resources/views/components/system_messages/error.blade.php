@props([
    'text' => '',
])

<div class="message" data-status="error">
    <div class="message__box">
        <div class="message__icon">
            <span data-icon="x-circle"></span>
        </div>
        <div class="message__body">
            <div class="message__text">Ошибка!</div>
            <div class="message__content">{{ $text }}</div>
        </div>
    </div>
    <div class="message__progress_bar"></div>
</div>