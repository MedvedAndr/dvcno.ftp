<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use \Carbon\Carbon;

use App\Models\Languages;
use App\Models\Dictionaries;

class Dictionary {
    protected ?string $locale                       = null;
    protected ?string $dictionary                   = null;
    protected ?string $key                          = null;
    protected bool $full                            = false;
    
    protected static ?array $allowed_locales        = null;
    protected static ?array $cached_dictionaries    = null;

    public function __construct() {
        
    }

    public function locale(?string $locale = null): self {
        if (self::$allowed_locales === null) {
            self::$allowed_locales = Languages::pluck('locale_code')->unique()->toArray();
        }
        
        // Ошибка, если передана пустая строка или только пробелы
        if ($locale === null || trim($locale) === '') {
            $locale = trim(env('APP_LOCALE', 'ru'));
        }
        
        // Проверка на существование локали в БД
        if(!in_array($locale, self::$allowed_locales, true)) {
            Log::error("Ошибка локали: {$locale} отсутствует в доступных локалях", [
                'allowed_locales' => self::$allowed_locales
            ]);

            throw new \InvalidArgumentException('Некорректная локаль: '. $locale);
        }

        $this->locale = $locale;
        return $this;
    }

    public function dictionary(int|string $dictionary): self {
        $dictionary = trim($dictionary);

        if($dictionary === '') {
            throw new \InvalidArgumentException("Псевдоним словаря должен быть непустой строкой.");
        }

        $this->dictionary = $dictionary;
        return $this;
    }

    public function key(string $key): self {
        $key = trim($key);

        if($key === '') {
            throw new \InvalidArgumentException("Ключ термина должен быть непустой строкой.");
        }

        $this->key = $key;
        return $this;
    }

    public function full(bool $full = true): self
    {
        $this->full = $full;
        return $this;
    }
    
    public function get() {
        return $this->filterData($this->getData());
    }

    protected function getData(): array {
        if(!self::$cached_dictionaries) {
            // Загружаем все словари и их термины со всеми локалями
            $raw_dictionaries = Dictionaries::from('dictionaries as dic')
                ->select(
                     'dic.id as dic_id',
                             'dic.aid as dic_aid',
                           'dic.alias as dic_alias',
                      'dic.created_at as dic_created_at',
                      'dic.updated_at as dic_updated_at',
                            'dic.name as dic_name',
                     'dic.description as dic_description',

                             'dici.id as dici_id',
                            'dici.aid as dici_aid',
                       'dici.item_key as dici_item_key',
                     'dici.item_value as dici_item_value',
                    'dici.description as dici_description',
                     'dici.created_at as dici_created_at',
                     'dici.updated_at as dici_updated_at',
                    
                       'l.locale_code as l_locale_code'
                )
                ->leftJoin('dictionary_items as dici', function($join) {
                    $join
                        ->on('dic.aid', '=', 'dici.dictionary_id')
                        ->on('dic.language_id', '=', 'dici.language_id');
                })
                ->join('languages as l', function($join) {
                    $join
                        ->on('dic.language_id', '=', 'l.aid');
                })
                ->orderBy('dici.item_key', 'asc')
                ->get();
            
            // Обрабатываем сырые данные в удобный формат
            self::$cached_dictionaries = $this->parseData($raw_dictionaries);
        }

        return self::$cached_dictionaries;
    }

    protected function parseData($raw_dictionaries): array {
        $parsed_data = [];

        foreach ($raw_dictionaries as $item) {
            $alias = $item->dic_alias;
            $locale = $item->l_locale_code;
            
            // Заполняем основную информацию о словаре (без ID и language_id)
            $parsed_data[$alias] ??= [
                'id'            => [],
                'aid'           => $item->dic_aid,
                'alias'         => $item->dic_alias,
                'name'          => [],
                'description'   => [],
                'created_at'    => $item->dic_created_at ? Carbon::parse($item->dic_created_at)->timestamp : null,
                'updated_at'    => $item->dic_updated_at ? Carbon::parse($item->dic_updated_at)->timestamp : null,
                'items'         => []
            ];

            // Группируем id
            if(!in_array($item->dic_id, $parsed_data[$alias]['id'])) {
                $parsed_data[$alias]['id'][] = $item->dic_id;
            }

            // Группируем заголовки и описания по языку
            $parsed_data[$alias]['name'][$locale] = $item->dic_name;
            $parsed_data[$alias]['description'][$locale] = $item->dic_description;

            if($item->dici_item_key) {
                // Добавляем элементы словаря (термины)
                $item_key = $item->dici_item_key;
    
                // Создаем структуру термина, если её еще нет
                $parsed_data[$alias]['items'][$item_key] ??= [
                    'id'            => [],
                    'aid'           => $item->dici_aid,
                    'item_key'      => $item_key,
                    'item_value'    => [],
                    'description'   => [],
                    'created_at'    => $item->dici_created_at ? Carbon::parse($item->dici_created_at)->timestamp : null,
                    'updated_at'    => $item->dici_updated_at ? Carbon::parse($item->dici_updated_at)->timestamp : null
                ];
    
                // Группируем id
                if(!in_array($item->dici_id, $parsed_data[$alias]['items'][$item_key]['id'])) {
                    $parsed_data[$alias]['items'][$item_key]['id'][] = $item->dici_id;
                }
    
                // Записываем значение термина и описание в нужную локаль
                $parsed_data[$alias]['items'][$item_key]['item_value'][$locale] = $item->dici_item_value;
                $parsed_data[$alias]['items'][$item_key]['description'][$locale] = $item->dici_description;
            }
        }

        return $parsed_data;
    }

    protected function filterData(array $parsed_data): array|string {
        // Фильтр по конкретному словарю
        if($this->dictionary) {
            $parsed_data = array_filter($parsed_data, function($value) {
                return $value['alias'] === $this->dictionary ||
                    $value['aid'] === $this->dictionary ||
                    in_array($this->dictionary, $value['id']);
            });
        }
        
        foreach($parsed_data as $dictionary_key => &$dictionary) {
            // Фильтр по локали
            if($this->locale) {
                // Оставляем только нужную локаль
                if(isset($dictionary['name'][$this->locale])){
                    $dictionary['name'] = $dictionary['name'][$this->locale];
                    $dictionary['description'] = $dictionary['description'][$this->locale];
                    
                    foreach($dictionary['items'] as $item_key => &$item) {
                        if(isset($item['item_value'][$this->locale])) {
                            $item['item_value'] = $item['item_value'][$this->locale];
                            $item['description'] = $item['description'][$this->locale];
                        }
                        else {
                            unset($parsed_data[$dictionary_key]['items'][$item_key]);
                            continue;
                        }
                    }
                }
                else {
                    unset($parsed_data[$dictionary_key]);
                    continue;
                }
            }

            // Фильтр по конкретному ключу термина
            if($this->key) {
                $dictionary['items'] = $dictionary['items'][$this->key] ?? [];
            }
            
            // Если "full" не указан, оставляем только items
            if(!$this->full) {
                if($this->key && !empty($dictionary['items'])) {
                    $dictionary = $dictionary['items']['item_value'];
                }
                else if($this->key && empty($dictionary['items'])) {
                    // $dictionary = [];
                    $dictionary = '';
                }
                else {
                    $dictionary = Arr::pluck(array_values($dictionary['items']), 'item_value', 'item_key');
                }
            }
        }

        return $this->dictionary ? reset($parsed_data) : $parsed_data;
    }
}