<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id', 'actor_role', 'method', 'route_name', 'action',
        'description', 'status_code', 'ip_address', 'user_agent',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
