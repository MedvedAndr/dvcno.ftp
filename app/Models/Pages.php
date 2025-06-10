<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'aid',
        'language_id',
        'slug',
        'title',
        'description',
        'enabled',
    ];

    // public function items() {
        // return $this->hasMany(MenuItems::class, 'menu_id', 'aid');
    // }

    public function language() {
        return $this->belongsTo(Languages::class, 'language_id', 'aid');
    }
}
