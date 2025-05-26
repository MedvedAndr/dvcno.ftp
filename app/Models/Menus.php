<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'aid',
        'language_id',
        'title',
        'description',
        'alias',
        'enabled',
    ];

    public function items() {
        // return $this->hasMany(MenuItems::class, 'menu_id', 'aid');
    }

    public function language() {
        return $this->belongsTo(Languages::class, 'language_id', 'aid');
    }
}
