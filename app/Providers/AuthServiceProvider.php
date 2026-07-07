<?php

namespace App\Providers;

use App\Domains\Company\Policies\CompanyPolicy;
use App\Models\Company;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Company::class => CompanyPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
