<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes(); // Registers routes for issuing and managing OAuth tokens

        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

        Passport::tokensCan([
            'admin_user' => 'Admin User Access',
            'property_user' => 'Property User Access',
        ]);

        Passport::setDefaultScope([
            'admin-user',
            'property-user'
        ]);
    }
}
