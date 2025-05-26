<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Files extends Authenticatable {
    protected $table = 'files';

    protected $fillable = [
        'aid',
        'name',
        'path',
        'size',
        'extension',
        'mime_type',
    ];
}
