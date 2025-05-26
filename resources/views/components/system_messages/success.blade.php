@props([
    'text' => '',
])

<div class="message" data-status="success">
    <div class="message__box">
        <div class="message__icon">
            <span data-icon="check-circle"></span>
        </div>
        <div class="message__body">
            <div class="message__text">Успешно</div>
            <div class="message__content">{{ $text }}</div>
        </div>
    </div>
    <div class="message__progress_bar"></div>
</div>