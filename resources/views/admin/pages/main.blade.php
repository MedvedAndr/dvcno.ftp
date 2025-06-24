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
                <div class="group__head">Список страниц</div>
                <div class="group__body">
                    @dump($section_aid)
                    @if(!empty($pages_list))
                    <table data-table="main">
                        <thead>
                            <tr>
                                <td>Название</td>
                                <td>Описание</td>
                                <td>Псевдоним</td>
                                <td>Ссылка</td>
                                <td>Статус</td>
                                <td>Языки</td>
                                <td>Управление</td>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($pages_list as $page)
                            <tr>
                                <td>{{ $page['title'][app('locale')] ?? reset($page['title']) }}</td>
                                <td>{{ $page['description'][app('locale')] ?? reset($page['description']) }}</td>
                                <td>{{ $page['slug'] }}</td>
                                <td>{{ $page['front_url'] }}</td>
                                <td class="center">{{ app('dictionary')->dictionary('technical_terms')->key('enabled_'. $page['enabled'])->get() }}</td>
                                <td class="center">
                                    @foreach(app('languages') as $language)
                                    <span class="lang"
                                        @if(!isset($page['title'][$language->locale_code])) data-status="enabled" @endif
                                    >{{ $language->locale_code }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="flex">
                                        <a class="button" href="{{ route('admin.pages.edit', ['aid' => $page['aid']]) }}" title="Редактировать"><span data-icon="edit"></span></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="empty_content">Список страниц пуст.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
