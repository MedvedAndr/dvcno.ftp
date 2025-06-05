<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'aid',
        'language_id',
        'slug',
        'title',
        'description',
        'content',
        'thumbnail',
        'enabled',
        'date_from',
        'date_to',
    ];

    // public function items() {
        // return $this->hasMany(MenuItems::class, 'menu_id', 'aid');
    // }

    public function language() {
        return $this->belongsTo(Languages::class, 'language_id', 'aid');
    }
}
