<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DictionaryItems extends Model
{
    protected $table = 'dictionary_items';

    protected $fillable = [
        'aid',
        'language_id',
        'dictionary_id',
        'item_key',
        'item_value',
        'description',
    ];

    public function dictionary()
    {
        return $this->belongsTo(Dictionaries::class, 'dictionary_id', 'aid');
    }
}
