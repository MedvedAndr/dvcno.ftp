<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BindRoleAccess extends Model
{
    use HasFactory;

    protected $table = 'bind_role_access';

    protected $fillable = [
        'aid',
        'role_id',
        'access_id',
        'enabled',
    ];

    // Связи с другими таблицами
    
    // Связь с доступами
    public function get_role()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'aid');
    }

    // Связь с доступами
    public function get_access()
    {
        return $this->belongsTo(Accesses::class, 'access_id', 'aid');
    }
}
