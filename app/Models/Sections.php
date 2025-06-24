<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sections extends Model
{
    protected $table = 'sections';

    protected $fillable = [
        'aid',
        'page_id',
        'language_id',
        'type',
        'content',
        'group',
        'order',
    ];

    // public function items() {
        // return $this->hasMany(MenuItems::class, 'menu_id', 'aid');
    // }

    public function language() {
        return $this->belongsTo(Languages::class, 'language_id', 'aid');
    }
}
