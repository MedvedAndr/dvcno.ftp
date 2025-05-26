<header>
    <div class="container">
        <div class="header__wrapper">
            <a href="{{ request()->fullUrlWithQuery(['auth' => 'sign_out']) }}">{{ app('dictionary')->dictionary('buttons')->key('sign_out')->get() }}</a>
            <x-form.select
                class="mini"
                :data="[
                    'cookie' => 'locale'
                ]"
                :options="collect(app('languages'))->map(function($language) {
                    return [
                        'value'    => $language->locale_code,
                        'title'    => $language->native_name,
                        'selected' => $language->locale_code == app('locale'),
                    ];
                })->toArray()"
            />
        </div>
    </div>
</header>