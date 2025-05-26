<section id="sign_in" class="section">
    <h3 class="section__title sign_in__title">{{ app('dictionary')->dictionary('headers')->key('sign_in')->get() }}</h3>

    <form class="sign_in_form" action="{{ route('ajax.form.validation') }}" method="POST" data-form="login">
        <x-form.text
            id="login"
            name="login"
            placeholder="{{ app('dictionary')->dictionary('form_labels')->key('login_placeholder')->get() }}"
            required
            autofocus
            title="{{ app('dictionary')->dictionary('form_labels')->key('login')->get() }}"
            icon="user"
            icon_class="left"
        />
        <x-form.password
            id="password"
            name="password"
            placeholder="{{ app('dictionary')->dictionary('form_labels')->key('password_placeholder')->get() }}"
            required
            title="{{ app('dictionary')->dictionary('form_labels')->key('password')->get() }}"
            icon="lock"
            icon_class="left"
            eye
        />
        {{-- <x-form.togglebox
            id="remember_me"
            name="remember_me"
            title="{{ app('dictionary')->dictionary('form_labels')->key('remember_me')->get() }}"
        /> --}}
        <div class="form__panel">
            <x-form.submit
                id="submit"
                name="submit"
                title="{{ app('dictionary')->dictionary('buttons')->key('sign_in')->get() }}"
                icon="log-in"
            />
        </div>
        
        <x-form.select
            class="mini right"
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
    </form>
</section>