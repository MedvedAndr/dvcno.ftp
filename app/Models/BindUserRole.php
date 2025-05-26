<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BindUserRole extends Model
{
    use HasFactory;

    protected $table = 'bind_user_role';

    protected $fillable = [
        'aid',
        'user_id',
        'role_id',
    ];

    // Связи с другими таблицами

    // Связь с ролями
    public function get_role()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'aid');
    }

    // Связь с пользователями
    public function get_user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'aid');
    }
}
