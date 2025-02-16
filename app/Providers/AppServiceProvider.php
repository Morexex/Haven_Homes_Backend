<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Handle Password Reset URL
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Load Modular Routes
        $this->loadModuleRoutes();
    }

    /**
     * Load routes dynamically from modules.
     */
    protected function loadModuleRoutes()
    {
        $modulesPath = base_path('app/Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $moduleName = basename($module);
            
            $apiRoutes = "{$module}/Routes/api.php";
            $webRoutes = "{$module}/Routes/web.php";

            if (File::exists($apiRoutes)) {
                Route::prefix('api')
                    ->middleware('api')
                    ->group($apiRoutes);
            }

            if (File::exists($webRoutes)) {
                Route::middleware('web')
                    ->group($webRoutes);
            }
        }
    }
}
