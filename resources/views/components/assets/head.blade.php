@foreach ($assets['styles'] as $style)
<link rel="stylesheet" href="{{ $style['href'] }}"
    @foreach ($style['attributes'] as $key => $value)
    {{ $key }}="{{ $value }}"
    @endforeach
    data-priority="{{ $style['priority'] }}"
/>
@endforeach

@foreach ($assets['scripts']['head'] as $script)
<script src="{{ $script['src'] }}"
    @foreach ($script['attributes'] as $key => $value)
    {{ $key }}="{{ $value }}"
    @endforeach
    data-priority="{{ $script['priority'] }}"
></script>
@endforeach