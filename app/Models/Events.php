<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'aid',
        'language_id',
        'slug',
        'title',
        'description',
        'content',
        'thumbnail',
        'address',
        'link_to_map',
        'enabled',
        'date_event',
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
