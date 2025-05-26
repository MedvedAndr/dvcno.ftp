@props([
    'text' => '',
])

<div class="message">
    <div class="message__box">
        <div class="message__body">
            <div class="message__text">Сообщение</div>
            <div class="message__content">{{ $text }}</div>
        </div>
    </div>
    <div class="message__progress_bar"></div>
</div>