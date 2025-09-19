<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'shared_with',
        'permission', // 'view'|'edit'|'download'
    ];

    // Permissions as constants (optional)
    public const PERMISSION_VIEW = 'view';
    public const PERMISSION_EDIT = 'edit';
    public const PERMISSION_DOWNLOAD = 'download';

    // Relationships
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function sharedWithUser()
    {
        return $this->belongsTo(User::class, 'shared_with');
    }
}
