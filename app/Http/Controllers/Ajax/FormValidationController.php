<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// use Illuminate\Support\Facades\DB;

use App\Services\GenerateID;
use App\Helpers\CaseBuilder;
use App\Helpers\Slugifier;

use App\Models\Files;
use App\Models\Menus;
use App\Models\MenuItems;
use App\Models\Events;
use App\Models\News;
use App\Models\Settings;
use App\Models\Dictionaries;
use App\Models\DictionaryItems;
use App\Models\Users;
use App\Models\BindUserRole;
//test 

class FormValidationController extends Controller
{
    public function uploadFiles(Request $request) {
        $file = $request->file('file');

        if (!$file->isValid()) {
            return [
                'status' => 'error',
                'error' => 'Ошибка загрузки файла',
            ];
        }

        $extension = $file->getClientOriginalExtension();
        $storage_path = 'storage/'. Str::after(
            Storage::disk('uploads')->path(''),
            storage_path() . DIRECTORY_SEPARATOR
        );
        $file_index_count = 4;

        $slugifier = new Slugifier();
        $slugifier->limit(255 - strlen($storage_path) - strlen('.'. $extension) - $file_index_count);
        $original_name = Str::beforeLast($file->getClientOriginalName(), '.');
        $name = $slugifier->slugify($original_name);

        $generated = $this->getAvailableFilename($name, $extension, $storage_path);

        $path = $file->storeAs('', $generated['filename'], ['disk' => 'uploads']);
        $url = parse_url(Storage::disk('uploads')->url($path), PHP_URL_PATH);

        $data = [
            'aid' => (new GenerateID)->table('files')->get(),
            'name' => mb_substr($original_name, 0, 255),
            'path' => $url,
            'size' => $file->getSize(),
            'extension' => $extension,
            'mime_type' => $file->getClientMimeType(),
        ];

        Files::create($data);

        return [
            'status' => 'success',
            'data' => $data,
        ];
    }

    protected function getAvailableFilename(string $base_name, string $extension, string $prefix): array {
        $disk = Storage::disk('uploads');
        $index = 0;

        do{
            $suffix = $index > 0 ? '_' . $index : '';
            $filename = $base_name . $suffix . '.' . $extension;
            $fullPath = $prefix . '/' . $filename;

            // Защита на случай, если base_name чуть длиннее, чем нужно
            if(strlen($fullPath) > 255) {
                $trimLength = 255 - strlen($prefix) - strlen($suffix . '.' . $extension) - 1;
                $base_name = substr($base_name, 0, $trimLength);
                $filename = $base_name . $suffix . '.' . $extension;
            }

            $index++;
        }
        while($disk->exists($filename));

        return [
            'name' => $base_name . ($index > 1 ? '_' . ($index - 1) : ''),
            'filename' => $filename
        ];
    }

    public function getSettings(Request $request) {
        $request_options = $request->only(
            'setting_keys'
        );

        $query_result = Settings::select('setting_value');

        if(isset($request_options['setting_keys'])) {
            $query_result = $query_result->whereIn('setting_key', $request_options['setting_keys']);
        }

        if($query_result->count() <= 1) {
            return $query_result->first()['setting_value'];
        }

        return array_map(function($item) {
            return json_decode($item['setting_value'], true);
        },$query_result->get()->toArray());
    }

    public function stringSlugify(Request $request) {
        $data = $request->only(
            'table',
            'value',
            'limit',
        );

        return [
            'data' => (new Slugifier())->table($data['table'])->limit($data['limit'])->slugify($data['value']),
        ];
    }

    public function formValidation(Request $request) {
        $return = [
            'status' => 'error',
            'error' => 'Not valid form',
            'meta' => $request->only([
                '__form_name',
                '__send_name',
                '__form_errors',
            ]),
        ];

        if(isset($return['meta']['__form_errors'])) {
            dump($return['meta']);
            $return['meta']['__form_errors'] = array_map(function($error_key) {
                return (app('dictionary'))->dictionary('system_messages')->key($error_key)->get();
            }, $return['meta']['__form_errors']);
        }

        // Получаем имя функции из __form_name
        $method_name = $return['meta']['__form_name'];

        // Вызываем метод обработки валидации, соответствующий названию формы
        if(method_exists($this, $method_name)) {
            return $this->$method_name($request, $return);
        }

        return $return;
    }

    private function file_edit(Request $request, array $return) {
        $data = $request->only([
            'name', 
            'alt',
            'file_aid'
        ]);
        
        if ($return['meta']['__send_name'] == 'save') {
            Files::where("aid", "=", $data['file_aid'])->update(['name' => $data['name'], 'alt' => $data['alt']]);
            
            $return['data'] = $data;
            $return['meta']['__system_messages']['success']['file_save_success'] = "Успешно";
        
        } else {
            Files::where("aid", "=", $data['file_aid'])->delete();
            
            $return['data'] = $data;
            $return['meta']['__system_messages']['success']['file_save_success'] = "Файл удален";
            $return['meta']['__redirect'] = '';
            $return['meta']['__redirect_delay'] = 4000;
        }

        return $return;
    }
    
