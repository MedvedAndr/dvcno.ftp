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

    <div class="page__content">
        <div class="group__box">
            <div class="group__container container">
                <div class="group__head">Список новостей</div>
                <div class="group__body">
                    @if(!empty($news_list))
                    <table data-table="main">
                        <thead>
                            <tr>
                                <td>Изображение</td>
                                <td>Название</td>
                                <td>Описание</td>
                                <td>Псевдоним</td>
                                <td>Статус</td>
                                <td>Языки</td>
                                <td>Управление</td>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($news_list as $news)
                            <tr>
                                <td>
                                    @if($news['thumbnail'])
                                    <div class="thumbnail"><img src="{{ $news['thumbnail']['path'] }}" /></div>
                                    @else
                                    
                                    @endif
                                </td>
                                <td>{{ $news['title'][app('locale')] ?? reset($news['title']) }}</td>
                                <td>{!! $news['description'][app('locale')] ?? reset($news['description']) !!}</td>
                                <td>{{ $news['slug'] }}</td>
                                <td class="center">{{ app('dictionary')->dictionary('technical_terms')->key('enabled_'. $news['enabled'])->get() }}</td>
                                <td class="center">
                                    @foreach(app('languages') as $language)
                                    <span class="lang"
                                        @if(!isset($news['title'][$language->locale_code])) data-status="enabled" @endif
                                    >{{ $language->locale_code }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="flex">
                                        <a class="button" href="{{ route('admin.news.edit', ['aid' => $news['aid']]) }}" title="Редактировать"><span data-icon="edit"></span></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty_content">Список новостей пуст. <a href="{{ route('admin.news.create') }}">Создайте</a> первое мероприятие</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>