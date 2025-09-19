<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserKey extends Model
{
    use HasFactory;

    // Primary key is user_id (1:1 with users), not auto-increment
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $table = 'user_keys';

    protected $fillable = [
        'user_id',
        'public_key',
        'encrypted_private_key', // nullable
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
