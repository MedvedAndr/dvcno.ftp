<div class="container">
    @if(isset($title) || isset($breadcrumbs))
    <div class="page__head">
        @isset($title)
        <h1>{{ $title }}</h1>
        @endisset
        
        @isset($breadcrumbs)
        <x-breadcrumbs :list="$breadcrumbs" />
        @endisset
    </div>
    @endif

    <a href="{{ route('admin.news.edit', ['aid' => '3bvdkc3ubhj']) }}">Редактировать новость</a>
</div>