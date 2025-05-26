{{-- Эти блоки открываются в /admin/header.blade.php --}}
{{-- <html> --}}
    {{-- <body> --}}
        {{-- <div class="page__wrapper"> --}}
            {{-- <main> --}}

            </main>

            {{-- Подвал сайта --}}
            @if (isset($footer_template) && $footer_template)
            @include('admin.footers.'. $footer_template)
            @elseif (!isset($footer_template))
            @include('admin.footers.main')
            @endif
        </div>

        <div id="system_messages"></div>
        <div id="go_to_top"><span data-icon="arrow-up"></span></div>
        <x-assets-body />
    </body>
</html>