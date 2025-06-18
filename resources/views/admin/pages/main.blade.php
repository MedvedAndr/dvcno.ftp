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
                    @if(!empty($pages_list))
                    <table>
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

                        </tbody>
                    </table>
                    @dump($pages_list)
                    @else
                    <div class="empty_content">Список страниц пуст.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>