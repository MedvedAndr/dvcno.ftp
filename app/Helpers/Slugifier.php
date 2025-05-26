<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class Slugifier
{
    protected static array $translitMap = [
        'а' => 'a',  'б' => 'b',  'в' => 'v',
        'г' => 'g',  'д' => 'd',  'е' => 'e',
        'ё' => 'e',  'ж' => 'zh', 'з' => 'z',
        'и' => 'i',  'й' => 'y',  'к' => 'k',
        'л' => 'l',  'м' => 'm',  'н' => 'n',
        'о' => 'o',  'п' => 'p',  'р' => 'r',
        'с' => 's',  'т' => 't',  'у' => 'u',
        'ф' => 'f',  'х' => 'h',  'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '',   'ы' => 'y',  'ь' => '',
        'э' => 'e',  'ю' => 'yu', 'я' => 'ya'
    ];

    protected ?int $max_length = null;
    protected ?string $table = null;
    protected ?string $column = null;

    public function limit(int $length): self
    {
        $this->max_length = $length;
        return $this;
    }

    public function table(string $table_dot_column): self
    {
        [$this->table, $this->column] = explode('.', $table_dot_column);
        return $this;
    }

    public function slugify(string $string): string
    {
        $string = mb_strtolower($string);
        $transliterated = strtr($string, self::$translitMap);
        $sanitized = preg_replace('/[^a-z0-9]+/', '_', $transliterated);
        $base_slug = trim($sanitized, '_');
        $slug = $this->applyLimit($base_slug);

        if($this->table && $this->column) {
            $original = $slug;
            $counter = 1;

            while(DB::table($this->table)->where($this->column, '=', $slug)->exists()) {
                $suffix = '_' . $counter;
                $slug = $this->applyLimit($original, mb_strlen($suffix)) . $suffix;
                $counter++;
            }
        }
        
        return $slug;
    }

    protected function applyLimit(string $slug, int $reserved = 0): string
    {
        if ($this->max_length !== null) {
            return mb_substr($slug, 0, $this->max_length - $reserved);
        }
        return $slug;
    }
}