    private function login(Request $request, array $return) {
        $data = $request->only([
            'login',
            'password',
        ]);

        if(!isset($return['meta']['__form_errors']['login']) && !isset($return['meta']['__form_errors']['password'])) {
            // Ищем пользователя с указанным логином и/или почтой
            $user = Users::whereAny([
                'login',
                'email'
            ], '=', $data['login'])
            ->first();
            
            //  Если пользователь есть в системе и набранный пароль соответствует его паролю в БД
            //      - то делаем проверку на права доступа
            //      - иначе формируем ошибку входа в систему
            if($user && Hash::check($data['password'], $user->password)) {
                // Проверяем у пользователя наличие права доступа в панель администратора
                $hasAccess = BindUserRole::from('bind_user_role as bur')
                    ->join('bind_role_access as bra', 'bur.role_id', '=', 'bra.role_id')
                    ->join('accesses as a', 'bra.access_id', '=', 'a.aid')
                    ->where('bur.user_id', '=', $user->aid)
                    ->where('a.name', '=', 'admin_panel')
                    ->where('bra.enabled', '=', 1)
                    ->exists();

                //  Если доступ есть
                //      - то формируем ответ на фронтэнд и вносим данные пользователя в сессию для авторизации
                //      - иначе формируем ошибку доступа
                if($hasAccess) {
                    $return['status']           = 'success';
                    $return['data']             = $data;
                    $return['data']['password'] = '********';
                    unset($return['error']);

                    $return['meta']['__system_messages']['success']['sign_in'] = (app('dictionary'))->dictionary('system_messages')->key('success_sign_in')->get();
                    $return['meta']['__redirect'] = '';
                    $return['meta']['__redirect_delay'] = 4000;

                    session()->put('user', [
                        'aid' => $user->aid,
                    ]);
                }
                else {
                    $return['error'] = '403 Access denied';
                    $return['meta']['__system_messages']['error']['access_denied'] = (app('dictionary'))->dictionary('system_messages')->key('admin_access_denied')->get();
                }
            }
            else {
                $return['error'] = 'Incorrect data';
                $return['meta']['__system_messages']['error']['sign_in'] = (app('dictionary'))->dictionary('system_messages')->key('invalid_sign_in')->get();
            }
        }
        else {
            $return['error'] = 'Incorrect data';
        }

        return $return;
    }

