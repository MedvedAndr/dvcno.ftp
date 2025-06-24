<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;

use App\Services\Dictionary;
// use App\Services\GenerateID;
use App\Helpers\AssetsManager;
// use App\Helpers\CaseBuilder;

use App\Models\Languages;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Список всех языков app('languages')
        $languages = Languages::where('enabled', '=', '1')->orderBy('order', 'asc')->get();
        app()->instance('languages', $languages);

        // Текущая локаль app('locale')
        try {
            $cookie = request()->cookie('locale');
    
            if($cookie) {
                $decrypted = Crypt::decryptString($cookie);
    
                // Проверяем, есть ли разделитель "|", иначе используем `env()`
                if(strpos($decrypted, '|') !== false) {
                    $locale = trim(explode('|', $decrypted, 2)[1]);
                }
                else {
                    $locale = trim(env('APP_LOCALE', 'ru'));
                }
            }
            else {
                $locale = trim(env('APP_LOCALE', 'ru'));
            }
        }
        catch (\Exception $e) {
            // Если кука повреждена или не может быть расшифрована, используем `env()`
            $locale = trim(env('APP_LOCALE', 'ru'));
        }
        app()->instance('locale', $locale);
        // app()->instance('locale', 'en');
        
        // Словари на текущем языке app('dictionary')
        $dictionary = (new Dictionary())->locale($locale);
        app()->instance('dictionary', $dictionary);
        
        // Задаём глобальные параметры для стилей и скриптов
        AssetsManager::setGlobalOptions([
            'version' => '0.4.6',
        ]);

        // Создаём наборы стилей и скриптов
        AssetsManager::setBundle('layout',
        [
            [
                'href'      => asset('/css/header.css'),
                'priority'  => 1000,
            ],
            [
                'href'      => asset('/css/footer.css'),
                'priority'  => 1000,
            ],
            [
                'href'      => asset('/css/main.css'),
                'priority'  => 10000,
            ],
            [
                'href'      => asset('/css/nav_panel.css'),
                'priority'  => 1000,
            ],
            [
                'href'      => asset('/css/models/form/select.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/breadcrumbs.css'),
                'priority'  => 600,
            ],
            [
                'href'      => asset('/css/models/icons.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/animations.css'),
                'priority'  => 500,
            ],
        ],
        [
            [
                'src'       => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'priority'  => 0,
                'version'   => '3.7.1',
            ],
            [
                'src'       => asset('/js/jQueryExtensions/DataAttributes.js'),
                'priority'  => 1,
                'version'   => '1.0.0',
            ],
            [
                'src'       => asset('/js/main.js'),
                'priority'  => 10000,
            ],
            [
                'src'       => asset('/js/models/nav_panel.js'),
                'priority'  => 500,
            ],
        ]);
        
        AssetsManager::setBundle('form',
        [
            [
                'href'      => asset('/css/models/form/core.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/label.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/button.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/datetime.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/file.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/number.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/password.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/select.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/text.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/textarea.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/form/togglebox.css'),
                'priority'  => 500,
            ],
            [
                'href'      => asset('/css/models/icons.css'),
                'priority'  => 500,
            ],
        ],
        [
            [
                'src'       => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'priority'  => 0,
                'version'   => '3.7.1',
            ],
            [
                'src'       => asset('/js/jQueryExtensions/DataAttributes.js'),
                'priority'  => 1,
                'version'   => '1.0.0',
            ],
            [
                'src'       => asset('/js/models/form.js'),
                'priority'  => 500,
            ],
        ]);

        AssetsManager::setBundle('ckeditor',
        [
            [
                'href'          => asset('/js/ckeditor5-43.2.0/ckeditor5/ckeditor5.css'),
                'priority'      => 500,
            ],
            [
                'href'          => asset('/css/models/form/ckeditor.css'),
                'priority'      => 500,
            ],
        ],
        [
            [
                'src'           => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'priority'      => 0,
                'version'       => '3.7.1',
            ],
            [
                'src'           => asset('/js/models/ckeditor_init.js'),
                'priority'      => 600,
                'version'       => '43.2.0',
                'attributes'    => [
                    'type' => 'module',
                ],
            ],
        ]);

        AssetsManager::setBundle('tabs',
        [
            [
                'href'      => asset('/css/models/tabs.css'),
                'priority'  => 500,
            ],
        ],
        [
            [
                'src'       => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'priority'  => 0,
                'version'   => '3.7.1',
            ],
            [
                'src'       => asset('/js/jQueryExtensions/DataAttributes.js'),
                'priority'  => 1,
                'version'   => '1.0.0',
            ],
            [
                'src'       => asset('/js/models/tabs.js'),
                'priority'  => 500,
            ],
        ]);

        AssetsManager::setBundle('accordions',
        [
            [
                'href'      => asset('/css/models/accordions.css'),
                'priority'  => 500,
            ],
        ],
        [
            [
                'src'       => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'priority'  => 0,
                'version'   => '3.7.1',
            ],
            [
                'src'       => asset('/js/jQueryExtensions/DataAttributes.js'),
                'priority'  => 1,
                'version'   => '1.0.0',
            ],
            [
                'src'       => asset('/js/models/accordions.js'),
                'priority'  => 500,
            ],
        ]);

        // Подключение глобальных стилей и скриптов
        AssetsManager::useBundle('layout');
    }
}
