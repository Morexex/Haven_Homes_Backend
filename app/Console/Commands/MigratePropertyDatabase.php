<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigratePropertyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:property {property_name} {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for a specific property database';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $propertyName = $this->argument('property_name');
        $isFresh = $this->option('fresh');

        // Normalize the property name to match the database naming convention
        $normalizedPropertyName = strtolower(str_replace(' ', '_', $propertyName));

        // Configure the database connection for the property
        Config::set('database.connections.property', [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'database' => $normalizedPropertyName,
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);

        // Refresh the connection
        DB::purge('property');
        DB::reconnect('property');

        // Define the path to the property-specific migrations
        $path = 'database/migrations/property_specific';

        // Run the migrations
        $this->info("Running migrations for property database: {$normalizedPropertyName}");

        if ($isFresh) {
            // Run fresh migrations only for the property-specific migrations
            $this->call('migrate:fresh', [
                '--database' => 'property',
                '--path' => $path,
            ]);
        } else {
            // Run migrations only for the property-specific migrations
            $this->call('migrate', [
                '--database' => 'property',
                '--path' => $path,
            ]);
        }

        $this->info("Migrations completed for property database: {$normalizedPropertyName}");
    }
}
