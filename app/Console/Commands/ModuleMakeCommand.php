<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleMakeCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Generate a new module structure';

    public function handle()
    {
        $name = $this->argument('name');
        $modulePath = base_path("app/Modules/{$name}");

        if (File::exists($modulePath)) {
            $this->error("Module {$name} already exists!");
            return;
        }

        File::makeDirectory($modulePath, 0755, true);
        File::makeDirectory("{$modulePath}/Controllers");
        File::makeDirectory("{$modulePath}/Models");
        File::makeDirectory("{$modulePath}/Routes");

        File::put("{$modulePath}/Routes/api.php", "<?php\n\nuse Illuminate\Support\Facades\Route;\n\nRoute::prefix('".strtolower($name)."')->group(function () {\n\n});");

        $this->info("Module {$name} created successfully!");
    }
}
