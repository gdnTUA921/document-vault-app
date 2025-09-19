<?php

namespace App\Providers;

use App\Models\File;
use App\Policies\FilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        File::class => FilePolicy::class,   // ✅ map File -> FilePolicy
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        // If you want to add Gates later, do it here.
    }
}
