<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'aid',
        'name',
        'description',
    ];

    // Связи с другими таблицами

    // Связь с доступами
    public function get_bind_with_accesses()
    {
        return $this->hasMany(BindRoleAccess::class, 'role_id', 'aid');
    }

    // Связь с пользователями
    public function get_bind_with_users()
    {
        return $this->hasMany(BindUserRole::class, 'role_id', 'aid');
    }
}
