<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesses extends Model
{
    use HasFactory;

    protected $table = 'accesses';

    protected $fillable = [
        'aid',
        'name',
        'description',
    ];

    // Связи с другими таблицами

    // Связь с ролями
    public function get_bind_with_roles() {
        return $this->hasMany(BindRoleAccess::class, 'access_id', 'aid');
    }
}
