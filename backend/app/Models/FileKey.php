<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'recipient_user_id',
        'encrypted_aes_key',
        'key_encryption_algo',
        'key_fingerprint',
    ];

    // Relationships
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
