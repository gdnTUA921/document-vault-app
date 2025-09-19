<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'department_id',
    ];

    protected $hidden = [
        'password',
    ];

    // 🔑 Required by JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // 🔗 Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
