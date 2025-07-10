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
                <div class="group__head">Список мероприятий</div>
                <div class="group__body">
                    @if(!empty($events_list))
                    <table data-table="main">
                        <thead>
                            <tr>
                                <td>Изображение</td>
                                <td>Название</td>
                                <td>Описание</td>
                                <td>Псевдоним</td>
                                <td>Дата проведения</td>
                                <td>Статус</td>
                                <td>Языки</td>
                                <td>Управление</td>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($events_list as $event)
                            <tr>
                                <td>
                                    @if($event['thumbnail'])
                                    <div class="thumbnail"><img src="{{ $event['thumbnail']['path'] }}" /></div>
                                    @else
                                    
                                    @endif
                                </td>
                                <td>{{ $event['title'][app('locale')] ?? reset($event['title']) }}</td>
                                <td>{!! $event['description'][app('locale')] ?? reset($event['description']) !!}</td>
                                <td>{{ $event['slug'] }}</td>
                                <td class="center">{{ date('d.m.Y', strtotime($event['date_event'])) }}</td>
                                <td class="center">{{ app('dictionary')->dictionary('technical_terms')->key('enabled_'. $event['enabled'])->get() }}</td>
                                <td class="center">
                                    @foreach(app('languages') as $language)
                                    <span class="lang"
                                        @if(!isset($event['title'][$language->locale_code])) data-status="enabled" @endif
                                    >{{ $language->locale_code }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="flex">
                                        <a class="button" href="{{ route('admin.events.edit', ['aid' => $event['aid']]) }}" title="Редактировать"><span data-icon="edit"></span></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty_content">Список мероприятий пуст. <a href="{{ route('admin.events.create') }}">Создайте</a> первое мероприятие</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>