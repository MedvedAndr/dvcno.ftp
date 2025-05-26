<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dictionaries extends Model
{
    protected $table = 'dictionaries';

    protected $fillable = [
        'aid',
        'language_id',
        'name',
        'description',
        'alias',
    ];

    public function items() {
        return $this->hasMany(DictionaryItems::class, 'dictionary_id', 'aid');
    }

    public function language() {
        return $this->belongsTo(Languages::class, 'language_id', 'aid');
    }
}