    private function create_menu(Request $request, array $return) {
        $data = $request->only([
            'menu',
            'points',
        ]);
        
        // Флаг для отслеживания заполненности языков
        $menus_empty_flag = false;

        // Переменные для парсинга
        $data_menus          = [];
        $data_menu_items      = [];

        // Генерируем 'aid' меню
        $menu_aid = (new GenerateID())->table('menus')->get();

        // Текущее дата/время
        $current_date = date('Y-m-d H:i:s');
        
        // Парсинг меню
        foreach($data['menu'] as $language_aid => $data_menu) {
            // Если меню обязательно для заполнения и поля не пустые начинаем парсинг
            if($data_menu['required'] === 'true' && ($data_menu['title'] || $data_menu['description'] || $data_menu['alias'])) {
                $menus_empty_flag = true;

                // Если поле 'name' пустое, то формируем ошибку
                if(!$data_menu['title']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['menu['. $language_aid .'][title]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'alias' пустое, то формируем ошибку
                if(!$data_menu['alias']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['menu['. $language_aid .'][alias]'] = 'Поле обязательно для заполнения';
                }

                // Проверка псевдонима на корректность
                if(preg_match('/^[a-zA-Z0-9_-]+$/', $data_menu['alias'])) {
                    // Проверяем наличие псевдонима в БД
                    if(Menus::where('alias', '=', $data_menu['alias'])->doesntExist()) {
                        $data_menus[] = [
                            'aid'           => $menu_aid,
                            'language_id'   => $language_aid,
                            'title'         => $data_menu['title'],
                            'description'   => $data_menu['description'] ?? '',
                            'alias'         => $data_menu['alias'],
                            'enabled'       => (int) filter_var($data_menu['enabled'], FILTER_VALIDATE_BOOLEAN),
                            'created_at'    => $current_date,
                            'updated_at'    => $current_date,
                        ];
                    }
                    else {
                        $return['error'] = 'Incorrect data';
                        $return['meta']['__form_errors']['menu['. $language_aid .'][alias]'] = 'Меню с таким псевдонимом уже есть в системе';
                    }
                }
                else {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['menu['. $language_aid .'][alias]'] = 'Не корректный формат записи';
                }
            }
            // Если меню обязательно для заполнения и все поля пустые делаем запрос на изменение
            elseif($data_menu['required'] === 'true' && (!$data_menu['title'] && !$data_menu['description'] && !$data_menu['alias'])) {
                $data['menu'][$language_aid]['required'] = 'false';
                $return['meta']['__set_data']['menu['. $language_aid .'][required]'] = 'false';
            }
        }
        
        // Парсинг пунктов меню
        if(isset($data['points'])) {
            // Генерируем 'aid' для пунктов меню
            $point_aids = array_map(function() {
                return (new GenerateID())->table('menu_items')->get();
            }, $data['points']);

            foreach($data['points'] as $menu_item_key => $menu_item) {
                // Генерируем 'aid' пункта меню
                // $point_aid = (new GenerateID())->table('menu_items')->get();

                foreach($menu_item as $language_aid => $data_item) {
                    if($data['menu'][$language_aid]['required'] === 'true') {
                        if($data_item['title'] && $data_item['url']) {
                            if($data_item['url'] !== '' && preg_match('/^(https?:\/\/((?!-)([a-zA-Z0-9-]+)(?<!-)\.)+(?!-)([a-zA-Z0-9-]+)(?<!-)(:\d+)?)?(\/([a-zA-Z0-9-_.]+(\/[a-zA-Z0-9-_.]+)*))?\/?(\?[a-zA-Z0-9-_.~]+=([a-zA-Z0-9-_.~:,+%]+)(&[a-zA-Z0-9-_.~]+=([a-zA-Z0-9-_.~:,+%]+))*)?(#[a-zA-Z0-9-_]+)?$/', $data_item['url'])) {
                                $data_menu_items[] = [
                                    'aid'           => $point_aids[$menu_item_key],
                                    'language_id'   => $language_aid,
                                    'menu_id'       => $menu_aid,
                                    'parent_id'     => $point_aids[$data_item['parent_id']] ?? null,
                                    'item_type'     => $data_item['item_type'],
                                    'item_id'       => $data_item['item_id'] ?? null,
                                    'title'         => $data_item['title'],
                                    'url'           => $data_item['url'],
                                    'icon'          => null,
                                    'access_roles'  => null,
                                    'order'         => (int) $data_item['order'],
                                    'enabled'       => (int) filter_var($data_item['enabled'], FILTER_VALIDATE_BOOLEAN),
                                    'created_at'    => $current_date,
                                    'updated_at'    => $current_date,
                                ];
                            }
                            else {
                                $return['error'] = 'Incorrect data';
                                $return['meta']['__form_errors']['points['. $menu_item_key .']['. $language_aid .'][url]'] = 'Не корректный формат записи';
                            }
                        }
                        else {
                            if(!$data_item['title']) {
                                $return['meta']['__form_errors']['points['. $menu_item_key .']['. $language_aid .'][title]'] = 'Поле обязательно для заполнения';
                            }
    
                            if(!$data_item['url']) {
                                $return['meta']['__form_errors']['points['. $menu_item_key .']['. $language_aid .'][url]'] = 'Поле обязательно для заполнения';
                            }
                        }
                    }
                }
            }
        }
        
        if($menus_empty_flag) {
            if(!isset($return['meta']['__form_errors'])) {
                $return['status']   = 'success';
                $return['data']     = $data;
                unset($return['error']);

                if(!empty($data_menus)) {
                    Menus::insert($data_menus);

                    if(!empty($data_menu_items)) {
                        MenuItems::insert($data_menu_items);
                    }
                }

                if(isset($return['meta']['__send_name'])) {
                    if($return['meta']['__send_name'] === 'save') {
                        $return['meta']['__redirect'] = route('admin.menus.edit', ['aid' => $menu_aid]);
                    }
                    else {
                        $return['meta']['__redirect'] = route('admin.menus');
                    }
                }
            }
        }
        else {
            $return['error'] = 'Incorrect data';
            $return['meta']['__system_messages']['error']['empty_data'] = 'Нужно заполнить хотя бы 1 язык.';
        }

        return $return;
    }

    private function create_event(Request $request, array $return) {
        // Получаем необходимые данные для обработки
        $data = $request->only([
            'event',
        ]);

        // Флаг для отслеживания заполненности языков
        $event_empty_flag = false;

        // Переменные для парсинга
        $data_events          = [];

        // Генерируем 'aid' мероприятия
        $event_aid = (new GenerateID())->table('events')->get();

        // Текущее дата/время
        $current_date = date('Y-m-d H:i:s');

        // Парсинг мераприятия
        foreach($data['event'] as $language_aid => $data_event) {
            // Если мероприятие обязательно для заполнения и поля не пустые начинаем парсинг
            if($data_event['required'] === 'true' && (
                $data_event['address'] || 
                $data_event['content'] || 
                $data_event['date_event'] || 
                $data_event['date_from'] || 
                $data_event['date_to'] || 
                $data_event['description'] || 
                $data_event['link_to_map'] || 
                $data_event['slug'] || 
                $data_event['title']
            )) {
                $event_empty_flag = true;

                // Если поле 'title' пустое, то формируем ошибку
                if(!$data_event['title']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['event['. $language_aid .'][title]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'address' пустое, то формируем ошибку
                if(!$data_event['address']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['event['. $language_aid .'][address]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'date_event' пустое, то формируем ошибку
                if(!$data_event['date_event']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['event['. $language_aid .'][date_event]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'slug' пустое, то формируем ошибку
                if(!$data_event['slug']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['event['. $language_aid .'][slug]'] = 'Поле обязательно для заполнения';
                }

                // Проверка ссылки на корректность
                if(preg_match('/^[a-zA-Z0-9_-]+$/', $data_event['slug'])) {
                    // Проверяем наличие ссылки в БД
                    if(Events::where('slug', '=', $data_event['slug'])->exists()) {
                        $return['error'] = 'Incorrect data';
                        $return['meta']['__form_errors']['event['. $language_aid .'][slug]'] = 'Мероприятие с такой ссылкой уже есть в системе';
                    }
                }
                else {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['event['. $language_aid .'][slug]'] = 'Не корректный формат записи';
                }

                $data_events[] = [
                    'aid'           => $event_aid,
                    'language_id'   => $language_aid,
                    'slug'          => $data_event['slug'],
                    'title'         => $data_event['title'],
                    'description'   => $data_event['description'] ?? '',
                    'content'       => $data_event['content'] ?? '',
                    'thumbnail'     => '',
                    'address'       => $data_event['address'],
                    'link_to_map'   => $data_event['link_to_map'],
                    'enabled'       => (int) filter_var($data_event['enabled'], FILTER_VALIDATE_BOOLEAN),
                    'date_event'    => $data_event['date_event'],
                    'date_from'     => $data_event['date_from'],
                    'date_to'       => $data_event['date_to'],
                    'created_at'    => $current_date,
                    'updated_at'    => $current_date,
                ];
            }
            // Если мероприятие обязательно для заполнения и все поля пустые делаем запрос на изменение
            elseif($data_event['required'] === 'true' && (
                !$data_event['title'] &&
                !$data_event['description'] &&
                !$data_event['slug']
            )) {
                $data['event'][$language_aid]['required'] = 'false';
                $return['meta']['__set_data']['event['. $language_aid .'][required]'] = 'false';
            }
        }

        if($event_empty_flag) {
            $return['status']   = 'success';
            $return['data']     = $data;
            // $return['meta']['debug']     = $data_events;
            unset($return['error']);

            if(!empty($data_events)) {
                Events::insert($data_events);
            }
        }
        else {
            $return['error'] = 'Incorrect data';
            $return['meta']['__system_messages']['error']['empty_data'] = 'Нужно заполнить хотя бы 1 язык.';
        }

        return $return;
    }

    private function create_news(Request $request, array $return) {
        // Получаем необходимые данные для обработки
        $data = $request->only([
            'news',
        ]);
        //dump($data);
        // Флаг для отслеживания заполненности языков
        $news_empty_flag = false;

        // Переменные для парсинга
        $data_news          = [];

        // Генерируем 'aid' мероприятия
        $news_aid = (new GenerateID())->table('news')->get();

        // Текущее дата/время
        $current_date = date('Y-m-d H:i:s');

        // Парсинг мераприятия
        foreach($data['news'] as $language_aid => $data_news_once) {
            // Если мероприятие обязательно для заполнения и поля не пустые начинаем парсинг
            if($data_news_once['required'] === 'true' && (
                $data_news_once['content'] || 
                $data_news_once['date_from'] || 
                $data_news_once['date_to'] || 
                $data_news_once['description'] || 
                $data_news_once['slug'] || 
                $data_news_once['subtitle'] || 
                $data_news_once['time_to_read'] || 
                $data_news_once['title']
            )) {
                $news_empty_flag = true;
                
                // Если поле 'title' пустое, то формируем ошибку
                if(!$data_news_once['title']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['news['. $language_aid .'][title]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'content' пустое, то формируем ошибку
                if(!$data_news_once['content']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['news['. $language_aid .'][content]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'slug' пустое, то формируем ошибку
                if(!$data_news_once['slug']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['news['. $language_aid .'][slug]'] = 'Поле обязательно для заполнения';
                }

                // Проверка ссылки на корректность
                if(preg_match('/^[a-zA-Z0-9_-]+$/', $data_news_once['slug'])) {
                    // Проверяем наличие ссылки в БД
                    if(Events::where('slug', '=', $data_news_once['slug'])->exists()) {
                        $return['error'] = 'Incorrect data';
                        $return['meta']['__form_errors']['news['. $language_aid .'][slug]'] = 'Новость с такой ссылкой уже есть в системе';
                    }
                }
                else {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['news['. $language_aid .'][slug]'] = 'Не корректный формат записи';
                }

                $data_events[] = [
                    'aid'           => $news_aid,
                    'language_id'   => $language_aid,
                    'slug'          => $data_news_once['slug'],
                    'title'         => $data_news_once['title'],
                    'description'   => $data_news_once['description'] ?? '',
                    'content'       => $data_news_once['content'] ?? '',
                    'subtitle'      => $data_news_once['subtitle'] ?? '',
                    'time_to_read'  => $data_news_once['time_to_read'] ?? '',
                    'thumbnail'     => null,
                    'enabled'       => (int) filter_var($data_news_once['enabled'], FILTER_VALIDATE_BOOLEAN),
                    'date_from'     => $data_news_once['date_from'],
                    'date_to'       => $data_news_once['date_to'],
                    'created_at'    => $current_date,
                    'updated_at'    => $current_date,
                ];
            }
            // Если мероприятие обязательно для заполнения и все поля пустые делаем запрос на изменение
            elseif($data_news_once['required'] === 'true' && (
                !$data_news_once['content'] &&
                !$data_news_once['date_from'] &&
                !$data_news_once['date_to'] &&
                !$data_news_once['description'] &&
                !$data_news_once['slug'] &&
                !$data_news_once['title'] &&
                !$data_news_once['subtitle'] &&
                !$data_news_once['time_to_read']
            )) {
                $data['news'][$language_aid]['required'] = 'false';
                $return['meta']['__set_data']['news['. $language_aid .'][required]'] = 'false';
            }
        }

        if($news_empty_flag) {
            $return['status']   = 'success';
            $return['data']     = $data;
            unset($return['error']);

            if (empty($return['meta']['__system_messages']['error']) && empty($return['meta']['__form_errors'])) {
                if(!empty($data_events)) {
                    News::insert($data_events);
                    if(isset($return['meta']['__send_name'])) {
                        if($return['meta']['__send_name'] === 'save') {
                            $return['meta']['__redirect'] = route('admin.news.edit', ['aid' => $news_aid]);
                        }
                        else {
                            $return['meta']['__redirect'] = route('admin.news');
                        }
                    }
                }
            }
        }
        else {
            $return['error'] = 'Incorrect data';
            $return['meta']['__system_messages']['error']['empty_data'] = 'Нужно заполнить хотя бы 1 язык.';
        }

        return $return;
    }

    private function create_dictionary(Request $request, array $return) {
        // Получаем необходимые данные для обработки
        $data = $request->only([
            'dictionary',
            'terms',
        ]);

        // Флаг для отслеживания заполненности языков
        $dictionaries_empty_flag = false;

        // Переменные для парсинга
        $data_dictionaries          = [];
        $data_dictionary_items      = [];
        $dictionary_item_aliases    = [];

        // Генерируем 'aid' словаря
        $dictionary_aid = (new GenerateID())->table('dictionaries')->get();

        // Парсинг словаря
        foreach($data['dictionary'] as $language_aid => $data_dictionary) {
            // Если словарь обязателен для заполнения и поля не пустые начинаем парсинг
            if($data_dictionary['required'] === 'true' && ($data_dictionary['name'] || $data_dictionary['description'] || $data_dictionary['alias'])) {
                $dictionaries_empty_flag = true;
                
                // Если поле 'name' пустое, то формируем ошибку
                if(!$data_dictionary['name']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['dictionary['. $language_aid .'][name]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'alias' пустое, то формируем ошибку
                if(!$data_dictionary['alias']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['dictionary['. $language_aid .'][alias]'] = 'Поле обязательно для заполнения';
                }

                // Проверка псевдонима на корректность
                if(preg_match('/^[a-zA-Z0-9_-]+$/', $data_dictionary['alias'])) {
                    // Проверяем наличие псевдонима в БД
                    if(Dictionaries::where('alias', '=', $data_dictionary['alias'])->doesntExist()) {
                        $data_dictionaries[] = [
                            'aid'           => $dictionary_aid,
                            'language_id'   => $language_aid,
                            'name'          => $data_dictionary['name'],
                            'description'   => $data_dictionary['description'] ?? '',
                            'alias'         => $data_dictionary['alias'],
                        ];
                    }
                    else {
                        $return['error'] = 'Incorrect data';
                        $return['meta']['__form_errors']['dictionary['. $language_aid .'][alias]'] = 'Словарь с таким псевдонимом уже есть в системе';
                    }
                }
                else {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['dictionary['. $language_aid .'][alias]'] = 'Не корректный формат записи';
                }
            }
            // Если словарь обязателен для заполнения и все поля пустые делаем запрос на изменение
            elseif($data_dictionary['required'] === 'true' && (!$data_dictionary['name'] && !$data_dictionary['description'] && !$data_dictionary['alias'])) {
                $return['meta']['__set_data']['dictionary['. $language_aid .'][required]'] = 'false';
            }
        }

        // Парсинг терминов
        foreach($data['terms'] as $dictionary_item_key => $dictionary_item) {
            // Генерируем 'aid' словаря
            $term_aid = (new GenerateID())->table('dictionary_items')->get();
            
            $term_alias_check_flag = true;

            foreach($dictionary_item as $language_aid => $data_item) {
                if($data_item['name'] && $data_item['alias']) {
                    if(preg_match('/^[a-zA-Z0-9_-]+$/', $data_item['alias'])) {
                        if($term_alias_check_flag) {
                            if(!in_array($data_item['alias'], $dictionary_item_aliases)) {
                                $dictionary_item_aliases[] = $data_item['alias'];
                                $term_alias_check_flag = false;
                            }
                            else {
                                $return['error'] = 'Incorrect data';
                                $return['meta']['__form_errors']['terms['. $dictionary_item_key .']['. $language_aid .'][alias]'] = 'В данном словаре уже существует термин с таким псевдонимом';
                            }
                        }

                        $data_dictionary_items[] = [
                            'aid'           => $term_aid,
                            'language_id'   => $language_aid,
                            'dictionary_id' => $dictionary_aid,
                            'item_key'      => $data_item['alias'],
                            'item_value'    => $data_item['name'],
                            'description'   => $data_item['description'] ?? '',
                        ];
                    }
                    else {
                        $return['error'] = 'Incorrect data';
                        $return['meta']['__form_errors']['terms['. $dictionary_item_key .']['. $language_aid .'][alias]'] = 'Не корректный формат записи';
                    }
                }
            }
        }

        if($dictionaries_empty_flag) {
            if(!isset($return['meta']['__form_errors'])) {
                $return['status']   = 'success';
                $return['data']     = $data;
                unset($return['error']);

                if(!empty($data_dictionaries)) {
                    Dictionaries::insert($data_dictionaries);

                    if(!empty($data_dictionary_items)) {
                        DictionaryItems::insert($data_dictionary_items);
                    }
                }

                if(isset($return['meta']['__send_name'])) {
                    if($return['meta']['__send_name'] === 'save') {
                        $return['meta']['__redirect'] = route('admin.dictionaries.edit', ['aid' => $dictionary_aid]);
                    }
                    else {
                        $return['meta']['__redirect'] = route('admin.dictionaries');
                    }
                }
            }
        }
        else {
            $return['error'] = 'Incorrect data';
            $return['meta']['__system_messages']['error']['empty_data'] = 'Нужно заполнить хотя бы 1 язык.';
        }

        return $return;
    }

    private function edit_dictionary(Request $request, array $return) {
        // Получаем необходимые данные для обработки
        $data = $request->only([
            'dictionary',
            'terms',
        ]);

        // Переменные для парсинга
        $data_dictionaries              = [];
        $data_dictionary_items_insert   = [];
        $data_dictionary_items_update   = [];
        $data_dictionary_items_delete   = [];
        $dictionary_item_aliases        = [];

        // Получаем 'aid' словаря
        $dictionary_aid = reset($data['dictionary'])['aid'];

        // Парсинг словаря
        foreach($data['dictionary'] as $language_aid => $data_dictionary) {
            // Если словарь обязателен для заполнения и поля не пустые начинаем парсинг
            if($data_dictionary['required'] === 'true' && ($data_dictionary['name'] || $data_dictionary['description'] || $data_dictionary['alias'])) {
                // Если поле 'name' пустое, то формируем ошибку
                if(!$data_dictionary['name']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['dictionary['. $language_aid .'][name]'] = 'Поле обязательно для заполнения';
                }

                // Если поле 'alias' пустое, то формируем ошибку
                if(!$data_dictionary['alias']) {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['dictionary['. $language_aid .'][alias]'] = 'Поле обязательно для заполнения';
                }

                // Проверяем наличие псевдонима в БД
                if(Dictionaries::where('alias', '=', $data_dictionary['alias'])->whereNot('aid', '=', $dictionary_aid)->doesntExist()) {
                    if(Dictionaries::where('aid', '=', $dictionary_aid)->where('language_id', '=', $language_aid)->exists()) {
                        $data_dictionaries['update'][] = [
                            'aid'           => $dictionary_aid,
                            'language_id'   => $language_aid,
                            'name'          => $data_dictionary['name'],
                            'description'   => $data_dictionary['description'] ?? '',
                            'alias'         => $data_dictionary['alias'],
                        ];
                    }
                    else {
                        $data_dictionaries['insert'][] = [
                            'aid'           => $dictionary_aid,
                            'language_id'   => $language_aid,
                            'name'          => $data_dictionary['name'],
                            'description'   => $data_dictionary['description'] ?? '',
                            'alias'         => $data_dictionary['alias'],
                        ];
                    }
                }
                else {
                    $return['error'] = 'Incorrect data';
                    $return['meta']['__form_errors']['dictionary['. $language_aid .'][alias]'] = 'Словарь с таким псевдонимом уже есть в системе';
                }
            }
            // Если словарь обязателен для заполнения и все поля пустые делаем запрос на изменение 
            elseif($data_dictionary['required'] === 'true' && (!$data_dictionary['name'] && !$data_dictionary['description'] && !$data_dictionary['alias'])) {
                $return['meta']['__set_data']['dictionary['. $language_aid .'][required]'] = 'false';
            }
        }

        // Парсинг терминов
        foreach($data['terms'] as $dictionary_item_key => $dictionary_item) {
            $term_aid = reset($dictionary_item)['aid'] ?? (new GenerateID())->table('dictionary_items')->get();

            $term_alias_check_flag = true;

            foreach($dictionary_item as $language_aid => $data_item) {
                if(!isset($data_item['aid'])) {
                    if($data_item['name'] && $data_item['alias']) {
                        if(preg_match('/^[a-zA-Z0-9_-]+$/', $data_item['alias'])) {
                            if($term_alias_check_flag) {
                                if(!in_array($data_item['alias'], $dictionary_item_aliases)) {
                                    $dictionary_item_aliases[] = $data_item['alias'];
                                    $term_alias_check_flag = false;
                                }
                                else {
                                    $return['error'] = 'Incorrect data';
                                    $return['meta']['__form_errors']['terms['. $dictionary_item_key .']['. $language_aid .'][alias]'] = 'В данном словаре уже существует термин с таким псевдонимом';
                                }
                            }

                            $data_dictionary_items_insert[] = [
                                'aid'           => $term_aid,
                                'language_id'   => $language_aid,
                                'dictionary_id' => $dictionary_aid,
                                'item_key'      => $data_item['alias'],
                                'item_value'    => $data_item['name'],
                                'description'   => $data_item['description'] ?? '',
                            ];
                        }
                    }
                }
                elseif(isset($data_item['aid']) && isset($data_item['alias'])) {
                    if($data_item['name'] && $data_item['alias']) {
                        if(DictionaryItems::where('aid', '=', $term_aid)->where('language_id', '=', $language_aid)->exists()) {
                            $data_dictionary_items_update['update'][] = [
                                'aid'           => $term_aid,
                                'language_id'   => $language_aid,
                                'dictionary_id' => $dictionary_aid,
                                'item_key'      => $data_item['alias'],
                                'item_value'    => $data_item['name'],
                                'description'   => $data_item['description'] ?? '',
                            ];
                        }
                        else {
                            $data_dictionary_items_update['insert'][] = [
                                'aid'           => $term_aid,
                                'language_id'   => $language_aid,
                                'dictionary_id' => $dictionary_aid,
                                'item_key'      => $data_item['alias'],
                                'item_value'    => $data_item['name'],
                                'description'   => $data_item['description'] ?? '',
                            ];
                        }
                    }
                }
                elseif(isset($data_item['aid']) && !isset($data_item['alias'])) {
                    if($term_alias_check_flag) {
                        $data_dictionary_items_delete[] = $term_aid;
                        $term_alias_check_flag = false;
                    }
                }
            }
        }

        if(!isset($return['meta']['__form_errors'])) {
            $return['status']           = 'success';
            $return['data']             = ['update' => $data];
            $return['debug']            = [
                'dictionary'    => $data_dictionaries,
                'terms'         => [
                    'insert'    => $data_dictionary_items_insert,
                    'update'    => $data_dictionary_items_update,
                    'delete'    => $data_dictionary_items_delete,
                ],
            ];
            unset($return['error']);


            if(!empty($data_dictionaries)) {
                if(isset($data_dictionaries['insert'])) {
                    Dictionaries::insert($data_dictionaries['insert']);
                }

                if(isset($data_dictionaries['update'])) {
                    $update_dictionaries = (new CaseBuilder())
                        ->setData($data_dictionaries['update'])
                        ->setFieldsToUpdate(['name', 'description'])
                        ->setWhenFields(['aid', 'language_id']);
                    
                    Dictionaries::whereIn('aid', $update_dictionaries->buildWhere())
                        ->update($update_dictionaries->buildCase());
                }

                if(!empty($data_dictionary_items_insert)) {
                    DictionaryItems::insert($data_dictionary_items_insert);
                }

                if(isset($data_dictionary_items_update['insert'])) {
                    DictionaryItems::insert($data_dictionary_items_update['insert']);
                }

                if(isset($data_dictionary_items_update['update'])) {
                    if(!empty($data_dictionary_items_update)) {
                        $update_items = (new CaseBuilder())
                            ->setData($data_dictionary_items_update['update'])
                            ->setFieldsToUpdate(['item_value', 'description'])
                            ->setWhenFields(['aid', 'language_id']);
                            
                        DictionaryItems::whereIn('aid', $update_items->buildWhere())
                            ->update($update_items->buildCase());
                    }
                }

                if(!empty($data_dictionary_items_delete)) {
                    DictionaryItems::whereIn('aid', $data_dictionary_items_delete)->delete();
                }
            }

            if(isset($return['meta']['__send_name'])) {
                if($return['meta']['__send_name'] === 'save') {
                    $return['meta']['__redirect'] = '';
                }
                else {
                    $return['meta']['__redirect'] = route('admin.dictionaries.view', ['aid' => $dictionary_aid]);
                }
            }
        }
        
        return $return;
    }

    private function edit_news(Request $request, array $return) {
        // Получаем необходимые данные для обработки
        $data = $request->only([
            'news'
        ]);
        // Переменные для парсинга
        $data_news              = [];

        $news_id = reset($data['news'])['aid'];

        // Парсинг
        foreach($data['news'] as $language_aid => $data_news) {

        }

        return $data;
    }

    private function edit_settings(Request $request, array $return) {
        $data = $request->only([
            'settings',
        ]);

        $data_settings = [];

        foreach($data['settings'] as $setting_key => $setting) {
            $pre_data = [
                'aid'           => $setting['aid'],
                'setting_key'   => $setting['setting_key'],
            ];

            if(in_array($setting_key, ['file_types'])) {
                foreach($setting['setting_value'] as $value) {
                    if($setting_key === 'file_types') {
                        if($value['type']) {
                            $pre_value          = [];
                            $pre_value['type']  = $value['type'];
                            $pre_value['size']  = $value['size'] ?? '';

                            $pre_data['setting_value'][] = $pre_value;
                        }
                    }
                    else {
                        $pre_value = [];

                        foreach($value as $key => $val) {
                            $pre_value[$key] = $val ?? '';
                        }

                        $pre_data['setting_value'][] = $pre_value;
                    }
                }

                $pre_data['setting_value'] = json_encode($pre_data['setting_value'], JSON_UNESCAPED_UNICODE);
            }
            elseif(in_array($setting_key, ['technical_works'])) {
                if($setting['setting_value'] === 'true') {
                    $pre_data['setting_value'] = '1';
                }
                elseif($setting['setting_value'] === 'false') {
                    $pre_data['setting_value'] = '0';
                }
                else {
                    $pre_data['setting_value'] = $setting['setting_value'] ?? '';
                }
            }
            else {
                $pre_data['setting_value'] = $setting['setting_value'] ?? '';
            }

            $data_settings[] = $pre_data;
        }

        // $data_file_types = [];

        // // Парсинг типов файлов
        // foreach($data['file_types']['setting_value'] as $type_item) {
        //     if($type_item['type']) {
        //         $pre_data = [
        //             'type' => $type_item['type'],
        //             'size' => $type_item['size'] ?? ''
        //         ];
        //         $data_file_types[] = $pre_data;
        //     }
        // }

        // $data_settings[] = [
        //     'aid' => $data['file_types']['aid'],
        //     'setting_key' => $data['file_types']['setting_key'],
        //     'setting_value' => json_encode($data_file_types, JSON_UNESCAPED_UNICODE)
        // ];

        if(!isset($return['meta']['__form_errors'])) {
            $return['status']           = 'success';
            $return['data']             = $data;
            unset($return['error']);

            $update_settings = (new CaseBuilder())
                ->setData($data_settings)
                ->setFieldsToUpdate(['setting_value'])
                ->setWhenFields(['aid', 'setting_key']);
            
            Settings::whereIn('aid', $update_settings->buildWhere())
                ->update($update_settings->buildCase());
            
            $return['meta']['__system_messages']['success']['settings_is_save'] = 'Настройки сохранены';
        }

        return $return;
    }

    private function edit_page(Request $request, array $return) {
        $data = $request->only([
            'sections',
        ]);

        $data_sections = [];

        foreach($data['sections'] as $language_aid => $data_section) {

        }

        $return['status'] = 'success';
        $return['data'] = $data;
        unset($return['error']);

        return $return;
    }

    public function getInfo(Request $request) {
        
        $request_options = $request->only(
            'aid'
        );
        $arr = Files::where('aid', '=', $request_options['aid'])->first();
        return $arr;
    }

    public function getFiles(Request $request) {
        $data = $request->only([
            'extensions',
            'type',
        ]);

        $query = Files::query()
            ->whereIn('extension', $data['extensions'] ?? [])
            ->orderBy('created_at', 'desc');

        $files = $query->get();

        $html    =  '<div class="files scroll">';
        $html   .=      '<input type="hidden" name="for" />';
        $html   .=      '<div class="files_list">';
        foreach($files as $file) {
            $html   .=      '<label class="file">';
            if($data['type'] === 'multiple') {
                $type = 'checkbox';
            }
            else {
                $type = 'radio';
            }

            if(explode('/', $file['mime_type'])[0] === 'image'){
                $thumb = ' data-image="'. $file['path'] .'"';
            }
            else {
                $thumb = '';
            }

            $html   .=          '<input type="'. $type .'" name="file" value="'. $file['aid'] .'" hidden'. $thumb .' data-name="'. $file['name'] .'.'. $file['extension'] .'" />';
            if(explode('/', $file['mime_type'])[0] === 'image') {
                $html   .=      '<div class="preview image">';
                $html   .=          '<img src="'. $file['path'] .'" />';
                $html   .=      '</div>';
            }
            else {
                $html   .=      '<div class="preview document">';
                $html   .=          '<div class="document__icon"><span data-icon="file"></span></div>';
                $html   .=          '<div class="document__title">'. $file['name'] .'.'. $file['extension'] .'</div>';
                $html   .=      '</div>';
            }
            $html   .=      '</label>';
        }
        $html   .=      '</div>';

        $html   .=      '<div class="files__panel">';
        if($data['type'] === 'multiple') {
            $html   .=      '<div class="files__count">Выбрано <span class="quantity">0</span> файлов</div>';
        }
        $html   .=          '<div class="button files__accept" data-status="disabled">Применить</div>';
        $html   .=          '<div class="button modal__close">Отмена</div>';
        $html   .=      '</div>';
        $html   .=  '</div>';

        $return['status'] = 'success';
        $return['data'] = $html;
        $return['debug'] = $files;
        unset($return['error']);

        return $return;
    }
}