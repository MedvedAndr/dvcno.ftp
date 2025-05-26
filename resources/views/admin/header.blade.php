<!DOCTYPE html>
<html lang="{{ app('locale') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- Токен --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title : 'Панель администратора' }}</title>

        {{-- Подключение стилей и скриптов --}}
        <x-assets-head />
    </head>

    <body>
        {{-- Панель навигации --}}
        @if (!isset($nav_panel_visibility) || $nav_panel_visibility)
        @include('admin.left_panel')
        @endif

        <div class="page__wrapper">
            {{-- Шапка сайта --}}
            @if (isset($header_template) && $header_template)
            @include('admin.headers.'. $header_template)
            @elseif (!isset($header_template))
            @include('admin.headers.main')
            @endif
            
            {{-- Основной контент --}}
            <main>

{{-- Эти блоки закрываются в /admin/footer.blade.php --}}
            {{-- </main> --}}
        {{-- </div> --}}
    {{-- </body> --}}
{{-- </html> --}}