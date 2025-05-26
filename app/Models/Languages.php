<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'aid',
        'name',
        'native_name',
        'locale',
        'locale_code',
        'order',
        'enabled',
    ];
}