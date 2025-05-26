<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItems extends Model
{
    protected $table = 'menu_items';

    protected $fillable = [
        'aid',
        'language_id',
        'menu_id',
        'parent_id',
        'item_type',
        'item_id',
        'title',
        'url',
        'icon',
        'access_roles',
        'order',
        'enabled',
    ];

    public function menu() {
        // return $this->hasMany(MenuItems::class, 'menu_id', 'aid');
    }

    public function language() {
        return $this->belongsTo(Languages::class, 'language_id', 'aid');
    }
}
