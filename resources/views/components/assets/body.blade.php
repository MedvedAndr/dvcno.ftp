@foreach ($assets['scripts']['body'] as $script)
<script src="{{ $script['src'] }}"
    @foreach ($script['attributes'] as $key => $value)
    {{ $key }}="{{ $value }}"
    @endforeach
    data-priority="{{ $script['priority'] }}"
></script>
@endforeach