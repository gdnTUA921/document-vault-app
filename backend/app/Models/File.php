<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'title',
        'original_name',
        'file_path',
        'mime_type',
        'size_bytes',
        'hash',
        'ocr_text',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    // Relationships
    public function user()                 // uploader
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function shares()
    {
        return $this->hasMany(FileShare::class);
    }

    public function keys()
    {
        return $this->hasMany(FileKey::class);
    }

    // Convenience: users the file is shared with (via FileShare)
    public function sharedWithUsers()
    {
        return $this->belongsToMany(User::class, FileShare::class, 'file_id', 'shared_with')
                    ->withPivot('permission')
                    ->withTimestamps();
    }
}
