<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    // Admins can do everything
    public function before(User $user, $ability)
    {
        if ($user->role === 'admin') {
            return true;
        }
    }

    public function view(User $user, File $file): bool
    {
        if ($file->user_id === $user->id) return true;
        return $file->shares()->where('shared_with', $user->id)->exists();
    }

    public function download(User $user, File $file): bool
    {
        // ✅ Staff bypass only for downloads
        if ($user->role === 'staff') {
            return true;
        }

        if ($file->user_id === $user->id) return true;

        return $file->shares()
            ->where('shared_with', $user->id)
            ->exists();
    }

    public function update(User $user, File $file): bool
    {
        if ($file->user_id === $user->id) return true;
        return $file->shares()
            ->where('shared_with', $user->id)
            ->exists();
    }

    public function delete(User $user, File $file): bool
    {
        return $file->user_id === $user->id; // (admin allowed via before())
    }

    public function manageShare(User $user, File $file): bool
    {
        return $file->user_id === $user->id; // (admin allowed via before())
    }
}
