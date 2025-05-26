<?php
// Класс для генерации 'id' таблицы
// Версия класса => 1.5.0
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateID {
    // Имя таблицы
    protected string $table_name;
    // Имя колонки, в которой хранятся 'id' (по умолчанию 'aid')
    protected string $column_name   = 'aid';
    // Длина генерируемого 'id' (по умолчанию 11)
    protected int $length           = 11;
    // Принудительный 'id'
    protected ?string $force_id;
    // Набор символов, из которых генерируется 'id'
    protected array $alphabet       = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
        'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
        'u', 'v', 'w', 'x', 'y', 'z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    ];
    // Максимальное количество попыток генерации
    protected int $max_generate     = 20;
    // Отключение кэша
    protected bool $use_cache       = true;

    // Кэширование сгенерированных 'id'
    private array $generatedIDs     = [];


    public function __construct() {
        
    }

    // Инициация имени таблицы
    public function table(string $table_name): self {
        $this->validateNotEmpty($table_name, 'Table name cannot be empty.');
        $this->table_name = $table_name;
        
        return $this;
    }

    // Инициация имени колонки
    public function column(string $column_name): self {
        $this->validateNotEmpty($column_name, 'Column name cannot be empty.');
        $this->column_name = $column_name;
        
        return $this;
    }

    // Инициация длины 'id'
    public function length(int $length): self {
        if($length <= 0) {
            throw new \InvalidArgumentException('The length of id cannot be less than 1.');
        }
        $this->length = $length;

        return $this;
    }

    // Инициация принудительного 'id'
    public function force(string $force_id): self {
        $this->validateNotEmpty($force_id, 'Forced ID cannot be empty.');
        $this->force_id = $force_id;
        
        return $this;
    }

    // Инициация набора символов для генерации
    public function alphabet(array $alphabet): self {
        if(empty($alphabet)) {
            throw new \InvalidArgumentException('Alphabet cannot be empty.');
        }
        
        $this->alphabet = $alphabet;

        return $this;
    }

    // Инициация максимального количества попыток генерации
    public function maxGenerate(int $attempts): self
    {
        if($attempts <= 0) {
            throw new \InvalidArgumentException('The length of attempts cannot be less than 1.');
        }
        
        $this->max_generate = $attempts;
        
        return $this;
    }

    public function noCache(bool $use_cache = false): self {
        $this->use_cache = $use_cache;

        return $this;
    }

    // Сгенерировать и получить 'id'
    public function get(): ?string {
        // Текущая попытка
        $attempts = 1;
        
        do{
            // Если есть принудительный 'id', берем его, если нет, генерируем новый
            $id = $this->force_id ?? $this->generateId();
            $this->force_id = null;

            // Попытка +1
            $attempts++;

            // Проверка на наличие сгенерированного id в кэше данного запроса
            if(isset($this->generatedIDs[$id])) {
                continue;
            }
            // Добавляем id в кэш текущего запроса
            $this->generatedIDs[$id] = true;

            if($this->use_cache) {
                // Формируем ключ кэша с учетом таблицы
                $cacheKey = 'aid_lock:'. $this->table_name .':'. $id;
    
                // Проверка на наличие сгенерированного id в кэше
                if(Cache::has($cacheKey)) {
                    continue;
                }
                // Добавляем id в кэш
                Cache::forever($cacheKey, true);
            }

            // Проверка на наличие сгенерированного id в БД
            $exists = DB::table($this->table_name)
                ->where($this->column_name, $id)
                ->exists();

            // Если id есть в БД, то чистим кэш и повторяем
            if($exists) {
                if($this->use_cache) {
                    Cache::forget($cacheKey);
                }
                continue;
            }

            return $id;
        }
        // Если id есть в БД и шаги не кончились делаем повторную генерацию.
        while($attempts <= $this->max_generate);
        
        Log::error('Error generating ID: failed after '. $this->max_generate .' attempts in table "'. $this->table_name .'", column "'. $this->column_name .'"');
        
        return null;
    }

    protected function generateId():string {
        return implode('', array_map(function() {
            return $this->alphabet[array_rand($this->alphabet)];
        }, range(1, $this->length)));
    }

    private function validateNotEmpty(string $value, string $message): void {
        if (trim($value) === '') {
            throw new \InvalidArgumentException($message);
        }
    }
}