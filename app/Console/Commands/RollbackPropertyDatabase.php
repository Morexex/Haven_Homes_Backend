<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RollbackPropertyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:rollback-property {property_name} {--step=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback migrations for a specific property database';

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
        $steps = $this->option('step');

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

        $this->info("Rolling back migrations for property database: {$normalizedPropertyName}");

        // Run the rollback command
        $this->call('migrate:rollback', [
            '--database' => 'property',
            '--path' => $path,
            '--step' => $steps,
        ]);

        $this->info("Rollback completed for property database: {$normalizedPropertyName}");
    }
}
