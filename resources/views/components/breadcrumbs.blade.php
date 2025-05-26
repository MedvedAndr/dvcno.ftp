<div class="breadcrumbs">
    <a class="home" href="{{ route('admin.index') }}"><span data-icon="home"></span></a>
    @foreach($list as $breadcrumb)
    <span class="chevron" data-icon="chevron-right"></span>
    @if(isset($breadcrumb['href']))
    <a class="breadcrumb" href="{{ route($breadcrumb['href']) }}">{{ $breadcrumb['title'] }}</a>
    @else
    <span class="breadcrumb">{{ $breadcrumb['title'] }}</span>
    @endif
    @endforeach
</div>