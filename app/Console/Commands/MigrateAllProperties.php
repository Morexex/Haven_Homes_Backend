<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\DatabaseManager;

class MigrateAllProperties extends Command
{
    protected $signature = 'migrate:all-properties {--fresh}';
    protected $description = 'Run migrations for all property databases';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $isFresh = $this->option('fresh');
        $properties = Property::all();
        $path = 'database/migrations/property_specific';

        foreach ($properties as $property) {
            $normalizedPropertyName = strtolower(str_replace(' ', '_', $property->property_name));

            // Configure the database connection for the property
            Config::set('database.connections.property', [
                'driver' => 'mysql',
                'host' => env('DB_HOST','127.0.0.1'),
                'port' => env('DB_PORT','3306'),
                'database' => $normalizedPropertyName,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
            ]);

            try {
                DB::purge('property');
                DB::reconnect('property');
                $this->info("Running migrations for property database: {$normalizedPropertyName}");

                if ($isFresh) {
                    $this->call('migrate:fresh', [
                        '--database' => 'property',
                        '--path' => $path,
                    ]);
                } else {
                    $this->call('migrate', [
                        '--database' => 'property',
                        '--path' => $path,
                    ]);
                }

                $this->info("Migrations completed for property database: {$normalizedPropertyName}");
            } catch (\Exception $e) {
                $this->error("Error migrating property database: {$normalizedPropertyName}. Error: {$e->getMessage()}");
            }
        }

        $this->info('Migrations completed for all property databases.');
    }
}
